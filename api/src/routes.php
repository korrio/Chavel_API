<?php
$app->group('/v1', function () use ($app) {
	$app->get('/user/search/[{query}]', function ($request, $response, $args) {
		$input = $request->getQueryParams();
		$field = "";
		$where = "";
		if(!empty($input["by"])){
			$field=",(SELECT count(*) FROM cha_follow WHERE follow_user_id=a.user_id and user_id= :user_by) as followed";
			$where=" AND a.user_id <> :user_by";
		}
		$result = "";
	    $sth = $this->db->prepare("SELECT a.*".$field." FROM cha_users a WHERE (a.user_name LIKE :user_name or a.user_email LIKE :user_email) ".$where);
	    $query = "%".$args['query']."%";
	    $sth->bindParam("user_name", $query);
	    $sth->bindParam("user_email", $query);
	    $sth->bindParam("user_by", $input["by"]);
	    $sth->execute();
	    $result["list"] = $sth->fetchAll();
	    $result["errors"]=array(
	    			'status' 	=> '200',
	    			'source'	=> '/user/search/'.$args['query'],
	    			'title'		=> 'Success',
	    			'detail'	=> 'Success',
	    			//'sql' => $sth->toSql()
	    		);
	    // not found
	    if(count($result["list"]) == 0 ){
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '400',
	    			'source'	=> '/user/search/'.$args['query'],
	    			'title'		=> 'Not found',
	    			'detail'	=> 'Not found'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});

	$app->post('/updateUser/[{id}]', function ($request, $response, $args) {
	    $input = $request->getParsedBody();
	    $sql = "UPDATE cha_users SET user_name=:user_name,user_email=:user_email,user_phone=:user_phone WHERE user_id=:id";
	    $sth = $this->db->prepare($sql);
	    $sth->bindParam("id", $args['id']);
	   	$sth->bindParam("user_name", $input['user_name']);
	    $sth->bindParam("user_email", $input['user_email']);
	    $sth->bindParam("user_phone", $input['user_phone']);
	    $sth->execute();
	    $input['id'] = $args['id'];
	    $input["errors"]=array(
    			'status' 	=> '200',
    			'source'	=> '/updateUser',
    			'title'		=> 'Success'
    		);
	    return $this->response->withJson($input);
	});

	$app->post('/updateUserImage/[{id}]', function ($request, $response, $args) {
	    $input = $request->getParsedBody();
	    $input['id'] = $args['i
	    d'];
	    $file_name = "user_".$args['id']."_".time().".jpg";
    	$output_file = "../uploads/".$file_name;
    	base64_to_jpeg($input['image_base_64'],$output_file);
    	$create_time = date('Y-m-d H:i:s');

	    $sql = "UPDATE cha_users SET user_image=:user_image WHERE user_id=:id";
	    $sth = $this->db->prepare($sql);
	    $sth->bindParam("id", $input['id']);
	    $sth->bindParam("user_image", $file_name);
	    $sth->execute();
	    $input["file_name"] = $file_name;
	    $input["errors"]=array(
    			'status' 	=> '200',
    			'source'	=> '/updateUserImage',
    			'title'		=> 'Success',
    			'create_time'=> $create_time
    		);
	    unset($input["image_base_64"]);
	    return $this->response->withJson($input);
	});

	$app->delete('/delUser/[{id}]', function ($request, $response, $args) {
	    $sth = $this->db->prepare("DELETE FROM cha_users WHERE user_id=:id");
	    $sth->bindParam("id", $args['id']);
	    $sth->execute();
	    $users = $sth->fetchAll();
	    return $this->response->withJson($users);
	});

	$app->post('/addUser', function ($request, $response) {
		$result = "";
		$input = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_email'])){
	    	$where = "user_email=:user_email";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_email", $input['user_email']);
	    }
	    if(!empty($input['user_phone'])){
	    	$where = "user_phone=:user_phone";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_phone", $input['user_phone']);
	    }

	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)==0){
	    	$sql = "INSERT INTO cha_users (user_name,user_email,user_pass,user_image,user_fb_id,user_phone) VALUES (:user_name,:user_email,:user_pass,:user_image,:user_fb_id,:user_phone)";
		    $sth = $this->db->prepare($sql);
		    $password = md5($input['user_pass']);
		    $sth->bindParam("user_name", 	$input['user_name']);
		    $sth->bindParam("user_email", 	$input['user_email']);
		    $sth->bindParam("user_pass", 	$password);
		    $sth->bindParam("user_image", 	$input["user_image"]);
		    $sth->bindParam("user_fb_id", 	$input['user_fb_id']);
		    $sth->bindParam("user_phone", 	$input['user_phone']);
		    $sth->execute();
		    $result['id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addUser',
    			'title'		=> 'Success',
    			'detail'	=> 'user_id: '.$result['id'].',user_name: '.$input['user_name']
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addUser',
	    			'title'		=> 'Dupplicate user',
	    			'detail'	=> 'Please re-check E-mail or phone'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});

	$app->get('/users', function ($request, $response, $args) {
		$result = "";
	    $sth = $this->db->prepare("SELECT * FROM cha_users");
	    $sth->execute();
	    $result = $sth->fetchAll();
	    // not found
	    if(count($result) == 0 ){
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '400',
	    			'source'	=> '/users',
	    			'title'		=> 'Not found',
	    			'detail'	=> 'Not found'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});

	$app->get('/user/[{id}]', function ($request, $response, $args) {
		$input = $request->getQueryParams();
	    $sth = $this->db->prepare("SELECT a.*,(SELECT count(*) FROM cha_route WHERE user_id = a.user_id and route_publish = 1) as routeCount,(SELECT count(*) FROM cha_follow WHERE follow_user_id = a.user_id ) as followerCount,(SELECT count(*) FROM cha_follow WHERE user_id = a.user_id ) as followingCount FROM cha_users a WHERE a.user_id=:id");
	    $sth->bindParam("id", $args['id']);
	    $sth->execute();
	    $result = $sth->fetchObject();
	    $follow_chk = 0;
	    $chkObj = null;
	    if(isset($input["user_chk_id"])){
	    	$sth = $this->db->prepare("SELECT count(*) as cc FROM cha_follow WHERE follow_user_id = :follow_user_id and user_id = :user_id ")
	    	;
	    	$sth->bindParam("follow_user_id", $args['id']);
	    	$sth->bindParam("user_id", $input['user_chk_id']);
	    	$sth->execute();
	    	$chkObj = $sth->fetchObject();
	    	if((int)$chkObj->cc > 0){
	    		$follow_chk=1;
	    	}
	    }
	    
	    $result->following = $follow_chk;
	    $result->errors = (object) array(
	    			'status' 	=> '200',
	    			'source'	=> '/users',
	    			'title'		=> 'Success',
	    			'detail'	=> 'userid : '.$args['id']
	    		);
	    // not found
	    if(empty($result)){
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '400',
	    			'source'	=> '/users',
	    			'title'		=> 'Not found',
	    			'detail'	=> 'Not found'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});

	$app->get('/getToken/[{key}]', function ($request, $response, $args) {
		$result = "";
		$sth = $this->db->prepare("SELECT UUID() as token");
		$sth->execute();
		$result = $sth->fetchAll();
		$token 	= base64_encode($result[0]['token']."_".$args['key']);
		$result = array( 'token' => $token );
		$expired_token = date('Y-m-d H:i:s', strtotime('30 minute'));
		$sql = "INSERT INTO cha_token (token,token_key,expired_token) VALUES (:token,:token_key,:expired_token)";
	    $sth = $this->db->prepare($sql);
	    $sth->bindParam("token", $token);
	    $sth->bindParam("token_key", $args['key']);
	    $sth->bindParam("expired_token", $expired_token);
	    $sth->execute();
		return $this->response->withJson($result);
	});
	$app->get('/listUsers', function ($request, $response, $args) {
	    // Sample log message
	    //$this->logger->info("Slim-Skeleton '/' route");
	    $sth = $this->db->prepare("SELECT * FROM cha_users");
	    $sth->execute();
	    $data = $sth->fetchAll();
	    // Render index view
	    return $this->renderer->render($response, 'listUser.phtml', $data);
	});
	$app->post('/login', function ($request, $response, $args) {
		$result = array();
		$input = $request->getParsedBody();
		if(!empty($input['username'])){
	    	$where = "(user_email=:username and user_pass=:user_pass) or (user_phone=:username and user_pass=:user_pass)";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$pass = md5($input['user_pass']);
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("username", $input['username']);
	    	$sth->bindParam("user_pass", $pass);
		    $sth->execute();
		    $result = $sth->fetch(PDO::FETCH_ASSOC);

		    if(count($result)>0){
		    	 $result['errors'] = array(
		    			'status' 	=> '200',
		    			'source'	=> '/login',
		    			'title'		=> 'Success',
		    			'detail'	=> 'Success',
		    			'user_id'		=> $result['user_id']
		    		);
		    }else{
		    	$result['errors'] = array(
		    			'status' 	=> '401',
		    			'source'	=> '/login',
		    			'title'		=> 'Fail',
		    			'detail'	=> 'Fail'
		    		);
		    }
	    }else{
	    	$result['errors'] = array(
	    			'status' 	=> '401',
	    			'source'	=> '/login',
	    			'title'		=> 'Empty',
	    			'detail'	=> 'E-mail or Phone empty'
	    		);
	    }
	    // Render index view
	    return $this->response->withJson($result);
	});
	$app->post('/loginFB', function ($request, $response, $args) {
		$result="";
		$input = $request->getParsedBody();
		if(!empty($input['user_fb_id'])){
			$sql_select = "SELECT * FROM cha_users WHERE user_fb_id=:user_fb_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_fb_id", $input['user_fb_id']);
		    $sth->execute();
		    $result = $sth->fetchAll();
		    if(count($result)==0){
		    	$sql = "INSERT INTO cha_users (user_name,user_email,user_pass,user_image,user_fb_id,user_phone) VALUES (:user_name,:user_email,:user_pass,:user_image,:user_fb_id,:user_phone)";
			    $sth = $this->db->prepare($sql);
			    $password = md5($input['user_pass']);
			    $sth->bindParam("user_name", 	$input['user_name']);
			    $sth->bindParam("user_email", 	$input['user_email']);
			    $sth->bindParam("user_pass", 	$password);
			    $sth->bindParam("user_image", 	$input['user_image']);
			    $sth->bindParam("user_fb_id", 	$input['user_fb_id']);
			    $sth->bindParam("user_phone", 	$input['user_phone']);

			    $sth->execute();
			    $lastID = $this->db->lastInsertId();
			    $where = "user_id=:user_id";
		    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
		    	$sth = $this->db->prepare($sql_select);
		    	$sth->bindParam("user_id", $lastID);
			    $sth->execute();
			    $result = $sth->fetch(PDO::FETCH_ASSOC);
			    $result['errors'] = array(
		    			'status' 	=> '200',
		    			'source'	=> '/loginFB',
		    			'title'		=> 'Success',
		    			'detail'	=> 'Success login fb',
		    			'user_id'	=> $result['user_id']
		    		);
		    }else{
		    	$where = "user_fb_id=:user_fb_id";
		    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
		    	$sth = $this->db->prepare($sql_select);
		    	$sth->bindParam("user_fb_id", $input['user_fb_id']);
			    $sth->execute();
			    $result = $sth->fetch(PDO::FETCH_ASSOC);
			    $result['errors'] = array(
		    			'status' 	=> '200',
		    			'source'	=> '/loginFB',
		    			'title'		=> 'Success',
		    			'detail'	=> 'Success insert new user fb'
		    		);
		    }
		}else{
			$result = array(
	    		'errorss' => array(
	    			'status' 	=> '400',
	    			'source'	=> '/loginFB',
	    			'title'		=> 'Empty',
	    			'detail'	=> 'ID facebook empty'
	    		)
	    	);
		}
		return $this->response->withJson($result);
	});
	$app->post('/addRoute', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
	    	$sql = "INSERT INTO cha_route (user_id,route_detail,route_create,route_like,route_title,route_activity,route_city,route_travel_method,route_budgetmin,route_budgetmax,route_suggestion,route_latitude,route_longitude) VALUES (:user_id,:route_detail,:route_create,:route_like,:route_title,:route_activity,:route_city,:route_travel_method,:route_budgetmin,:route_budgetmax,:route_suggestion,:route_latitude,:route_longitude)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("route_detail", 	$input['route_detail']);
		    $sth->bindParam("route_create", 	$input['route_create']);
		    $sth->bindParam("route_like", 	$input['route_like']);
		    $sth->bindParam("route_title", 	$input['route_title']);
		    $sth->bindParam("route_activity", 	$input['route_activity']);
		    $sth->bindParam("route_city", 	$input['route_city']);
		    $sth->bindParam("route_travel_method", 	$input['route_travel_method']);
		    $sth->bindParam("route_budgetmin", 	$input['route_budgetmin']);
		    $sth->bindParam("route_budgetmax", 	$input['route_budgetmax']);
		    $sth->bindParam("route_suggestion", 	$input['route_suggestion']);
		    $sth->bindParam("route_latitude", 	$input['route_latitude']);
		    $sth->bindParam("route_longitude", 	$input['route_longitude']);
		    $sth->execute();
		    $result['route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addRoute',
    			'title'		=> 'Success',
    			'detail'	=> 'route_id: '.$result['route_id']
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/updateRoute/[{route_id}]', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
	    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
	    if(count($result)>0){
		    $sql = "UPDATE cha_route SET route_detail=:route_detail,route_like=:route_like,route_title=:route_title,route_activity=:route_activity,route_city=:route_city,route_travel_method=:route_travel_method,route_budgetmin=:route_budgetmin,route_budgetmax=:route_budgetmax,route_suggestion=:route_suggestion,route_latitude=:route_latitude,route_longitude=:route_longitude WHERE route_id=:route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_detail", 	$input['route_detail']);
		    $sth->bindParam("route_like", 	$input['route_like']);
		    $sth->bindParam("route_title", 	$input['route_title']);
		    $sth->bindParam("route_activity", 	$input['route_activity']);
		    $sth->bindParam("route_city", 	$input['route_city']);
		    $sth->bindParam("route_travel_method", 	$input['route_travel_method']);
		    $sth->bindParam("route_budgetmin", 	$input['route_budgetmin']);
		    $sth->bindParam("route_budgetmax", 	$input['route_budgetmax']);
		    $sth->bindParam("route_suggestion", $input['route_suggestion']);
		    $sth->bindParam("route_latitude", 	$input['route_latitude']);
		    $sth->bindParam("route_longitude", $input['route_longitude']);
		   	$sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    	$sth->execute();
		    $result = $sth->fetch(PDO::FETCH_ASSOC);
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/updateRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$cout = count($result);
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/updateRoute',
	    			'title'		=> 'Not found route',
	    			'detail'	=> 'Please re-check route id',
	    			'$result' => $result
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/finishRoute', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sql = "UPDATE cha_route SET route_finish=:route_finish WHERE route_id=:route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_finish", $input['route_finish']);
		   	$sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    	$sth->execute();
		    $result = $sth->fetch(PDO::FETCH_ASSOC);
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/finishRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/finishRoute',
	    			'title'		=> 'Not found route',
	    			'detail'	=> 'Please re-check route id'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/publishRoute', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sql = "UPDATE cha_route SET route_publish=:route_publish WHERE route_id=:route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_publish", $input['route_publish']);
		   	$sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    	$sth->execute();
		    $result = $sth->fetch(PDO::FETCH_ASSOC);
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/publishRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/publishRoute',
	    			'title'		=> 'Not found route',
	    			'detail'	=> 'Please re-check route id'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/draftRoute', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sql = "UPDATE cha_route SET route_publish=0 WHERE route_id=:route_id";
		    $sth = $this->db->prepare($sql);
		   	$sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    	$sth->execute();
		    $result = $sth->fetch(PDO::FETCH_ASSOC);
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/draftRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/draftRoute',
	    			'title'		=> 'Not found route',
	    			'detail'	=> 'Please re-check route id'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/getRoute', function ($request, $response) {
	    $input = $request->getParsedBody();
	    //update view
	    $sql = "UPDATE cha_route SET route_view = route_view + 1 WHERE route_id=:route_id";
	    $sth = $this->db->prepare($sql);
	   	$sth->bindParam("route_id", $input['route_id']);
	    $sth->execute();

	    $sql_select = "SELECT * FROM cha_route WHERE route_id=:route_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
	    $result = $sth->fetch(PDO::FETCH_ASSOC);
	    if($result){
	    	$result["route_img"]=array();
	    	$sql_select = "SELECT *  FROM cha_image_route a WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    	$sth->execute();
	    	while ($val = $sth->fetch(PDO::FETCH_ASSOC)) {
	    		$result["route_img"][]=array(
	    				"img_id"=>$val["img_id"],
	    				"img_text"=>$val["img_text"]
	    			);
	    	}
	    	$result["route_pin"]=array();
	    	$sql_select = "SELECT a.place_id,(select img_text from cha_image_place where place_id=a.place_id limit 0,1) as img_text  FROM cha_place a WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    	$sth->execute();
	    	while ($val = $sth->fetch(PDO::FETCH_ASSOC)) {
	    		$img_text="";
	    		if(!empty($val["img_text"])){
	    			$img_text = $val["img_text"];
	    		}else{
	    			$img_text = "cover.jpg";
	    		}
	    		$result["route_pin"][]=array(
	    				"place_id"=>$val["place_id"],
	    				"img_text"=>$img_text
	    			);
	    	}
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/getRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/getRoute',
	    			'title'		=> 'Not found route',
	    			'detail'	=> 'Please re-check route id'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/getPlace', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_place WHERE place_id=:place_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("place_id", $input['place_id']);
    	$sth->execute();
	    $result = $sth->fetch(PDO::FETCH_ASSOC);
	    if($result){
	    	$result["place_img"]=array();
	    	$sql_select = "SELECT *  FROM cha_image_place a WHERE place_id=:place_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("place_id", $input['place_id']);
	    	$sth->execute();
	    	while ($val = $sth->fetch(PDO::FETCH_ASSOC)) {
	    		$result["place_img"][]=array(
	    				"img_id"=>$val["img_id"],
	    				"img_text"=>$val["img_text"]
	    			);
	    	}
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/getPlace',
				'title'		=> 'Success',
				'detail'	=> 'place_id: '.$input['place_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/getPlace',
	    			'title'		=> 'Not found place',
	    			'detail'	=> 'Please re-check place id place_id: '.$input['place_id']
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/getPlacesByRoute', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT a.*,img.img_text FROM cha_place a left join cha_image_place img on a.place_id=img.place_id WHERE a.route_id=:route_id order by a.place_id asc";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
	    $result["list"] = array();
	    if($numrows>0){
	    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
	    		if($countrow!=0){
	    			if($lastRid!=$data["place_id"]){
		    			$result["list"][]=$tmpLastData;
		    			$tmpImage=array();
		    			$lastRid = $data["place_id"];
		    		}
	    		}else{
	    			$lastRid = $data["place_id"];
	    		}
	    		if(!empty($data["img_text"])){
	    			$tmpImage[]=array(
	    				'img_text'=> $data["img_text"]
	    			);
	    		}
	    		$tmpLastData = array(
	    					'place_id'=>$data["place_id"],
	    					'place_name'=>$data["place_name"],
	    					'place_detail'=>$data["place_detail"],
	    					'place_create'=>$data["place_create"],
	    					'place_like'=>$data["place_like"],
	    					'place_latitude'=>$data["place_latitude"],
	    					'place_longitude'=>$data["place_longitude"],
	    					'place_img'=>$tmpImage
	    				);
	    		$countrow++;
	    		if($countrow==$numrows){
	    			$result["list"][]=array(
		    					'place_id'=>$data["place_id"],
		    					'place_name'=>$data["place_name"],
		    					'place_detail'=>$data["place_detail"],
		    					'place_create'=>$data["place_create"],
		    					'place_like'=>$data["place_like"],
		    					'place_latitude'=>$data["place_latitude"],
		    					'place_longitude'=>$data["place_longitude"],
		    					'place_img'=>$tmpImage
		    				);
	    			$tmpImage=array();
	    		}
	    	}
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/getRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/getRoute',
	    			'title'		=> 'Not found pins.',
	    			'detail'	=> 'Not found pins.'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/listNotification', function ($request, $response) {
	    $input = $request->getParsedBody();
	    $where ="1=1 ";
	    if($input["type"]=="follow"){
	    	$where.=" and a.noti_passive_user_id IN (select follow_user_id from cha_follow where user_id = :user_id)";
	    }
	    else if($input["type"]=="you"){
			$where.=" and a.noti_passive_user_id = :user_id";
	    }

	    $sql_select = "
	    	SELECT 
	    			a.*
	    			,TIMESTAMPDIFF(MINUTE, a.noti_create, now()) as diffDate
	    			,u.user_name,u.user_image
	    			,(select img_text from cha_image_route where route_id=b.route_id limit 1) as r_img
	    			,(select user_name from cha_users where user_id = a.noti_passive_user_id) as passive_name
	    			,(select user_image from cha_users where user_id = a.noti_passive_user_id) as passive_image
	    	FROM cha_notification a 
	    			inner join cha_users u on a.noti_active_user_id=u.user_id
	    			left join cha_route b on a.noti_route_id=b.route_id
			WHERE	".$where."	 order by a.noti_create desc limit 20
	    ";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("user_id", $input['user_id']);
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["list"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["noti_id"]){
	    			$result["list"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["noti_id"];
	    		}
    		}else{
    			$lastRid = $data["noti_id"];
    		}
    		$tmpLastData = array(
	    					'user_active_id'=>$data["noti_active_user_id"],
	    					'user_active_name'=>$data["user_name"],
	    					'user_active_image'=>$data["user_image"],
	    					'user_passive_name'=>$data["passive_name"],
	    					'user_passive_image'=>$data["passive_image"],
	    					'noti_type'=>$data["noti_type"],
	    					'noti_view'=>$data["noti_view"],
	    					'noti_route_id'=>$data["noti_route_id"],
	    					'noti_route_img'=>$data["r_img"],
	    					'noti_create'=>$data["noti_create"],
	    					'diffDate'=>$data["diffDate"]
	    					);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["list"][]=array(
    					'user_active_id'=>$data["noti_active_user_id"],
    					'user_active_name'=>$data["user_name"],
    					'user_active_image'=>$data["user_image"],
    					'user_passive_name'=>$data["passive_name"],
	    				'user_passive_image'=>$data["passive_image"],
    					'noti_type'=>$data["noti_type"],
    					'noti_view'=>$data["noti_view"],
    					'noti_route_id'=>$data["noti_route_id"],
    					'noti_route_img'=>$data["r_img"],
    					'noti_create'=>$data["noti_create"],
    					'diffDate'=>$data["diffDate"]
    				);
    		}
    	}

	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listNotification',
			'title'		=> 'Success',
			'detail'	=> 'Get all Notification.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesFeed', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "
	    SELECT 
	    	a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate
	    	,b.user_name
	    	,b.user_image
	    	,img.img_text 
	    FROM cha_route a 
	    	inner join cha_users b on a.user_id=b.user_id 
	    	left join cha_image_route img on a.route_id=img.route_id 
	    WHERE a.route_publish=1 order by a.route_id desc";
    	$sth = $this->db->prepare($sql_select);
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["list"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["list"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'user_image'=>$data["user_image"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["list"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'user_image'=>$data["user_image"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}

	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesFeed',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesFeedExplore', function ($request, $response) {
	    $input = $request->getParsedBody();
	    $where="";
	    if(!empty($input["searchtext"])){
	    	$where = " and (a.route_title LIKE :search OR a.route_detail LIKE :search)";
	    }
	    $sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,img.img_text FROM 
	    cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE a.route_publish=1 ".$where." order by a.route_view desc limit 0,12";
    	$sth = $this->db->prepare($sql_select);
    	if(!empty($input["searchtext"])){
    		$searchtext = "%".$input['searchtext']."%";
	    	$sth->bindParam("search", $searchtext);
	    }
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["listpop"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["listpop"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["listpop"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}
    	$routes_id = "";
    	$sqlNearbyRoute = "
			SELECT distinct route_id
			FROM
			(
			SELECT route_id, 111.045 * DEGREES(ACOS(COS(RADIANS(".$input["latitude"]."))
			 * COS(RADIANS(place_latitude))
			 * COS(RADIANS(place_longitude) - RADIANS(".$input["longitude"]."))
			 + SIN(RADIANS(".$input["latitude"]."))
			 * SIN(RADIANS(place_latitude))))
			 AS distance_in_km
			FROM cha_place
			ORDER BY distance_in_km ASC
			LIMIT 0,12
			) A
    	";
    	$sth = $this->db->prepare($sqlNearbyRoute);
    	$sth->execute();
    	$i=0;
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($i!=0){
    			$routes_id .=",";
    		}
    		$routes_id.=$data["route_id"];
    		$i++;
    	}
    	$where ="";
    	$where = " and a.route_id IN (".$routes_id.")";
    	$result["listnear"] = array();
    	 $sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,img.img_text FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE a.route_publish=1 ".$where." order by a.route_view desc limit 0,12";
    	$sth = $this->db->prepare($sql_select);
    	if(!empty($input["searchtext"])){
    		$searchtext = "%".$input['searchtext']."%";
	    	$sth->bindParam("search", $searchtext);
	    }
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["listnear"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["listnear"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}

	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesFeedExplore',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesSearchExplore', function ($request, $response) {
	    $input = $request->getParsedBody();
	    $where="";
	    if(!empty($input["searchtext"])){
	    	$where = " and (a.route_title LIKE :search OR a.route_detail LIKE :search)";
	    }
	    $sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,img.img_text FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE a.route_publish=1 ".$where." order by a.route_view desc limit 0,12";
    	$sth = $this->db->prepare($sql_select);
    	if(!empty($input["searchtext"])){
    		$searchtext = "%".$input['searchtext']."%";
	    	$sth->bindParam("search", $searchtext);
	    }
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["listpop"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["listpop"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["listpop"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}
	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesSearchExplore',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesFeedHome', function ($request, $response) {
	    $input = $request->getParsedBody();

	    $sql_select = "SELECT 
	    a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,
	    b.user_name,
	    b.user_image,
	    img.img_text,
	    IFNULL((SELECT like_status 
	    	FROM cha_log_like 
	    	WHERE route_id=a.route_id 
	    	and user_id = :user_id 
	    	Order by create_like desc limit 0,1),0) as like_status,
	    IFNULL((SELECT favorite_status 
	    	FROM cha_log_favorite 
	    	WHERE route_id=a.route_id 
	    	and user_id = :user_id 
	    	Order by create_favorite desc limit 0,1),0) as favorite_status 
	    FROM cha_route a 
	    inner join cha_users b on a.user_id=b.user_id 
	    left join cha_image_route img 
	    	on a.route_id=img.route_id 
	    WHERE a.route_publish=1 
	    and (a.user_id IN (
	    		select follow_user_id 
	    		from cha_follow 
	    		where user_id = :user_id) OR a.user_id = :user_id) 
	    order by a.route_id desc";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("user_id", $input['user_id']);
    	$sth->execute();
    	$numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["list"]=array();
    	if($numrows>0){
    		while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
	    		if($countrow!=0){
	    			if($lastRid!=$data["route_id"]){
		    			$result["list"][]=$tmpLastData;
		    			$tmpImage=array();
		    			$lastRid = $data["route_id"];
		    		}
	    		}else{
	    			$lastRid = $data["route_id"];
	    		}
	    		
	    		if(!empty($data["img_text"])){
	    			$tmpImage[]=array(
	    				'img_text'=> $data["img_text"]
	    			);
	    		} else {
	    			$tmpImage[]=array('img_text' => 'placeholder');
	    		}
	    		$tmpLastData = array(
		    					'user_id'=>$data["user_id"],
		    					'user_name'=>$data["user_name"],
		    					'user_image'=>$data["user_image"],
		    					'route_id'=>$data["route_id"],
		    					'route_title'=>$data["route_title"],
		    					'route_detail'=>$data["route_detail"],
		    					'diffDate'=>$data["diffDate"],
		    					'like_status'=> $data["like_status"],
		    					'favorite_status'=> $data["favorite_status"],
		    					'route_activity'=>$data["route_activity"],
		    					'route_city'=>$data["route_city"],
		    					'route_travel_method'=>$data["route_travel_method"],
		    					'route_budgetmin'=>$data["route_budgetmin"],
		    					'route_budgetmax'=>$data["route_budgetmax"],
		    					'route_suggestion'=>$data["route_suggestion"],
		    					'route_img'=>$tmpImage
		    				);
	    		$countrow++;
	    		if($countrow==$numrows){
	    			$result["list"][]=array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'user_image'=>$data["user_image"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'like_status'=> $data["like_status"],
	    					'favorite_status'=> $data["favorite_status"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
	    			$tmpImage=array();
	    		}
	    	}
    	}else{
    		$sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,b.user_image,img.img_text,IFNULL((SELECT like_status FROM cha_log_like WHERE route_id=a.route_id and user_id = :user_id Order by create_like desc limit 0,1),0) as like_status,IFNULL((SELECT favorite_status FROM cha_log_favorite WHERE route_id=a.route_id and user_id = :user_id Order by create_favorite desc limit 0,1),0) as favorite_status FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE a.route_publish=1 order by a.route_id desc limit 10";
		    	$sth = $this->db->prepare($sql_select);
		    	$sth->bindParam("user_id", $input['user_id']);
		    	$sth->execute();
		    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
	    		if($countrow!=0){
	    			if($lastRid!=$data["route_id"]){
		    			$result["list"][]=$tmpLastData;
		    			$tmpImage=array();
		    			$lastRid = $data["route_id"];
		    		}
	    		}else{
	    			$lastRid = $data["route_id"];
	    		}
	    		
	    		if(!empty($data["img_text"])){
	    			$tmpImage[]=array(
	    				'img_text'=> $data["img_text"]
	    			);
	    		} else {
	    			$tmpImage[]=array('img_text' => 'placeholder');
	    		}
	    		$tmpLastData = array(
		    					'user_id'=>$data["user_id"],
		    					'user_name'=>$data["user_name"],
		    					'user_image'=>$data["user_image"],
		    					'route_id'=>$data["route_id"],
		    					'route_title'=>$data["route_title"],
		    					'route_detail'=>$data["route_detail"],
		    					'diffDate'=>$data["diffDate"],
		    					'like_status'=> $data["like_status"],
		    					'favorite_status'=> $data["favorite_status"],
		    					'route_activity'=>$data["route_activity"],
		    					'route_city'=>$data["route_city"],
		    					'route_travel_method'=>$data["route_travel_method"],
		    					'route_budgetmin'=>$data["route_budgetmin"],
		    					'route_budgetmax'=>$data["route_budgetmax"],
		    					'route_suggestion'=>$data["route_suggestion"],
		    					'route_img'=>$tmpImage
		    				);
	    		$countrow++;
	    		if($countrow==$numrows){
	    			$result["list"][]=array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'user_image'=>$data["user_image"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'like_status'=> $data["like_status"],
	    					'favorite_status'=> $data["favorite_status"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
	    			$tmpImage=array();
	    		}
	    	}
    	}

//     	array_walk_recursive($result['list'], function (&$item, $key) {
//     $item = null === $item ? '' : $item;
// });
    	

	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesFeed',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'.$input["user_id"]
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesFeedByUser', function ($request, $response) {
	    $input = $request->getParsedBody();
	    $where = "";
	    if(empty($input["status"])){
	    	$where.="a.route_publish=1 ";
	    }
	    else{
	    	if($input["status"]=="draft"){
	    		$where.="a.route_publish=0 ";
	    	}
	    	else{
	    		$where.="";
	    	}
	    }
	    $sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,b.user_image,img.img_text,IFNULL((SELECT favorite_status FROM cha_log_favorite WHERE route_id=a.route_id and user_id = :user_id Order by create_favorite desc limit 0,1),0) as favorite_status,IFNULL((SELECT like_status FROM cha_log_like WHERE route_id=a.route_id and user_id = :user_id Order by create_like desc limit 0,1),0) as like_status FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE ".$where." and a.user_id = :user_id order by a.route_id desc";
	    //$sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,img.img_text FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE ".$where." and a.user_id = :user_id  order by a.route_id desc";
    	$sth = $this->db->prepare($sql_select); 
		$sth->bindParam("user_id", $input['user_id']);
    	$sth->execute();
	    $numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["list"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["list"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'user_image'=>$data["user_image"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'like_status'=> $data["like_status"],
	    					'favorite_status'=> $data["favorite_status"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["list"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'user_image'=>$data["user_image"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'like_status'=> $data["like_status"],
    					'favorite_status'=> $data["favorite_status"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}
	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesFeedByUser',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesFeedByUserLike', function ($request, $response) {
	    $input = $request->getParsedBody();
	    $where = "";
	    if(empty($input["status"])){
	    	$where.="a.route_publish=1 ";
	    }
	    else{
	    	if($input["status"]=="draft"){
	    		$where.="a.route_publish=0 ";
	    	}
	    	else{
	    		$where.="";
	    	}
	    }
	    $sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,b.user_image,img.img_text,IFNULL((SELECT favorite_status FROM cha_log_favorite WHERE route_id=a.route_id and user_id = :user_id Order by create_favorite desc limit 0,1),0) as favorite_status,IFNULL((SELECT like_status FROM cha_log_like WHERE route_id=a.route_id and user_id = :user_id Order by create_like desc limit 0,1),0) as like_status FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE ".$where." and IFNULL((SELECT like_status FROM cha_log_like WHERE route_id=a.route_id and user_id = :user_id Order by create_like desc limit 0,1),0) = 1 order by a.route_id desc";
	    //$sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,img.img_text FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE ".$where." and a.user_id = :user_id  order by a.route_id desc";
    	$sth = $this->db->prepare($sql_select); 
		$sth->bindParam("user_id", $input['user_id']);
    	$sth->execute();
	    $numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["list"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["list"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'user_image'=>$data["user_image"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'like_status'=> $data["like_status"],
	    					'favorite_status'=> $data["favorite_status"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["list"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'user_image'=>$data["user_image"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'like_status'=> $data["like_status"],
    					'favorite_status'=> $data["favorite_status"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}
	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesFeedByUser',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/listRoutesFeedByUserFavorite', function ($request, $response) {
	    $input = $request->getParsedBody();
	    $where = "";
	    if(empty($input["status"])){
	    	$where.="a.route_publish=1 ";
	    }
	    else{
	    	if($input["status"]=="draft"){
	    		$where.="a.route_publish=0 ";
	    	}
	    	else{
	    		$where.="";
	    	}
	    }
	    $sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,b.user_image,img.img_text,IFNULL((SELECT favorite_status FROM cha_log_favorite WHERE route_id=a.route_id and user_id = :user_id Order by create_favorite desc limit 0,1),0) as favorite_status,IFNULL((SELECT favorite_status FROM cha_log_favorite WHERE route_id=a.route_id and user_id = :user_id Order by create_favorite desc limit 0,1),0) as favorite_status FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE ".$where." and IFNULL((SELECT favorite_status FROM cha_log_favorite WHERE route_id=a.route_id and user_id = :user_id Order by create_favorite desc limit 0,1),0) = 1 order by a.route_id desc";
	    //$sql_select = "SELECT a.*,TIMESTAMPDIFF(MINUTE, a.route_create, now()) as diffDate,b.user_name,img.img_text FROM cha_route a inner join cha_users b on a.user_id=b.user_id left join cha_image_route img on a.route_id=img.route_id WHERE ".$where." and a.user_id = :user_id  order by a.route_id desc";
    	$sth = $this->db->prepare($sql_select); 
		$sth->bindParam("user_id", $input['user_id']);
    	$sth->execute();
	    $numrows=$sth->rowCount();
    	$countrow = 0;
    	$lastRid = 0;
    	$tmpImage = array();
    	$tmpLastData = array();
    	$result["list"]=array();
    	while($data = $sth->fetch(PDO::FETCH_ASSOC)){ 
    		if($countrow!=0){
    			if($lastRid!=$data["route_id"]){
	    			$result["list"][]=$tmpLastData;
	    			$tmpImage=array();
	    			$lastRid = $data["route_id"];
	    		}
    		}else{
    			$lastRid = $data["route_id"];
    		}
    		
    		if(!empty($data["img_text"])){
    			$tmpImage[]=array(
    				'img_text'=> $data["img_text"]
    			);
    		}
    		$tmpLastData = array(
	    					'user_id'=>$data["user_id"],
	    					'user_name'=>$data["user_name"],
	    					'user_image'=>$data["user_image"],
	    					'route_id'=>$data["route_id"],
	    					'route_title'=>$data["route_title"],
	    					'route_detail'=>$data["route_detail"],
	    					'diffDate'=>$data["diffDate"],
	    					'like_status'=> $data["like_status"],
	    					'favorite_status'=> $data["favorite_status"],
	    					'route_activity'=>$data["route_activity"],
	    					'route_city'=>$data["route_city"],
	    					'route_travel_method'=>$data["route_travel_method"],
	    					'route_budgetmin'=>$data["route_budgetmin"],
	    					'route_budgetmax'=>$data["route_budgetmax"],
	    					'route_suggestion'=>$data["route_suggestion"],
	    					'route_img'=>$tmpImage
	    				);
    		$countrow++;
    		if($countrow==$numrows){
    			$result["list"][]=array(
    					'user_id'=>$data["user_id"],
    					'user_name'=>$data["user_name"],
    					'user_image'=>$data["user_image"],
    					'route_id'=>$data["route_id"],
    					'route_title'=>$data["route_title"],
    					'route_detail'=>$data["route_detail"],
    					'diffDate'=>$data["diffDate"],
    					'like_status'=> $data["like_status"],
    					'favorite_status'=> $data["favorite_status"],
    					'route_activity'=>$data["route_activity"],
    					'route_city'=>$data["route_city"],
    					'route_travel_method'=>$data["route_travel_method"],
    					'route_budgetmin'=>$data["route_budgetmin"],
    					'route_budgetmax'=>$data["route_budgetmax"],
    					'route_suggestion'=>$data["route_suggestion"],
    					'route_img'=>$tmpImage
    				);
    			$tmpImage=array();
    		}
    	}
	    $result['errors'] = array(
			'status' 	=> '200',
			'source'	=> '/listRoutesFeedByUserFavorite',
			'title'		=> 'Success',
			'detail'	=> 'Get all feed.'
		);
	    return $this->response->withJson($result);
	});
	$app->post('/delRoute', function ($request, $response) {
		 $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_route";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("route_id", $input['route_id']);
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sth = $this->db->prepare("DELETE FROM cha_route WHERE route_id=:route_id");
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		     $sth = $this->db->prepare("DELETE FROM cha_image_place WHERE place_id IN (select place_id from cha_place where route_id=:route_id)");
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $sth = $this->db->prepare("DELETE FROM cha_place WHERE route_id=:route_id");
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $sth = $this->db->prepare("DELETE FROM cha_image_route WHERE route_id=:route_id");
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $res['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/delRoute',
				'title'		=> 'Success',
				'detail'	=> 'route_id: '.$input['route_id']
			);
		}else{
			$res = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/delRoute',
	    			'title'		=> 'Not found route',
	    			'detail'	=> 'Please re-check route id'
	    		)
	    	);
		}
	    return $this->response->withJson($res);
	});
	$app->post('/addPlace', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    
	    if(count($result)>0){
	    	$sql = "INSERT INTO cha_place (route_id,place_name,place_detail,place_create,place_like,place_latitude,place_longitude) VALUES (:route_id,:place_name,:place_detail,:place_create,:place_like,:place_latitude,:place_longitude)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->bindParam("place_name", 	$input['place_name']);
		    $sth->bindParam("place_detail", 	$input['place_detail']);
		    $sth->bindParam("place_create", 	$input['place_create']);
		    $sth->bindParam("place_like", 	$input['place_like']);
		    $sth->bindParam("place_latitude", $input['place_latitude']);
		    $sth->bindParam("place_longitude", $input['place_longitude']);
		    $sth->execute();
		    $result['place_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
	    			'status' 	=> '200',
	    			'source'	=> '/addPlace',
	    			'title'		=> 'Success',
	    			'detail'	=> 'place_id: '.$result['place_id'],
	    			'place_id'	=> $result['place_id']
	    		);
		    $result['place_name'] = $input['place_name'];
		    $result['place_latitude']=$input['place_latitude'];
		    $result['place_longitude']=$input['place_longitude'];
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addPlace',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/updatePlace/[{place_id}]', function ($request, $response, $args) {
	    $input = $request->getParsedBody();
	    $sql_select = "SELECT * FROM cha_place";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("place_id", $input['place_id']);
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sql = "UPDATE cha_place SET place_name=:place_name,place_detail=:place_detail,place_like=:place_like,place_latitude=:place_latitude,place_longitude=:place_longitude WHERE place_id=:place_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("place_name", 	$input['place_name']);
		    $sth->bindParam("place_detail", 	$input['place_detail']);
		    $sth->bindParam("place_like", 	$input['place_like']);
		    $sth->bindParam("place_latitude", 	$input['place_latitude']);
		    $sth->bindParam("place_longitude", 	$input['place_longitude']);
		   	$sth->bindParam("place_id", $input['place_id']);
		    $sth->execute();
		    $result = array();
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/updatePlace',
				'title'		=> 'Success',
				'detail'	=> 'place_id: '.$input['place_id']
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/updatePlace',
	    			'title'		=> 'Not found place',
	    			'detail'	=> 'Please re-check place id'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/delPlace', function ($request, $response) {
		 $input = $request->getParsedBody();

	    $sql_select = "SELECT * FROM cha_place WHERE place_id=:place_id";
    	$sth = $this->db->prepare($sql_select);
    	$sth->bindParam("place_id", $input['place_id']);
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sth = $this->db->prepare("DELETE FROM cha_place WHERE place_id=:place_id");
		    $sth->bindParam("place_id", $input['place_id']);
		    $sth->execute();
		    $res['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/delPlace',
				'title'		=> 'Success',
				'detail'	=> 'place_id: '.$input['place_id'],
				'place_deleted' => $result
			);
		}else{
			$res = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/delPlace',
	    			'title'		=> 'Not found place',
	    			'detail'	=> 'Please re-check place id'
	    		)
	    	);
		}
	    return $this->response->withJson($res);
	});
	$app->post('/addTakePlace', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
	    	$sql = "INSERT INTO cha_take_route (user_id,route_id,place_id,user_created) VALUES (:user_id,:route_id,:place_id,:user_created)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->bindParam("place_id", 	$input['place_id']);
		    $sth->bindParam("user_created", 	date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['place_take_route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addPlace',
    			'title'		=> 'Success',
    			'detail'	=> 'place_take_route_id: '.$result['place_take_route_id'],
    			'place_take_route_id'	=> $result['place_take_route_id']
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addPlace',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/listTakePlaceUser/[{user_id}]', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$sql_select = "SELECT * FROM cha_take_route WHERE user_id=:user_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $res['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/listTakePlaceUser',
    			'title'		=> 'Success',
    			'detail'	=> $result,
    			'dateType'	=> 'json'
    		);
	    }else{
	    	$res = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/listTakePlaceUser',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/listTakePlaceRoute/[{route_id}]', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['route_id'])){
	    	$sql_select = "SELECT * FROM cha_take_route WHERE route_id=:route_id";
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("route_id", $input['route_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $res['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/listTakePlaceRoute',
    			'title'		=> 'Success',
    			'detail'	=> $result,
    			'dateType'	=> 'json'
    		);
	    }else{
	    	$res = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/listTakePlaceRoute',
	    			'title'		=> 'Not found',
	    			'detail'	=> 'Please re-check route id'
	    		)
	    	);
	    }
	    return $this->response->withJson($res);
	});
	$app->post('/addFriend', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
	    	$sql = "INSERT INTO cha_friend (user_id,user_friend_id,friend_create) VALUES (:user_id,:user_friend_id,:friend_create)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("user_friend_id", 	$input['user_friend_id']);
		    $sth->bindParam("friend_create", 	date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['friend_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addFriend',
    			'title'		=> 'Success',
    			'detail'	=> 'friend_id: '.$result['friend_id'],
    			'friend_id'	=> $result['friend_id']
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addFriend',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/unFriends/[{user_id}]', function ($request, $response, $args) {
		$input = $request->getParsedBody();

	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
    	$sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
		    $sth = $this->db->prepare("DELETE FROM cha_friend WHERE user_id=:user_id and user_friend_id=:user_friend_id");
		    $sth->bindParam("user_id", $input['user_id']);
		    $sth->bindParam("user_friend_id", $input['user_friend_id']);
		    $sth->execute();
		    //$result = $sth->fetchAll();
		    $result['errors'] = array(
				'status' 	=> '200',
				'source'	=> '/unFriends',
				'title'		=> 'Success',
				'detail'	=> ''
			);
		}else{
			$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/unFriends',
	    			'title'		=> 'Not found',
	    			'detail'	=> 'Please re-check'
	    		)
	    	);
		}
	    return $this->response->withJson($result);
	});
	$app->post('/addImageRoute', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    if(count($result)>0){

	    	$file_name = "route_".$input["ref_id"]."_".time().".jpg";
	    	$output_file = "../uploads/".$file_name;
	    	base64_to_jpeg($input['image_base_64'],$output_file);
	    	$create_time = date('Y-m-d H:i:s');

	    	$sql = "INSERT INTO cha_image_route (route_id,user_id,img_text,img_created) VALUES (:route_id,:user_id,:img_text,:img_created)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id"	,$input['route_id']);
		    $sth->bindParam("user_id"	,$input['user_id']);
		    $sth->bindParam("img_text"	,$file_name);
		    $sth->bindParam("img_created",$create_time);
		    
		    $sth->execute();
		    
		    $result['img_id'] 	= $this->db->lastInsertId();
		    $result['img_path']	= 'uploads/'.$file_name;

		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addImageRoute',
    			'title'		=> 'Success',
    			'create_time'=> $create_time
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addImagePlace',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/delImageRoute', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    
	    if(count($result)>0){
	    	$sql = "DELETE FROM cha_image_route Where route_id = :route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id"	,$input['route_id']);
		    $sth->execute();
		    
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/delImageRoute',
    			'title'		=> 'Success',
    			'create_time'=> date("Y-m-d H:i:s")
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/delImageRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/delImagePlace', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    
	    if(count($result)>0){
	    	$sql = "DELETE FROM cha_image_place Where place_id = :place_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("place_id"	,$input['place_id']);
		    $sth->execute();
		    
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/delImagePlace',
    			'title'		=> 'Success',
    			'create_time'=> date("Y-m-d H:i:s")
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/delImagePlace',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/addImagePlace', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    
	    if(count($result)>0){

	    	$file_name = "place_".$input["ref_id"]."_".time().".jpg";
	    	$output_file = "../uploads/".$file_name;
	    	base64_to_jpeg($input['image_base_64'],$output_file);
	    	$create_time = date('Y-m-d H:i:s');

	    	
	    	$sql = "INSERT INTO cha_image_place (place_id,user_id,img_text,img_created) VALUES (:place_id,:user_id,:img_text,:img_created)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("place_id"	,$input['place_id']);
		    $sth->bindParam("user_id"	,$input['user_id']);
		    $sth->bindParam("img_text"	,$file_name);
		    $sth->bindParam("img_created",$create_time);
		    
		    $sth->execute();
		    
		    $result['img_id'] 	= $this->db->lastInsertId();
		    $result['img_path']	= 'uploads/'.$file_name;

		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addImagePlace',
    			'title'		=> 'Success',
    			'create_time'=> $create_time
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addImagePlace',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/addComment', function ($request, $response) {
		$result = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    
	    if(count($result)>0){
	    	$sql = "INSERT INTO cha_comment_route (route_id,user_id,comment_detail,comment_create) VALUES (:route_id,:user_id,:comment_detail,:comment_create)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("comment_detail", 	$input['comment_detail']);
		    $sth->bindParam("comment_create", 	date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['comment_route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addComment',
    			'title'		=> 'Success',
    			'detail'	=> 'comment_route_id: '.$result['comment_route_id'],
    			'comment_route_id'	=> $result['comment_route_id']
    		);
    		//Notification ADD
		    $sqlgetroute = "SELECT * FROM cha_route WHERE route_id = :route_id";
		    $sth = $this->db->prepare($sqlgetroute);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $resRoute = $sth->fetchAll();

    		$notiType = "comment";
    		$sqlNoti = "INSERT INTO cha_notification (noti_active_user_id,noti_passive_user_id,noti_type,noti_route_id,noti_create) VALUES(:active_user_id,:passive_user_id,:noti_type,:route_id,'".date("Y-m-d H:i:s")."')";
    		$sth = $this->db->prepare($sqlNoti);
    		$sth->bindParam("active_user_id", 	$input['user_id']);
		    $sth->bindParam("passive_user_id", $resRoute[0]['user_id']);
		    $sth->bindParam("noti_type", $notiType);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addComment',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/updateComment', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
	    	$sql = "UPDATE cha_comment_route SET comment_detail:comment_detail WHERE comment_route_id:comment_route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("comment_detail", 	$input['comment_detail']);
		    $sth->bindParam("comment_route_id", 	$input['comment_route_id']);
		    $sth->execute();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/updateComment',
    			'title'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/updateComment',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/deleteComment', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
	    	$sql = "DELETE FROM cha_comment_route WHERE comment_route_id=:comment_route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("comment_route_id", 	$input['comment_route_id']);
		    $sth->execute();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addComment',
    			'title'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/deleteComment',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/addFavoriteRoute', function ($request, $response) {
		$result = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	    if(count($result)>0){
	    	// $sql = "UPDATE cha_route SET route_like=route_like+1 WHERE route_id=:route_id";
		    // $sth = $this->db->prepare($sql);
		    // $sth->bindParam("route_id", 	$input['route_id']);
		    // $sth->execute();
		    
    		$favorite_status = "1";
    		$sql = "INSERT INTO cha_log_favorite (user_id,route_id,favorite_status,create_favorite) VALUES (:user_id,:route_id,:favorite_status,:create_favorite)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->bindParam("favorite_status", $favorite_status);
		    $sth->bindParam("create_favorite", date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['favorite_route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addFavoriteRoute',
    			'title'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addFavoriteRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/deleteFavoriteRoute', function ($request, $response) {
		$result = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    if(count($result)>0){
	    	
		    $favorite_status = "0";
    		$sql = "INSERT INTO cha_log_favorite (user_id,route_id,favorite_status,create_favorite) VALUES (:user_id,:route_id,:favorite_status,:create_favorite)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->bindParam("favorite_status", $favorite_status);
		    $sth->bindParam("create_favorite", date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['favorite_route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/deleteLikeRoute',
    			'title'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/deleteLikeRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/addLikeRoute', function ($request, $response) {
		$result = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	    if(count($result)>0){
	    	$sql = "UPDATE cha_route SET route_like=route_like+1 WHERE route_id=:route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->execute();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addLikeRoute',
    			'title'		=> 'Success'
    		);
    		$like_status = "1";
    		$sql = "INSERT INTO cha_log_like (user_id,route_id,like_status,create_like) VALUES (:user_id,:route_id,:like_status,:create_like)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->bindParam("like_status", $like_status);
		    $sth->bindParam("create_like", date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['like_route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addLikeRoute',
    			'title'		=> 'Success'
    		);
    		//Notification ADD
		    $sqlgetroute = "SELECT * FROM cha_route WHERE route_id = :route_id";
		    $sth = $this->db->prepare($sqlgetroute);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();
		    $resRoute = $sth->fetchAll();

    		$notiType = "like";
    		$sqlNoti = "INSERT INTO cha_notification (noti_active_user_id,noti_passive_user_id,noti_type,noti_route_id,noti_create) VALUES(:active_user_id,:passive_user_id,:noti_type,:route_id,'".date("Y-m-d H:i:s")."')";
    		$sth = $this->db->prepare($sqlNoti);
    		$sth->bindParam("active_user_id", 	$input['user_id']);
		    $sth->bindParam("passive_user_id", $resRoute[0]['user_id']);
		    $sth->bindParam("noti_type", $notiType);
		    $sth->bindParam("route_id", $input['route_id']);
		    $sth->execute();

	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addLikeRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/deleteLikeRoute', function ($request, $response) {
		$result = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	   
	    if(count($result)>0){
	    	$sql = "UPDATE cha_route SET route_like=route_like-1 WHERE route_id=:route_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->execute();
		    $like_status = "0";
    		$sql = "INSERT INTO cha_log_like (user_id,route_id,like_status,create_like) VALUES (:user_id,:route_id,:like_status,:create_like)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 		$input['user_id']);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->bindParam("like_status", 	$like_status);
		    $sth->bindParam("create_like", 	date('Y-m-d H:i:s'));
		    $sth->execute();
		    $result['like_route_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/deleteLikeRoute',
    			'title'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/deleteLikeRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/addFollowUser', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
    		$sql = "INSERT INTO cha_follow (user_id,follow_user_id,follow_date_create) VALUES (:user_id,:follow_user_id,:follow_date_create)";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 		$input['user_id']);
		    $sth->bindParam("follow_user_id", 	$input['follow_user_id']);
		    $sth->bindParam("follow_date_create", 	date('Y-m-d H:i:s'));
		    $sth->execute();

		    $result['follow_id'] = $this->db->lastInsertId();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/addFollow',
    			'title'		=> 'Success',
    			'detail'	=> 'follow_id: '.$result['follow_id'],
    			'follow_id'	=> $result['follow_id']
    		);
    		//Notification ADD

    		$notiType = "follow";
    		$sqlNoti = "INSERT INTO cha_notification (noti_active_user_id,noti_passive_user_id,noti_type,noti_create) VALUES(:active_user_id,:passive_user_id,:noti_type,'".date("Y-m-d H:i:s")."')";
    		$sth = $this->db->prepare($sqlNoti);
    		$sth->bindParam("active_user_id", 	$input['user_id']);
		    $sth->bindParam("passive_user_id", $input['follow_user_id']);
		    $sth->bindParam("noti_type", $notiType);
		    $sth->execute();
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/addFollow',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/unFollowUser', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
    		$sql = "DELETE FROM cha_follow WHERE user_id=:user_id and follow_user_id=:follow_user_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->bindParam("follow_user_id", 	$input['follow_user_id']);
		    $sth->execute();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/unFollowUser',
    			'title'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/unFollowUser',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
	$app->post('/listFollowUser', function ($request, $response) {
		$result = "";
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    }
	   
	    $sth->execute();
	    $result = $sth->fetchAll();
	    if(count($result)>0){
    		$sql = "SELECT * FROM cha_follow WHERE user_id=:user_id";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("user_id", 	$input['user_id']);
		    $sth->execute();
	    	$result = $sth->fetchAll();
	    	$res['list'] = $result;
		    $res['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/listFollowUser',
    			'title'		=> 'Success',
    			'data'		=> $result
    		);
	    }else{
	    	$res = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/unFollowUser',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($res);
	});
	$app->post('/listCommentRoute', function ($request, $response) {
		$result = array();
	    $input = $request->getParsedBody();
	    $where = "";
	    if(!empty($input['user_id'])){
	    	$where = "user_id=:user_id";
	    	$sql_select = "SELECT * FROM cha_users WHERE ".$where;
	    	$sth = $this->db->prepare($sql_select);
	    	$sth->bindParam("user_id", $input['user_id']);
	    	$sth->execute();
	    	$result = $sth->fetchAll();
	    }
	    if(count($result)>0){
    		$sql = "SELECT a.*,b.user_name,TIMESTAMPDIFF(MINUTE, a.comment_create, now()) as diffDate FROM cha_comment_route a inner join cha_users b on a.user_id=b.user_id WHERE a.route_id=:route_id order by a.comment_create desc";
		    $sth = $this->db->prepare($sql);
		    $sth->bindParam("route_id", 	$input['route_id']);
		    $sth->execute();
	    	$result["list"] = $sth->fetchAll();
		    $result['errors'] = array(
    			'status' 	=> '200',
    			'source'	=> '/listCommentRoute',
    			'title'		=> 'Success',
    			'detail'		=> 'Success'
    		);
	    }else{
	    	$result = array(
	    		'errors' => array(
	    			'status' 	=> '207',
	    			'source'	=> '/listCommentRoute',
	    			'title'		=> 'Not found user',
	    			'detail'	=> 'Please re-check user id'
	    		)
	    	);
	    }
	    return $this->response->withJson($result);
	});
});
function test(){
	echo "a";
	exit();
}
function base64_to_jpeg($base64_string, $output_file) {
    $ifp = fopen($output_file, "wb"); 
    fwrite($ifp, base64_decode($base64_string)); 
    fclose($ifp); 

    return $output_file; 
}