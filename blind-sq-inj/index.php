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

$path = explode("/", explode("?", $_SERVER['REQUEST_URI'])[0]);
$msg = '';
switch($path[1] ?? ''){
    case 'login':
        if($_POST){
            $msg = 'Username or password incorrect';
            if(login($_POST['un']??'', $_POST['pw']??'')){
                http_response_code(302);
                header('Location: /');
                exit;
            }
        }
        out('login');
    case 'register':
        switch($_POST['action'] ?? ''){
            case 'select_username':
                $u = $_POST['u'] ?? '';
                if($u == ''){
                    $msg = 'Missing required parameters !';
                }
                elseif(!bug_chk_uname($u)){
                    $msg = 'Sorry the username is occupied !';
                }
                else{
                    out('register', ['username' => $u]);
                }
                out('username');
            case 'register':
                $u_n = $_POST['un'] ?? '';
                $u_f = $_POST['fn'] ?? '';
                $u_l = $_POST['ln'] ?? 'NULL';
                $p = $_POST['pw'] ?? '';
                if($u_n == '' || $u_f == '' || $p == ''){
                    $msg = 'Missing required parameters !';
                }
                elseif(!chk_uname($u_n)){
                    $msg = 'Sorry the username is occupied !';
                }
                else{
                    if(reg_usr($u_f, $u_l, $u_n, $p)){
                        http_response_code(302);
                        header('Location: /login');
                        exit;
                    }
                    else{
                        $msg = 'Something went wrong !';
                    }
                }
                out('register');
            default:
                break;
        }
        out('username');
    case 'profile':
        if(!isset($_SESSION['usr'])){
            http_response_code(302);
            header('Location: /login');
            exit;
        }
        switch($_POST['action'] ?? ''){
            case 'update_proc':
                $n_uf = $_POST['new_unf'] ?? '';
                $n_ul = $_POST['new_unl'] ?? 'NULL';
                $n_u = $_POST['new_un'] ?? 'NULL';
                if($n_uf == ''){
                    $msg = 'Missing required parameters !';
                    break;
                }
                if(update_usr($n_u, $n_uf, $n_ul)){
                    $msg = 'Successful !';
                    break;
                }
                else{
                    $msg = 'Something went wrong !';
                    break;
                }
            case 'update_pw':
                $o_p = $_POST['old_pw'] ?? '';
                $n_p = $_POST['new_pw'] ?? '';
                if($o_p == '' || $n_p == ''){
                    $msg = 'Missing required parameters !';
                    break;
                }
                if(update_pw($o_p, $n_p)){
                    $msg = 'Successful !';
                    break;
                }
                else{
                    $msg = 'Something went wrong !\n\nWRONG PASSWORD??';
                    break;
                }
            case 'logout':
                session_destroy();
                http_response_code(302);
                header('Location: /');
                exit;
            default:
                break;
        }
        out('profile', $_SESSION);
    case 'news':
        if($_POST && ($_SESSION['usr_type'] ?? '') == 'admin'){
            if(isset($_POST['title']) && isset($_POST['content']) && isset($_SESSION['nickname'])){
                add_news($_POST['title'], $_POST['content'], $_SESSION['nickname']);
            }
        }
        $writer = 'hidden';
        if(($_SESSION['usr_type'] ?? '') == 'admin'){
            $writer = 'visible';
        }
        $news = '';
        foreach(latest_news_table() as $r){
            $news .= $r;
        }
        out('news', ['news' => $news, 'writer' => $writer]);
    case 'challenge':
        out('challenge');
    default:
        out('index');
}