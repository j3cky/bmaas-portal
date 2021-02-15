<?php
header ("Content-Type: image/webp");
//IPMI IP, User & Pass
$IP = "10.150.0.16";
$username = "admin";
$password = "admin";
//IPMI URLs
$url_login =  "http://$IP/cgi/login.cgi";
$url_capture = "http://$IP/cgi/CapturePreview.cgi";
$url_redirect = "http://$IP/cgi/url_redirect.cgi?url_name=Snapshot&url_type=img";
//Login to IPMI and get cookie
$postinfo = "name=$username&pwd=$password";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
curl_setopt($ch, CURLOPT_COOKIELIST,"");
curl_setopt($ch, CURLOPT_URL, $url_login);
$response = curl_exec($ch);
echo $response;
//$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); //opsiyon
//$header = substr($response, 0, $header_size); //opsiyon
//$response = substr($response, $header_size); //opsiyon
$cookies = curl_getinfo($ch, CURLINFO_COOKIELIST);
$cookie = preg_replace('/\s+/',' ',$cookies[0]);
$pieces = explode(' ', $cookie);
$cookie_secret = array_pop($pieces);
curl_close($ch);
//Tell IPMI to get a picture
$cookie = "Cookie: langSetFlag=0; language=English; SID=$cookie_secret; mainpage=system; subpage=sys_info";
$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, array($cookie));
curl_setopt($ch, CURLOPT_URL, $url_capture);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, 1);
$response = curl_exec($ch);
echo $response;
//Get the picture from IPMI
curl_setopt($ch, CURLOPT_URL, $url_redirect);
$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$response = substr($response, $header_size);
if(strlen($response)>10000)
        echo $response;
else
{
        $sleeping = file_get_contents("sleeping.webp");
        echo $sleeping;
}
curl_close($ch);
?>
