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


    private function resolveEndpointUrl(
        string $endpoint
    ): string {
        if ($this->url === '') {
            throw new \RuntimeException(
                "AI Sales Agent URL is not configured."
            );
        }

        $baseUrl =
            preg_replace(
                '#/generate-reply/?$#',
                '',
                $this->url
            );

        if (
            $baseUrl === null ||
            trim($baseUrl) === ''
        ) {
            throw new \RuntimeException(
                "Could not resolve AI Sales Agent URL."
            );
        }

        return
            rtrim(
                $baseUrl,
                '/'
            ) .
            '/' .
            ltrim(
                $endpoint,
                '/'
            );
    }


    private function postJson(
        string $url,
        array $payload,
        int $timeout = 30
    ): array {
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
                $url
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
                    $timeout
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


        return $data;
    }


	public function generateReply(
        array $context
    ): array {
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


        $data =
            $this->postJson(
                $this->resolveEndpointUrl(
                    "generate-reply"
                ),
                $payload
            );


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


    public function discoverCompanies(
        array $request
    ): array {
        $searches =
            $request["searches"] ?? null;

        if (
            !is_array($searches) ||
            empty($searches)
        ) {
            throw new \InvalidArgumentException(
                "At least one company search is required."
            );
        }


        $suggestions =
            $request["suggestions"] ?? [];

        if (!is_array($suggestions)) {
            throw new \InvalidArgumentException(
                "Company suggestions must be an array."
            );
        }


        $payload = [
            "searches" =>
                array_values(
                    $searches
                ),

            "suggestions" =>
                array_values(
                    $suggestions
                )
        ];


        $data =
            $this->postJson(
                $this->resolveEndpointUrl(
                    "discover-companies"
                ),
                $payload,
                120
            );


        $companies =
            $data["companies"] ?? null;

        if (!is_array($companies)) {
            throw new \RuntimeException(
                "AI Sales Agent returned invalid company discovery data."
            );
        }


        return $companies;
    }


    public function researchCompany(
        array $company
    ): array {
        $companyName =
            trim(
                (string)(
                    $company["company_name"] ?? ''
                )
            );

        if ($companyName === '') {
            throw new \InvalidArgumentException(
                "Company name is required for research."
            );
        }


        $payload = [
            "name" =>
                $companyName,

            "website" =>
                $company["website"] ?? null,

            "domain" =>
                $company["domain"] ?? null,

            "market" =>
                $company["market"] ?? null,

            "country" =>
                $company["country"] ?? null,

            "city" =>
                $company["city"] ?? null,

            "industry" =>
                $company["industry"] ?? null,

            "language" =>
                $company["language"] ?? null
        ];


        $data =
            $this->postJson(
                $this->resolveEndpointUrl(
                    "research-company"
                ),
                $payload,
                120
            );


        $summary =
            trim(
                (string)(
                    $data["summary"] ?? ''
                )
            );

        if ($summary === '') {
            throw new \RuntimeException(
                "AI Sales Agent returned an empty company research summary."
            );
        }


        $sources =
            $data["sources"] ?? [];

        if (!is_array($sources)) {
            throw new \RuntimeException(
                "AI Sales Agent returned invalid research sources."
            );
        }


        return [
            "summary" =>
                $summary,

            "inventory_context" =>
                $data["inventory_context"] ?? null,

            "city" =>
                $data["city"] ?? null,

            "industry" =>
                $data["industry"] ?? null,

            "language" =>
                $data["language"] ?? null,

            "sources" =>
                array_values(
                    $sources
                ),

            "model" =>
                $data["model"] ?? null
        ];
    }


    public function qualifyCompany(
        array $company
    ): array {
        $companyName =
            trim(
                (string)(
                    $company["company_name"] ?? ''
                )
            );

        if ($companyName === '') {
            throw new \InvalidArgumentException(
                "Company name is required for qualification."
            );
        }


        $researchSummary =
            trim(
                (string)(
                    $company["research_summary"] ?? ''
                )
            );

        if ($researchSummary === '') {
            throw new \InvalidArgumentException(
                "Company research is required before qualification."
            );
        }


        $payload = [
            "name" =>
                $companyName,

            "market" =>
                $company["market"] ?? null,

            "country" =>
                $company["country"] ?? null,

            "city" =>
                $company["city"] ?? null,

            "industry" =>
                $company["industry"] ?? null,

            "language" =>
                $company["language"] ?? null,

            "research_summary" =>
                $researchSummary,

            "inventory_context" =>
                $company[
                    "research_inventory_context"
                ] ?? null
        ];


        $data =
            $this->postJson(
                $this->resolveEndpointUrl(
                    "qualify-company"
                ),
                $payload,
                60
            );


        $score =
            (int)(
                $data["score"] ?? -1
            );

        $level =
            strtoupper(
                trim(
                    (string)(
                        $data["level"] ?? ''
                    )
                )
            );

        $reason =
            trim(
                (string)(
                    $data["reason"] ?? ''
                )
            );


        if (
            $score < 0 ||
            $score > 100
        ) {
            throw new \RuntimeException(
                "AI Sales Agent returned an invalid qualification score."
            );
        }

        if (
            !in_array(
                $level,
                [
                    "LOW",
                    "MEDIUM",
                    "HIGH"
                ],
                true
            )
        ) {
            throw new \RuntimeException(
                "AI Sales Agent returned an invalid qualification level."
            );
        }

        if ($reason === '') {
            throw new \RuntimeException(
                "AI Sales Agent returned an empty qualification reason."
            );
        }


        return [
            "score" =>
                $score,

            "level" =>
                $level,

            "reason" =>
                $reason,

            "model" =>
                $data["model"] ?? null
        ];
    }


    public function findCompanyContacts(
        array $company
    ): array {
        $companyName =
            trim(
                (string)(
                    $company["company_name"] ?? ''
                )
            );

        if ($companyName === '') {
            throw new \InvalidArgumentException(
                "Company name is required for contact search."
            );
        }


        $payload = [
            "name" =>
                $companyName,

            "website" =>
                $company["website"] ?? null,

            "domain" =>
                $company["domain"] ?? null,

            "market" =>
                $company["market"] ?? null,

            "country" =>
                $company["country"] ?? null,

            "city" =>
                $company["city"] ?? null,

            "industry" =>
                $company["industry"] ?? null,

            "language" =>
                $company["language"] ?? null,

            "research_summary" =>
                $company["research_summary"] ?? null
        ];


        $data =
            $this->postJson(
                $this->resolveEndpointUrl(
                    "find-company-contacts"
                ),
                $payload,
                120
            );


        $contacts =
            $data["contacts"] ?? null;

        if (!is_array($contacts)) {
            throw new \RuntimeException(
                "AI Sales Agent returned invalid company contacts."
            );
        }


        return [
            "contacts" =>
                array_values(
                    $contacts
                ),

            "model" =>
                $data["model"] ?? null
        ];
    }
}