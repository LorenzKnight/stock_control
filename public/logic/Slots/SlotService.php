<?php

namespace App\Slots;

class SlotService
{
	private SlotRepository $repository;

	public function __construct(
		SlotRepository $repository
	) {
		$this->repository = $repository;
	}


	public function getSlots(
		int $companyId,
		string $search = '',
		int $selectSlot = 0
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access: company not found."
			);
		}

		$search = trim($search);

		$slotId =
			$selectSlot > 0
				? $selectSlot
				: null;

		return $this->repository
			->findSlots(
				$companyId,
				$search,
				$slotId
			);
	}

    public function createSlot(
		int $userId,
		int $companyId,
		array $data
	): int {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid company."
			);
		}

		$slotName = trim(
			(string)($data["slot_name"] ?? '')
		);

		if ($slotName === '') {
			throw new \InvalidArgumentException(
				"Slot Name is required."
			);
		}

		$existingSlotId =
			$this->repository->findIdByName(
				$companyId,
				$slotName
			);

		if ($existingSlotId !== null) {
			throw new \Exception(
				"A slot with this name already exists."
			);
		}

		$slotData = [
			"company_id" => $companyId,
			"slot_name" => $slotName,
			"current_capacity" =>
				(int)($data["current_capacity"] ?? 0),
			"max_capacity" =>
				(int)($data["max_capacity"] ?? 0),
			"slot_description" =>
				trim(
					(string)(
						$data["slot_description"] ?? ''
					)
				),
			"status" =>
				(int)($data["status"] ?? 0),
			"created_by" => $userId,
			"created_at" =>
				date("Y-m-d H:i:s")
		];

		return $this->repository
			->create($slotData);
	}

	public function updateSlot(
		int $companyId,
		int $slotId,
		array $data
	): void {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid company."
			);
		}

		if ($slotId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid slot ID."
			);
		}

		$slotName = trim(
			(string)($data["slot_name"] ?? '')
		);

		if ($slotName === '') {
			throw new \InvalidArgumentException(
				"Slot Name is required."
			);
		}

		$currentSlot =
			$this->repository->findSlots(
				$companyId,
				'',
				$slotId
			);

		if (empty($currentSlot)) {
			throw new \Exception(
				"Slot not found."
			);
		}

		$existingSlotId =
			$this->repository->findIdByName(
				$companyId,
				$slotName
			);

		if (
			$existingSlotId !== null &&
			$existingSlotId !== $slotId
		) {
			throw new \Exception(
				"A slot with this name already exists."
			);
		}

		$slotData = [
			"slot_name" => $slotName,
			"current_capacity" => (int)($data["current_capacity"] ?? 0),
			"max_capacity" => (int)($data["max_capacity"] ?? 0),
			"slot_description" => trim((string)($data["slot_description"] ?? '')),
			"status" => (int)($data["status"] ?? 0)
		];

		$this->repository->update(
			$companyId,
			$slotId,
			$slotData
		);
	}

	public function deleteSlot(
		int $companyId,
		int $slotId
	): void {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID not found for user."
			);
		}

		if ($slotId <= 0) {
			throw new \InvalidArgumentException(
				"Slot ID is required."
			);
		}

		$slot = $this->repository->findSlots(
			$companyId,
			'',
			$slotId
		);

		if (empty($slot)) {
			throw new \Exception(
				"Slot not found."
			);
		}

		$this->repository->delete(
			$companyId,
			$slotId
		);
	}
}