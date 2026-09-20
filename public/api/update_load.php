<?php
use App\Shippings\LoadRepository;
use App\Shippings\LoadService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "images/sys-img/error.gif",
	"redirect_url" => null
];

$transactionStarted = false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

    $authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? null);
    $companyId = (int)($authUser["company_id"] ?? null);

    if ($userId <= 0) throw new Exception("User session not found.");
	if ($companyId <= 0) throw new Exception("Company ID is required.");

    // if (!check_user_permission($userId, 'sales_handler')) {
	// 	throw new Exception("Access denied. You do not have permission to edit loads.");
	// }

    $input = json_decode(file_get_contents("php://input"), true);

	if (!is_array($input) || empty($input)) {
		throw new Exception("Invalid JSON input.");
	}

	$loadId = (int)($input["load_id"] ?? 0);

	if ($loadId <= 0) throw new Exception("Load ID is required.");

	$repository = new LoadRepository();
	$service = new LoadService($repository);

	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start load update transaction.");
	}

	$transactionStarted = true;

	$service->updateLoad(
		$userId,
		$companyId,
		$loadId,
		$input
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete load update.");
	}

	$transactionStarted = false;

    // $input = json_decode(file_get_contents("php://input"), true);
    // if (empty($input)) throw new Exception("Invalid JSON input.");

    // $loadId = $input["load_id"] ?? null;
    // if (empty($loadId)) throw new Exception("Load ID is required.");

    // // Datos principales
    // $fields = [
    //     "customer_id" => $input["customer_id"],
    //     "from_currency" => $input["from_currency"],
    //     "to_currency" => $input["to_currency"],
    //     "price_per_kg" => $input["price_per_kg"],
    //     "total_kg" => $input["total_kg"],
    //     "discount" => $input["discount"],
    //     "taxes" => $input["taxes"],
    //     "price_total_exchanged" => $input["price_total_exchanged"],
    //     "destination" => $input["destination"],
    //     "comment" => $input["comment"],
    //     "company_id" => $companyId
    // ];

    // // ✅ Actualizar tabla principal
    // $updateResult = update_table("loads", $fields, ["load_id" => $loadId]);
    // $updateParsed = json_decode($updateResult, true);

    // if (empty($updateParsed["success"])) {
    //     throw new Exception("Failed to update load details.");
    // }

    // // ✅ Actualizar productos
    // $products = $input["products"] ?? [];
    // if (!empty($products)) {
    //     // Borrar productos previos
    //     delete_from("loaded_products", ["load_id" => $loadId]);

    //     // Insertar los nuevos productos
    //     foreach ($products as $p) {
    //         insert_into("loaded_products", [
    //             "load_id" => $loadId,
    //             "product_id" => $p["product_id"],
    //             "quantity" => $p["quantity"],
    //             "total_kg" => $p["total_kg"],
    //             "from_currency" => $input["from_currency"],
    //             "total_kg_price" => $p["total_kg_price"],
    //             "to_currency" => $input["to_currency"],
    //             "total_price_exchanged" => $p["total_price_exchanged"]
    //         ]);
    //     }
    // }

    log_activity(
		$userId,
		"update load",
		"User updated load info (ID: {$loadId}).",
		"loads",
		$loadId
	);

	// 🔔 Opcional: enviar notificación en tiempo real
	// triggerRealtimeNotification($userId);

    $response = [
        "success" => true,
        "message" => "Load updated successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
		"redirect_url" => ""
    ];

} catch (Throwable $e) {
    if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

    $response = [
		"success" => false,
		"message" => $e->getMessage(),
		"img_gif" => "../images/sys-img/error.gif",
		"redirect_url" => ""
	];
}

echo json_encode($response);
exit;