<?php
if(!isset($_POST['paste']) || !isset($_POST['url'])){
	out(false, "error, no paste data or url entered");
}
if(!is_dir("rawpaste")){
	mkdir("rawpaste", 644);
}
if(isset($_POST['paste'])){
	$pasteData = $_POST['paste'] ?? '';
}
if(isset($_POST['url'])){
	try{
		$pasteData = file_get_contents(($_POST['url'] ?? ''));
	}
	catch(Exception){
		out(false, "failed to fetch data");
	}
}
$paste_id = uniqid();

if(file_put_contents("rawpaste/$paste_id", $pasteData)){
	out(true, $paste_id);
}
else{
	out(false, "Something went wrong. Please try again");
}

function out($status, $msg){
	echo json_encode(['ok' => $status, 'output' => $msg]);
	exit;
}
?>