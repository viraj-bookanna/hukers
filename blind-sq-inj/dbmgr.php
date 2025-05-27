<?php
include 'config.php';
function conct(){
	global $DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME;
	$con = new mysqli($DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME);    //connect
	if($con->connect_error){                                                           //checks connection
		http_response_code(400);
		echo "ERROR Database Connection Failed: " . $con->connect_error, E_USER_ERROR;   //reports a DB connection failure
	}
	return $con;
}
function login($usr, $pass){
	$con = conct();
	$usr = $con -> real_escape_string($usr);
	$pass = sha1($pass);
	$qqq = "SELECT * FROM uxr_table WHERE (uxr_id='{$usr}' OR nickname='{$usr}') AND password='{$pass}';";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	if($numrows != 1){
		return false;
	}
	$row = mysqli_fetch_assoc($query);
	$_SESSION['usr'] = $row['uxr_id'];
	$_SESSION['usr_fname'] = $row['uxr_fname'];
	$_SESSION['usr_lname'] = $row['uxr_lname'];
	$_SESSION['usr_type'] = $row['uxr_type'];
	$_SESSION['nickname'] = $row['nickname'];
	return true;
}
function mk_users(){
	$con = conct();
	$qqq = "DROP TABLE uxr_table;";
	$query = mysqli_query($con, $qqq);

	$qqq = <<<EOC
CREATE TABLE uxr_table(
    uxr_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	uxr_fname CHAR(20) NOT NULL,
	uxr_lname CHAR(20),
	password CHAR(40) NOT NULL,
	nickname CHAR(32),
	uxr_type CHAR(10) NOT NULL
);
EOC;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$qqq = "ALTER TABLE uxr_table AUTO_INCREMENT=10000000;";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));

	$p = [
		sha1('123'),
		sha1('admin123'),
	];

	$qqq = <<<EOL
INSERT INTO uxr_table(uxr_fname, uxr_lname, password, nickname, uxr_type)
values
    ('Indika', 'Viraj', '{$p[0]}', 'ReverseLIvE', 'user'),
    ('Super', 'Admin', '{$p[1]}', 'administrator', 'admin')
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
}
function update_usr($new_nick, $new_fname, $new_lname){
	$con = conct();
	$new_fname = $con -> real_escape_string($new_fname);
	$new_lname = $con -> real_escape_string($new_lname);
	$new_nick = $con -> real_escape_string($new_nick);
	$qqq = <<<EOL
UPDATE uxr_table
SET
    uxr_fname='{$new_fname}',
    uxr_lname='{$new_lname}',
    nickname='{$new_nick}'
WHERE
    uxr_id={$_SESSION['usr']}
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$_SESSION['usr_fname'] = $new_fname;
	$_SESSION['usr_lname'] = $new_lname;
	$_SESSION['nickname'] = $new_nick;
	return true;
}
function update_pw($old_pass, $new_pass){
	$con = conct();
	$old_pass = sha1($old_pass);
	$new_pass = sha1($new_pass);
	$qqq = "SELECT password FROM uxr_table WHERE uxr_id={$_SESSION['usr']}";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	if($numrows != 1){
		return false;
	}
	$row = mysqli_fetch_assoc($query);
	$db_pass = $row['password'];
	if($db_pass != $old_pass){
		return false;
	}
	$qqq = <<<EOL
UPDATE uxr_table
SET
    password='{$new_pass}'
WHERE
    uxr_id={$_SESSION['usr']}
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	return true;
}
function reg_usr($fname, $lname, $uname, $pass){
	$con = conct();
	$uname = $con -> real_escape_string($uname);
	$fname = $con -> real_escape_string($fname);
	$lname = $con -> real_escape_string($lname);
	$pass = sha1($pass);
	$qqq = <<<EOL
INSERT INTO uxr_table(uxr_fname, uxr_lname, password, nickname, uxr_type)
values
    ('{$fname}', '{$lname}', '{$pass}', '{$uname}', 'user')
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	return true;
}
function mk_nws_tbl(){
	$con = conct();
	$qqq = "DROP TABLE nwxx_tbl;";
	$query = mysqli_query($con, $qqq);

	$qqq = <<<EOC
CREATE TABLE nwxx_tbl(
    title CHAR(50) NOT NULL,
	content TEXT(300) NOT NULL,
	writer char(32) NOT NULL
);
EOC;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));

	$qqq = <<<EOL
INSERT INTO nwxx_tbl(title, content, writer)
values
    ('Great NEWS', 'Site created', 'ReverseLIvE')
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
}
function latest_news_table(){
	$con = conct();
	$qqq = "SELECT * FROM nwxx_tbl;";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	for($i=0;$i<$numrows;$i++){
		$row = mysqli_fetch_assoc($query);
		$c = str_replace("\n", '<br>', $row['content']);
		yield <<<EOT
		<div class="news-item">
            <div class="news-title">{$row['title']}</div>
            <div class="news-content">
                {$c}
            </div>
            <div class="news-meta">
                <span class="onorr">Posted by:</span> @{$row['writer']}
            </div>
        </div>
EOT;
	}
}
function add_news($title, $content, $writer){
	$con = conct();
	$title = $con -> real_escape_string(htmlentities($title));
	$content = $con -> real_escape_string(htmlentities($content));
	$writer = $con -> real_escape_string($writer);

	$qqq = <<<EOL
INSERT INTO nwxx_tbl(title, content, writer)
values
    ('{$title}', '{$content}', '{$writer}')
;
EOL;
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	return true;
}
function chk_uname($username){
	$con = conct();
	$username = $con -> real_escape_string($username);
	$qqq = "SELECT * FROM uxr_table WHERE nickname='{$username}'";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	if($numrows == 0){
		return true;
	}
	return false;
}
function bug_chk_uname($username){
	$con = conct();
	//--------creating a bug here ;-)
	//$username = $con -> real_escape_string($username);
	$qqq = "SELECT * FROM uxr_table WHERE nickname='{$username}'";
	$query = mysqli_query($con, $qqq) or die(mysqli_error($con));
	$numrows = mysqli_num_rows($query);
	if($numrows == 0){
		return true;
	}
	return false;
}
?>