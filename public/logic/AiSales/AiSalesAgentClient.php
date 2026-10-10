<?php

namespace App\AiSales;

class AiSalesAgentClient
{
	private string $url;

	private ?string $token;

	public function __construct(
		?string $url = null,
		?string $token = null
	) {
		$this->url =
			trim(
				$url ??
				(string)getenv(
					'AI_SALES_AGENT_URL'
				)
			);

		$resolvedToken =
			$token ??
			getenv(
				'AI_SALES_AGENT_TOKEN'
			);

		$this->token =
			$resolvedToken !== false &&
			trim((string)$resolvedToken) !== ''
				? trim((string)$resolvedToken)
				: null;
	}


	public function generateReply(
		array $context
	): array {
		if ($this->url === '') {
			throw new \RuntimeException(
				"AI Sales Agent URL is not configured."
			);
		}

		if (empty($context)) {
			throw new \InvalidArgumentException(
				"AI Sales context cannot be empty."
			);
		}


		$payload = [
			"task" =>
				"generate_sales_reply",

			"context" =>
				$context
		];


		$jsonPayload =
			json_encode(
				$payload,
				JSON_UNESCAPED_UNICODE |
				JSON_UNESCAPED_SLASHES
			);

		if ($jsonPayload === false) {
			throw new \RuntimeException(
				"Could not encode AI Sales request."
			);
		}


		$headers = [
			"Content-Type: application/json",
			"Accept: application/json"
		];

		if ($this->token !== null) {
			$headers[] =
				"Authorization: Bearer " .
				$this->token;
		}


		$curl =
			curl_init(
				$this->url
			);

		if ($curl === false) {
			throw new \RuntimeException(
				"Could not initialize AI Sales request."
			);
		}


		curl_setopt_array(
			$curl,
			[
				CURLOPT_POST =>
					true,

				CURLOPT_RETURNTRANSFER =>
					true,

				CURLOPT_HTTPHEADER =>
					$headers,

				CURLOPT_POSTFIELDS =>
					$jsonPayload,

				CURLOPT_CONNECTTIMEOUT =>
					5,

				CURLOPT_TIMEOUT =>
					30
			]
		);


		$response =
			curl_exec(
				$curl
			);

		$httpCode =
			(int)curl_getinfo(
				$curl,
				CURLINFO_HTTP_CODE
			);

		$curlError =
			curl_error(
				$curl
			);

		curl_close(
			$curl
		);


		if ($response === false) {
			throw new \RuntimeException(
				"AI Sales Agent request failed: " .
				$curlError
			);
		}


		if (
			$httpCode < 200 ||
			$httpCode >= 300
		) {
			throw new \RuntimeException(
				"AI Sales Agent returned HTTP {$httpCode}."
			);
		}


		$data =
			json_decode(
				$response,
				true
			);

		if (!is_array($data)) {
			throw new \RuntimeException(
				"AI Sales Agent returned invalid JSON."
			);
		}


		$subject =
			trim(
				(string)(
					$data["subject"] ?? ''
				)
			);

		$message =
			trim(
				(string)(
					$data["message"] ?? ''
				)
			);

		$model =
			trim(
				(string)(
					$data["model"] ?? ''
				)
			);


		if ($message === '') {
			throw new \RuntimeException(
				"AI Sales Agent returned an empty reply."
			);
		}


		return [
			"subject" =>
				$subject !== ''
					? $subject
					: null,

			"message" =>
				$message,

			"model" =>
				$model !== ''
					? $model
					: null
		];
	}
}