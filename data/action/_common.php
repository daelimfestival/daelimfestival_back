<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

$reactJSData = json_decode(file_get_contents("php://input"));

// json_decode will create an object so if you need in array format
$reactJSData = (array)$reactJSData;

if (get_magic_quotes_gpc()) {
    $reactJSData = array_map_deep('stripslashes', $reactJSData);
}

// sql_escape_string 적용
$reactJSData = array_map_deep(DAELIM_ESCAPE_FUNCTION, $reactJSData);
$_POST = $reactJSData;


/* API 액션 페이지 헤더 부분 */
$response = "error";
$msg = "Connection Fail";

$json = json_decode(urldecode(base64_decode($_POST['json'])));