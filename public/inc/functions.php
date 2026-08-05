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

		if (preg_match('/^(.+)\s+BETWEEN$/i', $column, $matches) && is_array($value) && count($value) === 2) {
			$field = trim($matches[1]);
			$fieldFormatted = (preg_match('/\bCAST\s*\(.+\)/i', $field) || strpos($field, '(') !== false)
				? $field
				: ((strpos($field, '.') === false) ? "\"$field\"" : $field);

			$fromVal = is_numeric($value[0])
				? $value[0]
				: "'" . pg_escape_string($sql, (string)$value[0]) . "'";

			$toVal = is_numeric($value[1])
				? $value[1]
				: "'" . pg_escape_string($sql, (string)$value[1]) . "'";

			$whereParts[] = "$fieldFormatted BETWEEN $fromVal AND $toVal";
			continue;
		}

		if (stripos($column, 'CAST(') === 0 || stripos($column, '(') !== false) {
			$colFormatted = $column;
		} else {
			$colFormatted = (strpos($column, '.') === false) ? "\"$column\"" : $column;
		}

		if (is_array($value) && isset($value['condition'])) {
			$condition = strtoupper($value['condition']);
			if (in_array($condition, ['IS NULL', 'IS NOT NULL'])) {
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
					$orParts[] = "$orColFormatted IN (" . implode(', ', $escapedVals) . ")";
					continue;
				}

				if (preg_match('/^(.+)\s+BETWEEN$/i', $orKey, $matches) && is_array($orVal) && count($orVal) === 2) {
					$field = trim($matches[1]);
					$fieldFormatted = (preg_match('/\bCAST\s*\(.+\)/i', $field) || strpos($field, '(') !== false)
						? $field
						: ((strpos($field, '.') === false) ? "\"$field\"" : $field);

					$fromVal = is_numeric($orVal[0])
						? $orVal[0]
						: "'" . pg_escape_string($sql, (string)$orVal[0]) . "'";

					$toVal = is_numeric($orVal[1])
						? $orVal[1]
						: "'" . pg_escape_string($sql, (string)$orVal[1]) . "'";

					$orParts[] = "$fieldFormatted BETWEEN $fromVal AND $toVal";
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
				$orInParts[] = "$orInCol IN (" . implode(', ', $escapedVals) . ")";
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
		
		if (preg_match('/\(|\.|\s/', $orderByRaw)) {
			$orderClause = " ORDER BY $orderByRaw $orderDirection";
		} else {
			$orderClause = " ORDER BY \"$orderByRaw\" $orderDirection";
		}
	}

	$limitClause = '';
	if (!empty($options['limit']) && is_numeric($options['limit'])) {
		$limitClause = " LIMIT " . intval($options['limit']);
	}

	$lockClause = '';
	if (!empty($options['for_update'])) {
		$lockClause = ' FOR UPDATE';
	}

	$query = "SELECT $columnNames FROM $escapedTable$whereClauseStr$orderClause$limitClause$lockClause;";

	if (isset($options['echo_query']) && $options['echo_query'] && php_sapi_name() === 'cli') {
		echo "Q: $query\n";
	}

	$result = pg_query($sql, $query);

	if (!$result) {
		return json_encode([
			"success"	=> false,
			"message"	=> "Error executing query: " . pg_last_error($sql),
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

function notify_user($toUserId, $userId = null, $content = null, $link = null, $type = 'info', $is_read = 0) {
	$data = [
		"from_user_id"			=> $userId,	
		"to_user_id"			=> $toUserId,
		"notification_content"	=> $content,
		"notification_link"		=> $link,
		"notification_type"		=> $type,
		"is_read"				=> (int)$is_read,
		"created_at"			=> date("Y-m-d H:i:s")
	];
	insert_into("notifications", $data);
}

function handle_uploaded_image(
	string $fieldName,
	string $uploadDir,
	string $fileName,
	?int $userId = null,
	array $allowedExts = ['jpg', 'jpeg', 'png', 'webp'],
	?string $previousImageName = null
): ?string {
	if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
		return null;
	}

	$uploadedFile = $_FILES[$fieldName];

	$ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
	if (!in_array($ext, $allowedExts, true)) {
		throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowedExts));
	}

	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
		throw new Exception("Failed to create upload directory.");
	}

	$prefix     = $userId ? "{$fileName}_user_{$userId}" : "uploaded";
	$imageName  = $prefix . '_' . time() . '.' . $ext;
	$uploadDir  = rtrim($uploadDir, '/');
	$targetPath = $uploadDir . '/' . $imageName;

	if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
		throw new Exception("Failed to move uploaded file.");
	}

	if ($previousImageName) {
		$prev = trim($previousImageName);

		if ($prev !== '' && $prev !== '.gitkeep') {
			$prevPath = $uploadDir . '/' . basename($prev);

			$realDir  = realpath($uploadDir);
			$realPrev = realpath($prevPath);

			if ($realDir && $realPrev && strpos($realPrev, $realDir) === 0 && is_file($realPrev)) {
				@unlink($realPrev);
			}
		}
	}

	return $imageName;
}

function delete_image_from_record(array $params): array
{
    global $sql;
    if (!$sql) $sql = get_pg_connection();

    $table        = $params['table'] ?? null;
    $idColumn     = $params['id_column'] ?? null;
    $idValue      = $params['id_value'] ?? null;
    $imageColumn  = $params['image_column'] ?? null;
    $imageFolder  = $params['image_folder'] ?? null;

    // NUEVOS:
    $clearDb      = $params['clear_db'] ?? true;          // por defecto, limpia DB
    $dbNull       = $params['db_null'] ?? true;           // true => NULL, false => ''

    if (!$table || !$idColumn || !$idValue || !$imageColumn || !$imageFolder) {
        return ["success" => false, "message" => "Missing required parameters for image deletion."];
    }

    $q = "SELECT \"$imageColumn\" FROM \"$table\" WHERE \"$idColumn\" = $1 LIMIT 1;";
    $result = pg_query_params($sql, $q, [$idValue]);

    if (!$result || pg_num_rows($result) === 0) {
        return ["success" => true, "message" => "Record not found or no image assigned."];
    }

    $row = pg_fetch_assoc($result);
    $imageName = $row[$imageColumn] ?? null;

    // Si no hay imagen, igual puedes limpiar DB si quieres (normalmente no hace falta)
    if (!$imageName || trim($imageName) === '' || trim($imageName) === '.gitkeep') {
        if ($clearDb) {
            $val = $dbNull ? null : '';
            $u = "UPDATE \"$table\" SET \"$imageColumn\" = $2 WHERE \"$idColumn\" = $1;";
            pg_query_params($sql, $u, [$idValue, $val]);
        }
        return ["success" => true, "message" => "No image assigned. Nothing to delete."];
    }

    // Seguridad de path
    $baseDir = realpath(__DIR__ . "/../" . trim($imageFolder, "/"));
    if ($baseDir) {
        $target = $baseDir . "/" . basename($imageName);
        $realTarget = realpath($target);

        if ($realTarget && strpos($realTarget, $baseDir) === 0 && is_file($realTarget)) {
            @unlink($realTarget);
        }
    }

    if ($clearDb) {
        $val = $dbNull ? null : '';
        $u = "UPDATE \"$table\" SET \"$imageColumn\" = $2 WHERE \"$idColumn\" = $1;";
        pg_query_params($sql, $u, [$idValue, $val]);
    }

    return ["success" => true, "message" => "Image removed successfully."];
}

function mapProductRelations(array $product, $companyId): ?array
{
	// Cargar idioma dentro de la función si todavía no existe
	if (!function_exists('tr')) {
		require_once(__DIR__ . '/../logic/mini_language_switcher.php');
	}

	// Seguridad por compañía
	$productCompanyId = isset($product["company_id"]) ? (int)$product["company_id"] : 0;
	$filterCompanyId = !empty($companyId) ? (int)$companyId : 0;

	if ($filterCompanyId <= 0 || $productCompanyId !== $filterCompanyId) {
		return null;
	}

	$product["mark_name"] = tr("uncategorized", "Uncategorized");
	$product["model_name"] = tr("no_model", "No model assigned");
	$product["submodel_name"] = tr("no_submodel", "No submodel assigned");

	// Marca
	if (!empty($product['product_mark'])) {
		$res = json_decode(select_from(
			"category",
			["category_name"],
			["category_id" => $product['product_mark']],
			["fetch_first" => true]
		), true);

		$product["mark_name"] = $res["data"]["category_name"] ?? tr("uncategorized", "Uncategorized");
	}

	// Modelo
	if (!empty($product['product_model'])) {
		$res = json_decode(select_from(
			"category",
			["category_name"],
			["category_id" => $product['product_model']],
			["fetch_first" => true]
		), true);

		$product["model_name"] = $res["data"]["category_name"] ?? tr("no_model", "No model assigned");
	}

	// Submodelo
	if (!empty($product['product_sub_model'])) {
		$res = json_decode(select_from(
			"category",
			["category_name"],
			["category_id" => $product['product_sub_model']],
			["fetch_first" => true]
		), true);

		$product["submodel_name"] = $res["data"]["category_name"] ?? tr("no_submodel", "No submodel assigned");
	}

	// Propósito
	if (isset($product['purpose'])) {
		$purposeMap = GlobalArrays::$productPurpose;
		$product["purpose_text"] = $purposeMap[$product['purpose']] ?? tr("unknown_purpose", "Unknown purpose");
	} else {
		$product["purpose_text"] = tr("no_purpose", "No purpose assigned");
	}

	return $product;
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

function requireAuth() {
    $authData = getAuthenticatedUserId();

    if (!$authData || empty($authData["user_id"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "reason" => "NO_TOKEN"
        ]);
        exit;
    }

	// 🔐 token puede venir por sesión o JWT
    $headers = function_exists('getallheaders')
		? getallheaders()
		: $_SERVER;

    $token = null;

    if (!empty($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }

    // ✅ Si hay token, validar en DB
    if ($token) {
        $check = select_from("user_tokens", ["status"], [
            "token" => $token,
            "status" => "active"
        ], ["fetch_first" => true]);

        $tokenData = json_decode($check, true);

        if (!$tokenData["success"]) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "reason" => "TOKEN_REVOKED"
            ]);
            exit;
        }
    }

    return [
        "user_id"    => $authData["user_id"],
        "company_id" => $authData["company_id"] ?? null
    ];
}

function getAuthenticatedUserId() {
    if (!empty($_SESSION["sc_UserId"])) {
        return [
            "user_id" => $_SESSION["sc_UserId"],
            "company_id" => $_SESSION["sc_CompanyId"] ?? null
        ];
    }

    $headers = function_exists('getallheaders')
		? getallheaders()
		: $_SERVER;

    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
        $token = trim(substr($authHeader, 7));

		$userData = verifyAuthToken($token, true);
		if (!empty($userData["user_id"])) {
			return $userData;
		}
    }

    return null;
}

function verifyAuthToken($token, $returnData = false)
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

		if ($returnData) {
            return [
                "user_id" => $decoded->user_id ?? null,
                "email" => $decoded->email ?? null,
                "company_id" => $decoded->company_id ?? null
            ];
        }

        return $decoded->user_id ?? null;
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
			return $returnData
                ? ["user_id" => $result["data"]["user_id"], "company_id" => null]
                : $result["data"]["user_id"];
		}

		invalidateExpiredToken($token);
		return null;
	} catch (Exception $e) {
		error_log("DB token verification failed: " . $e->getMessage());
		return null;
	}
}

function invalidateExpiredToken($token, $force = false)
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

	$where = $force
        ? ["token" => $token]
        : ["token" => $token, "RAW" => "\"expires_at\" <= NOW()"];

	try {
		// Actualizar el estado del token a 'expired' cuando ya haya pasado su fecha de expiración
		$response = update_table("user_tokens", 
			["status" => "expired"],
			$where,
			["echo_query" => false]
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


// 🌍 Obtener IP
function getUserIP() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
	} else {
		return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}
}

// 📱 Detectar dispositivo (muy simple, puedes mejorarlo después con una librería)
function getDeviceType() {
    $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
	$appClient = strtolower($_SERVER['HTTP_X_APP_CLIENT'] ?? '');

	if ($appClient === 'mobile') {
        return 'mobile';
    }

    if (preg_match('/mobile|android|iphone|ipad/', $agent)) {
		return 'mobile';
	}
    
	return 'desktop';
}

function getDeviceName() {
    $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    // 📱 Mobile
    if (strpos($agent, 'iphone') !== false) return 'iPhone';
    if (strpos($agent, 'ipad') !== false) return 'iPad';
    if (strpos($agent, 'android') !== false) return 'Android';

    // 💻 Desktop
    if (strpos($agent, 'macintosh') !== false || strpos($agent, 'mac os') !== false) {
        return 'Mac';
    }
    if (strpos($agent, 'windows nt') !== false) {
        return 'Windows';
    }
    if (strpos($agent, 'linux') !== false) {
        return 'Linux';
    }

    return 'Unknown';
}

// 📍 Obtener ubicación aproximada (por IP)
function getLocationByIP($ip) {
	try {
		$data = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,regionName,city");
		if ($data) {
			$json = json_decode($data, true);
			if (!empty($json['country'])) {
				return "{$json['city']}, {$json['regionName']}, {$json['country']}";
			}
		}
	} catch (Exception $e) {
		// No hacemos nada si falla
	}
	return 'Unknown';
}

function enforce_service_right($serviceName) {
    $authUser = requireAuth(); // obtiene el usuario logueado
    $userId = $authUser["user_id"] ?? null;

    if (empty($userId)) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Not authenticated"]);
        exit;
    }

    $rightsResponse = select_from(
        "service_rights",
        ["can_access"],
        [
            "user_id" => $userId,
            "service_name" => $serviceName
        ],
        ["fetch_first" => true]
    );

    $data = json_decode($rightsResponse, true);

    // Verifica acceso
    if (
        empty($data["success"]) ||
        empty($data["data"]) ||
        empty($data["data"]["can_access"]) ||
        $data["data"]["can_access"] != 1
    ) {
        header("Location: /profile.php");
        exit;
    }

    return true;
}

function sendShippingStatusPush(
	$shippingId, 
	$newStatus, 
	array $allowedUserIds = [],
    ?int $excludeUserId = null)
{
	// ✅ Si no hay usuarios permitidos → nada que hacer
    if (empty($allowedUserIds)) {
        return;
    }

    // ✅ Excluir al usuario que hizo el scan
    if ($excludeUserId !== null) {
        $allowedUserIds = array_diff($allowedUserIds, [(int)$excludeUserId]);
    }

    if (empty($allowedUserIds)) {
        return;
    }

    // 1️⃣ Obtener company_id del shipping
    $shippingQuery = select_from(
        "shippings",
        ["shipping_no"],
        ["shippings_id" => $shippingId],
        ["fetch_first" => true]
    );

    $shippingData = json_decode($shippingQuery, true)["data"] ?? null;
    if (!$shippingData) return;

	$shippingNo	= $shippingData["shipping_no"];

    // 3️⃣ Obtener suscripciones activas SOLO de esos usuarios
    $subsQuery = select_from(
        "push_subscriptions",
        ["endpoint", "p256dh", "auth"],
        [
            "is_active" => true,
			"RAW" => "\"user_id\" IN (" . implode(",", array_map("intval", $allowedUserIds)) . ")"
        ]
    );

    $subs = json_decode($subsQuery, true)["data"] ?? [];
    if (empty($subs)) return;

    // 4️⃣ Mensaje según estado
    switch ((int)$newStatus) {
		case 2:
			$statusText = "in transit";
			break;
		case 3:
			$statusText = "delivered";
			break;
		default:
			$statusText = "updated";
			break;
	}

    // 5️⃣ Enviar push
    foreach ($subs as $sub) {
        sendPush(
            $sub,
            [
                "title" => "Shipping status 📦",
                "body"  => "The shipment #{$shippingNo} is now {$statusText}",
                "url"   => "/shipping-status"
            ]
        );
    }
}

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function sendPush(array $subscriptionData, array $payload)
{
    $auth = [
        'VAPID' => [
            'subject' => $_ENV['VAPID_SUBJECT'],
            'publicKey' => $_ENV['VAPID_PUBLIC_KEY'],
            'privateKey' => $_ENV['VAPID_PRIVATE_KEY'],
        ]
    ];

    $webPush = new WebPush($auth);

    $subscription = Subscription::create([
        'endpoint' => $subscriptionData['endpoint'],
        'keys' => [
            'p256dh' => $subscriptionData['p256dh'],
            'auth'   => $subscriptionData['auth'],
        ],
    ]);

    $report = $webPush->sendOneNotification(
        $subscription,
        json_encode($payload)
    );

    // 🧹 Si el push falló permanentemente → desactivar suscripción
    if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
        update_table(
            "push_subscriptions",
            ["is_active" => false],
            ["endpoint" => $subscriptionData["endpoint"]]
        );
    }

    return $report->isSuccess();
}


use PHPMailer\PHPMailer\PHPMailer;

function sendSystemEmail(string $from, $to, string $subject, string $htmlContent): bool
{
    if (!is_string($to) && !is_array($to)) {
        throw new InvalidArgumentException('$to must be string or array');
    }

    // 🔎 Validar variables necesarias
    $requiredEnv = [
        'SMTP_HOST',
        'SMTP_PORT',
        'SMTP_USER',
        'SMTP_PASS',
        'SMTP_FROM_NAME',
        'SMTP_ALLOWED_DOMAIN'
    ];

    foreach ($requiredEnv as $key) {
        if (empty($_ENV[$key])) {
            error_log("Missing env variable: $key");
            return false;
        }
    }

    // 🔒 Validar FROM (inline)
    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid FROM email: $from");
        return false;
    }

    $allowedDomain = $_ENV['SMTP_ALLOWED_DOMAIN'];

    if (substr($from, -strlen('@' . $allowedDomain)) !== '@' . $allowedDomain) {
		error_log("FROM domain not allowed: $from");
		return false;
	}

    if (!empty($_ENV['SMTP_ALLOWED_FROM'])) {
        $allowedFrom = array_map(
            'trim',
            explode(',', $_ENV['SMTP_ALLOWED_FROM'])
        );

        if (!in_array($from, $allowedFrom, true)) {
            error_log("FROM not whitelisted: $from");
            return false;
        }
    }

    $mail = new PHPMailer(true);

    try {
        // 🔐 SMTP (login fijo)
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->Port       = (int) $_ENV['SMTP_PORT'];

        $mail->SMTPSecure = ($_ENV['SMTP_SECURE'] === 'ssl')
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        // 📧 FROM dinámico
        $mail->setFrom($from, $_ENV['SMTP_FROM_NAME']);

        // 👥 DESTINATARIOS
        foreach ((array) $to as $email) {
            $mail->addAddress($email);
        }

        // 📨 CONTENIDO
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = buildEmailTemplate($htmlContent);
        $mail->AltBody = strip_tags($htmlContent);

        $mail->send();
        return true;

    } catch (\Throwable $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

function buildEmailTemplate(string $content): string
{
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background:#f5f5f5; }
			.box { background:#fff; padding:30px; max-width:750px; margin:auto; }
			.header { text-align: left; border-bottom: 1px solid #eee; }
			.logo { text-align:center; width:70px; filter: brightness(0) saturate(105%) invert(11%) sepia(87%) saturate(6795%) hue-rotate(195deg) brightness(82%) contrast(105%); }
			.content { height:70vh; font-size:14px; padding-top:20px; }
			.info { font-size:12px; color:#777; margin-top:10px; }
			.footer { font-size:12px; color:#777; text-align:center; margin-top:20px; }
		</style>
    </head>
    <body>
        <div class='box'>
			<div class='header'>
				<img class='logo' src='https://allstockcontrol.com/images/sys-img/asc-logo.png' alt='AllStockControl'>
			</div>
			<div class='content'>
            	$content
			</div>
        </div>
        <div class='footer'>
            © " . date('Y') . " AllStockControl · support@allstockcontrol.com
        </div>
    </body>
    </html>
    ";
}

function tooManyEmailsFromIP(string $ip): bool
{
    $maxAttempts = 3;
    $windowSeconds = 3600; // 1 hora
    $dir = __DIR__ . '/rate_limits';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $key = md5($ip);
    $file = "$dir/$key.json";
    $now = time();

    $attempts = [];

    if (file_exists($file)) {
        $attempts = json_decode(file_get_contents($file), true) ?? [];
        // limpiar intentos viejos
        $attempts = array_filter(
            $attempts,
            fn($t) => ($now - $t) <= $windowSeconds
        );
    }

    if (count($attempts) >= $maxAttempts) {
        return true; // 🚫 bloquear
    }

    // registrar intento
    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts));

    return false; // ✅ permitir
}


//function to display any type of variable
if (!headers_sent() && ob_get_level() === 0) {
    ob_start();
}
function cdebug($var, $name = 'var', $die = false)
{
    // Detecta si este request debe responder JSON
    $accept      = $_SERVER['HTTP_ACCEPT']   ?? '';
    $contentType = $_SERVER['CONTENT_TYPE']  ?? '';
    $isJson = (stripos($accept, 'application/json') !== false) || (stripos($contentType, 'application/json') !== false);

    // ---------------------------
    // MODO JSON (endpoints)
    // ---------------------------
    if ($isJson) {
        if (!isset($GLOBALS['__CDEBUG_TEXT__'])) {
            $GLOBALS['__CDEBUG_TEXT__'] = [];
        }

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerFile = $bt[0]['file'] ?? 'unknown file';
        $callerLine = $bt[0]['line'] ?? 'unknown line';

        $GLOBALS['__CDEBUG_TEXT__'][] = [
            'name' => $name,
            'file' => $callerFile,
            'line' => $callerLine,
            'data' => $var
        ];

        if ($die) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'cdebug die',
                '_debug'  => $GLOBALS['__CDEBUG_TEXT__']
            ]);
            exit;
        }

        return;
    }

    // ---------------------------
    // MODO HTML (views)
    // ---------------------------

    // Asegura buffering (si alguien imprimió antes, ya no se puede "subir" nada)
    if (ob_get_level() === 0) {
        @ob_start();
    }

    // Inicializa almacenamiento HTML
    if (!isset($GLOBALS['__CDEBUG__'])) {
        $GLOBALS['__CDEBUG__'] = '';
    }

    // Registra el shutdown una sola vez para imprimir debug ARRIBA
    static $shutdownRegistered = false;
    if (!$shutdownRegistered) {
        $shutdownRegistered = true;

        register_shutdown_function(function () {
            $debug = $GLOBALS['__CDEBUG__'] ?? '';

            // Si no hay debug, deja salir la página normal
            if ($debug === '') {
                while (ob_get_level() > 0) {
                    echo ob_get_clean();
                }
                return;
            }

            // Junta TODO el output en $content
            $content = '';
            while (ob_get_level() > 0) {
                $content = ob_get_clean() . $content;
            }

            // Debug primero, luego el contenido normal
            echo $debug . $content;

            // Limpia
            $GLOBALS['__CDEBUG__'] = '';
        });
    }

    // Construye el bloque debug HTML
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $callerFile = $bt[0]['file'] ?? 'unknown file';
    $callerLine = $bt[0]['line'] ?? 'unknown line';

    ob_start();
    $dieMsg = '<div style="border:1px solid #ddd;background:#fff;padding:10px;margin:10px 0;font-family:monospace;font-size:12px;line-height:1.35;position:relative;z-index:999999">';
    
	$dieMsg .= '<div style="margin-bottom: 6px; color: #007BFF;"><b>' . htmlspecialchars($name) . '</b></div>';
    $dieMsg .= '<div style="margin-top:8px;color:#666;background:#f8f8f8;padding:6px;border:1px solid #eee;">';
    $dieMsg .= '<b>cdebug() called from:</b><br>';
	$dieMsg .= '<b>» File :</b> ' . htmlspecialchars($callerFile) . '<br>'; 
    $dieMsg .= '<b>» Line :</b> ' . htmlspecialchars((string)$callerLine). '<br><br>';
	
	$dieMsg .= '<b>» print :</b>';
	$dieMsg .= '<pre style="margin: 0; white-space: pre-wrap; color: #007BFF;">';
    $dieMsg .= $var === null ? 'NULL' : htmlspecialchars(print_r($var, true));
    $dieMsg .= '</pre>';

	$dieMsg .= '</div>';
    $dieMsg .= '</div>';

	echo $dieMsg;
    $html = ob_get_clean();

    // Acumula para imprimir arriba
    $GLOBALS['__CDEBUG__'] .= $html;

    if ($die) {
        exit; // el shutdown imprimirá debug + contenido ya capturado
    }
}