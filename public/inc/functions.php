<?php
function select_from($tableName, array $columns = [], array $whereClause = [], array $options = []) : string
{
	if (empty($tableName)) {
		return json_encode(["success" => false, "message" => "Table name is required"]);
	}

	$columnNames = empty($columns) ? '*' : implode(', ', $columns);

	$whereParts = [];
	foreach ($whereClause as $column => $value) {
		if ($value === '' || $value === null) continue;
		$escapedValue = "'" . pg_escape_string((string)$value) . "'";
		$whereParts[] = "$column = $escapedValue";
	}
	$whereClauseStr = empty($whereParts) ? '' : ' WHERE ' . implode(' AND ', $whereParts);

	$orderClause = '';
	if (!empty($options['order_by'])) {
		$orderDirection = isset($options['order_direction']) && strtolower($options['order_direction']) === 'desc' ? 'DESC' : 'ASC';
		$orderClause = " ORDER BY " . pg_escape_string($options['order_by']) . " $orderDirection";
	}

	$limitClause = '';
	if (!empty($options['limit']) && is_numeric($options['limit'])) {
		$limitClause = " LIMIT " . intval($options['limit']);
	}

	$query = "SELECT $columnNames FROM $tableName$whereClauseStr$orderClause$limitClause;";

	if (isset($options['echo_query']) && $options['echo_query']) {
		echo "Q: $query<br>\n";
	}

	$result = pg_query($query);

	if (!$result) {
		return json_encode(["success" => false, "message" => "Error executing query", "count" => 0]);
	}


	if (!empty($options['fetch_first'])) {
		$row = pg_fetch_assoc($result);
		return json_encode([
			"success" => !empty($row),
			"message" => empty($row) ? "No records found" : "Record retrieved successfully",
			"count" => !empty($row) ? 1 : 0,
			"data" => $row ?: []
		]);
	}

	$data = pg_fetch_all($result) ?: [];

	return json_encode([
		"success" => !empty($data),
		"message" => empty($data) ? "No records found" : "Records retrieved successfully",
		"count" => count($data),
		"data" => $data
	]);
}

function insert_into($tableName, array $queryData = [], array $options = []) : string
{
	if (empty($queryData)) {
		return json_encode(["success" => false, "message" => "There is no data to insert"]);
	}

	$columnNames = implode(', ', array_keys($queryData));
	
	$columnValues = array_map(function($value) {
		return is_numeric($value) ? $value : "'" . pg_escape_string($value) . "'";
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

	$result = pg_query($query);

	if ($result && !empty($returningId)) {
		$row = pg_fetch_assoc($result);
		$insertedId = $row[$options['id']];
		return json_encode(["success" => true, "id" => $insertedId, "message" => "Record inserted successfully"]);
	} else {
		return json_encode(["success" => false, "message" => "Error inserting record"]);
	}
}


//function to display any type of variable
function cdebug($var, $name = 'var', $die = false)
{
	// Start output buffering
	ob_start();
	
	// Print the variable with the name and preformatting
	print('<pre style="font-size: 12px; color: #666;">');
	print('<span style="color: #007BFF;">*' . $name . '*</span><br/>');
	print_r($var);
	print('</pre></br>');
	
	// Capture the buffer contents
	$buffer = ob_get_contents();
	
	// End and clean the output buffer
	ob_end_clean();
	
	// Get the backtrace for debugging information
	$backtrace = debug_backtrace();
	
	// Generate the backtrace information to print
	$dieMsg  = '<pre style="font-size: 12px; color: #666; background-color: #F0F0F0; padding: 10px;">';
	$dieMsg .= '<b>var dump cdebug() called in:</b><br>';
	$dieMsg .= isset($backtrace[0]['file']) ? '» <span style="color: #666; display: inline-block; width: 12ch;">file</span>: <b>' . $backtrace[0]['file'] . '</b><br>' : '';
	$dieMsg .= isset($backtrace[0]['line']) ? '» <span style="color: #666; display: inline-block; width: 12ch;">line</span>: <b>' . $backtrace[0]['line'] . '</b><br>' : '';
	$dieMsg .= isset($backtrace[1]['class']) ? '» <span style="color: #666; display: inline-block; width: 12ch;">class</span>: <b>' . $backtrace[1]['class'] . '</b><br>' : '';
	$dieMsg .= isset($backtrace[1]['function']) ? '» <span style="color: #666; display: inline-block; width: 12ch;">function</span>: <b>' . $backtrace[1]['function'] . '</b><br>' : '';
	$dieMsg .= '</pre>';
	
	// Print the buffer contents
	print($buffer);
	
	// If $die is true, terminate the script
	if ($die == true) 
	{
		die($dieMsg);
	} 
	else 
	{
		// Otherwise, print the backtrace information
		print($dieMsg);
	}
}
?>