<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Sale could not be created",
	"img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
	$authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;
	$companyId = $authUser["company_id"] ?? null;

	if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

	if (!check_user_permission($userId, 'sales_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$input = json_decode(file_get_contents('php://input'), true);
	if (!$input) throw new Exception("No data received.");

	if (empty($input["products"]) || !is_array($input["products"])) {
        throw new Exception("At least one product is required.");
    }

	$required = ["customer_id", "price_sum", "initial", "delivery_date", "remaining", "interest", "installments_month", "no_installments", "payment_date", "due"];
	foreach ($required as $field) {
		if (!isset($input[$field])) {
			throw new Exception("Missing required field: $field");
		}
	}

	$customerId = (int)($input["customer_id"] ?? 0);

	if ($customerId <= 0) {
		throw new Exception(
			"A customer is required to create a sale."
		);
	}

	$customerCheck = json_decode(select_from("customers",
		[
			"customer_id"
		],
		[
			"customer_id" => $customerId,
			"company_id" => $companyId
		],
		[
			"fetch_first" => true
		]
	), true);

	if (empty($customerCheck["success"]) || empty($customerCheck["data"])) {
		throw new Exception("The selected customer does not exist or does not belong to this company.");
	}

	$currency = $input["currency"] ?? "USD"; // Default to USD if not provided
	$priceSum = number_format((float)$input["price_sum"], 2, '.', '');
	$initial = number_format((float)$input["initial"], 2, '.', '');
	$remaining = number_format((float)$input["remaining"], 2, '.', '');
	$interest = (int)$input["interest"];
	$installmentsMonth = (int)$input["installments_month"];
	$noInstallments = (int)$input["no_installments"];
	$due = number_format((float)$input["due"], 2, '.', '');

	$delTS = strtotime($input["delivery_date"]);
    $payTS = strtotime($input["payment_date"]);
    if ($delTS === false) throw new Exception("Invalid delivery_date.");
    if ($payTS === false) throw new Exception("Invalid payment_date.");
    $deliveryDate = date('Y-m-d H:i:s', $delTS);
    $paymentDate  = date('Y-m-d H:i:s', $payTS);

	$newOrdNo = get_next_increment_value("sales", "ord_no", $companyId, 10000000);

	$showSaleReward = false;

	$saleData = [
		"ord_no"				=> $newOrdNo,
		"customer_id"			=> $customerId,
		"company_id"			=> $companyId,
		"currency"				=> $currency,
		"price_sum"				=> $priceSum,
		"initial"				=> $initial,
		"delivery_date"			=> $deliveryDate,
		"remaining"				=> $remaining,
		"interest"				=> $interest,
		"installments_month" 	=> $installmentsMonth,
		"no_installments" 		=> $noInstallments,
		"payment_date"			=> $paymentDate,
		"due"					=> $due,
		"status"				=> 1,
		"create_by"				=> $userId
	];

	$saleInsert = json_decode(insert_into("sales", $saleData, ["id" => "sales_id"]), true);
	if (!$saleInsert["success"]) {
		throw new Exception("Failed to create sale record.");
	}
	$saleId = $saleInsert["id"];

	$tolerance = 0.01;
	$sumFromFront = 0.0;

	foreach ($input["products"] as $p) {
		$qty   = (int)($p["quantity"] ?? 1);
		if ($qty < 1) $qty = 1;

		// El front debe mandar "total" por línea. Si no viene, fallback a price*qty.
		$price = (float)($p["price"] ?? 0);
		$lineTotalFront = isset($p["total"]) ? (float)$p["total"] : ($price * $qty);
		$sumFromFront += $lineTotalFront;
	}

	if (abs($sumFromFront - (float)$input["price_sum"]) > $tolerance) {
		// Puedes cambiar a Exception si quieres bloquear la operación
		log_activity(
			$userId,
			"warning_sum_mismatch",
			"Mismatch: price_sum={$input["price_sum"]} vs sumLines=$sumFromFront",
			"sales",
			$saleId
		);
	}

	foreach ($input["products"] as $product) {
		$productId = (int)($product["product_id"] ?? 0);
        if ($productId <= 0) throw new Exception("Invalid product_id in products array.");

        $quantity = max(1, (int)($product["quantity"] ?? 1));

		$productInfoJson = select_from(
			"products", 
			["product_id", "quantity", "min_quantity", "company_id", "product_name"], 
			["product_id" => $productId], 
			["fetch_first" => true]
		);
		$productInfo = json_decode($productInfoJson, true);

		if (!$productInfo["success"] || empty($productInfo["data"])) {
			throw new Exception("Error fetching product stock for ID: $productId");
		}

		$pData = $productInfo["data"];

		$currentStock	= (int)($pData["quantity"] ?? 0);
		$minQty			= isset($pData["min_quantity"]) ? (int)$pData["min_quantity"] : null;
		$prodCompany	= $pData["company_id"] ?? $companyId;
		$productName	= $pData["product_name"] ?? "Unknown Product";

		if ($currentStock < $quantity) {
			throw new Exception("Insufficient stock for product ID: $productId. Available: $currentStock, Requested: $quantity");
		}

		$newStock = $currentStock - $quantity;

		$updateData = ["quantity" => $newStock];
		if ($newStock === 0) {
			$updateData["status"] = 0;
		}

		$updateResult = json_decode(update_table("products", $updateData, ["product_id" => $productId]), true);
		
		if (!$updateResult["success"]) {
			throw new Exception("Failed to update stock/status for product ID: $productId");
		}

		$price    = (float)($product["price"] ?? 0);
		$discount = (float)($product["discount"] ?? 0);
		if ($discount < 0) $discount = 0;

		$lineTotal = isset($product["total"])
			? (float)$product["total"]
			: ($price * $quantity);

		$purchased = [
			"sales_id"		=> $saleId,
			"customer_id"	=> $customerId,
			"product_id"	=> (int)$product["product_id"],
			"quantity"		=> (int)($product["quantity"] ?? 1),
			"price"			=> number_format($price, 2, '.', ''),
			"discount"		=> number_format($discount, 2, '.', ''),
			"total"			=> number_format($lineTotal, 2, '.', ''),
			"create_by"		=> $userId
		];

		$productInsert = json_decode(insert_into("purchased_products", $purchased), true);
		if (!$productInsert["success"]) {
			throw new Exception("Error inserting product with ID {$purchased["product_id"]}");
		}

		if ($minQty !== null && $newStock == $minQty) {
			$UserData = json_decode(select_from("users", ["user_id"], ["company_id" => $prodCompany]), true);

			if ($UserData["success"] && !empty($UserData["data"])) {
				foreach ($UserData["data"] as $userRow) {
					$toUserId = (int)($userRow["user_id"] ?? 0);
                    if ($toUserId <= 0) continue;

					notify_user(
						$toUserId,
						null,
						"$productName is low on stock (Current: $newStock)",
						$productId,
						"Stock Update",
						0
					);

					triggerRealtimeNotification($toUserId);

					// sendEmail( 
					// 	$toUserId,
					// 	"Low Stock Alert: $productName",
					// 	"<p>The stock for product <strong>$productName</strong> (ID: $productId) has reached its minimum threshold.</p>
					// 	<p>Current stock: <strong>$newStock</strong></p>
					// 	<p>Please consider restocking this item.</p>"
					// );

					// Log activity for each user
					log_activity(
						$toUserId,
						"low_stock_info",
						"$productName is low on stock (Current: $newStock)",
						"notifications",
						$productId
					);
				}
			}
		}
	}

	// ✅ Onboarding: primera venta
	$onboardingCheck = json_decode(select_from("user_onboarding",
		[
			"user_id",
			"company",
			"product",
			"client",
			"sale",
			"sale_reward_seen"
		],
		[
			"user_id" => $userId
		],
		[
			"fetch_first" => true
		]
	), true);

	if (
		!empty($onboardingCheck["success"]) &&
		!empty($onboardingCheck["data"])
	) {
		$saleCompleted =
			$onboardingCheck["data"]["sale"] === true ||
			$onboardingCheck["data"]["sale"] === "t" ||
			$onboardingCheck["data"]["sale"] === 1 ||
			$onboardingCheck["data"]["sale"] === "1";

		$rewardAlreadySeen =
			$onboardingCheck["data"]["sale_reward_seen"] === true ||
			$onboardingCheck["data"]["sale_reward_seen"] === "t" ||
			$onboardingCheck["data"]["sale_reward_seen"] === 1 ||
			$onboardingCheck["data"]["sale_reward_seen"] === "1";

		$companyCompleted =
			$onboardingCheck["data"]["company"] === true ||
			$onboardingCheck["data"]["company"] === "t" ||
			$onboardingCheck["data"]["company"] === 1 ||
			$onboardingCheck["data"]["company"] === "1";

		$productCompleted =
			$onboardingCheck["data"]["product"] === true ||
			$onboardingCheck["data"]["product"] === "t" ||
			$onboardingCheck["data"]["product"] === 1 ||
			$onboardingCheck["data"]["product"] === "1";

		$clientCompleted =
			$onboardingCheck["data"]["client"] === true ||
			$onboardingCheck["data"]["client"] === "t" ||
			$onboardingCheck["data"]["client"] === 1 ||
			$onboardingCheck["data"]["client"] === "1";

		$completeOnboarding =
			$companyCompleted &&
			$productCompleted &&
			$clientCompleted;

		$showSaleReward =
			!$saleCompleted &&
			!$rewardAlreadySeen;

		if (!$saleCompleted) {
			$onboardingResult = json_decode(update_table("user_onboarding",
				[
					"sale" => true,
					"onboarding_completed" => $completeOnboarding,
					"updated_at" => date("Y-m-d H:i:s")
				],
				[
					"user_id" => $userId
				]
			), true);

			if (empty($onboardingResult["success"])) {
				error_log(
					"Could not update onboarding sale step for user_id: " .
					$userId
				);

				$showSaleReward = false;
			}
		}

	} elseif (
		($onboardingCheck["message"] ?? "") === "No records found"
	) {
		$onboardingResult = json_decode(insert_into("user_onboarding",
			[
				"user_id" => $userId,
				"sale" => true,
				"sale_reward_seen" => false,
				"onboarding_completed" => false,
				"created_at" => date("Y-m-d H:i:s"),
				"updated_at" => date("Y-m-d H:i:s")
			]
		), true);

		$showSaleReward =
			!empty($onboardingResult["success"]);

		if (!$showSaleReward) {
			error_log("Could not create onboarding sale step for user_id: " . $userId);
		}

	} else {
		$showSaleReward = false;

		error_log("Could not read onboarding sale state for user_id: " . $userId);
	}

	log_activity(
		$userId,
		"create_sale",
		"Created new sale #$saleId with customer_id {$customerId}",
		"sales",
		$saleInsert["id"] ?? null
	);

	$response = [
        "success" => true,
        "message" => "Sale created successfully",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => "",
		"show_reward_modal" => $showSaleReward,
		"reward_type" => $showSaleReward
			? "first_sale"
			: null,
		"sale_id" => $saleId,
		"order_no" => $newOrdNo
    ];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;