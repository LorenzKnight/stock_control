<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function select_from($tableName, array $columns = [], array $whereClause = [], array $options = []) : string
{
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	if (empty($tableName)) {
		return json_encode(["success" => false, "message" => "Table name is required"]);
	}

	$columnNames = empty($columns)
	? '*'
	: ($columns === ['*']
		? '*'
		: implode(', ', array_map(function($col) {
			// Detectar funciones agregadas y alias
			if (preg_match('/\b(COUNT|SUM|AVG|MIN|MAX)\s*\(/i', $col) || stripos($col, ' as ') !== false) {
				return $col;
			}
			return "\"$col\"";
		}, $columns)));

	if (preg_match('/\s|JOIN|\(|\)/i', $tableName)) {
		$escapedTable = $tableName;
	} else {
		$escapedTable = '"' . pg_escape_string($sql, $tableName) . '"';
	}

	$whereParts = [];

	if (isset($whereClause['RAW'])) {
		$whereParts[] = $whereClause['RAW'];
		unset($whereClause['RAW']);
	}
	
	foreach ($whereClause as $column => $value) {
		if (preg_match('/^(.+)\s+IN$/i', $column, $matches) && is_array($value)) {
			$field = trim($matches[1]);
			$fieldFormatted = (strpos($field, '.') === false) ? "\"$field\"" : $field;
			$escapedVals = array_map(fn($val) => "'" . pg_escape_string($sql, (string)$val) . "'", $value);
			$whereParts[] = "$fieldFormatted IN (" . implode(', ', $escapedVals) . ")";
			continue;
		}

		if (stripos($column, 'CAST(') === 0 || stripos($column, '(') !== false) {
			// No escapamos expresiones tipo CAST(...) o funciones
			$colFormatted = $column;
		} else {
			$colFormatted = (strpos($column, '.') === false) ? "\"$column\"" : $column;
		}

		if (is_array($value) && isset($value['condition'])) {
			$condition = strtoupper($value['condition']);
			if (in_array($condition, ['IS NULL', 'IS NOT NULL'])) {
				// No necesitamos un valor si es IS NULL o IS NOT NULL
				$whereParts[] = "$colFormatted {$condition}";
			} else {
				$escapedVal = is_numeric($value['value'])
					? $value['value']
					: "'" . pg_escape_string($sql, $value['value']) . "'";
				$whereParts[] = "$colFormatted {$condition} $escapedVal";
			}
		} elseif ($column === 'OR' && is_array($value)) {
			$orParts = [];
			foreach ($value as $orKey => $orVal) {
				if (preg_match('/^(.+)\s+IN$/i', $orKey, $matches) && is_array($orVal)) {
					$orField = trim($matches[1]);
					$orColFormatted = (strpos($orField, '.') === false) ? "\"$orField\"" : $orField;
					$escapedVals = array_map(fn($val) => "'" . pg_escape_string($sql, $val) . "'", $orVal);
					$orParts[] = "$orColFormatted IN (" . implode(',', $escapedVals) . ")";
					continue;
				}

				if (preg_match('/^(.+)\s+(ILIKE|LIKE)$/i', $orKey, $matches)) {
					$field = trim($matches[1]);
					$operator = strtoupper($matches[2]);

					$fieldFormatted = (preg_match('/\bCAST\s*\(.+\)/i', $field) || strpos($field, '(') !== false)
						? $field
						: ((strpos($field, '.') === false) ? "\"$field\"" : $field);

					$escapedVal = pg_escape_string($sql, (string)$orVal);
					$orParts[] = "$fieldFormatted $operator '%$escapedVal%'";
				} else {
					$orColFormatted = (strpos($orKey, '.') === false) ? "\"$orKey\"" : $orKey;
					$escapedVal = pg_escape_string($sql, (string)$orVal);
					$orParts[] = "$orColFormatted = '$escapedVal'";
				}
			}
			$whereParts[] = '(' . implode(' OR ', $orParts) . ')';
		} elseif ($column === 'OR_IN' && is_array($value)) {
			$orInParts = [];
			foreach ($value as $field => $inValues) {
				$orInCol = (strpos($field, '.') === false) ? "\"$field\"" : $field;
				$escapedVals = array_map(fn($val) => "'" . pg_escape_string($sql, $val) . "'", $inValues);
				$orInParts[] = "$orInCol IN (" . implode(',', $escapedVals) . ")";
			}
			$whereParts[] = '(' . implode(' OR ', $orInParts) . ')';
		} elseif (preg_match('/^(.+)\s+(ILIKE|LIKE)$/i', $column, $matches)) {
			$field = trim($matches[1]);
			$operator = strtoupper($matches[2]);

			$fieldFormatted = (preg_match('/\bCAST\s*\(.+\)/i', $field) || strpos($field, '(') !== false)
				? $field
				: ((strpos($field, '.') === false) ? "\"$field\"" : $field);
			$escapedVal = pg_escape_string($sql, (string)$value);

			$whereParts[] = "$fieldFormatted $operator '%$escapedVal%'";
		} elseif ($value === null) {
			$whereParts[] = "$colFormatted IS NULL";
		} else {
			$escapedVal = pg_escape_string($sql, (string)$value);
			$whereParts[] = "$colFormatted = '$escapedVal'";
		}
	}

	$whereClauseStr = empty($whereParts) ? '' : ' WHERE ' . implode(' AND ', $whereParts);

	$orderClause = '';
	if (!empty($options['order_by'])) {
		$orderByRaw = $options['order_by'];
		$orderDirection = isset($options['order_direction']) && strtolower($options['order_direction']) === 'desc' ? 'DESC' : 'ASC';
		$orderClause = " ORDER BY \"$orderByRaw\" $orderDirection";
	}

	$limitClause = '';
	if (!empty($options['limit']) && is_numeric($options['limit'])) {
		$limitClause = " LIMIT " . intval($options['limit']);
	}

	$query = "SELECT $columnNames FROM $escapedTable$whereClauseStr$orderClause$limitClause;";

	if (isset($options['echo_query']) && $options['echo_query'] && php_sapi_name() === 'cli') {
		echo "Q: $query\n";
	}

	$result = pg_query($sql, $query);

	if (!$result) {
		return json_encode([
			"success"	=> false,
			"message"	=> "Error executing query",
			"query"		=> (isset($options['echo_query']) && $options['echo_query']) ? $query : null,
			"count"		=> 0
		]);
	}

	if (!empty($options['fetch_first'])) {
		$row = pg_fetch_assoc($result);
		return json_encode([
			"success"	=> !empty($row),
			"message"	=> empty($row) ? "No records found" : "Record retrieved successfully",
			"query"		=> (isset($options['echo_query']) && $options['echo_query']) ? $query : null,
			"count"		=> !empty($row) ? 1 : 0,
			"data"		=> $row ?: []
		]);
	}

	$data = pg_fetch_all($result) ?: [];

	return json_encode([
		"success"	=> !empty($data),
		"message"	=> empty($data) ? "No records found" : "Records retrieved successfully",
		"query"		=> (isset($options['echo_query']) && $options['echo_query']) ? $query : null,
		"count"		=> count($data),
		"data"		=> $data
	]);
}

function insert_into($tableName, array $queryData = [], array $options = []) : string
{
	global $sql;
	if (!$sql) {
		$sql = get_pg_connection();
	}

	if (empty($queryData)) {
		return json_encode(["success" => false, "message" => "There is no data to insert"]);
	}

	$columnNames = implode(', ', array_keys($queryData));
	
	$columnValues = array_map(function($value) use ($sql) {
		if (is_null($value)) return 'NULL';
		if (is_bool($value)) return $value ? 'TRUE' : 'FALSE';
		if (is_int($value)) return $value;
		if (is_float($value)) return $value;
		if (is_numeric($value)) {
			// Si es entero (sin punto decimal)
			return (strpos($value, '.') === false) ? (int)$value : (float)$value;
		}
		return "'" . pg_escape_string($sql, (string)$value) . "'";
	}, array_values($queryData));
	
	$columnValues = implode(', ', $columnValues);

	$returningId = '';
	if (isset($options['id']) && !empty($options['id'])) {
		$returningId = " RETURNING " . $options['id'];
	}

	$query = "INSERT INTO $tableName ($columnNames) VALUES ($columnValues)$returningId;";

	if (isset($options['echo_query']) && $options['echo_query']) {
		echo "Q: $query<br>\n";
	}

	$result = pg_query($sql, $query);

	if (!$result) {
		return json_encode([
			"success" => false,
			"message" => pg_last_error(), // Mensaje real del error de PostgreSQL
			"query" => $query
		]);
	}
	
	if (!empty($returningId)) {
		$row = pg_fetch_assoc($result);
		$insertedId = $row[$options['id']] ?? null;
		return json_encode([
			"success" => true,
			"id" => $insertedId,
			"message" => "Record inserted successfully"
		]);
	}
	
	return json_encode([
		"success" => true,
		"message" => "Record inserted successfully"
	]);
}

function update_table($tableName, array $queryData = [], array $whereClause = [], array $options = []) : string
{
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	if (empty($tableName)) {
		return json_encode(["success" => false, "message" => "Table name is required", "count" => 0]);
	}

	if (empty($queryData)) {
		return json_encode(["success" => false, "message" => "No data to update", "count" => 0]);
	}

	if (empty($whereClause)) {
		return json_encode(["success" => false, "message" => "Update condition is missing", "count" => 0]);
	}

	$setParts = [];
	foreach ($queryData as $column => $value) {
		if ($value === "NULL" || is_null($value)) {
            $setParts[] = "$column = NULL";
        } elseif (is_numeric($value)) {
            $setParts[] = "$column = $value";
        } elseif (preg_match('/^\s*([a-zA-Z_]+\s*[\+\-\*\/]\s*\d+)\s*$/', $value)) {
            $setParts[] = "$column = $value";
        } else {
            $escapedValue = "'" . pg_escape_string($sql, (string)$value) . "'";
            $setParts[] = "$column = $escapedValue";
        }
	}
	$setClause = implode(', ', $setParts);

	$whereParts = [];
	foreach ($whereClause as $column => $value) {
		$escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($sql, (string)$value) . "'";
		$whereParts[] = "$column = $escapedValue";
	}
	$whereClauseStr = ' WHERE ' . implode(' AND ', $whereParts);

	$query = "UPDATE $tableName SET $setClause$whereClauseStr;";

	$returnQuery = null;
	if (isset($options['echo_query']) && $options['echo_query']) {
		$returnQuery = $query;
	}

	$result = pg_query($sql, $query);

	if (!$result) {
		return json_encode([
			"success" => false,
			"message" => "Error executing update query",
			"count" => 0,
			"query" => $returnQuery
		]);
	}

	$affectedRows = pg_affected_rows($result);

	return json_encode([
		"success" => $affectedRows > 0,
		"message" => $affectedRows > 0 ? "Row(s) updated successfully" : "No rows were updated",
		"count" => $affectedRows,
		"query" => $returnQuery
	]);
}

function delete_from(string $tableName, array $whereClause = [], array $options = []) : string
{
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	if (empty($tableName)) {
		return json_encode([
			"success" => false, 
			"message" => "Table name is required.",
			"count" => 0
		]);
	}

	if (empty($whereClause)) {
		return json_encode([
			"success" => false, 
			"message" => "Delete condition missing.",
			"count" => 0
		]);
	}

	$whereParts = [];
	foreach ($whereClause as $column => $value) {
		$escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($sql, $value) . "'";
		$whereParts[] = "$column = $escapedValue";
	}
	$whereSQL = implode(' AND ', $whereParts);

	$query = "DELETE FROM {$tableName} WHERE {$whereSQL};";

	if (!empty($options['echo_query'])) {
		error_log("Q: {$query}");
	}

	$result = pg_query($sql, $query);
	if (!$result) {
		return json_encode(["success" => false, "message" => "Query execution failed."]);
	}

	$affected = pg_affected_rows($result);

	return json_encode([
		"success"	=> true,
		"message"	=> $affected > 0 ? "Deleted successfully." : "No record deleted.",
		"count"		=> $affected
	]);
}

function log_activity($userId, $actionType, $description, $relatedTable = null, $relatedId = null) {
	$data = [
		"user_id" => $userId,
		"action_type" => $actionType,
		"action_description" => $description,
		"related_table" => $relatedTable,
		"related_id" => $relatedId,
		"created_at" => date("Y-m-d H:i:s")
	];

	return insert_into("activity_history", $data);
}

function notify_user($toUserId, $content, $userId = null, $link = null, $type = 'info') {
	$data = [
		"from_user_id"			=> $userId,	
		"to_user_id"			=> $toUserId,
		"notification_content"	=> $content,
		"notification_link"		=> $link,
		"notification_type"		=> $type,
		"created_at"			=> date("Y-m-d H:i:s")
	];
	insert_into("notifications", $data);
}

function handle_uploaded_image(
	string $fieldName,
	string $uploadDir,
	string $fileName,
	?int $userId = null,
	array $allowedExts = ['jpg', 'jpeg', 'png', 'webp']
): ?string {
	if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
		return null;
	}

	$uploadedFile = $_FILES[$fieldName];

	$ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
	if (!in_array($ext, $allowedExts)) {
		throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowedExts));
	}

	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
		throw new Exception("Failed to create upload directory.");
	}

	$prefix = $userId ? "{$fileName}_user_{$userId}" : "uploaded";
	$imageName = $prefix . '_' . time() . '.' . $ext;
	$targetPath = rtrim($uploadDir, '/') . '/' . $imageName;

	if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
		throw new Exception("Failed to move uploaded file.");
	}

	return $imageName;
}

function delete_image_from_record(array $params): array
{
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	// Esperados: 'table', 'id_column', 'id_value', 'image_column', 'image_folder'

	$table        = $params['table'] ?? null;
	$idColumn     = $params['id_column'] ?? null;
	$idValue      = $params['id_value'] ?? null;
	$imageColumn  = $params['image_column'] ?? null;
	$imageFolder  = $params['image_folder'] ?? null;

	if (!$table || !$idColumn || !$idValue || !$imageColumn || !$imageFolder) {
		return [
			"success" => false,
			"message" => "Missing required parameters for image deletion."
		];
	}

	// 1. Obtener nombre del archivo de imagen
	// $imageQuery = "SELECT {$imageColumn} FROM {$table} WHERE {$idColumn} = $1 LIMIT 1;";
	$imageQuery = "SELECT \"$imageColumn\" FROM \"$table\" WHERE \"$idColumn\" = $1 LIMIT 1;";
	$result = pg_query_params($sql, $imageQuery, [$idValue]);

	if (!$result || pg_num_rows($result) === 0) {
		return [
			"success" => true,
			"message" => "No image assigned to record. Nothing to delete."
		];
	}

	$row = pg_fetch_assoc($result);
	$imageName = $row[$imageColumn] ?? null;

	// 2. Eliminar imagen (si existe)
	if ($imageName && trim($imageName) !== "") {
		$absolutePath = realpath(__DIR__ . "/../{$imageFolder}/" . $imageName);
		if ($absolutePath && file_exists($absolutePath)) {
			unlink($absolutePath);
			return [
				"success" => true,
				"message" => "Image deleted successfully."
			];
		}
	}

	return [
		"success" => true,
		"message" => "Image not found on disk. Possibly already deleted."
	];
}

function get_next_increment_value(string $table, string $field, int $companyId, int $startFrom = 10000): int
{
	global $sql;
	if (!$sql) {
		$sql = get_pg_connection();
	}

	$resultJson = select_from($table, [$field], ['company_id' => $companyId], [
		"order_by" => $field,
		"order_direction" => "DESC",
		"limit" => 1,
		"fetch_first" => true
	]);

	$result = json_decode($resultJson, true);

	if (
		is_array($result) &&
		isset($result["success"]) && $result["success"] &&
		isset($result["data"]) &&
		is_array($result["data"]) &&
		isset($result["data"][$field]) &&
		is_numeric($result["data"][$field])
	) {
		return (int)$result["data"][$field] + 1;
	}

	return $startFrom;
}

function check_user_permission($userId, $permissionName) {
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	// Obtener el ID del permiso solicitado
	$permResponse = select_from(
        "permissions",
        ["permission_id"],
        ["permission_name" => $permissionName],
        ["fetch_first" => true]
    );
    $permResult = json_decode($permResponse, true);

    if (
		!is_array($permResult) ||
		!($permResult["success"] ?? false) ||
		empty($permResult["data"]["permission_id"])
	) {
		throw new Exception("Permission '$permissionName' not found.");
	}

    $requestedPermissionId = (int)$permResult["data"]["permission_id"];

	// Obtener el permiso más alto del usuario
	$userPermResponse = select_from(
        "users u
		JOIN roles r ON u.rank = r.role_id
		JOIN role_permissions rp ON r.role_id = rp.role_id
		JOIN permissions p ON rp.permission_id = p.permission_id",
		["MIN(p.permission_id) as user_permission_id"],
		["u.user_id" => $userId],
		["fetch_first" => true]
    );
    $userPermResult = json_decode($userPermResponse, true);

    if (
		!is_array($userPermResult) ||
		!($userPermResult["success"] ?? false)
	) {
		throw new Exception("Failed to fetch user permissions.");
	}

    $userPermissionId = (int)($userPermResult["data"]["user_permission_id"] ?? 9999);

	return $userPermissionId <= $requestedPermissionId;
}

function verifyAuthToken($token)
{
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	if (empty($token)) {
		return null;
	}

	try {
		// ✅ Intentar decodificar el JWT directamente
		$decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));

		// Verificar expiración
		if (!empty($decoded->exp) && $decoded->exp < time()) {
			invalidateExpiredToken($token);
			return null;
		}

		// Si tiene user_id válido, devolvemos directamente
		if (!empty($decoded->user_id)) {
			return $decoded->user_id;
		}

	} catch (Exception $e) {
		error_log("JWT verification failed: " . $e->getMessage());
		// No salimos todavía — intentamos verificar con la BD como respaldo
	}

	// ✅ Fallback: buscar en la base de datos (por si el token fue guardado manualmente)
	try {
		$queryResponse = select_from("user_tokens", ["user_id"], [
			"token" => $token,
			"status" => "active",
			"RAW" => "\"expires_at\" > NOW()"
		], ["fetch_first" => true]);

		$result = json_decode($queryResponse, true);

		if (!empty($result["success"]) && !empty($result["data"]["user_id"])) {
			return $result["data"]["user_id"];
		}

		invalidateExpiredToken($token);
		return null;

	} catch (Exception $e) {
		error_log("DB token verification failed: " . $e->getMessage());
		return null;
	}
}

function invalidateExpiredToken($token)
{
	global $sql;

	if (!$sql) {
		$sql = get_pg_connection();
	}

	if (empty($token)) {
		return json_encode([
			"success" => false,
			"message" => "Token is required",
			"count"   => 0
		]);
	}

	try {
		// Actualizar el estado del token a 'expired' cuando ya haya pasado su fecha de expiración
		$response = update_table("user_tokens", 
			[
				"status" => "expired"
			],
			[
				"token" => $token,
				"RAW" => "\"expires_at\" <= NOW()" // 🔥 condición extra: vencido
			],
			[
				"echo_query" => false
			]
		);

		return $response; // Devuelve el JSON estándar de update_table()
	} catch (Exception $e) {
		return json_encode([
			"success" => false,
			"message" => "Error invalidating token: " . $e->getMessage(),
			"count"   => 0
		]);
	}
}

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// /**
//  * Lee la config SMTP desde variables de entorno (o defaults).
//  */
// function get_smtp_config(): array {
//     return [
//         'host'       => getenv('SMTP_HOST') ?: 'smtp.example.com',
//         'port'       => (int)(getenv('SMTP_PORT') ?: 587),
//         'username'   => getenv('SMTP_USER') ?: '',
//         'password'   => getenv('SMTP_PASS') ?: '',
//         'secure'     => getenv('SMTP_SECURE') ?: 'tls', // 'tls' | 'ssl' | ''
//         'from_email' => getenv('MAIL_FROM') ?: 'no-reply@allstockcontrol.com',
//         'from_name'  => getenv('MAIL_FROM_NAME') ?: 'AllStockControl',
//         'reply_to'   => getenv('MAIL_REPLY_TO') ?: '', // opcional
//     ];
// }

// /**
//  * Convierte destinatarios flexibles a una lista de [email, name].
//  * Acepta: user_id (int/string numérica), email (string) o array mixto.
//  */
// function resolve_recipients($to): array {
//     $out = [];

//     $push = function($email, $name = '') use (&$out) {
//         $email = trim((string)$email);
//         if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
//             $out[] = [$email, trim((string)$name)];
//         }
//     };

//     $resolveUserId = function($userId) use ($push) {
//         $uid = (int)$userId;
//         if ($uid <= 0) return;
//         $res = json_decode(select_from("users", ["email","full_name"], ["user_id" => $uid], ["fetch_first" => true]), true);
//         if ($res && !empty($res["success"]) && !empty($res["data"]["email"])) {
//             $push($res["data"]["email"], $res["data"]["full_name"] ?? '');
//         }
//     };

//     if (is_array($to)) {
//         foreach ($to as $t) {
//             if (is_numeric($t))       $resolveUserId($t);
//             elseif (is_array($t))     $push($t[0] ?? '', $t[1] ?? '');
//             elseif (is_string($t)) {
//                 if (ctype_digit($t))  $resolveUserId($t);
//                 else                  $push($t, '');
//             }
//         }
//     } else {
//         if (is_numeric($to))        $resolveUserId($to);
//         elseif (is_string($to))    $push($to, '');
//     }

//     return $out;
// }

// /**
//  * Envuelve tu contenido HTML en una plantilla simple, con branding opcional.
//  */
// function wrap_email_html(string $subject, string $contentHtml, array $brand = []): string {
//     $appName = $brand['app_name'] ?? 'AllStockControl';
//     $logoUrl = $brand['logo_url'] ?? ''; // si tienes un logo público
//     $year    = date('Y');

//     $logoHtml = $logoUrl ? "<img src=\"$logoUrl\" alt=\"$appName\" height=\"32\" style=\"display:block;\">" : "<strong>$appName</strong>";

//     return <<<HTML
// 		<!doctype html>
// 		<html lang="es">
// 		<head>
// 		<meta charset="utf-8">
// 		<meta name="x-apple-disable-message-reformatting">
// 		<meta name="viewport" content="width=device-width, initial-scale=1">
// 		<title>{$subject}</title>
// 		</head>
// 		<body style="margin:0;background:#f6f7fb;">
// 		<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f6f7fb;">
// 			<tr>
// 			<td align="center" style="padding:24px;">
// 				<table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
// 				<tr>
// 					<td style="background:#111827;color:#ffffff;padding:16px 20px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial;sans-serif;">
// 					$logoHtml
// 					</td>
// 				</tr>
// 				<tr>
// 					<td style="padding:20px 20px 8px 20px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;font-size:18px;color:#111827;font-weight:600;">
// 					{$subject}
// 					</td>
// 				</tr>
// 				<tr>
// 					<td style="padding:0 20px 16px 20px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#111827;line-height:1.6;">
// 					{$contentHtml}
// 					</td>
// 				</tr>
// 				<tr>
// 					<td style="padding:16px 20px 24px 20px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;font-size:12px;color:#6b7280;">
// 					© {$year} {$appName}. Todos los derechos reservados.
// 					</td>
// 				</tr>
// 				</table>
// 			</td>
// 			</tr>
// 		</table>
// 		</body>
// 		</html>
// 	HTML;
// }

// /**
//  * Genera texto alternativo a partir del HTML (simple).
//  */
// function html_to_text(string $html): string {
//     // Quita scripts/estilos y tags, decodifica entidades.
//     $text = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
//     $text = strip_tags($text);
//     $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
//     // Normaliza espacios
//     $text = preg_replace('/[ \t]+/', ' ', $text);
//     $text = preg_replace('/\R{3,}/', "\n\n", $text);
//     return trim($text);
// }

// /**
//  * Envía email (multi-uso).
//  *
//  * @param int|string|array $to   user_id, email, o array de ellos. También admite [[email, name], ...]
//  * @param string           $subject
//  * @param string           $htmlBody  HTML "contenido" (se envuelve en plantilla)
//  * @param array            $opts  [
//  *   'brand'       => ['app_name' => '...', 'logo_url' => '...'],
//  *   'from_email'  => '...', 'from_name' => '...',
//  *   'reply_to'    => '...',
//  *   'cc'          => [ ... mismos formatos que $to ... ],
//  *   'bcc'         => [ ... ],
//  *   'attachments' => [ ['path'=>'/tmp/file.pdf','name'=>'Factura.pdf'] , ... ],
//  *   'priority'    => 1|3|5, // 1=High, 3=Normal, 5=Low
//  *   'company_id'  => 123,   // opcional para log/branding por empresa
//  * ]
//  * @return array           ['success'=>bool,'message'=>string]
//  */
// function sendEmail($to, string $subject, string $htmlBody, array $opts = []): array {
//     $cfg = get_smtp_config();

//     $brand       = $opts['brand'] ?? [];
//     $fromEmail   = $opts['from_email'] ?? $cfg['from_email'];
//     $fromName    = $opts['from_name']  ?? $cfg['from_name'];
//     $replyTo     = $opts['reply_to']   ?? ($cfg['reply_to'] ?: null);
//     $ccList      = $opts['cc']         ?? [];
//     $bccList     = $opts['bcc']        ?? [];
//     $attachments = $opts['attachments']?? [];
//     $priority    = (int)($opts['priority'] ?? 3);

//     $recipients  = resolve_recipients($to);
//     if (empty($recipients)) {
//         return ['success' => false, 'message' => 'No valid recipients'];
//     }

//     $wrappedHtml = wrap_email_html($subject, $htmlBody, $brand);
//     $altText     = html_to_text($htmlBody);

//     // Preferir PHPMailer si está instalado
//     if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
//         try {
//             $mail = new PHPMailer(true);
//             $mail->isSMTP();
//             $mail->Host       = $cfg['host'];
//             $mail->Port       = $cfg['port'];
//             $mail->SMTPAuth   = !empty($cfg['username']);
//             if ($mail->SMTPAuth) {
//                 $mail->Username = $cfg['username'];
//                 $mail->Password = $cfg['password'];
//             }
//             if (!empty($cfg['secure'])) {
//                 $mail->SMTPSecure = $cfg['secure']; // 'tls' o 'ssl'
//             }

//             $mail->CharSet = 'UTF-8';
//             $mail->setFrom($fromEmail, $fromName);
//             if ($replyTo) $mail->addReplyTo($replyTo);

//             foreach ($recipients as [$email, $name]) {
//                 $mail->addAddress($email, $name ?: '');
//             }
//             foreach (resolve_recipients($ccList) as [$email, $name]) {
//                 $mail->addCC($email, $name ?: '');
//             }
//             foreach (resolve_recipients($bccList) as [$email, $name]) {
//                 $mail->addBCC($email, $name ?: '');
//             }
//             foreach ($attachments as $att) {
//                 if (!empty($att['path'])) {
//                     $mail->addAttachment($att['path'], $att['name'] ?? '');
//                 }
//             }

//             // Prioridad (X-Priority)
//             if (in_array($priority, [1,3,5], true)) {
//                 $mail->Priority = $priority;
//             }

//             $mail->Subject = $subject;
//             $mail->isHTML(true);
//             $mail->Body    = $wrappedHtml;
//             $mail->AltBody = $altText;

//             $mail->send();

//             if (function_exists('log_activity')) {
//                 $actor = $_SESSION['sc_UserId'] ?? null;
//                 log_activity($actor, 'send_email', "Email sent: {$subject}", 'emails', null);
//             }

//             return ['success' => true, 'message' => 'Email sent'];
//         } catch (Exception $e) {
//             if (function_exists('log_activity')) {
//                 $actor = $_SESSION['sc_UserId'] ?? null;
//                 log_activity($actor, 'send_email_error', "Mailer error: ".$e->getMessage(), 'emails', null);
//             }
//             return ['success' => false, 'message' => 'Mailer error: '.$e->getMessage()];
//         }
//     }

//     // Fallback nativo: mail()
//     try {
//         $headers  = "MIME-Version: 1.0\r\n";
//         $headers .= "Content-type: text/html; charset=UTF-8\r\n";
//         $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
//         if ($replyTo) $headers .= "Reply-To: {$replyTo}\r\n";

//         // CC/BCC en mail() se agregan en headers
//         $ccResolved  = resolve_recipients($ccList);
//         $bccResolved = resolve_recipients($bccList);
//         if (!empty($ccResolved))  $headers .= "Cc: ".implode(',', array_column($ccResolved, 0))."\r\n";
//         if (!empty($bccResolved)) $headers .= "Bcc: ".implode(',', array_column($bccResolved, 0))."\r\n";

//         // Solo enviamos a la primera dirección en 'To' con mail(), puedes iterar si quieres
//         $toHeader = implode(',', array_map(fn($r) => $r[0], $recipients));
//         $ok = @mail($toHeader, "=?UTF-8?B?".base64_encode($subject)."?=", $wrappedHtml, $headers);

//         if ($ok) {
//             if (function_exists('log_activity')) {
//                 $actor = $_SESSION['sc_UserId'] ?? null;
//                 log_activity($actor, 'send_email', "Email sent (mail()): {$subject}", 'emails', null);
//             }
//             return ['success' => true, 'message' => 'Email sent (mail())'];
//         }
//         return ['success' => false, 'message' => 'mail() failed'];
//     } catch (\Throwable $t) {
//         if (function_exists('log_activity')) {
//             $actor = $_SESSION['sc_UserId'] ?? null;
//             log_activity($actor, 'send_email_error', "mail() error: ".$t->getMessage(), 'emails', null);
//         }
//         return ['success' => false, 'message' => 'mail() error: '.$t->getMessage()];
//     }
// }


//function to display any type of variable
function cdebug($var, $name = 'var', $die = false)
{
	ob_start();

    echo '<pre style="font-size: 12px; color: #333;">';
    echo '<span style="color: #007BFF;">*' . htmlspecialchars($name) . '*</span><br/>';
    print_r($var);
    echo '</pre><br/>';

    $buffer = ob_get_clean();

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

    $callerFile = $backtrace[0]['file'] ?? 'unknown file';
    $callerLine = $backtrace[0]['line'] ?? 'unknown line';
    $callerClass = $backtrace[1]['class'] ?? '';
    $callerFunction = $backtrace[1]['function'] ?? '';

    $dieMsg = '<pre style="font-size: 12px; color: #666; background-color: #F8F8F8; padding: 10px; border: 1px solid #ccc;">';
    $dieMsg .= '<b>cdebug() called from:</b><br>';
    $dieMsg .= "» <span style='color: #888;'>file</span>: <b>$callerFile</b><br>";
    $dieMsg .= "» <span style='color: #888;'>line</span>: <b>$callerLine</b><br>";
    if ($callerClass) {
        $dieMsg .= "» <span style='color: #888;'>class</span>: <b>$callerClass</b><br>";
    }
    if ($callerFunction) {
        $dieMsg .= "» <span style='color: #888;'>function</span>: <b>$callerFunction</b><br>";
    }
    $dieMsg .= '</pre>';

    echo $buffer;

    if ($die) {
        die($dieMsg);
    } else {
        echo $dieMsg;
    }
}
?>