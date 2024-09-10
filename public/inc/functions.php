<?php

use PhpParser\Node\Expr\Cast\Double;

function checkUniqueEmail(string $email) : bool // esto se puede hacer con la funcion select_from()
{
	$query_ConsultaFuncion = "SELECT email FROM users WHERE email = $email";
	
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

	if(isset($options['count_query']))
	{
		$logcount = pg_fetch_assoc($sql);

		foreach($logcount as $columnName => $columnValue)
		{
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
		"sid", "user_id", "cover", "artist", "song_name", "gender", "report", "public", "song_date"
	);
}

function pick_user($columns = "*", $requestData = array(), array $options = []) : array
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
		$validColumns = dbUserColumnNames();

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

	$query .= "from users ";

  	// check other conditions
	$conditions = "";

	if(isset($requestData['search']) && !empty($requestData['search']))
	{
		$searchTerms = explode(' ', $requestData['search']);
		if(count($searchTerms) == 2) {
			$nameSearch = pg_escape_string($searchTerms[0]);
			$surnameSearch = pg_escape_string($searchTerms[1]);
			$conditions .= " AND (name ILIKE '%$nameSearch%' AND surname ILIKE '%$surnameSearch%') ";
		} else {
			$search = pg_escape_string($requestData['search']);
			$conditions .= " AND (name ILIKE '%$search%' OR surname ILIKE '%$search%') ";
		}
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
			$query .= "user_id asc";
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

	if(isset($options['count_query']))
	{
		$logcount = pg_fetch_assoc($sql);

		foreach($logcount as $columnName => $columnValue)
		{
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
				'user_id'		=> $columnData['user_id'],
				'name'			=> $columnData['name'],
				'surname'		=> $columnData['surname']
			];
		}
	}

	return $res;
}

function dbUserColumnNames()
{
	return array(
		"user_id", "name", "surname", "email", "username", "password", "image", "verified", "birthday", "signup_date", "rank", "status", "status_by_admin"
	);
}

function select_from($tableName, array $columns = [], array $whereClause = [], array $options = []) : array
{
	$columnNames = empty($columns) ? '*' : implode(', ', $columns);

	$whereParts = [];
	foreach ($whereClause as $column => $value) {
		if ($value === '' || $value === null) continue;
		$escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($value) . "'";
		$whereParts[] = "$column = $escapedValue";
	}
	$whereClause = empty($whereParts) ? '' : ' WHERE ' . implode(' AND ', $whereParts);

	$limitClause = '';
	if (isset($options['limit']) && !empty($options['limit']) && is_numeric($options['limit'])) {
		$limitClause = " LIMIT " . intval($options['limit']);
	}

	$orderClause = '';
	if (isset($options['order_by']) && !empty($options['order_by'])) {
		$orderDirection = isset($options['order_direction']) && strtolower($options['order_direction']) == 'desc' ? 'DESC' : 'ASC';
		$orderClause = " ORDER BY " . pg_escape_string($options['order_by']) . " $orderDirection";
	}

	$query = "SELECT $columnNames FROM $tableName$whereClause$orderClause$limitClause;";

	if (isset($options['echo_query']) && $options['echo_query']) {
		echo "Q: $query<br>\n";
	}

	$result = pg_query($query);

	if (!$result) {
		return [];
	}

	if (!empty($options['fetch_first'])) {
        return pg_fetch_assoc($result) ?: [];
    }

	return pg_fetch_all($result) ?: [];
}

function insert_into($tableName, array $queryData = [], array $options = []) : string
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

function update_table($tableName, array $queryData = [], array $whereClause = [], array $options = []) : string
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

function delete_from($tableName, array $whereClause = [], array $options = []) : string
{
    if (empty($whereClause)) {
        return json_encode(["success" => false, "message" => "No conditions specified for deletion."]);
    }

    $whereParts = [];
    foreach ($whereClause as $column => $value) {
        $escapedValue = is_numeric($value) ? $value : "'" . pg_escape_string($value) . "'";
        $whereParts[] = "$column = $escapedValue";
    }
    $whereSQL = implode(' AND ', $whereParts);

    $query = "DELETE FROM $tableName WHERE $whereSQL;";

    if (isset($options['echo_query']) && $options['echo_query']) {
        echo "Q: $query<br>\n"; //has to fix
    }

    $result = pg_query($query);

    if ($result) {
        return json_encode(["success" => true, "message" => "Rows deleted successfully."]);
    } else {
        return json_encode(["success" => false, "message" => "Error deleting rows."]);
    }
}

function favorite_list_cover($favoriteListId) : string
{
	$query = "SELECT * FROM playlist WHERE list_id = $favoriteListId";
	$sql = pg_query($query);
	
	$listElement = [];
	while ($row_playlistId = pg_fetch_assoc($sql)) {
		$listElement[] = $row_playlistId['song_id'];
	}

	if (empty($listElement)) {
		return '';
	}

	$songIds = implode(',', $listElement);

	$query2 = "SELECT * FROM song WHERE sid IN ($songIds) ORDER BY sid ASC";
	$sql2 = pg_query($query2);

	$songs = [];
	while ($row_song = pg_fetch_assoc($sql2)) {
		$songs[] = $row_song['cover'];
	}

	return !empty($songs) && isset($songs[0]) ? $songs[0] : '';
}


function getUpdatedFavListContent() {
    $requestData['user_id'] = !isset($_SESSION['mp_UserId']) ? null : $_SESSION['mp_UserId'];
	$favoriteLists = select_from('favorite_lists', [], $requestData);
	
    $output = '';
	if (!empty($favoriteLists)) {
		foreach ($favoriteLists as $playlist) {
			if (isset($playlist['list_id'])) {
				$listData = select_from('listings', [], ['lid' => $playlist['list_id']]);
				foreach ($listData as $listDetail) {
					$listOwner = select_from('users', ['user_id', 'name', 'surname'], ['user_id' => $listDetail['user_id']]);
					$listCover = favorite_list_cover($listDetail['lid']);
						// $output .= '<div class="fav-list" data-favlist="' . $playlist['lid'] . '">';
						$output .= '<div class="fav-list" data-favlist="">';
							$output .= '<div class="list-cover">';
								if (!empty($listCover)) {
									$output .= '<img src="images/cover/' . $listCover . '">';
								}
								$output .= '<div class="list-options">';
									$output .= '<ul>';
										$output .= '<li>';
											$output .= '<a href="#" class="playlist-mini-menu">...</a>'; // <-- AQUI
											$output .= '<div class="playlist-options">';
												$output .= '<ul>';
													$output .= '<li class="album-dislike" data-albumId="' . $listDetail['lid'] . '">Dislike</li>';
												$output .= '</ul>';
											$output .= '</div>';
										$output .= '</li>';
										// $output .= '<li>option</li>';
									$output .= '</ul>';
								$output .= '</div>';
							$output .= '</div>';
							$output .= '<div class="list-info">';
								$output .= '<div class="list-name">' . $listDetail['list_name'] . '</div>';
								$output .= '<div class="list-owner" data-ownerId="' . $listOwner[0]['user_id'] . '">' . $listOwner[0]['name'].' '.$listOwner[0]['surname'] . '</div>';
							$output .= '</div>';
						$output .= '</div>';
				}
			}
		}
	} else {
		$output .= "<p class='frame-central'>You don't have any list yet</p>";
	}

	return $output;
}
?>