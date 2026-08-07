<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $userId = $authUser["user_id"];
	$companyId = $authUser["company_id"] ?? null;

    if (empty($userId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

    $productCompanyId       = intval($_POST["company_id"] ?? $companyId);
    $unitType         		= intval($_POST["unit_type"] ?? 1);
    $units         			= is_numeric($_POST["units"] ?? null) ? intval($_POST["units"]) : 1;
    $weightUnit         	= is_numeric($_POST["weight_unit"] ?? null) ? floatval($_POST["weight_unit"]) : 0;
    $totalWeight         	= $weightUnit * $units;
    $productName     		= trim($_POST["product_name"] ?? '');
    $hsCode         		= trim($_POST["hs_code"] ?? '');
    $productType            = (isset($_POST["product_type"]) && $_POST["product_type"] !== '' && is_numeric($_POST["product_type"]))
                                ? intval($_POST["product_type"])
                                : null;
    $productMark     		= intval($_POST["product_mark"] ?? 0);
    $productModel    		= intval($_POST["product_model"] ?? 0);
    $productSubModel		= intval($_POST["product_sub_model"] ?? 0);
    $productPriceCurrency   = trim($_POST["currency"] ?? '');
    $productPrice         	= trim($_POST["price"] ?? '');
    $productYear     		= intval($_POST["product_year"] ?? '');
    $purpose         		= intval($_POST["product_purpose"] ?? 1);
    $productQuantity        = is_numeric($_POST["quantity"] ?? null) ? intval($_POST["quantity"]) : 0;
    $productMinQuantity     = isset($_POST["min_quantity"]) && trim($_POST["min_quantity"]) !== '' ? intval($_POST["min_quantity"]) : 10;
    $description     		= trim($_POST["description"] ?? '');
    $confirmUpdate          = $_POST["confirm_update"] ?? 'false';

    $showProductReward = false;

    if ($productName === '') throw new Exception("Product name is required.");
    if ($productQuantity < 0) throw new Exception("Quantity must be 0 or more.");
    if ($productPrice < 0) throw new Exception("Price must be 0 or more.");

    $imageName = null;
	try {
		$imageName = handle_uploaded_image(
			"product_image",
			__DIR__ . "/../images/products/",
            "product",
			$userId,
            ["jpg", "jpeg", "png", "webp"],
		);
	} catch (Exception $ex) {
		throw new Exception("Image upload failed: " . $ex->getMessage());
	}

    $insertData = [
        "created_by"         => $userId,
        "company_id"        => $productCompanyId,
        "sale_unit_type"    => $unitType,
        "units_per_pack"    => $units,
        "weight_per_unit"   => $weightUnit,
        "total_weight"      => $totalWeight,
        "product_name"      => $productName,
        "hs_code"           => $hsCode,
        "product_type"      => $productType,
        "product_mark"      => $productMark,
        "product_model"     => $productModel,
        "product_sub_model" => $productSubModel,
        "product_year"      => $productYear,
        "purpose"           => $purpose,
		"quantity"          => $productQuantity,
        "min_quantity"      => $productMinQuantity,
		"currency"			=> $productPriceCurrency,
        "price"				=> $productPrice,
        "description"       => $description,
        "status"            => 1,
        "created_at"        => date("Y-m-d H:i:s")
    ];
	
    if ($imageName) {
        $insertData["product_image"] = $imageName;
    }

    $productRes = json_decode(select_from("products", ["product_id", "quantity"], [
        "company_id"        => $productCompanyId,
        "product_name"      => $productName,
        "product_mark"      => $productMark,
        "product_model"     => $productModel,
        "product_sub_model" => $productSubModel,
        "product_year"      => $productYear
    ], ["fetch_first" => true]), true);

    $existingProduct = $productRes["data"] ?? [];

    if ($productRes["success"] && !empty($existingProduct) && $confirmUpdate !== 'true') {
        $response = [
            "success" => false,
            "needs_confirmation" => true,
            "message" => "This product already exists. Do you want to update the quantity?",
            "existing_product_id" => $existingProduct["product_id"],
            "existing_quantity" => $existingProduct["quantity"]
        ];
        echo json_encode($response);
        exit;
    }

    if ($productRes["success"] && !empty($existingProduct) && $confirmUpdate === 'true') {
        $updateResult = json_decode(update_table("products", [
            "quantity" => $existingProduct["quantity"] + $productQuantity,
            "updated_at" => date("Y-m-d H:i:s")
        ], ["product_id" => $existingProduct["product_id"]]), true);

        if (!$updateResult["success"]) {
            throw new Exception("Error updating existing product quantity.");
        }

        $finalProductId = $existingProduct["product_id"];
    } else {
        $insertResult = json_decode(insert_into("products", $insertData, ["id" => "product_id"]), true);

        if (!$insertResult["success"]) {
            throw new Exception("Error saving product data.");
        }

        $finalProductId = $insertResult["id"];

        // ✅ Validar si este es el primer producto creado
        $productCountRes = json_decode(
            select_from(
                "products",
                ["COUNT(*) AS total"],
                [
                    "company_id" => $productCompanyId,
                    "created_by" => $userId
                ],
                [
                    "fetch_first" => true
                ]
            ),
            true
        );

        if (
            !is_array($productCountRes) ||
            empty($productCountRes["success"])
        ) {
            error_log(
                "Could not count products for onboarding. User ID: " .
                $userId
            );
        } else {
            $totalProducts = intval(
                $productCountRes["data"]["total"] ?? 0
            );

            if ($totalProducts === 1) {
                $onboardingCheck = json_decode(
                    select_from(
                        "user_onboarding",
                        [
                            "user_id",
                            "product",
                            "product_reward_seen"
                        ],
                        [
                            "user_id" => $userId
                        ],
                        [
                            "fetch_first" => true
                        ]
                    ),
                    true
                );

                if (
                    !is_array($onboardingCheck) ||
                    !array_key_exists("success", $onboardingCheck)
                ) {
                    error_log(
                        "Invalid onboarding response for user_id: " .
                        $userId
                    );

                } elseif (
                    empty($onboardingCheck["success"]) &&
                    ($onboardingCheck["message"] ?? "") !==
                        "No records found"
                ) {
                    error_log(
                        "Could not read onboarding state for user_id " .
                        $userId .
                        ": " .
                        ($onboardingCheck["message"] ?? "Unknown error")
                    );

                } elseif (
                    !empty($onboardingCheck["success"]) &&
                    !empty($onboardingCheck["data"])
                ) {
                    $rewardAlreadySeen =
                        $onboardingCheck["data"]["product_reward_seen"] === true ||
                        $onboardingCheck["data"]["product_reward_seen"] === "t" ||
                        $onboardingCheck["data"]["product_reward_seen"] === 1 ||
                        $onboardingCheck["data"]["product_reward_seen"] === "1";

                    $showProductReward = !$rewardAlreadySeen;

                    $onboardingResult = json_decode(
                        update_table(
                            "user_onboarding",
                            [
                                "product" => true,
                                "updated_at" => date("Y-m-d H:i:s")
                            ],
                            [
                                "user_id" => $userId
                            ]
                        ),
                        true
                    );

                    if (empty($onboardingResult["success"])) {
                        error_log(
                            "Could not update onboarding product step for user_id: " .
                            $userId
                        );
                    }

                } else {
                    $showProductReward = true;

                    $onboardingResult = json_decode(
                        insert_into(
                            "user_onboarding",
                            [
                                "user_id" => $userId,
                                "product" => true,
                                "product_reward_seen" => false,
                                "created_at" => date("Y-m-d H:i:s"),
                                "updated_at" => date("Y-m-d H:i:s")
                            ]
                        ),
                        true
                    );

                    if (empty($onboardingResult["success"])) {
                        error_log(
                            "Could not create onboarding state for user_id: " .
                            $userId
                        );

                        /*
                        * Si no pudimos guardar el estado, evitamos mostrar
                        * una recompensa que podría repetirse indefinidamente.
                        */
                        $showProductReward = false;
                    }
                }
            }
        }
    }

    log_activity(
        $userId,
        "create_product",
        "User added a new product: {$productName}",
        "products",
        $finalProductId
    );

    $response = [
        "success" => true,
        "message" => "Product created successfully!",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => "",
        "show_reward_modal" => $showProductReward,
        "reward_type" => $showProductReward
            ? "first_product"
            : null,
        "product_id" => $finalProductId,
        "product_name" => $productName
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;