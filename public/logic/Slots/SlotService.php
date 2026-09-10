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
}