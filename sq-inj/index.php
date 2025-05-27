<?php
include 'config.php';

function x($data){
	$key = openssl_random_pseudo_bytes(16);
	$iv = openssl_random_pseudo_bytes(16);
	$hkey = bin2hex($key);
	$hiv = bin2hex($iv);
	$random = uniqid('div-');
	$ciphertext = bin2hex(openssl_encrypt($data, 'aes-128-cbc', $key, 1, $iv));
	return <<<DECODER
	<div id="{$random}"></div>
<script>
var a = toNumbers("{$hkey}"),
    b = toNumbers("{$hiv}"),
    c = toNumbers("{$ciphertext}");
theDoctor = new TextDecoder("utf-8");
document.querySelector("#{$random}").innerHTML = theDoctor.decode(new Uint8Array(slowAES.decrypt(c, 2, a, b)));
</script>
DECODER;
}
function scramble($out){
	$p = rand(2, 5);
	$realout = implode('<!--'.str_shuffle('f*ck1ng sc@nners').'-->', str_split($out, $p));
	return x($realout);
}
function out($message=''){
	$template = file_get_contents('template.html');
	echo str_replace(['__MESSAGE__', '__YEAR__'], [$message, date('Y')], $template);
	exit;
}

if(isset($_POST['u_id']) && isset($_POST['pw'])){
	$u_id = $_POST['u_id'];
	$pass = sha1($_POST['pw']);
	$con = new mysqli($DB_ADDRESS,$DB_USER,$DB_PASS,$DB_NAME);
    if($con->connect_error){
        http_response_code(400);
        echo "ERROR Database Connection Failed: " . $con->connect_error, E_USER_ERROR;
        exit;
    }
    $u_id = $con -> real_escape_string($u_id);
	$qqq = "SELECT * FROM usr WHERE u_id=$u_id AND pw='$pass';";
	$query = mysqli_query($con, $qqq) or out(scramble(mysqli_error($con)));
	$numrows = mysqli_num_rows($query);
	if($numrows == 1){
		while($row = mysqli_fetch_assoc($query)){
			$dbusername = scramble($row['u_id']);
            $dbpassword = $row['pw'];
            $user_type = $row['user_type'];
		}
        if($user_type == 'admin'){
            out("Welcome, $dbusername ! You are logged in as an <b>Admin</b>.");
        }
		out("Welcome, $dbusername !");
	}
	out("Invalid Username or Password");
}
out();