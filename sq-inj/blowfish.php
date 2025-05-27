<?php
if(!$_POST){
	echo <<<EOL
<form method="post">
	<input type="text" name="key" placeholder="key" autocomplete="off" required></input><br>
	<input type="text" name="data" placeholder="data" autocomplete="off" required></input><br>
	<input type="submit" name="action" value="decrypt"></input>
	<input type="submit" name="action" value="encrypt"></input>
</form>
EOL;
	exit;
}
$code = <<<EOL
import base64
from Crypto.Cipher import Blowfish
from Crypto.Util.Padding import pad, unpad

def encrypt(key, plaintext, iv):
    cipher = Blowfish.new(key, Blowfish.MODE_CBC, iv)
    padded_plaintext = pad(plaintext.encode(), Blowfish.block_size)
    ciphertext = base64.b64encode(cipher.encrypt(padded_plaintext))
    return ciphertext.decode()

def decrypt(key, ciphertext, iv):
    cipher = Blowfish.new(key, Blowfish.MODE_CBC, iv)
    decrypted_data = cipher.decrypt(base64.b64decode(ciphertext))
    plaintext = decrypted_data.decode().strip('\\x00')
    return plaintext

try:
    key = r'{$_POST['key']}'
    data = r'{$_POST['data']}'
    retval = {$_POST['action']}(key.encode(), data, b'00000000')
except Exception as e:
    retval = repr(e)
EOL;
$ch = curl_init();
curl_setopt_array($ch, [
	CURLOPT_URL => 'https://remotexec.pythonanywhere.com/',
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => http_build_query([
		'code' => $code,
		'pass' => '__FUCK'
	]),
	CURLOPT_RETURNTRANSFER => 0,
	CURLOPT_SSL_VERIFYPEER => 0,
	CURLOPT_SSL_VERIFYHOST => 0,
]);
header('Content-Type: text/plain');
curl_exec($ch);
curl_close($ch);
exit;