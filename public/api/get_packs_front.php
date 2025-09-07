<?php
define('DISABLE_SESSION', true);
define('DISABLE_SECURITY', true); 

require_once('../logic/stock_be.php');

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Asegura JSON incluso ante errores PHP
set_error_handler(function($errno,$errstr,$errfile,$errline){
	http_response_code(500);
	echo json_encode([
		"success"=>false,
		"packages"=>[],
		"message"=>"PHP error: $errstr",
		"file"=>$errfile, "line"=>$errline
	], JSON_UNESCAPED_UNICODE);
	exit;
});
set_exception_handler(function($ex){
	http_response_code(500);
	echo json_encode([
		"success"=>false,
		"packages"=>[],
		"message"=>"Exception: ".$ex->getMessage()
	], JSON_UNESCAPED_UNICODE);
	exit;
});

$response = [
	"success"  => false,
	"packages" => [],
	"message"  => "Unable to fetch packages"
];

try {
	$minMembersRaw = filter_input(INPUT_GET, 'min_members', FILTER_VALIDATE_INT);
	$minMembers = ($minMembersRaw !== false && $minMembersRaw !== null) ? (int)$minMembersRaw : null;

	$limitRaw = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
	$limit = ($limitRaw !== false && $limitRaw !== null) ? max(1, min(100, (int)$limitRaw)) : null;

	$sortRaw = $_GET['sort'] ?? 'package_id';
	$dirRaw  = strtoupper($_GET['dir'] ?? 'ASC');
	$dir     = ($dirRaw === 'DESC') ? 'DESC' : 'ASC';

	$allowedSort = [
		'package_id','package_name','package_price',
		'members_limit','admins_limit','branch_affiliate_limit',
		'products_limit','package_duration','created_at'
	];
	$sort = in_array($sortRaw, $allowedSort, true) ? $sortRaw : 'package_id';

	$where = ["package_status" => 1];
	if ($minMembers !== null) {
		$where['RAW'] = "(\"members_limit\" IS NULL OR \"members_limit\" >= {$minMembers})";
	}

	$options = [
		"order_by"        => $sort,
		"order_direction" => $dir
	];
	if ($limit !== null) {
		$options["limit"] = $limit;
	}

	$packagesResponse = select_from("packages", ["*"], $where, $options);
	$packagesData = json_decode($packagesResponse, true);

	if (!$packagesData["success"] || empty($packagesData["data"])) {
		throw new Exception("No active packages found");
	}

	$response["success"]  = true;
	$response["packages"] = array_values($packagesData["data"]);
	$response["message"]  = "Packages retrieved successfully";
} catch (Exception $e) {
	http_response_code(200);
	$response = [
		"success" => false,
		"packages" => [],
		"message" => $e->getMessage(),
		"img_gif" => "../images/sys-img/error.gif",
		"redirect_url" => ""
	];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;