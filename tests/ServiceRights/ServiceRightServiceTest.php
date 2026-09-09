<?php

use App\ServiceRights\ServiceRightRepository;
use App\ServiceRights\ServiceRightService;
use PHPUnit\Framework\TestCase;

final class ServiceRightServiceTest extends TestCase
{
	public function testAllowsAccessWhenRightIsEnabled(): void
	{
		$repository = $this->createMock(
			ServiceRightRepository::class
		);

		$repository
			->method('findByUserAndService')
			->with(10, 'customers')
			->willReturn([
				"right_id" => 1,
				"can_access" => 1
			]);

		$service = new ServiceRightService($repository);

		$this->assertTrue(
			$service->canAccessService(
				10,
				'customers'
			)
		);
	}


	public function testDeniesAccessWhenRightDoesNotExist(): void
	{
		$repository = $this->createMock(
			ServiceRightRepository::class
		);

		$repository
			->method('findByUserAndService')
			->willReturn(null);

		$service = new ServiceRightService($repository);

		$this->assertFalse(
			$service->canAccessService(
				10,
				'customers'
			)
		);
	}


	public function testRejectsDuplicateRight(): void
	{
		$repository = $this->createMock(
			ServiceRightRepository::class
		);

		$repository
			->method('findByUserAndService')
			->with(10, 'customers')
			->willReturn([
				"right_id" => 5,
				"can_access" => 1
			]);

		$service = new ServiceRightService($repository);

		$this->expectException(Exception::class);

		$this->expectExceptionMessage(
			"This user already has a right with the same service name."
		);

		$service->createUserRight(
			10,
			1,
			'customers',
			1
		);
	}
}