<?php
include '../config.php';
//mk_fakemail_user();
//mk_mailbox();
function conct(){
	global $DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME;
	$con = new mysqli($DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME);    //connect
	if($con->connect_error){                                                           //checks connection
		http_response_code(400);
		echo "ERROR Database Connection Failed: " . $con->connect_error, E_USER_ERROR;   //reports a DB connection failure
	}
	return $con;
}
function login($username, $password){
	$con = conct();
	$username = $con -> real_escape_string($username);
	$password = sha1($password);
	$qqq = "SELECT username FROM fakemail_user WHERE username='{$username}' AND password='{$password}'";
	$query = mysqli_query($con, $qqq);
	$numrows = mysqli_num_rows($query);
	if($numrows == 1){
		$row = mysqli_fetch_assoc($query);
		$_SESSION['fake_mail_user'] = $row['username'];
		return true;
	}
	return false;
}
function register($username, $password){
	$con = conct();
	$username = $con -> real_escape_string($username);
	$password = sha1($password);
	$qqq = <<<EOL
INSERT INTO fakemail_user(username, password)
values
    ('{$username}', '{$password}')
;
EOL;
	mysqli_query($con, $qqq);
	if(mysqli_affected_rows($con) == 1){
		return true;
	}
	return false;
}
function get_mails($username){
	$con = conct();
	$username = $con -> real_escape_string($username);
	$qqq = "SELECT id,date,sender,subject FROM mailbox WHERE mail_user='{$username}'";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	for($i=0;$i<$numrows;$i++){
		$row = mysqli_fetch_assoc($query);
		yield $row;
	}
}
function get_mesg($username, $mail_id){
	$con = conct();
	$username = $con -> real_escape_string($username);
	$mail_id = $con -> real_escape_string($mail_id);
	$qqq = "SELECT date,sender,subject,data FROM mailbox WHERE mail_user='{$username}' AND id='{$mail_id}'";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$row = mysqli_fetch_assoc($query);
	return $row;
}
function mk_fakemail_user(){
	$con = conct();
	$qqq = "DROP TABLE fakemail_user;";
	$query = mysqli_query($con, $qqq);

	$qqq = <<<QUERY
CREATE TABLE fakemail_user(
	username CHAR(32) NOT NULL PRIMARY KEY,
	password CHAR(40) NOT NULL
);
QUERY;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
}
function mk_mailbox(){
	$con = conct();
	$qqq = "DROP TABLE mailbox;";
	$query = mysqli_query($con, $qqq);

	$qqq = <<<QUERY
CREATE TABLE mailbox(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	date CHAR(20) NOT NULL,
	sender CHAR(40) NOT NULL,
	subject CHAR(255) NOT NULL,
	data TEXT NOT NULL,
	mail_user CHAR(32) NOT NULL
);
QUERY;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
}
?>