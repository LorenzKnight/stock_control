<?php

namespace App\AiSales;

class SalesMailboxProcessor
{
    public function __construct(
        private SalesMailboxClient $mailbox,
        private SalesMessageRepository $messages,
        private SalesConversationRepository $conversations,
        private SalesContactRepository $contacts,
        private SalesMessageService $service
    ) {}

    public function process(
        int $limit = 30,
        bool $dryRun = true
    ): array {
        global $sql;

        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException(
                "Limit must be between 1 and 100."
            );
        }

        $stats = [
            "scanned" => 0,
            "matched" => 0,
            "processed" => 0,
            "skipped" => 0,
            "errors" => []
        ];

        $client = $this->mailbox->connect();

        try {
            $folder = $client->getFolderByPath("INBOX");

            if ($folder === null) {
                throw new \RuntimeException(
                    "INBOX not found."
                );
            }

            $emails = $folder
                ->messages()
                ->all()
                ->leaveUnread()
                ->setFetchOrderDesc()
                ->limit($limit)
                ->get();

            foreach ($emails as $email) {
                $stats["scanned"]++;

                $headers = $email->getHeader();

                $messageId = $this->normalizeId(
                    (string)$headers->get("message_id")
                );

                $inReplyTo = $this->normalizeId(
                    (string)$headers->get("in_reply_to")
                );

                if (
                    $messageId === '' ||
                    $inReplyTo === ''
                ) {
                    $stats["skipped"]++;
                    continue;
                }

                try {
                    $original = $this->messages
                        ->findByProviderMessageId(
                            $inReplyTo
                        );

                    if (
                        $original === null ||
                        $original["direction"] !== "OUTBOUND" ||
                        $original["status"] !== "SENT"
                    ) {
                        $stats["skipped"]++;
                        continue;
                    }

                    $conversationId = (int)
                        $original["conversation_id"];

                    $conversation = $this->conversations
                        ->findById($conversationId);

                    if (
                        $conversation === null ||
                        strtoupper(
                            (string)$conversation["channel"]
                        ) !== "EMAIL"
                    ) {
                        $stats["skipped"]++;
                        continue;
                    }

                    $contactId = (int)(
                        $conversation["sales_contact_id"] ?? 0
                    );

                    $contact = $this->contacts
                        ->findById($contactId);

                    if ($contact === null) {
                        $stats["skipped"]++;
                        continue;
                    }

                    $from = $email->getFrom()->first();
                    $senderEmail = strtolower(
                        trim((string)($from->mail ?? ""))
                    );

                    $expectedEmail = strtolower(
                        trim((string)($contact["email"] ?? ""))
                    );

                    if (
                        $senderEmail === '' ||
                        $senderEmail !== $expectedEmail
                    ) {
                        $stats["skipped"]++;
                        continue;
                    }

                    $existing = $this->messages
                        ->findByProviderMessageId(
                            $messageId
                        );

                    if ($existing !== null) {
                        $stats["skipped"]++;
                        continue;
                    }

                    $stats["matched"]++;

                    if ($dryRun) {
                        echo "MATCHED: {$messageId}" . PHP_EOL;
                        echo "Conversation: {$conversationId}" .
                            PHP_EOL;
                        continue;
                    }

                    if (!$sql) {
                        throw new \RuntimeException(
                            "PostgreSQL connection unavailable."
                        );
                    }

                    if (!pg_query($sql, "BEGIN")) {
                        throw new \RuntimeException(
                            "Could not begin transaction."
                        );
                    }

                    try {
                        // Prevent concurrent workers from
                        // processing the same email.
                        $lock = pg_query_params(
                            $sql,
                            "SELECT pg_advisory_xact_lock(
                                hashtext($1)
                            )",
                            [$messageId]
                        );

                        if ($lock === false) {
                            throw new \RuntimeException(
                                "Could not acquire message lock."
                            );
                        }

                        $existing = $this->messages
                            ->findByProviderMessageId(
                                $messageId
                            );

                        if ($existing !== null) {
                            pg_query($sql, "ROLLBACK");
                            $stats["skipped"]++;
                            continue;
                        }

                        $body = trim(
                            (string)$email->getTextBody()
                        );

                        if ($body === '') {
                            $body = trim(
                                strip_tags(
                                    (string)$email->getHTMLBody()
                                )
                            );
                        }

						$body = $this->extractReplyText($body);

                        if ($body === '') {
                            throw new \RuntimeException(
                                "Email body is empty."
                            );
                        }

                        $subject = (string)
                            $email->get("subject");

                        $this->service->receiveMessage(
                            $conversationId,
                            $body,
                            $subject,
                            "<{$messageId}>"
                        );

                        if (!pg_query($sql, "COMMIT")) {
                            throw new \RuntimeException(
                                "Could not commit transaction."
                            );
                        }

                        $stats["processed"]++;

                    } catch (\Throwable $e) {
                        pg_query($sql, "ROLLBACK");
                        throw $e;
                    }

                } catch (\Throwable $e) {
                    $stats["errors"][] = [
                        "message_id" => $messageId,
                        "error" => $e->getMessage()
                    ];
                }
            }

        } finally {
            if ($client->isConnected()) {
                $client->disconnect();
            }
        }

        return $stats;
    }


	private function extractReplyText(string $body): string
	{
		$body = trim(
			str_replace(
				["\r\n", "\r"],
				"\n",
				$body
			)
		);

		if ($body === '') {
			return '';
		}

		// Common email reply separators.
		$patterns = [
			// Gmail in Spanish:
			// El dom, 1 nov 2026 ... escribió:
			'/^El[^\n]*(?:\n[^\n]*){0,2}\bescribi[oó]:[ \t]*$/miu',

			// Gmail in English:
			// On Sun, Nov 1, 2026 ... wrote:
			'/^On\s.+\bwrote:\s*$/mi',

			// Original message separator.
			'/^-{2,}\s*Original Message\s*-{2,}$/mi',

			// Quoted lines starting with >.
			'/^\s*>/m'
		];

		$cutPosition = strlen($body);

		foreach ($patterns as $pattern) {
			if (
				preg_match(
					$pattern,
					$body,
					$matches,
					PREG_OFFSET_CAPTURE
				) === 1
			) {
				$position = $matches[0][1];

				$cutPosition = min(
					$cutPosition,
					$position
				);
			}
		}

		$cleanBody = trim(
			substr(
				$body,
				0,
				$cutPosition
			)
		);

		// Preserve the original body if no
		// safe content was extracted.
		return $cleanBody !== ''
			? $cleanBody
			: $body;
	}


    private function normalizeId(string $id): string
    {
        return trim($id, " <> \t\n\r");
    }
}
