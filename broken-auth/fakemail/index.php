<?php
session_start();
include 'maildb.php';

function out($page, $args=[]){
    global $msg;
    $file = file_get_contents("templates/{$page}.html");
    $file = str_replace(['__MESSAGE__', '__YEAR__'], [$msg, date('Y')], $file);
    if(count($args) > 0){
        foreach($args as $k => $v){
            $k = strtoupper($k);
            $file = str_replace("__{$k}__", $v, $file);
        }
    }
    echo $file;
    exit;
}

$path = explode("/", explode("?", explode('fakemail', $_SERVER['REQUEST_URI'], 2)[1], 2)[0]);
$msg = '';
switch($path[1]??''){
	case 'login':
		if(isset($_POST['username']) && isset($_POST['password'])){
			if(login($_POST['username'], $_POST['password'])){
				http_response_code(302);
				header('Location: ./mail');
				exit;
			}
			else{
				$msg = 'Error: WRONG CREDENTIALS';
			}
		}
		out('login');
	case 'register':
		if(isset($_POST['username']) && isset($_POST['password'])){
			if(register($_POST['username'], $_POST['password'])){
				http_response_code(302);
				header('Location: ./login');
				exit;
			}
			else{
				$msg = 'Error: USERNAME EXISTS';
			}
		}
		out('register');
	case 'logout':
		unset($_SESSION['fake_mail_user']);
		http_response_code(302);
		header('Location: ./login');
		exit;
	default:
		break;
}
if(!isset($_SESSION['fake_mail_user'])){
	http_response_code(302);
	header('Location: ./login');
	exit;
}
if(isset($_GET['view'])){
	$message = get_mesg($_SESSION['fake_mail_user'], $_GET['view']);
	$message['data'] = str_replace("\n", '<br>', $message['data']);
	out('mail', $message);
}
$email = "{$_SESSION['fake_mail_user']}@{$_SERVER['SERVER_NAME']}";
$table = '';
foreach(get_mails($_SESSION['fake_mail_user']) as $mail){
	$table .= "<tr><td>{$mail['date']}</td><td>{$mail['sender']}</td><td><a href=\"?view={$mail['id']}\">{$mail['subject']}</a></td></tr>";
}
out('index', ['email'=>$email, 'table'=>$table]);