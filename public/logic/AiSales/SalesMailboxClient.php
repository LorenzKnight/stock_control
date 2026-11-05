<?php

namespace App\AiSales;

use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;

class SalesMailboxClient
{
	public function connect(): Client
	{
		$requiredEnv = [
			"IMAP_HOST",
			"IMAP_PORT",
			"IMAP_ENCRYPTION",
			"IMAP_USER",
			"IMAP_PASS"
		];


		foreach ($requiredEnv as $key) {

			if (
				empty(
					$_ENV[$key]
				)
			) {
				throw new \RuntimeException(
					"Missing mailbox configuration: {$key}."
				);
			}
		}


		$validateCert =
			filter_var(
				$_ENV["IMAP_VALIDATE_CERT"] ?? true,
				FILTER_VALIDATE_BOOLEAN
			);


		try {

			$clientManager =
				new ClientManager(
					[]
				);


			$client =
				$clientManager->make([
					"host" =>
						trim(
							(string)$_ENV["IMAP_HOST"]
						),

					"port" =>
						(int)$_ENV["IMAP_PORT"],

					"encryption" =>
						trim(
							(string)$_ENV["IMAP_ENCRYPTION"]
						),

					"validate_cert" =>
						$validateCert,

					"username" =>
						trim(
							(string)$_ENV["IMAP_USER"]
						),

					"password" =>
						(string)$_ENV["IMAP_PASS"],

					"protocol" =>
						"imap"
				]);


			$client->connect();


			return $client;

		} catch (\Throwable $e) {

			throw new \RuntimeException(
				"Sales mailbox connection failed: " .
				$e->getMessage(),
				0,
				$e
			);
		}
	}


	public function checkInbox(): array
	{
		$client =
			$this->connect();


		try {

			$folder =
				$client->getFolderByPath(
					"INBOX"
				);


			if ($folder === null) {
				throw new \RuntimeException(
					"Sales mailbox INBOX could not be found."
				);
			}


			$info =
				$folder->examine();


			return [
				"connected" =>
					$client->isConnected(),

				"folder" =>
					"INBOX",

				"messages" =>
					(int)($info["exists"] ?? 0)
			];

		} catch (\Throwable $e) {

			throw new \RuntimeException(
				"Sales mailbox INBOX check failed: " .
				$e->getMessage(),
				0,
				$e
			);

		} finally {

			if ($client->isConnected()) {
				$client->disconnect();
			}
		}
	}
}