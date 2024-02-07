<?php

use PhpParser\Node\Expr\Cast\Double;

function checkUniqueEmail($email)
{
	$query_ConsultaFuncion = "SELECT email FROM users WHERE email = $email";
	//echo $query_ConsultaFuncion;
	$ConsultaFuncion = pg_query($query_ConsultaFuncion);
	$totalRows_ConsultaFuncion = pg_num_rows($ConsultaFuncion);
	
	if ($totalRows_ConsultaFuncion==0)
    {
        return true;
    }
		
    return false;
	
	pg_free_result($ConsultaFuncion);
}

function u_all_info($columns = "*", $requestData = array(), array $options = []) : array
{
  	if(empty($columns))
	{
		$columns = "*";
	}

	if($columns != "*" && !is_array($columns))
	{
		$columns = "*";
	}

	$queryColumnNames = "*";
	if(is_array($columns))
	{
		$queryColumnNames = "";
		$validColumns = dbUsersColumnNames();

		foreach ($columns as $column) 
		{
			if(in_array($column, $validColumns))
			{
				$queryColumnNames .= $column.",";
			}
		}

		if(empty($queryColumnNames))
		{
			return null;
		}
		
		$queryColumnNames = substr($queryColumnNames, 0, -1);
	}

  	$query = "select $queryColumnNames ";

  	if(isset($options['count_query']) && $options['count_query'])
	{
		$query = "select count(*) ";
	}

	$query .= "from users ";

  	//check other conditions
	$conditions = "";

  	if(isset($requestData['user_id']) && !empty($requestData['user_id']))
	{
		$conditions .= " and user_id = " . $requestData['user_id']. ' ';
	}

  	if(isset($requestData['username']) && !empty($requestData['username']))
	{
    	$requestData['username'] = pg_escape_string(trim($requestData["username"]));
    
		$conditions .= " and username = '" . $requestData['username']. "' ";
	}

  	if(!empty($conditions))
	{
		$query .= " where " . substr($conditions, 5);
	}

	//set order
	if(!isset($options['count_query']))
	{
		$query .= "order by ";
		if(isset($options['order']))
		{
		$query .= $options['order'];
		} 
		else
		{
		$query .= "user_id asc";
		}
	}

	//set limit
	if(isset($options['limit']) && !empty($options['limit']))
	{
		$query .= " limit " . intval($options['limit']);
	}

	if(isset($options['echo_query']) && $options['echo_query'])
	{
		echo "Q: ".$query."<br>\t\n";
	}

	$sql = pg_query($query);
	$totalRow_userinfo = pg_num_rows($sql);
	
	$res = [];

	if(!empty($totalRow_userinfo))
	{
		$row_userinfo = pg_fetch_assoc($sql);
		foreach($row_userinfo as $column => $columnData) {
		// var_dump($row_userinfo);
		$res[$column] = $columnData;
		}
	}

	return $res;
}

function dbUsersColumnNames()
{
	return array(
		"user_id", "name", "surname", "email", "username", "password", "image", "verified", "birthday", "signup_date", "rank", "status", "status_by_admin"
	);
}

function listings($columns = "*", $requestData = array(), array $options = []) : array
{
  	if(empty($columns))
	{
		$columns = "*";
	}

	if($columns != "*" && !is_array($columns))
	{
		$columns = "*";
	}

	$queryColumnNames = "*";
	if(is_array($columns))
	{
		$queryColumnNames = "";
		$validColumns = dbListingsColumnNames();

		foreach ($columns as $column) 
		{
			if(in_array($column, $validColumns))
			{
				$queryColumnNames .= $column.",";
			}
		}

		if(empty($queryColumnNames))
		{
			return [];
		}
		
		$queryColumnNames = substr($queryColumnNames, 0, -1);
	}

  	$query = "select $queryColumnNames ";

  	if(isset($options['count_query']) && $options['count_query'])
	{
		$query = "select count(*) ";
	}

	$query .= "from listings ";

  	//check other conditions
	$conditions = "";

  	if(isset($requestData['lid']) && !empty($requestData['lid']))
	{
		$conditions .= " and lid = " . $requestData['lid']. ' ';
	}

  	if(isset($requestData['user_id']) && !empty($requestData['user_id']))
	{
		$conditions .= " and user_id = " . $requestData['user_id']. ' ';
	}

  	if(!empty($conditions))
	{
		$query .= " where " . substr($conditions, 5);
	}

	//set order
	if(!isset($options['count_query']))
	{
		$query .= "order by ";
		if(isset($options['order']))
		{
		$query .= $options['order'];
		} 
		else
		{
		$query .= "lid asc";
		}
	}

	//set limit
	if(isset($options['limit']) && !empty($options['limit']))
	{
		$query .= " limit " . intval($options['limit']);
	}

	if(isset($options['echo_query']) && $options['echo_query'])
	{
		echo "Q: ".$query."<br>\t\n";
	}

	$sql = pg_query($query);
	$total_listings = pg_num_rows($sql);

	$res = [];

	if(isset($options['count_query'])) {
		$postRatecount = pg_fetch_assoc($sql);

		foreach($postRatecount as $columnName => $columnValue) {
		$res[$columnName] = $columnValue;
		}

		return $res;
	}

	if(!empty($total_listings))
	{
		$row_listings = pg_fetch_all($sql);

		foreach($row_listings as $item)
		{
		$res [] = [
			'listingsId'  => $item['lid'],
			'userId'      => $item['user_id'],
			'public'      => $item['public'],
			'listName'    => $item['list_name'],
			'listDate'    => $item['list_date']
		];
		}
	}

	return $res;
}

function dbListingsColumnNames()
{
	return array(
		"lid", "user_id", "list_name", "public", "list_date"
	);
}

function song_data($columns = "*", $requestData = array(), array $options = []) : array
{
  	if(empty($columns))
	{
		$columns = "*";
	}

	if($columns != "*" && !is_array($columns))
	{
		$columns = "*";
	}

	$queryColumnNames = "*";
	if(is_array($columns))
	{
		$queryColumnNames = "";
		$validColumns = dbMusicListColumnNames();

		foreach ($columns as $column) 
		{
			if(in_array($column, $validColumns))
			{
				$queryColumnNames .= $column.",";
			}
		}

		if(empty($queryColumnNames))
		{
			return [];
		}
		
		$queryColumnNames = substr($queryColumnNames, 0, -1);
	}

	$query = "select $queryColumnNames ";

  	if(isset($options['count_query']) && $options['count_query'])
	{
		$query = "select count(*) ";
	}

	$query .= "from song ";

  	// check other conditions
	$conditions = "";

  	if(isset($requestData['sid']) && !empty($requestData['sid']))
	{
		$conditions .= " and sid in (" . $requestData['sid']. ") ";
	}

	if(isset($requestData['search']) && !empty($requestData['search']))
	{
		$conditions .= " and artist like '%" . $requestData['search']. "%' or song_name like '%" . $requestData['search']. "%' ";
	}

  	if(isset($requestData['artist']) && !empty($requestData['artist']))
	{
		$conditions .= " and artist like '%" . $requestData['artist']. "%' ";
	}

  	if(isset($requestData['song_name']) && !empty($requestData['song_name']))
	{
		$conditions .= " and song_name like '%" . $requestData['song_name']. "%' ";
	}

  	if(isset($requestData['user_id']) && !empty($requestData['user_id']))
	{
		$conditions .= " and user_id = " . $requestData['user_id']. ' ';
	}

  	if(isset($requestData['lid']) && !empty($requestData['lid']))
	{
		$conditions .= " and list_id = " . $requestData['lid']. ' ';
	}

  	if(!empty($conditions))
	{
		$query .= " where " . substr($conditions, 5);
	}

	// set order
	// Ex: read_log('*', $requestData, ['order' => 'log_id desc']);
	if(!isset($options['count_query']))
	{
		$query .= "order by ";
		if(isset($options['order']))
		{
		$query .= $options['order'];
		} 
		else
		{
		$query .= "sid asc";
		}
	}

	// set limit
	if(isset($options['limit']) && !empty($options['limit']))
	{
		$query .= " limit " . intval($options['limit']);
	}

	// ex. $suggestions = followers('*', $requestMyList, ['echo_query' => true]);
	if(isset($options['echo_query']) && $options['echo_query'])
	{
		echo "Q: ".$query."<br>\t\n";
	}

	$sql = pg_query($query);
	$totalRow_request = pg_num_rows($sql);
	
	$res = [];

	if(isset($options['count_query'])) {
		$logcount = pg_fetch_assoc($sql);

		foreach($logcount as $columnName => $columnValue) {
		$res[$columnName] = $columnValue;
		}

		return $res;
	}

	if(!empty($totalRow_request))
	{
		$row_request = pg_fetch_all($sql);
		
		foreach($row_request as $columnData)
		{  
		$res [] = [
			'songId'        => $columnData['sid'],
			'userId'        => $columnData['user_id'],
			'listId'        => $columnData['list_id'],
			'cover'         => $columnData['cover'],
			'artist'        => $columnData['artist'],
			'songName'      => $columnData['song_name'],
			'fileName'      => $columnData['file_name'],
			'gender'        => $columnData['gender'],
			'report'        => $columnData['report'],
			'public'        => $columnData['public'],
			'songDate'      => $columnData['song_date']
		];
		}
	}

	return $res;
}

function dbMusicListColumnNames()
{
	return array(
		"sid", "user_id", "list_id", "cover", "artist", "song_name", "gender", "report", "public", "song_date"
	);
}

function song_list(int $songId) : array
{
	$query_songData = "SELECT * FROM song WHERE sid = $songId";
	$sql = pg_query($query_songData);
	$totalRow_request = pg_num_rows($sql);

	$res = [
		'songId'        => false,
		'userId'        => false,
		'listId'        => false,
		'cover'         => false,
		'artist'        => false,
		'songName'      => false,
		'fileName'      => false,
		'gender'        => false,
		'report'        => false,
		'public'        => false,
		'songDate'      => false
	];

	if(!empty($totalRow_request))
	{
		$columnData = pg_fetch_assoc($sql);

		$res = [
			'songId'        => $columnData['sid'],
			'userId'        => $columnData['user_id'],
			'listId'        => $columnData['list_id'],
			'cover'         => $columnData['cover'],
			'artist'        => $columnData['artist'],
			'songName'      => $columnData['song_name'],
			'fileName'      => $columnData['file_name'],
			'gender'        => $columnData['gender'],
			'report'        => $columnData['report'],
			'public'        => $columnData['public'],
			'songDate'      => $columnData['song_date']
		];
	}

	return $res;
}

function playlist_details($columns = "*", $requestData = array(), array $options = []) : array
{
  	if(empty($columns))
	{
		$columns = "*";
	}

	if($columns != "*" && !is_array($columns))
	{
		$columns = "*";
	}

	$queryColumnNames = "*";
	if(is_array($columns))
	{
		$queryColumnNames = "";
		$validColumns = dbListDetailsColumnNames();

		foreach ($columns as $column) 
		{
			if(in_array($column, $validColumns))
			{
				$queryColumnNames .= $column.",";
			}
		}

		if(empty($queryColumnNames))
		{
			return [];
		}
		
		$queryColumnNames = substr($queryColumnNames, 0, -1);
	}

  	$query = "select $queryColumnNames ";

  	if(isset($options['count_query']) && $options['count_query'])
	{
		$query = "select count(*) ";
	}

	$query .= "from playlist ";

  	// check other conditions
	$conditions = "";


  	if(isset($requestData['user_id']) && !empty($requestData['user_id']))
	{
		$conditions .= " and user_id = " . $requestData['user_id']. ' ';
	}

  	if(isset($requestData['lid']) && !empty($requestData['lid']))
	{
		$conditions .= " and list_id = " . $requestData['lid']. ' ';
	}

  	if(!empty($conditions))
	{
		$query .= " where " . substr($conditions, 5);
	}

	// set order
	// Ex: read_log('*', $requestData, ['order' => 'log_id desc']);
	if(!isset($options['count_query']))
	{
		$query .= "order by ";
		if(isset($options['order']))
		{
		$query .= $options['order'];
		} 
		else
		{
		$query .= "pid asc";
		}
	}

	// set limit
	if(isset($options['limit']) && !empty($options['limit']))
	{
		$query .= " limit " . intval($options['limit']);
	}

	// ex. $suggestions = followers('*', $requestMyList, ['echo_query' => true]);
	if(isset($options['echo_query']) && $options['echo_query'])
	{
		echo "Q: ".$query."<br>\t\n";
	}

	$sql = pg_query($query);
	$totalRow_request = pg_num_rows($sql);
	
	$res = [];

	if(isset($options['count_query'])) {
		$logcount = pg_fetch_assoc($sql);

		foreach($logcount as $columnName => $columnValue) {
		$res[$columnName] = $columnValue;
		}

		return $res;
	}

	if(!empty($totalRow_request))
	{
		$row_request = pg_fetch_all($sql);
		
		foreach($row_request as $columnData)
		{  
		$res [] = [
			'pId'          => $columnData['pid'],
			'userId'        => $columnData['user_id'],
			'listId'        => $columnData['list_id'],
			'songId'        => $columnData['song_id'],
			'listDate'      => $columnData['list_date']
		];
		}
	}

  	return $res;
}

function dbListDetailsColumnNames()
{
	return array(
		"pid", "user_id", "list_id", "song_id", "list_date"
	);
}

function select_from($tableName, array $columns = [], array $whereClause = [], array $options = []): array
{
    // Determinar las columnas a seleccionar
    $columnNames = empty($columns) ? '*' : implode(', ', $columns);

    // Construir la cláusula WHERE
    $whereParts = [];
    foreach ($whereClause as $column => $value) {
        $escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($value) . "'";
        $whereParts[] = "$column = $escapedValue";
    }
    $whereClause = empty($whereParts) ? '' : ' WHERE ' . implode(' AND ', $whereParts);

    // Construir la consulta
    $query = "SELECT $columnNames FROM $tableName$whereClause;";

    // Opción para imprimir la consulta
    if (isset($options['echo_query']) && $options['echo_query']) {
        echo "Q: $query<br>\n";
    }

    // Ejecutar la consulta
    $result = pg_query($query);

    if (!$result) {
        return []; // o manejar el error
    }

    // Obtener y devolver los resultados
    return pg_fetch_all($result) ?: [];
}

function insert_into($tableName, array $queryData = [], array $options = []): string
{
    if (empty($queryData)) {
        return 0;
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
        echo "Q: $query<br>\n"; //has to fix
    }

	$result = pg_query($query);

    if ($result && !empty($returningId)) {
        $row = pg_fetch_assoc($result);
        $insertedId = $row[$options['id']];
        return json_encode(["success" => true, "id" => $insertedId, "message" => "Row inserted successfully"]);
    } else {
        return json_encode(["success" => false, "message" => "Error inserting row"]);
    }
}

function update_table($tableName, array $queryData = [], array $whereClause = [], array $options = []): string
{
    if (empty($queryData) || empty($whereClause)) {
        return json_encode(["success" => false, "message" => "Data or condition for update is missing"]);
    }

    $setParts = [];
    foreach ($queryData as $column => $value) {
        $escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($value) . "'";
        $setParts[] = "$column = $escapedValue";
    }
    $setClause = implode(', ', $setParts);

    $whereParts = [];
    foreach ($whereClause as $column => $value) {
        $escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($value) . "'";
        $whereParts[] = "$column = $escapedValue";
    }
    $whereClause = implode(' AND ', $whereParts);

    $query = "UPDATE $tableName SET $setClause WHERE $whereClause;";

    $sql = pg_query($query);

	$response = ["success" => $sql !== false, "message" => $sql ? "Row updated successfully" : "Error updating row"];

	if (isset($options['echo_query']) && $options['echo_query']) {
        $response["query"] = $query;
    }

    return json_encode($response);
}
?>