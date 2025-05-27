<?php
include 'config.php';

function install(){
	global $DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME;
	$con = new mysqli($DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME);    //connect
	if($con->connect_error){                                                           //checks connection
		http_response_code(400);
		echo "ERROR Database Connection Failed: " . $con->connect_error, E_USER_ERROR;   //reports a DB connection failure
	}
	$qqq = "DROP TABLE usr;";
	$query = mysqli_query($con, $qqq);

	$qqq = "CREATE TABLE usr(u_id int, pw char(40), user_type char(20));";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));

	$p = [
		sha1('unbreakableAdmin#<|>?'),
		sha1('123'),
		sha1('CuStOm'),
		sha1('blah blah'),
		sha1('dragon'),
		sha1('test'),
	];

	$qqq = <<<EOL
INSERT INTO usr
(u_id, pw, user_type)
values
(42365975, '{$p[0]}', 'admin'),
(44444444, '{$p[1]}', 'user'),
(12345678, '{$p[2]}', 'user'),
(42356989, '{$p[3]}', 'user'),
(20000000, '{$p[4]}', 'user'),
(24356795, '{$p[5]}', 'user');
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
}
