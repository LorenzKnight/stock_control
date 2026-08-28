<?php
// para ejecutar
// docker compose exec php sh -lc 'cd /app/public && php test-environment/test_select_from_return.php
define('DISABLE_SECURITY', true);

require_once __DIR__ . '/../logic/stock_be.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST select_from() return_type ===\n\n";


/*
|--------------------------------------------------------------------------
| TEST 1
| Comportamiento legacy: sin return_type debe devolver JSON
|--------------------------------------------------------------------------
*/

$jsonResult = select_from(
	'customers',
	['customer_id'],
	[],
	[
		'limit' => 1
	]
);

echo "TEST 1 - Default JSON\n";
echo "Tipo recibido: " . gettype($jsonResult) . "\n";

if (is_string($jsonResult)) {
	echo "✅ Devuelve string\n";

	$decoded = json_decode($jsonResult, true);

	if (json_last_error() === JSON_ERROR_NONE) {
		echo "✅ El string contiene JSON válido\n";
	} else {
		echo "❌ El string NO contiene JSON válido\n";
	}
} else {
	echo "❌ Esperábamos string y recibimos " . gettype($jsonResult) . "\n";
}

echo "\nResultado:\n";
print_r($jsonResult);


/*
|--------------------------------------------------------------------------
| TEST 2
| Nuevo comportamiento: return_type=array
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$arrayResult = select_from(
	'customers',
	['customer_id'],
	[],
	[
		'limit' => 1,
		'return_type' => 'array'
	]
);

echo "TEST 2 - Array\n";
echo "Tipo recibido: " . gettype($arrayResult) . "\n";

if (is_array($arrayResult)) {
	echo "✅ Devuelve array PHP\n";
} else {
	echo "❌ Esperábamos array y recibimos " . gettype($arrayResult) . "\n";
}

echo "\nResultado:\n";
print_r($arrayResult);


/*
|--------------------------------------------------------------------------
| TEST 3
| Comprobar también una respuesta de error como array
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$errorResult = select_from(
	'',
	[],
	[],
	[
		'return_type' => 'array'
	]
);

echo "TEST 3 - Error como array\n";
echo "Tipo recibido: " . gettype($errorResult) . "\n";

if (
	is_array($errorResult) &&
	isset($errorResult['success']) &&
	$errorResult['success'] === false
) {
	echo "✅ Los errores también respetan return_type=array\n";
} else {
	echo "❌ La respuesta de error no tiene el formato esperado\n";
}

echo "\nResultado:\n";
print_r($errorResult);