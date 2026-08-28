<?php
// para ejecutar
// docker compose exec php sh -lc 'cd /app/public && php test-environment/test_delete_from_return.php'
define('DISABLE_SECURITY', true);

require_once __DIR__ . '/../logic/stock_be.php';

$sql = get_pg_connection();

if (!$sql) {
	echo "❌ No se pudo establecer conexión con PostgreSQL\n";
	exit(1);
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST delete_from() return_type ===\n\n";


/*
|--------------------------------------------------------------------------
| Crear tabla temporal
|--------------------------------------------------------------------------
*/

$createTable = pg_query($sql, "
	CREATE TEMP TABLE test_delete_from (
		id SERIAL PRIMARY KEY,
		name VARCHAR(100) NOT NULL
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
	INSERT INTO test_delete_from (name)
	VALUES
		('Record one'),
		('Record two'),
		('Record three');
");

if (!$seed) {
	echo "❌ No se pudieron insertar los datos iniciales:\n";
	echo pg_last_error($sql) . "\n";
	exit(1);
}

echo "✅ Se crearon 3 registros temporales\n\n";


/*
|--------------------------------------------------------------------------
| TEST 1
| Legacy: sin return_type debe devolver JSON
|--------------------------------------------------------------------------
*/

$jsonResult = delete_from(
	'test_delete_from',
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
			echo "✅ Se eliminó exactamente 1 registro\n";
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

$arrayResult = delete_from(
	'test_delete_from',
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
	echo "✅ Se eliminó exactamente 1 registro\n";
} else {
	echo "❌ La respuesta no tiene el formato esperado\n";
}

echo "\nResultado:\n";
print_r($arrayResult);


/*
|--------------------------------------------------------------------------
| TEST 3
| Intentar borrar un registro inexistente
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$noRowsResult = delete_from(
	'test_delete_from',
	[
		'id' => 99999
	],
	[
		'return_type' => 'array'
	]
);

echo "TEST 3 - Ningún registro eliminado\n";

if (
	is_array($noRowsResult) &&
	isset($noRowsResult['success']) &&
	$noRowsResult['success'] === true &&
	$noRowsResult['count'] === 0
) {
	echo "✅ Mantiene success=true y count=0\n";
	echo "✅ Se preservó el comportamiento legacy\n";
} else {
	echo "❌ La respuesta no tiene el resultado esperado\n";
}

echo "\nResultado:\n";
print_r($noRowsResult);


/*
|--------------------------------------------------------------------------
| TEST 4
| Protección: WHERE vacío
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$missingWhereResult = delete_from(
	'test_delete_from',
	[],
	[
		'return_type' => 'array'
	]
);

echo "TEST 4 - Protección WHERE vacío\n";

if (
	is_array($missingWhereResult) &&
	isset($missingWhereResult['success']) &&
	$missingWhereResult['success'] === false &&
	$missingWhereResult['count'] === 0
) {
	echo "✅ DELETE sin WHERE fue rechazado correctamente\n";
} else {
	echo "❌ La protección del WHERE no respondió como esperábamos\n";
}

echo "\nResultado:\n";
print_r($missingWhereResult);


/*
|--------------------------------------------------------------------------
| TEST 5
| Table name vacío
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$emptyTableResult = delete_from(
	'',
	[
		'id' => 3
	],
	[
		'return_type' => 'array'
	]
);

echo "TEST 5 - Table name vacío\n";

if (
	is_array($emptyTableResult) &&
	isset($emptyTableResult['success']) &&
	$emptyTableResult['success'] === false &&
	$emptyTableResult['count'] === 0
) {
	echo "✅ El error respeta return_type=array\n";
} else {
	echo "❌ La respuesta no tiene el resultado esperado\n";
}

echo "\nResultado:\n";
print_r($emptyTableResult);


/*
|--------------------------------------------------------------------------
| TEST 6
| Verificar los registros restantes
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$verifyResult = select_from(
	'test_delete_from',
	['id', 'name'],
	[],
	[
		'order_by' => 'id',
		'return_type' => 'array'
	]
);

echo "TEST 6 - Verificación de datos\n";

if (
	is_array($verifyResult) &&
	$verifyResult['count'] === 1 &&
	($verifyResult['data'][0]['id'] ?? null) == 3 &&
	($verifyResult['data'][0]['name'] ?? null) === 'Record three'
) {
	echo "✅ Queda exactamente el registro esperado\n";
} else {
	echo "❌ Los datos restantes no son los esperados\n";
}

echo "\nResultado:\n";
print_r($verifyResult);


/*
|--------------------------------------------------------------------------
| Limpiar
|--------------------------------------------------------------------------
*/

pg_query($sql, "DROP TABLE IF EXISTS test_delete_from;");

echo "\n\n----------------------------------------\n\n";
echo "✅ Tabla temporal eliminada\n";
echo "=== FIN DEL TEST ===\n";