<?php
// para ejecutar
// docker compose exec php sh -lc 'cd /app/public && php test-environment/test_insert_into_return.php
define('DISABLE_SECURITY', true);

require_once __DIR__ . '/../logic/stock_be.php';

$sql = get_pg_connection();

if (!$sql) {
	echo "❌ No se pudo establecer conexión con PostgreSQL\n";
	exit(1);
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST insert_into() return_type ===\n\n";

/*
|--------------------------------------------------------------------------
| Crear tabla temporal
|--------------------------------------------------------------------------
|
| Esta tabla existe solamente durante esta conexión PostgreSQL.
| No modifica ninguna tabla real del sistema.
|
*/

$createTable = pg_query($sql, "
	CREATE TEMP TABLE test_insert_into (
		id SERIAL PRIMARY KEY,
		name VARCHAR(100) NOT NULL
	);
");

if (!$createTable) {
	echo "❌ No se pudo crear la tabla temporal:\n";
	echo pg_last_error($sql) . "\n";
	exit(1);
}

echo "✅ Tabla temporal creada\n\n";


/*
|--------------------------------------------------------------------------
| TEST 1
| Comportamiento legacy: debe devolver JSON
|--------------------------------------------------------------------------
*/

$jsonResult = insert_into(
	'test_insert_into',
	[
		'name' => 'Legacy JSON test'
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

$arrayResult = insert_into(
	'test_insert_into',
	[
		'name' => 'Array test'
	],
	[
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
| Array + RETURNING id
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$idResult = insert_into(
	'test_insert_into',
	[
		'name' => 'Returning ID test'
	],
	[
		'id' => 'id',
		'return_type' => 'array'
	]
);

echo "TEST 3 - Array con RETURNING id\n";
echo "Tipo recibido: " . gettype($idResult) . "\n";

if (
	is_array($idResult) &&
	isset($idResult['success']) &&
	$idResult['success'] === true &&
	isset($idResult['id'])
) {
	echo "✅ Devuelve array y el ID insertado\n";
	echo "✅ ID recibido: " . $idResult['id'] . "\n";
} else {
	echo "❌ No recibimos el ID como esperábamos\n";
}

echo "\nResultado:\n";
print_r($idResult);


/*
|--------------------------------------------------------------------------
| TEST 4
| Error previo a SQL como array
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$errorResult = insert_into(
	'test_insert_into',
	[],
	[
		'return_type' => 'array'
	]
);

echo "TEST 4 - Error como array\n";
echo "Tipo recibido: " . gettype($errorResult) . "\n";

if (
	is_array($errorResult) &&
	isset($errorResult['success']) &&
	$errorResult['success'] === false
) {
	echo "✅ El error también respeta return_type=array\n";
} else {
	echo "❌ El error no tiene el formato esperado\n";
}

echo "\nResultado:\n";
print_r($errorResult);


/*
|--------------------------------------------------------------------------
| TEST 5
| Verificar que realmente se insertaron los 3 registros temporales
|--------------------------------------------------------------------------
*/

echo "\n\n----------------------------------------\n\n";

$verifyResult = select_from(
	'test_insert_into',
	['id', 'name'],
	[],
	[
		'order_by' => 'id',
		'return_type' => 'array'
	]
);

echo "TEST 5 - Verificación con select_from()\n";

if (
	is_array($verifyResult) &&
	isset($verifyResult['count']) &&
	$verifyResult['count'] === 3
) {
	echo "✅ Se insertaron exactamente 3 registros\n";
} else {
	echo "❌ Esperábamos 3 registros\n";
}

echo "\nResultado:\n";
print_r($verifyResult);


/*
|--------------------------------------------------------------------------
| Limpiar
|--------------------------------------------------------------------------
*/

pg_query($sql, "DROP TABLE IF EXISTS test_insert_into;");

echo "\n\n----------------------------------------\n\n";
echo "✅ Tabla temporal eliminada\n";
echo "=== FIN DEL TEST ===\n";