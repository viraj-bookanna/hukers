<?php
if(isset($_POST['job']) && isset($_POST['password'])){
	if($_POST['password'] != '__FUCK'){
		echo 'wrong password';
		exit;
	}
	switch($_POST['job']){
		case 'full_install':
			$DB_ADDRESS = $_POST['DB_ADDRESS'] ?? "localhost";
			$DB_USER = $_POST['DB_USER'] ?? "root";
			$DB_PASS = $_POST['DB_PASS'] ?? "";
			$DB_NAME = $_POST['DB_NAME'] ?? "fuk";
			$password = $_POST['password'] ?? "";
			$c = <<<EOPHP
<?php
\$DB_ADDRESS = "{$DB_ADDRESS}";
\$DB_USER = "{$DB_USER}";
\$DB_PASS = "{$DB_PASS}";
\$DB_NAME = "{$DB_NAME}";
?>
EOPHP;
			file_put_contents('config.php', $c);
			chmod('config.php', 0644);
			include 'dbmgr.php';
			install();
			echo "Done !";
			exit;
		case 'reset_db':
			include 'dbmgr.php';
			install();
			echo "Done !";
			exit;
		default:
			break;
	}
}
else{
	echo <<<EOL
Full install:
<br>
<br>
<form method="post">
	<input type="hidden" name="job" value="full_install" required></input>
	<label>DB_ADDRESS: </label><input type="text" name="DB_ADDRESS" placeholder="required" required></input><br>
	<label>DB_USER: </label><input type="text" name="DB_USER" placeholder="required" required></input><br>
	<label>DB_PASS: </label><input type="text" name="DB_PASS"></input><br>
	<label>DB_NAME: </label><input type="text" name="DB_NAME" placeholder="required" required></input><br>
	<label>Install Password: </label><input type="text" name="password" placeholder="required" required></input><br>
	<input type="submit" value="install"></input><br>
</form>
<br>
Only reset Databases:
<br>
<br>
<form method="post">
	<input type="hidden" name="job" value="reset_db" required></input>
	<label>Install Password: </label><input type="text" name="password" placeholder="required" autofocus required></input><br>
	<input type="submit" value="install"></input><br>
</form>
EOL;
}
?>