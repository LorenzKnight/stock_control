<?php
use App\Products\ProductRepository;
use App\Products\ProductService;

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
    $userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

    $productCompanyId       = (int)($_POST["company_id"] ?? $companyId);
    $productName     		= (string)($_POST["product_name"] ?? '');
    $productQuantity        = is_numeric($_POST["quantity"] ?? null) ? (int)($_POST["quantity"]) : 0;
    $productPrice         	= (string)($_POST["price"] ?? '');

    if ($productName === '') throw new Exception("Product name is required.");
    if ($productQuantity < 0) throw new Exception("Quantity must be 0 or more.");
    if (is_numeric($productPrice) && (float)$productPrice < 0) throw new Exception("Price must be 0 or more.");

    $imageName = null;

	try {
		$imageName = handle_uploaded_image(
			"product_image",
			__DIR__ . "/../images/products/",
            "product",
			$userId,
            [
                "jpg", 
                "jpeg", 
                "png", 
                "webp"
            ],
		);
	} catch (Exception $ex) {
		throw new Exception("Image upload failed: " . $ex->getMessage());
	}

    $productData = [
        "unit_type" => (int)($_POST["unit_type"] ?? 1),
		"units" => is_numeric($_POST["units"] ?? null)
				? (int)$_POST["units"]
				: 1,
		"weight_unit" => is_numeric($_POST["weight_unit"] ?? null)
				? (float)$_POST["weight_unit"]
				: 0,
		"product_name" => $productName,
		"hs_code" => trim((string)($_POST["hs_code"] ?? '')),
		"product_type" => $_POST["product_type"] ?? null,
		"product_mark" => (int)($_POST["product_mark"] ?? 0),
		"product_model" => (int)($_POST["product_model"] ?? 0),
		"product_sub_model" => (int)($_POST["product_sub_model"] ?? 0),
		"currency" => trim((string)($_POST["currency"] ?? '')),
		"price" => $productPrice,
		"product_year" => (int)($_POST["product_year"] ?? 0),
		"purpose" => (int)($_POST["product_purpose"] ?? 1),
		"quantity" => $productQuantity,
		"min_quantity" => isset($_POST["min_quantity"]) && trim((string)$_POST["min_quantity"]) !== ''
				? (int)$_POST["min_quantity"]
				: 10,
		"description" => trim((string)($_POST["description"] ?? ''))
    ];

    $confirmUpdate = ($_POST["confirm_update"] ?? 'false') === 'true';

    $repository = new ProductRepository();
	$service = new ProductService($repository);

	try {
		$result =
			$service->createProduct(
				$userId,
				$productCompanyId,
				$productData,
				$imageName,
				$confirmUpdate
			);
	} catch (Throwable $serviceException) {
		if (
			$imageName !== null &&
			$imageName !== ''
		) {
			$imagePath =
				__DIR__ .
				"/../images/products/" .
				$imageName;

			if (is_file($imagePath)) {
				unlink($imagePath);
			}
		}

		throw $serviceException;
	}

    /*
	 * Si no se creó un producto nuevo,
	 * la imagen recién subida no será utilizada.
	 *
	 * handle_uploaded_image() genera un nombre
	 * único usando user + timestamp, por lo que
	 * podemos eliminar únicamente ese archivo.
	 */
    if (
		$imageName !== null &&
		$imageName !== '' &&
		(
			!empty($result["needs_confirmation"]) ||
			!empty($result["updated_existing"])
		)
	) {
		$imagePath = __DIR__ . "/../images/products/" . $imageName;

		if (is_file($imagePath)) {
			unlink($imagePath);
		}
	}

    if (!empty($result["needs_confirmation"])) {
        $response = [
            "success" => false,
            "needs_confirmation" => true,
            "message" => "This product already exists. Do you want to update the quantity?",
            "existing_product_id" => $result["existing_product_id"],
            "existing_quantity" => $result["existing_quantity"]
        ];

        echo json_encode($response);

        exit;
    }

    $productId = (int)$result["product_id"];

    log_activity(
        $userId,
        "create_product",
        "User added a new product: {$productName}",
        "products",
        $productId
    );

    $response = [
        "success" => true,
        "message" => "Product created successfully!",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => "",
        "show_reward_modal" => $result["show_reward_modal"],
        "reward_type" => $result["reward_type"],
        "product_id" => $productId,
        "product_name" => $result["product_name"]
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