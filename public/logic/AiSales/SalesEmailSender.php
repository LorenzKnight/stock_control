<?php

namespace App\AiSales;

use PHPMailer\PHPMailer\PHPMailer;

class SalesEmailSender
{
	public function send(
		string $to,
		string $subject,
		string $message
	): array {
		$to =
			trim(
				$to
			);

		$subject =
			trim(
				$subject
			);

		$message =
			trim(
				$message
			);


		if (
			$to === '' ||
			!filter_var(
				$to,
				FILTER_VALIDATE_EMAIL
			)
		) {
			throw new \InvalidArgumentException(
				"Invalid sales email recipient."
			);
		}


		if ($subject === '') {
			throw new \InvalidArgumentException(
				"Sales email subject is required."
			);
		}


		if ($message === '') {
			throw new \InvalidArgumentException(
				"Sales email message is required."
			);
		}


		$requiredEnv = [
			"SMTP_HOST",
			"SMTP_PORT",
			"SMTP_USER",
			"SMTP_PASS",
			"AI_SALES_FROM_EMAIL",
			"AI_SALES_FROM_NAME"
		];


		foreach ($requiredEnv as $key) {

			if (
				empty(
					$_ENV[$key]
				)
			) {
				throw new \RuntimeException(
					"Missing email configuration: {$key}."
				);
			}
		}


		$fromEmail =
			trim(
				(string)$_ENV["AI_SALES_FROM_EMAIL"]
			);

		$fromName =
			trim(
				(string)$_ENV["AI_SALES_FROM_NAME"]
			);


		if (
			!filter_var(
				$fromEmail,
				FILTER_VALIDATE_EMAIL
			)
		) {
			throw new \RuntimeException(
				"Invalid AI Sales sender email."
			);
		}


		$mail =
			new PHPMailer(
				true
			);


		try {

			$mail->isSMTP();

			$mail->Host =
				$_ENV["SMTP_HOST"];

			$mail->SMTPAuth =
				true;

			$mail->Username =
				$_ENV["SMTP_USER"];

			$mail->Password =
				$_ENV["SMTP_PASS"];

			$mail->Port =
				(int)$_ENV["SMTP_PORT"];


			$smtpSecure =
				strtolower(
					trim(
						(string)($_ENV["SMTP_SECURE"] ?? "tls")
					)
				);


			if ($smtpSecure === "ssl") {

				$mail->SMTPSecure =
					PHPMailer::ENCRYPTION_SMTPS;

			} else {

				$mail->SMTPSecure =
					PHPMailer::ENCRYPTION_STARTTLS;
			}


			$mail->CharSet =
				"UTF-8";


			$mail->setFrom(
				$fromEmail,
				$fromName
			);


			$mail->addAddress(
				$to
			);


			$mail->isHTML(
				false
			);


			$mail->Subject =
				$subject;

			$mail->Body =
				$message;

                
            $mail->addCustomHeader(
                "X-AllStockControl-Source",
                "ai-sales"
            );

			$mail->send();


			$providerMessageId =
				trim(
					(string)$mail->getLastMessageID()
				);


			return [
				"provider_message_id" =>
					$providerMessageId !== ''
						? $providerMessageId
						: null
			];

		} catch (\Throwable $e) {

			throw new \RuntimeException(
				"Sales email delivery failed: " .
				$e->getMessage(),
				0,
				$e
			);
		}
	}
}