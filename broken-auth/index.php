<?php
session_start();
include 'dbmgr.php';

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

$SITE = $_SERVER['SERVER_NAME'];
$path = explode("/", explode("?", $_SERVER['REQUEST_URI'])[0]);
$msg = '';
switch($path[1] ?? ''){
    case 'login':
        if(isset($_SESSION['user'])){
            http_response_code(302);
            header('Location: /profile');
            exit;
        }
        if(isset($_POST['email']) && isset($_POST['pw'])){
            if(login($_POST['email'], $_POST['pw'])){
                http_response_code(302);
                header('Location: /');
                exit;
            }
            else{
                $msg = "Invalid credentials !";
            }
        }
        out('login');
    case 'register':
		switch($_POST['action']??''){
			case 'get_mail':
				$email = $_POST['email'] ?? '';
				$domain = preg_quote($SITE, '/');
				if(preg_match("/^[a-zA-Z0-9\.\+]+@{$domain}$/", $email)){
					$token = hash_hmac('md5', $email, '__LIvE__');
					$message = 'Hello, your email verification link for Vulnerable Site is below:\n\n<a target="_blank" href="http://__SITE__/confirm_email?email=__EMAIL__&token=__TOKEN__">CONFIRMATION LINK</a>';
					$message = str_replace(['__SITE__', '__EMAIL__', '__TOKEN__', '\n'], [$SITE, $email, $token, "\n"], $message);
					custom_mail($email, "Confirm your email", $message);
					$msg = "We have sent you an email to confirm your account.<br>now you can close this tab and follow the link in email to continue creating your account.";
				}
				else{
					$msg = "As this is a demo site, we don't support real emails<br>You have to create a fake email account from our <a href=\"/fakemail/mail\" target=\"_blank\">practice section</a> to use in this site";
				}
				out('get_mail');
			case 'confirm_mail':
				$email = $_POST['email'] ?? '';
				$token = $_POST['token'] ?? '';
				if(hash_hmac('md5', $email, '__LIvE__') != $token){
					$msg = 'the confirmation link is not valid<br><a href="/index">Return to home page</a>';
					out('message');
				}
				out('create_acc', $_POST);
			case 'create_acc':
				$email = $_POST['email'] ?? '';
				$token = $_POST['token'] ?? '';
				$username = $_POST['username'] ?? '';
				$pass = $_POST['pass'] ?? '';
				if(user_exists($username)){
					$msg = "sorry, the username is occupied";
				}
				elseif(hash_hmac('md5', $email, '__LIvE__') == $token){
					$msg = "account creation failed, try again";
					if(create_account($email, $username, $pass)){
						http_response_code(302);
						header('Location: /login');
						exit;
					}
				}
				out('create_acc', $_POST);
			default:
				break;
		}
		out('get_mail');
	case 'confirm_email':
		if(hash_hmac('md5', $_GET['email'], '__LIvE__') != $_GET['token']){
			$msg = 'the confirmation link is not valid<br><a href="/index">Return to home page</a>';
			out('message');
		}
		$msg = 'You have successfully confirmed your email<br>';
		out('confirm', $_GET);
	case 'reset_password':
		switch($_POST['action']??''){
			case 'get_mail':
				$email = $_POST['email'] ?? '';
				$domain = preg_quote($SITE, '/');
				if(preg_match("/^[a-zA-Z0-9\.\+]+@{$domain}$/", $email)){
					$code = rand(100000,999999);
					$token = hash_hmac('md5', $code, '__LIvE__');
					$message = "Dear user,\nYou have requested to reset your password in Vulnerable site\n\nHere is your password reset code <pre>__CODE__</pre>\n\nIf you haven't requested to reset your password, please ignore this email\nAND DO NOT GIVE THE CODE TO ANYONE ELSE";
					$message = str_replace(['__CODE__'], [$code], $message);
					custom_mail($email, "Password reset code", $message);
					$_SESSION['retry'] = 1;
					out('get_code', ['email' => $email,'token' => $token]);
				}
				else{
					$msg = "As this is a demo site, we don't support real emails<br>You have to create a fake email account from our <a href=\"/fakemail/mail\" target=\"_blank\">practice section</a> to use in this site";
				}
				out('reset');
			case 'get_code':
				$code = $_POST['code'] ?? '';
				$token = $_POST['token'] ?? '';
				$email = $_POST['email'] ?? '';
				if(hash_hmac('md5', $code, '__LIvE__') == $token){
					out('reset_pw', $_POST);
				}
				elseif($_SESSION['retry'] > 3){
					$msg = "Max retry attempts reached";
				}
				else{
					$_SESSION['retry'] += 1;
					$msg = "Invalid code";
				}
				out('get_code', $_POST);
			case 'reset_pw':
				$code = $_POST['code'] ?? '';
				$token = $_POST['token'] ?? '';
				$new_password = $_POST['new_password'] ?? '';
				if(isset($_POST['email']) && hash_hmac('md5', $code, '__LIvE__') == $token){
					if(reset_password($new_password, $_POST['email'])){
						$msg = 'Password reset successfully<br>You can now <a href="/login">login</a> with your new password';
						out('message');
					}
					else{
						$msg = "Something went wrong";
					}
				}
				else{
					$msg = "ERROR";
				}
				out('reset_pw', $_POST);
		}
		out('reset');
	case 'profile':
        if(!isset($_SESSION['user'])){
            http_response_code(302);
            header('Location: /login');
            exit;
        }
        switch($_POST['action']??''){
            case 'change_username':
                if(change_username($_POST['username'] ?? 'NULL')){
                    $msg = "SUCCESS !";
                }
                else{
                    $msg = "Something went wrong";
                }
                break;
            case 'change_password':
                if(change_password($_POST['old_password'] ?? 'NULL', $_POST['new_password'] ?? 'NULL')){
                    $msg = "SUCCESS !";
                }
                else{
                    $msg = "Something went wrong";
                }
                break;
            default:
                break;
        }
        out('profile', $_SESSION);
	case 'admin':
		if(($_SESSION['user_type']??'')!='admin'){
			http_response_code(401);
			exit;
		}
		if(isset($_POST['message'])){
			file_put_contents('.adm_msg', $_POST['message']);
			$msg = "Message Updated Successfully !";
		}
		$message = file_exists('.adm_msg') ? file_get_contents('.adm_msg') : '';
		out('admin', ['user'=> $_SESSION['user'], 'a_message' => $message]);
    case 'logout':
        session_destroy();
        http_response_code(302);
        header('Location: /');
        exit;
	default:
		$message = file_exists('.adm_msg') ? file_get_contents('.adm_msg') : '';
		$a_card = ($_SESSION['user_type']??'')=='admin'?'visible':'hidden';
        out('index', ['a_card'=> $a_card, 'admin_msg'=> $message]);
}
