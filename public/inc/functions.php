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
		"user_id", "name", "surname", "email", "username", "password", "image", "rate", "job", "verified", "birthday", "signup_date", "rank", "status", "status_by_admin"
	);
}

function get_followers_and_following($userId) : array
{
  $query_followers = "SELECT count(is_following) FROM followers WHERE is_following = $userId AND accepted = 1";
  $sql = pg_query($query_followers);
  $followers = pg_fetch_assoc($sql);

  $query_following = "SELECT count(user_id) FROM followers WHERE user_id = $userId AND accepted = 1";
  $sql = pg_query($query_following);
  $following = pg_fetch_assoc($sql);

  return [
    'followers'   => $followers['count'],
    'following'   => $following['count']
  ];
}

function count_posts($userId) : array
{
  $query_count_post = "SELECT count(user_id) FROM river WHERE user_id = $userId";
  $sql = pg_query($query_count_post);
  $count_post = pg_fetch_assoc($sql);

  return [
    'allpost'   => $count_post['count']
  ];
}

function following_control(int $userId, int $myId) : array
{
  $query_following_control = "SELECT * FROM followers WHERE user_id = $userId AND is_following = $myId";
  $sql = pg_query($query_following_control);
  $totalRow_following = pg_num_rows($sql);

  $res = [
    'accepted'    => false,
    'existing'    => false
  ];

  if (empty($totalRow_following)) {
    return $res;
  }

  $following_control = pg_fetch_assoc($sql);
  
  $res = [
    'accepted'    => $following_control['accepted'] == 1 ? true : false,
    'existing'    => $totalRow_following > 0 && $following_control['accepted'] != 2 ? true : false
  ];

  return $res;
}

function follow_request(int $myId, int $userId)
{
  if (!following_control($myId, $userId)['existing']) {
    $requestData['user_id'] = $userId;

    $status = u_all_info("*", $requestData)['status'] == 1 ? 1 : 0; 

    $follow_date = date("Y-m-d H:i:s");

    $query = "INSERT INTO followers (user_id, is_following, accepted, follow_date) values ($myId, $userId, $status, '$follow_date')";
	  $sql = pg_query($query);
  } else {
    $sql = false;
  }

  return $sql;
}

function follow_confirm(int $myId, int $userId, int $confirm)
{
  if (!following_control($userId, $myId)['accepted']) {
    $query = "UPDATE followers SET accepted = $confirm WHERE user_id = $userId AND is_following = $myId";
    $sql = pg_query($query);
  } else {
    $sql = false;
  }

  return $sql;
}

function unfollow(int $myId, int $userId)
{
  if (following_control($myId, $userId)['existing']) {
    $query_unfollow = "DELETE FROM followers WHERE user_id = $myId AND is_following = $userId AND accepted = 1";
    $sql = pg_query($query_unfollow);
  } else {
    $sql = false;
  }

  return $sql;
}

function remove_request(int $myId, int $userId)
{
  if (following_control($myId, $userId)['existing']) {
    $query_unfollow = "DELETE FROM followers WHERE user_id = $userId AND is_following = $myId AND accepted = 0";
    $sql = pg_query($query_unfollow);
  } else {
    $sql = false;
  }

  return $sql;
}

function log_checked(int $myId, $userId = null , int $status)
{
  if ($userId == null) {
    $query = "UPDATE log SET checked = $status WHERE to_userid = $myId AND checked = 0";
  }
  else
  {
    $query = "UPDATE log SET checked = $status WHERE from_userid = $userId AND to_userid = $myId";
  }
  $sql = pg_query($query);

  return $sql;
}

function post_all_data(int $postId) : array
{
  $query_postalldata = "SELECT * FROM river WHERE r_id = $postId";
  $sql = pg_query($query_postalldata);

  $res = [
    'postId'      => false,
    'userId'      => false,
    'content'     => false,
    'status'      => false,
    'postcount'   => false
  ];

  $row_postalldata = pg_fetch_assoc($sql);
  $totalRows_postalldata = pg_num_rows($sql);

  $res = [
    'postId'      => $row_postalldata['r_id'],
    'userId'      => $row_postalldata['user_id'],
    'content'     => $row_postalldata['content'],
    'status'      => $row_postalldata['status'],
    'postcount'   => $totalRows_postalldata
  ];

  return $res;
}

function followers($columns = "*", $requestData = array(), array $options = []) : array
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
		$validColumns = dbFollowersColumnNames();

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

	$query .= "from followers ";

  // check other conditions
	$conditions = "";



  if(isset($requestData['user_id']) && !empty($requestData['user_id']))
	{
		$conditions .= " and user_id = " . $requestData['user_id']. ' ';
	}

  if(isset($requestData['is_following']) && !empty($requestData['is_following']) && is_array($requestData['is_following']))
  {
    $selectedData = json_encode($requestData['is_following']);
    $selectedData = substr($selectedData, 1, -1);
    
    $conditions .= " and is_following in (" . $selectedData. ') ';
  }
  else if(isset($requestData['is_following']) && !empty($requestData['is_following']) && !is_array($requestData['is_following']))
	{
		$conditions .= " and is_following = " . $requestData['is_following']. ' ';
	}

  if(isset($requestData['isnt_user_id']) && !empty($requestData['isnt_user_id']))
	{
		$conditions .= " and user_id != " . $requestData['isnt_user_id']. ' ';
    $conditions .= " and is_following not in (select user_id from followers where user_id = " . $requestData['isnt_user_id']. ') ';
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
      $query .= "follow_id asc";
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
  $totalRow_followers = pg_num_rows($sql);
  
  $res = [];

  if(isset($options['count_query'])) {
    $logcount = pg_fetch_assoc($sql);

    foreach($logcount as $columnName => $columnValue) {
      $res[$columnName] = $columnValue;
    }

    return $res;
  }

  if(!empty($totalRow_followers))
  {
    $row_followers = pg_fetch_all($sql);
    
    foreach($row_followers as $columnData)
    {  
      $res [] = [
        'followId'        => $columnData['follow_id'],
        'userId'          => $columnData['user_id'],
        'isFollowing'     => $columnData['is_following'],
        'accepted'        => $columnData['accepted'],
        'condition'       => $columnData['condition'],
        'followDate'      => $columnData['follow_date']
      ];
    }
  }

  return $res;
}

function dbFollowersColumnNames()
{
	return array(
		"follow_id", "user_id", "is_following", "accepted", "condition", "follow_date"
	);
}

function followers_list(int $userId)
{
  $query = "SELECT * FROM followers WHERE user_id = $userId";
  $sql = pg_query($query);

  $totalRows_followersList = pg_num_rows($sql);
  
  $res = [];
  
  if(!empty($totalRows_followersList))
  {
      $row_followersList = pg_fetch_all($sql);

      foreach($row_followersList as $item)
      {
        $res [] = (int)$item['is_following'];
      }
  }
  
  return $res;
}

function post_wall_profile($userId = null) : array
{
  if(!empty($userId) && is_array($userId))
  {
    $selectedData = json_encode($userId);
    $selectedData = substr($selectedData, 1, -1);
    
    $query_postwall = "SELECT * FROM river WHERE user_id in ($selectedData) ORDER BY r_id DESC";
  }
  else if(!empty($userId) && !is_array($userId))
  {
    $query_postwall = "SELECT * FROM river WHERE user_id = $userId ORDER BY r_id DESC";
  }
  else
  {
    $query_postwall = "SELECT * FROM river ORDER BY r_id DESC";
  }

  $sql = pg_query($query_postwall);
  $totalRows_postwall = pg_num_rows($sql);

  $res = [];

  if(!empty($totalRows_postwall))
  {
    $row_postwall = pg_fetch_all($sql);

    foreach($row_postwall as $item)
    {
      $res [] = [
        'postId'      => $item['r_id'],
        'userId'      => $item['user_id'],
        'content'     => $item['content'],
        'status'      => $item['status']
      ];
    }
  }

  return $res;
}

function rate_in_post($columns = "*", $requestData = array(), array $options = []) : array
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
		$validColumns = dbRatesColumnNames();

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

	$query .= "from rates ";

  //check other conditions
	$conditions = "";

  if(isset($requestData['user_id']) && !empty($requestData['user_id']))
	{
		$conditions .= " and user_id = " . $requestData['user_id']. ' ';
	}

  if(isset($requestData['post_id']) && !empty($requestData['post_id']))
	{
		$conditions .= " and post_id = " . $requestData['post_id']. ' ';
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
      $query .= "rate_id asc";
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
  $totalPost_rates_list = pg_num_rows($sql);

  $res = [];

  if(isset($options['count_query'])) {
    $postRatecount = pg_fetch_assoc($sql);

    foreach($postRatecount as $columnName => $columnValue) {
      $res[$columnName] = $columnValue;
    }

    return $res;
  }

  if(!empty($totalPost_rates_list))
  {
    $row_postRates_list = pg_fetch_all($sql);

    foreach($row_postRates_list as $item)
    {
      $res [] = [
        'rateId'      => $item['rate_id'],
        'stars'       => $item['stars'],
        'rateBonus'   => $item['rate_bonus'],
        'userId'      => $item['user_id'],
        'postId'      => $item['post_id'],
        'commentId'   => $item['comment_id'],
        'rateDate'    => $item['rate_date']
      ];
    }
  }

  return $res;
}

function dbRatesColumnNames()
{
	return array(
		"rate_id", "stars", "rate_bonus", "user_id", "to_user_id", "post_id", "comment_id", "rate_date"
	);
}

function add_comments($userId, $postId, $comment, $comment_date)
{
  $query = "INSERT INTO comments (user_id, post_id, comment, comment_date) values ($userId, $postId, '$comment', '$comment_date')";
	$sql = pg_query($query);
		
	return $sql;
}

function comment_in_post($columns = "*", $requestData = array(), array $options = []) : array
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
		$validColumns = dbCommentsColumnNames();

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

	$query .= "from comments ";

  //check other conditions
	$conditions = "";

  if(isset($requestData['comment_id']) && !empty($requestData['comment_id']))
	{
		$conditions .= " and comment_id = " . $requestData['comment_id']. ' ';
	}

  if(isset($requestData['post_id']) && !empty($requestData['post_id']))
	{
		$conditions .= " and post_id = " . $requestData['post_id']. ' ';
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
      $query .= "comment_id desc";
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
  $totalRows_postcomment = pg_num_rows($sql);
  
  $res = [];
  
  if(!empty($totalRows_postcomment))
  {
    $row_postcomment = pg_fetch_all($sql);

    foreach($row_postcomment as $item)
    {
      $res [] = [
          'userId'          => $item['user_id'],
          'postId'          => $item['post_id'],
          'comment'         => $item['comment'],
          'commentDate'     => $item['comment_date']
          // 'totalComments'   => $totalRows_postcomment
      ];
    }
  }

  return $res;
}

function count_comments($columns = "*", $requestData = array()) : int
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
		$validColumns = dbCommentsColumnNames();

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

	$query .= "from comments ";

  //check other conditions
	$conditions = "";

  if(isset($requestData['comment_id']) && !empty($requestData['comment_id']))
	{
		$conditions .= " and comment_id = " . $requestData['comment_id']. ' ';
	}

  if(isset($requestData['post_id']) && !empty($requestData['post_id']))
	{
		$conditions .= " and post_id = " . $requestData['post_id']. ' ';
	}

  if(!empty($conditions))
	{
		$query .= " where " . substr($conditions, 5);
	}

  $sql = pg_query($query);
  $totalPost_rates_list = pg_num_rows($sql);

  return $totalPost_rates_list;
}

function dbCommentsColumnNames()
{
	return array(
		"comment_id", "user_id", "post_id", "comment", "comment_date"
	);
}

function add_rate(int $userId, int $stars, $postId = null)
{
  $requestUserData['user_id'] = $userId;

  $rate      = u_all_info('*', $requestUserData);
  $rateBonus = rate_bonus($stars, $rate['rate']);
  $rateDate  = date("Y-m-d H:i:s");

  $query = "INSERT INTO rates (user_id, stars, rate_bonus, post_id, rate_date) VALUES ($userId, $stars, $rateBonus, $postId, '$rateDate') RETURNING rate_bonus";
	$sql = pg_query($query);

  $ultimo = pg_fetch_row($sql);
		
	return $ultimo;
}

//update exemple
function rate_update($requestData = array(), $rateData = array(), $rateData2 = array())
{
	if(empty($requestData) || empty($rateData))
	{
		return false;
	}

	if(!isset($rateData['rate_id']))
	{
		return false;
	}

	if(empty($rateData['rate_id']))
	{
		return false;
	}
	
	$validColumns = dbRatesColumnNames();
	unset($validColumns['rate_id']);
	
	//validate requestData
	foreach ($requestData as $keyColumn => $value) 
	{
		if(!in_array($keyColumn, $validColumns))
		{
			unset($requestData[$keyColumn]);
		}

		if(isset($rateData[$keyColumn]) && $value === $rateData[$keyColumn])
		{
			unset($requestData[$keyColumn]);
		}
	}
	if(empty($requestData))
	{
		return false;
	}
	
	$queryParams = "";
	foreach ($requestData as $keyColumn => $value) 
	{
		$queryParams .= $keyColumn."='".pg_escape_string($value)."',";
	}

	if(empty($queryParams))
	{
		return false;
	}

	$query = "UPDATE rates SET ". substr($queryParams, 0, -1) ." WHERE post_id=".$rateData['post_id']." AND user_id=".$rateData2['user_id'];
	
	$sql = pg_query($query);

	return $sql;
}

function rate_star($rateData, $star)
{
    if ($rateData >= $star)
    {
        return 'star_checked';
    }
    else
    {
        return '';
    }
}

function rate_bonus($stars, $rate)
{
  if($rate == 0 || $rate == '') {
    return $stars * 0.5 / 9999;
  }
  else
  {
    return $stars * $rate / 9999;
  }
}

function update_user_rate($stars, $returRateBonus, $postId)
{
  $postUser = (int)post_all_data($postId)['userId'];
  $requestUserData['user_id'] = $postUser;

  $userRate = (float)u_all_info('*', $requestUserData)['rate'];

  if($stars < 3)
  {
    $res = $userRate - $returRateBonus;
  }
  else if($stars >= 3)
  {
    $res = $userRate + $returRateBonus;
  }

  $query = "UPDATE users SET rate = $res WHERE user_id = $postUser";
  $sql = pg_query($query);

  return $sql;
}

function create_new_post(int $userId, string $content, $status = null) 
{
  $postDate  = date("Y-m-d H:i:s");

  $query = "INSERT INTO river (user_id, content, status, post_date) VALUES ($userId, '$content', $status, '$postDate') RETURNING r_id";
	$sql = pg_query($query);

  $return_post_id = pg_fetch_row($sql);
		
	return $return_post_id[0];
}

function add_post_media($insertValues)
{
	$inserQuery = '';

	foreach($insertValues as $value)
	{
		$inserQuery .= "({$value['userId']}, {$value['postId']}, '{$value['name']}', '{$value['mediaDate']}')";
		if (count($insertValues) > 1)
		{
			$inserQuery .= ",";
		}
	}

	if (count($insertValues) > 1) {
		$inserQuery = substr($inserQuery, 0, -1);
	}

  $query = "INSERT INTO media (user_id, post_id, name, media_date) VALUES $inserQuery";
	$sql = pg_query($query);

  return $sql;
}

function show_post_images(int $postId) : array
{
  $query = "SELECT * FROM media WHERE post_id = $postId ORDER BY media_id DESC";
  $sql = pg_query($query);
  $totalRows_postmedia = pg_num_rows($sql);
  
  $res = [];
  
  if(!empty($totalRows_postmedia))
  {
      $row_postmedia = pg_fetch_all($sql);

      foreach($row_postmedia as $item)
      {
        $res [] = [
            'name'        => $item['name'],
            'format'      => $item['format'],
            'total_pic'   => $totalRows_postmedia
        ];
      }
  }
  
  return $res;
}

function search_users() : array
{
  $query_userInfo = "SELECT * FROM users ORDER BY user_id ASC";
  $sql = pg_query($query_userInfo);
  $totalRows_userInfo = pg_num_rows($sql);
  
  $res = [];
  
  if(!empty($totalRows_userInfo))
  {
      $row_userinfo = pg_fetch_all($sql);
 
      foreach($row_userinfo as $item)
      {
        $res [] = [
            'id'          => $item['user_id'],
            'name'        => $item['name'],
            'surname'     => $item['surname'],
            'image'       => $item['image'],
            'email'       => $item['email'],
            'username'    => $item['username'],
            'rate'        => $item['rate'],
            'job'         => $item['job']
        ];
      }
  }
  
  return $res;
}

function profileRateInPost($rateData) {
  if ($rateData !== 'null' && strlen($rateData) == 1) {
    $desimalpri = $rateData.'.0';
    // $desimalsec = '00';
  }
  else if ($rateData !== 'null' && strlen($rateData) > 1 && strlen($rateData) < 4) {
    $desimalpri = substr($rateData, 0, 3);
    // $desimalsec = '00';
  }
  else if ($rateData !== 'null' && strlen($rateData) == 4) {
    $desimalpri = substr($rateData, 0, 3);
    // $desimalsec = substr($rateData, 3, 4)+'0';
  }
  else if ($rateData !== 'null' && strlen($rateData) > 4) {
    $desimalpri = substr($rateData, 0, 3);
    // $desimalsec = substr($rateData, 3, 5);
  }

  return $desimalpri;
}

function read_log($columns = "*", $requestData = array(), array $options = []) : array
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
		$validColumns = dbLogColumnNames();

		foreach ($columns as $column) 
		{
			if(in_array($column, $validColumns))
			{
				$queryColumnNames .= $column.", ";
			}
		}

		if(empty($queryColumnNames))
		{
			return [];
		}
		
		$queryColumnNames = substr($queryColumnNames, 0, -2);
	}

  $query = "select $queryColumnNames ";

  if(isset($options['count_query']) && $options['count_query'])
	{
    if(isset($options['group_by']))
    {
      $query = "select count(distinct from_userid) ";
    }
    else
    {
      $query = "select count(*) ";
    }
	}

	$query .= "from log ";

  // check other conditions
	$conditions = "";

  if(isset($requestData['from_userid']) && !empty($requestData['from_userid']))
	{
		$conditions .= " and from_userid = " . $requestData['from_userid']. ' ';
	}

  if(isset($requestData['action']) && !empty($requestData['action']))
	{
    // rate-post
    // rate-comment
    // comment
    // answer-comment
    // follow-request
    // follow

    $requestData['action'] = pg_escape_string(trim($requestData["action"]));
    
		$conditions .= " and action = '" . $requestData['action']. "' ";
	}

  if(isset($requestData['obj_id']) && !empty($requestData['obj_id']))
	{
		$conditions .= " and obj_id = " . $requestData['obj_id']. ' ';
	}

  if(isset($requestData['to_userid']) && !empty($requestData['to_userid']))
	{
		$conditions .= " and to_userid = " . $requestData['to_userid']. ' ';
	}

  if(isset($requestData['checked']))
	{
    if($requestData['checked'] == 2) 
    {
      $conditions .= " and checked != 2 ";
    }
    else
    {
		  $conditions .= " and checked = " . $requestData['checked']. ' ';
    }
	}

  if(isset($requestData['log_date']) && !empty($requestData['log_date']))
	{
    $requestData['log_date'] = pg_escape_string(trim($requestData["log_date"]));
    
		$conditions .= " and log_date = '" . $requestData['log_date']. "' ";
	}

  if(!empty($conditions))
	{
		$query .= " where " . substr($conditions, 5);
	}

  if(isset($options['group_by']))
  {
    $query .= " group by " . $queryColumnNames. ' ';
  }

  // set order
  // Ex: read_log('*', $requestData, ['order' => 'log_id asc']);
  if(!isset($options['count_query']))
  {
    $query .= "order by ";
    if(isset($options['order']))
    {
      $query .= $options['order'];
    } 
    else
    {
      $query .= "log_id asc";
    }
  }

  // set limit
  if(isset($options['limit']) && !empty($options['limit']))
  {
    $query .= " limit " . intval($options['limit']);
  }

  if(isset($options['echo_query']) && $options['echo_query'])
  {
    echo "Q: ".$query."<br>\t\n";
  }

  $sql = pg_query($query);
  $totalRow_loginfo = pg_num_rows($sql);
  
  $res = [];

  if(isset($options['count_query'])) {
    $logcount = pg_fetch_assoc($sql);
    if(!$logcount)
    {
      return ['count' => 0];
    }
    foreach($logcount as $columnName => $columnValue) {
      $res[$columnName] = $columnValue;
    }

    return $res;
  }

  if(!empty($totalRow_loginfo)) {
    $row_loginfo = pg_fetch_all($sql);

    foreach($row_loginfo as $column => $columnData) {
      $res[$column] = $columnData;
    }
  }

  return $res;
}

function dbLogColumnNames() 
{
  return array(
		"log_id", "from_userid", "action", "obj_id", "to_userid", "commentary", "checked", "log_date"
	);
}

// function remove_from_log(string $action, int $userId, int $myId)
// {
//   $query_removeLog = "DELETE FROM log WHERE action = $action AND from_userid = $userId AND to_userid = $myId";
//   $sql = pg_query($query_removeLog);

//   return $sql;
// }

function brind_post_preview($post_id) : array
{
  $query_postPreview = "SELECT * FROM media WHERE post_id = $post_id ORDER BY media_id DESC";
  $sql = pg_query($query_postPreview);
  $totalRow_postPreview = pg_num_rows($sql);

  $res = [
    'preview'    => false
  ];

  if (empty($totalRow_postPreview)) {
    return $res;
  }

  $preview = pg_fetch_assoc($sql);
  
  $res = [
    'preview'   => $preview['name']
  ];

  return $res;
}

function insert_log(int $fromUserid, string $action, int $objId, int $toUserid, $commentary = null, int $checked)
{
  // rate-post
  // rate-comment
  // comment
  // answer-comment
  // follow-request
  // follow
  
  $log_date = date("Y-m-d H:i:s");

  $query = "INSERT INTO log (from_userid, action, obj_id, to_userid, commentary, checked, log_date) VALUES ($fromUserid, '$action', $objId, $toUserid, '$commentary', $checked, '$log_date')";
	$sql = pg_query($query);
		
	return $sql;
}

function cipherSimplifyer($my_followers) {
  // $my_followers = $my_followers.toString();
      
  if(strlen($my_followers) >= 5 && strlen($my_followers) < 6)
  {
      return substr($my_followers, 0, 2).''._simplifierAfterPoint(substr($my_followers, 2, 5)).'K';
  }
  else if(strlen($my_followers) >= 6 && strlen($my_followers) < 7)
  {
      return substr($my_followers, 0, 3).''._simplifierAfterPoint(substr($my_followers, 3, 6)).'K';
  }
  else if(strlen($my_followers) >= 7 && strlen($my_followers) < 8)
  {
      return substr($my_followers, 0, 1).''._simplifierAfterPoint(substr($my_followers, 1, 7)).'M';
  }
  else if(strlen($my_followers) >= 8 && strlen($my_followers) < 9)
  {
      return substr($my_followers, 0, 2).''._simplifierAfterPoint(substr($my_followers, 1, 8)).'M';
  }
  else if(strlen($my_followers) >= 9 && strlen($my_followers) < 10)
  {
      return substr($my_followers, 0, 3).''._simplifierAfterPoint(substr($my_followers, 1, 9)).'M';
  }
  else
  {
      return $my_followers;
  }
}

function _simplifierAfterPoint($my_followers) {

  if(($my_followers > 99 && $my_followers < 200) || ($my_followers > 99999 && $my_followers < 200000)){
      return '.'+1;
  }
  else if(($my_followers > 199 && $my_followers < 300) || ($my_followers > 199999 && $my_followers < 300000)){
      return '.'+2;
  }
  else if(($my_followers > 299 && $my_followers < 400) || ($my_followers > 299999 && $my_followers < 400000)){
      return '.'+3;
  }
  else if(($my_followers > 399 && $my_followers < 500) || ($my_followers > 399999 && $my_followers < 500000)){
      return '.'+4;
  }
  else if(($my_followers > 499 && $my_followers < 600) || ($my_followers > 499999 && $my_followers < 600000)){
      return '.'+5;
  }
  else if(($my_followers > 599 && $my_followers < 700) || ($my_followers > 599999 && $my_followers < 700000)){
      return '.'+6;
  }
  else if(($my_followers > 699 && $my_followers < 800) || ($my_followers > 699999 && $my_followers < 800000)){
      return '.'+7;
  }
  else if(($my_followers > 799 && $my_followers < 900) || ($my_followers > 799999 && $my_followers < 900000)){
      return '.'+8;
  }
  else if(($my_followers > 899 && $my_followers < 1000) || ($my_followers > 899999 && $my_followers < 1000000)){
      return '.'+9;
  }
  else
  {
      return '';
  }
}
?>