<?php
include 'config.php';
//mk_user();
function conct(){
	global $DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME;
	$con = new mysqli($DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME);    //connect
	if($con->connect_error){                                                           //checks connection
		http_response_code(400);
		echo "ERROR Database Connection Failed: " . $con->connect_error, E_USER_ERROR;   //reports a DB connection failure
	}
	return $con;
}
function custom_mail($to, $subject, $message){
	$con = conct();
	$to = $con -> real_escape_string(explode("@", $to)[0]);
	$message = $con -> real_escape_string($message);
	$subject = $con -> real_escape_string($subject);
	$date = date("Y/m/d H:i:s");
	$sender = "admin@hukers.org";
	$qqq = <<<EOL
INSERT INTO mailbox(date, sender, subject, data, mail_user)
values
    ('{$date}', '{$sender}', '{$subject}', '{$message}', '{$to}')
;
EOL;
	mysqli_query($con, $qqq);
	if(mysqli_affected_rows($con) == 1){
		return true;
	}
	return false;
}
function login($email, $pass){
	$con = conct();
	$email = $con -> real_escape_string($email);
	$pass = sha1($pass);
	$qqq = "SELECT id,username,type FROM users WHERE (email='{$email}' OR username='{$email}') AND password='{$pass}'";
	$query = mysqli_query($con, $qqq);
	$numrows = mysqli_num_rows($query);
	if($numrows == 1){
		$row = mysqli_fetch_assoc($query);
		$_SESSION['user_id'] = $row['id'];
		$_SESSION['user'] = $row['username'];
		$_SESSION['user_type'] = $row['type'];
		return true;
	}
	else{
		return false;
	}
}
function change_username($new_username){
	$con = conct();
	$new_username = $con -> real_escape_string($new_username);
	$qqq = "UPDATE users SET username='{$new_username}' WHERE id='{$_SESSION['user_id']}'";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_affected_rows($query);
	if($numrows == 1){
		return true;
	}
	else{
		return false;
	}
}
function change_password($old_pass, $new_pass){
	$con = conct();
	$old_pass = sha1($old_pass);
	$new_pass = sha1($new_pass);
	$qqq = "UPDATE users SET password='{$new_pass}' WHERE id='{$_SESSION['user_id']}' AND password='{$old_pass}'";
	mysqli_query($con, $qqq);
	if(mysqli_affected_rows($con) == 1){
		return true;
	}
	else{
		return false;
	}
}
function reset_password($new_pass, $email){
	$con = conct();
	$email = $con -> real_escape_string($email);
	$new_pass = sha1($new_pass);
	$qqq = "UPDATE users SET password='{$new_pass}' WHERE email='{$email}'";
	mysqli_query($con, $qqq);
	if(mysqli_affected_rows($con) == 1){
		return true;
	}
	else{
		return false;
	}
}
function user_exists($username){
	$con = conct();
	$username = $con -> real_escape_string($username);
	$qqq = "SELECT username FROM users WHERE username='{$username}'";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	if($numrows == 0){
		return false;
	}
	else{
		return true;
	}
}
function create_account($email, $username, $pass){
	$con = conct();
	$email = $con -> real_escape_string($email);
	$username = $con -> real_escape_string($username);
	$password = sha1($pass);
	$qqq = <<<EOL
INSERT INTO users(email, username, password, type)
values
    ('{$email}', '{$username}', '{$password}', 'user')
;
EOL;
	mysqli_query($con, $qqq);
	if(mysqli_affected_rows($con) == 1){
		return true;
	}
	return false;
}
function mk_user(){
	$con = conct();
	$qqq = "DROP TABLE users;";
	$query = mysqli_query($con, $qqq);

	$qqq = <<<QUERY
CREATE TABLE users(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	email CHAR(128) NOT NULL,
	username CHAR(32) NOT NULL,
	password CHAR(40) NOT NULL,
	type CHAR(5) NOT NULL
);
QUERY;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$password = sha1('fuck___');
	$qqq = <<<EOL
INSERT INTO users(email, username, password, type)
values
    ('admin@hukers.org', 'admin', '{$password}', 'admin')
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
}
?>