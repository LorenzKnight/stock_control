<?php
// para ejecutar
// docker compose exec php sh -lc 'cd /app/public && php test-environment/test_update_table_return.php'
define('DISABLE_SECURITY', true);

require_once __DIR__ . '/../logic/stock_be.php';

$sql = get_pg_connection();

if (!$sql) {
	echo "❌ No se pudo establecer conexión con PostgreSQL\n";
	exit(1);
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST update_table() return_type ===\n\n";


/*
|--------------------------------------------------------------------------
| Crear tabla temporal
|--------------------------------------------------------------------------
*/

$createTable = pg_query($sql, "
	CREATE TEMP TABLE test_update_table (
		id SERIAL PRIMARY KEY,
		name VARCHAR(100) NOT NULL,
		quantity INTEGER NOT NULL DEFAULT 0
	);
");

if (!$createTable) {
	echo "❌ No se pudo crear la tabla temporal:\n";
	echo pg_last_error($sql) . "\n";
	exit(1);
}

echo "✅ Tabla temporal creada\n";


/*
|--------------------------------------------------------------------------
| Insertar datos iniciales
|--------------------------------------------------------------------------
*/

$seed = pg_query($sql, "
	INSERT INTO test_update_table (name, quantity)
	VALUES
		('Original one', 10),
		('Original two', 20);
");

if (!$seed) {
	echo "❌ No se pudieron insertar los datos iniciales:\n";
	echo pg_last_error($sql) . "\n";
	exit(1);
}

echo "✅ Datos iniciales creados\n\n";


/*
|--------------------------------------------------------------------------
| TEST 1
| Legacy: sin return_type debe devolver JSON
|--------------------------------------------------------------------------
*/

$jsonResult = update_table(
	'test_update_table',
	[
		'name' => 'Updated legacy'
	],
	[
		'id' => 1
	]
);

echo "TEST 1 - Default JSON\n";
echo "Tipo recibido: " . gettype($jsonResult) . "\n";

if (is_string($jsonResult)) {
	echo "✅ Devuelve string\n";

	$decoded = json_decode($jsonResult, true);

	if (json_last_error() === JSON_ERROR_NONE) {
		echo "✅ El string contiene JSON válido\n";

		if (
			isset($decoded['success']) &&
			$decoded['success'] === true &&
			$decoded['count'] === 1
		) {
			echo "✅ Se actualizó exactamente 1 registro\n";
		} else {
			echo "❌ La respuesta JSON no tiene el resultado esperado\n";
		}
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

$arrayResult = update_table(
	'test_update_table',
	[
		'name' => 'Updated array',
		'quantity' => 25
	],
	[
		'id' => 2
	],
	[
		'return_type' => 'array'
	]
);

echo "TEST 2 - Array\n";
echo "Tipo recibido: " . gettype($arrayResult) . "\n";

if (
	is_array($arrayResult) &&
	isset($arrayResult['success']) &&
	$arrayResult['success'] === true &&
	$arrayResult['count'] === 1
) {
	echo "✅ Devuelve array PHP\n";
	echo "✅ Se actualizó exactamente 1 registro\n";
} else {
	echo "❌ La respuesta no tiene el formato esperado\n";
}

echo "\nResultado:\n";
print_r($arrayResult);


/*
|--------------------------------------------------------------------------
| TEST 3
| Verificar que los valores realmente cambiaron
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$verifyResult = select_from(
	'test_update_table',
	['id', 'name', 'quantity'],
	[],
	[
		'order_by' => 'id',
		'return_type' => 'array'
	]
);

echo "TEST 3 - Verificación de datos\n";

$firstRow  = $verifyResult['data'][0] ?? [];
$secondRow = $verifyResult['data'][1] ?? [];

if (
	($firstRow['name'] ?? null) === 'Updated legacy' &&
	($secondRow['name'] ?? null) === 'Updated array' &&
	(string)($secondRow['quantity'] ?? '') === '25'
) {
	echo "✅ Los datos fueron modificados correctamente\n";
} else {
	echo "❌ Los datos no tienen los valores esperados\n";
}

echo "\nResultado:\n";
print_r($verifyResult);


/*
|--------------------------------------------------------------------------
| TEST 4
| WHERE que no coincide con ningún registro
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$noRowsResult = update_table(
	'test_update_table',
	[
		'name' => 'Should not exist'
	],
	[
		'id' => 99999
	],
	[
		'return_type' => 'array'
	]
);

echo "TEST 4 - Ninguna fila actualizada\n";

if (
	is_array($noRowsResult) &&
	isset($noRowsResult['success']) &&
	$noRowsResult['success'] === false &&
	$noRowsResult['count'] === 0
) {
	echo "✅ Devuelve success=false y count=0\n";
} else {
	echo "❌ La respuesta no tiene el resultado esperado\n";
}

echo "\nResultado:\n";
print_r($noRowsResult);


/*
|--------------------------------------------------------------------------
| TEST 5
| Protección: WHERE vacío
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$missingWhereResult = update_table(
	'test_update_table',
	[
		'name' => 'Dangerous update'
	],
	[],
	[
		'return_type' => 'array'
	]
);

echo "TEST 5 - Protección WHERE vacío\n";

if (
	is_array($missingWhereResult) &&
	isset($missingWhereResult['success']) &&
	$missingWhereResult['success'] === false &&
	$missingWhereResult['count'] === 0
) {
	echo "✅ UPDATE sin WHERE fue rechazado correctamente\n";
} else {
	echo "❌ La protección del WHERE no respondió como esperábamos\n";
}

echo "\nResultado:\n";
print_r($missingWhereResult);


/*
|--------------------------------------------------------------------------
| TEST 6
| queryData vacío
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$emptyDataResult = update_table(
	'test_update_table',
	[],
	[
		'id' => 1
	],
	[
		'return_type' => 'array'
	]
);

echo "TEST 6 - queryData vacío\n";

if (
	is_array($emptyDataResult) &&
	isset($emptyDataResult['success']) &&
	$emptyDataResult['success'] === false
) {
	echo "✅ El error respeta return_type=array\n";
} else {
	echo "❌ La respuesta no tiene el formato esperado\n";
}

echo "\nResultado:\n";
print_r($emptyDataResult);


/*
|--------------------------------------------------------------------------
| Limpiar
|--------------------------------------------------------------------------
*/

pg_query($sql, "DROP TABLE IF EXISTS test_update_table;");

echo "\n\n----------------------------------------\n\n";
echo "✅ Tabla temporal eliminada\n";
echo "=== FIN DEL TEST ===\n";