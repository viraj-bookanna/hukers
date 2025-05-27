<?php
$in = $_GET['in'] ?? '';
$hm = $_GET['hm'] ?? '';
$out = "";
switch($_GET['action'] ?? ''){
	case 'HEX':
		$out = '0x'.bin2hex($in);
		break;
	case 'UNHEX':
		if(preg_match('#^(0x)?[0-9a-fA-F]+$#', $in) && strlen($in)%2==0){
			$out = hex2bin(str_replace('0x', '', $in));
		}
		break;
	case 'HASH':
		$out = hash($hm, $in);
		break;
	case 'UNHASH':
		$res = search($in);
		$private = $res['private'] ?? false;
		if(!$res['ok']){
			$out = "something went wrong";
		}
		elseif(!$private){
			$out = $res[$in] ?? 'not_found';
		}
		else{
			$out = "private_hash";
		}
		break;
}
echo <<<EOL
<table class="main">
	<tr><th>input</th><th>output</th></tr>
	<form>
	<tr><td><input type="text" name="in" value="{$in}" placeholder="input"></input></td><td><input type="text" value="{$out}" readonly></input></td></tr>
	<tr><td colspan="2">
		<input type="submit" name="action" value="HEX"></input><br><br>
		<input type="submit" name="action" value="UNHEX"></input><br><br>
		<input type="text" name="hm" value="{$hm}" placeholder="hash method"></input><br>
		<input type="submit" name="action" value="HASH"></input><br><br>
		<input type="submit" name="action" value="UNHASH"></input>
	</td></tr>
	</form>
</table>
<style>
html, body{padding:0;margin:0;}
html, body, .outer{height:100%;}
body{background-color:#444;color:#0f0;}
.inner{height:100%;}
.main{padding:0;margin:0;}
.main{text-align:center;width:100%;height:100%;}
input[type=text]{border:none;background-color:#000;color:#0f0;width:80%;height:40px;}
input[type=submit]{border:none;background-color:#750;color:#000;width:80%;height:40px;}
</style>
EOL;
exit;


function search($hash){
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => "http://lite.bluecode.info/?search[]=$hash&",
		CURLOPT_HTTPHEADER => ['Connection: close','User-Agent: MD5 LITE 2.4.5'],
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_TIMEOUT => 15,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	]);
	//curl_setopt($ch, CURLOPT_PROXY, 'socks5h://127.0.0.1:1080');
	//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8080');
	$response = curl_exec($ch);
	if(curl_errno($ch)){
		echo 'Error:'.curl_error($ch)."<br>ERR no:".curl_errno($ch);
		exit;
	}
	curl_close($ch);
	return json_decode($response, true);
}