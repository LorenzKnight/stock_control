<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = ["success" => false, "ids" => []];

try {
	$keyword = $_GET["keyword"] ?? '';
	if (empty($keyword)) throw new Exception("Keyword is empty.");

	$search = select_from("category", ["category_id"], [
		"category_name LIKE" => "%" . $keyword . "%"
	]);

	$data = json_decode($search, true);
	if ($data["success"] && !empty($data["data"])) {
		$response["ids"] = array_column($data["data"], "category_id");
		$response["success"] = true;
	}
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>