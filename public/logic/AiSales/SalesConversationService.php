<?php

namespace App\AiSales;

class SalesConversationService
{
	private SalesConversationRepository $repository;

	public function __construct(
		SalesConversationRepository $repository
	) {
		$this->repository = $repository;
	}

	public function getConversationsByLead(
		int $salesLeadId
	): array {
		if ($salesLeadId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales lead ID."
			);
		}

		$result = $this->repository->findByLeadId(
			$salesLeadId
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return [];
		}

		$conversations = array_values(
			$result["data"]
		);

		foreach ($conversations as &$conversation) {
			$conversation["conversation_id"] =
				(int)($conversation["conversation_id"] ?? 0);

			$conversation["sales_lead_id"] =
				(int)($conversation["sales_lead_id"] ?? 0);

			$salesContactId =
				$conversation["sales_contact_id"] ?? null;

			$conversation["sales_contact_id"] =
				$salesContactId !== null
					? (int)$salesContactId
					: null;

			$conversation["channel"] =
				(string)(
					$conversation["channel"] ?? "EMAIL"
				);

			$conversation["status"] =
				(string)(
					$conversation["status"] ?? "OPEN"
				);
		}

		unset($conversation);

		return $conversations;
	}
}