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


		public function getOrCreateInitialConversation(
		array $lead,
		array $primaryContact
	): array {
		$salesLeadId =
			(int)(
				$lead["sales_lead_id"]
					?? 0
			);


		if ($salesLeadId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales lead ID."
			);
		}


		$salesContactId =
			(int)(
				$primaryContact["sales_contact_id"]
					?? 0
			);


		if ($salesContactId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales contact ID."
			);
		}


		$leadPrimaryContactId =
			(int)(
				$lead["primary_contact_id"]
					?? 0
			);


		if (
			$leadPrimaryContactId > 0 &&
			$leadPrimaryContactId !==
				$salesContactId
		) {
			throw new \RuntimeException(
				"Sales contact does not match lead primary contact."
			);
		}


		$existingResult =
			$this->repository->findByLeadId(
				$salesLeadId
			);


		if (
			!empty($existingResult["success"]) &&
			!empty($existingResult["data"])
		) {
			foreach (
				$existingResult["data"]
				as $conversation
			) {
				if (
					(int)(
						$conversation["sales_contact_id"]
							?? 0
					)
					=== $salesContactId
				) {
					return [
						"conversation_id" =>
							(int)(
								$conversation["conversation_id"]
									?? 0
							),

						"channel" =>
							(string)(
								$conversation["channel"]
									?? "EMAIL"
							),

						"created" =>
							false
					];
				}
			}
		}


		$channel =
			$this->resolveInitialChannel(
				$primaryContact
			);


		$conversationId =
			$this->repository->create(
				[
					"sales_lead_id" =>
						$salesLeadId,

					"sales_contact_id" =>
						$salesContactId,

					"channel" =>
						$channel,

					"subject" =>
						null,

					"status" =>
						"OPEN"
				]
			);


		return [
			"conversation_id" =>
				$conversationId,

			"channel" =>
				$channel,

			"created" =>
				true
		];
	}


	private function resolveInitialChannel(
		array $contact
	): string {
		$email =
			trim(
				(string)(
					$contact["email"]
						?? ''
				)
			);


		if ($email !== '') {
			return "EMAIL";
		}


		$linkedinUrl =
			trim(
				(string)(
					$contact["linkedin_url"]
						?? ''
				)
			);


		if ($linkedinUrl !== '') {
			return "LINKEDIN";
		}


		throw new \RuntimeException(
			"Primary contact has no supported outreach channel."
		);
	}
}