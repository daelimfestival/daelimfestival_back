<?php
// ==================================================================================
// 공통 변수, 상수, 코드
// ==================================================================================
error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING);
ini_set("display_errors", 1);

// ==================================================================================
// extract($_GET); => page.php?_POST[var1]=data1&_POST[var2]=data2 와 같은 코드가 _POST 변수로 사용 방지
// ==================================================================================
$ext_arr = array(
    'PHP_SELF',
    '_ENV',
    '_GET',
    '_POST',
    '_FILES',
    '_SERVER',
    '_COOKIE',
    '_SESSION',
    '_REQUEST',
    'HTTP_ENV_VARS',
    'HTTP_GET_VARS',
    'HTTP_POST_VARS',
    'HTTP_POST_FILES',
    'HTTP_SERVER_VARS',
    'HTTP_COOKIE_VARS',
    'HTTP_SESSION_VARS',
    'GLOBALS'
);

$ext_cnt = count($ext_arr);

// POST, GET 으로 선언된 전역변수가 있다면 unset() 시킴
for ($i = 0; $i < $ext_cnt; $i++) {
    if (isset($_GET[$ext_arr[$i]])) {
        unset($_GET[$ext_arr[$i]]);
    }

    if (isset($_POST[$ext_arr[$i]])) {
        unset($_POST[$ext_arr[$i]]);
    }
}

// ==================================================================================
// 경로 함수
// ==================================================================================
function daelim_path()
{
    $chroot = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], dirname(__FILE__)));

    $result['path'] = str_replace('\\', '/', $chroot . dirname(__FILE__));

    $tilde_remove = preg_replace('/^\/\~[^\/]+(.*)$/', '$1', $_SERVER['SCRIPT_NAME']);

    $document_root = str_replace($tilde_remove, '', $_SERVER['SCRIPT_FILENAME']);

    $pattern = '/' . preg_quote($document_root, '/') . '/i';

    $root = preg_replace($pattern, '', $result['path']);

    $port = ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? '' : ':' . $_SERVER['SERVER_PORT'];

    $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';

    $user = str_replace(preg_replace($pattern, '', $_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_NAME']);

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

    if (isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host)) {
        $host = preg_replace('/:[0-9]+$/', '', $host);
    }

    $host = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", '', $host);

    if (isset($_REQUEST) && !empty($_REQUEST)) {
        $request = $_REQUEST;
        $result['request'] = $request;
    }

    $result['url'] = $http . $host . $port . $user . $root;

    return $result;
}

$daelim_path = daelim_path();

define('DAELIM_SESSION_PATH', $daelim_path['path'] . '/data/session');

$dbconfig_file = $daelim_path['path'] . '/data/dbconfig.php';
$s3config_file = $daelim_path['path'] . '/data/s3config.php';
$student_login_check_file = $daelim_path['path'] . '/data/student_login_check.php';

include_once($daelim_path['path'] . '/config.php');   // 설정 파일

define('DAELIM_COOKIE_DOMAIN',  '');

// ==================================================================================
// 다차원 배열에 사용자지정 함수적용 함수
// ==================================================================================
function array_map_deep($fn, $array)
{
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = array_map_deep($fn, $value);
            } else {
                $array[$key] = call_user_func($fn, $value);
            }
        }
    } else {
        $array = call_user_func($fn, $array);
    }

    return $array;
}

// ==================================================================================
// SQL Injection 대응 문자열 필터링 함수
// ==================================================================================
function sql_escape_string($str)
{
    $str = call_user_func('addslashes', $str);

    return $str;
}

// ==================================================================================
// 암호화 함수 지정 사이트 운영 중 설정을 변경하면 로그인이 안되는 등의 문제가 발생합니다.
// ==================================================================================
define('DAELIM_STRING_ENCTYPE_FUNCTION', 'sql_password');

// ==================================================================================
// escape string 처리 함수 지정, addslashes 로 변경 가능
// ==================================================================================
define('DAELIM_ESCAPE_FUNCTION', 'sql_escape_string');

// ==================================================================================
// SQL Injection 등으로 부터 보호를 위해 sql_escape_string() 적용
// magic_quotes_gpc 에 의한 backslashes 제거
// ==================================================================================
if (get_magic_quotes_gpc()) {
    $_POST = array_map_deep('stripslashes',  $_POST);
    $_GET = array_map_deep('stripslashes',  $_GET);
    $_COOKIE  = array_map_deep('stripslashes',  $_COOKIE);
    $_REQUEST = array_map_deep('stripslashes',  $_REQUEST);
}

// ==================================================================================
// sql_escape_string 적용
// ==================================================================================
$_POST = array_map_deep(DAELIM_ESCAPE_FUNCTION,  $_POST);
$_GET = array_map_deep(DAELIM_ESCAPE_FUNCTION,  $_GET);
$_COOKIE = array_map_deep(DAELIM_ESCAPE_FUNCTION,  $_COOKIE);
$_REQUEST = array_map_deep(DAELIM_ESCAPE_FUNCTION,  $_REQUEST);

// ==================================================================================
// PHP 4.1.0 부터 지원됨, php.ini 의 register_globals=off 일 경우
// ==================================================================================
@extract($_GET);
@extract($_POST);
@extract($_SERVER);

// ==================================================================================
// 공통
// ==================================================================================
$baseConfig = array();
$socialConfig = array();
$daelim_festival = array();

if (file_exists($dbconfig_file)) {
    include_once($dbconfig_file);
    include_once($daelim_path['path'] . '/common_lib.php'); // 공통 라이브러리

    $connect_db = sql_connect(DAELIM_HOSTNAME, DAELIM_USERNAME, DAELIM_PASSWORD) or die('MySQL Connect Error!!!');
    $connect_db_pdo = sql_connect(DAELIM_HOSTNAME, DAELIM_USERNAME, DAELIM_PASSWORD, true) or die('MySQL POD Connect Error!!!');
    $select_db  = sql_select_db(DAELIM_DATABASE, $connect_db) or die('MySQL DB Error!!!');

    // mysql connect resource $daelim_festival 배열에 저장 - 명랑폐인님 제안
    $daelim_festival['connect_db'] = $connect_db;
    $daelim_festival['connect_db_pdo'] = $connect_db_pdo;

    sql_set_charset('utf8mb4', $connect_db);
    if (defined('DAELIM_MYSQL_SET_MODE') && DAELIM_SET_MODE) sql_query("SET SESSION sql_mode = ''");
} else {
?>
    <!doctype html>
    <html lang="ko">

    <head>
        <meta charset="utf-8">
        <title>잘못된 접근</title>
    </head>

    <body>
        잘못된 접근입니다.
    </body>

    </html>
<?php
    exit;
}

if (file_exists($s3config_file)) {
    include_once($s3config_file);
}

if (file_exists($student_login_check_file)) {
    include_once($student_login_check_file);
}

unset($daelim_path);

// ==================================================================================
// SESSION 설정
// ==================================================================================
@ini_set("session.use_trans_sid", 0);
@ini_set("url_rewriter.tags", "");
// @ini_set("session.trans_sid_tags", "");

session_save_path(DAELIM_SESSION_PATH);

if (isset($SESSION_CACHE_LIMITER))
    @session_cache_limiter($SESSION_CACHE_LIMITER);
else
    @session_cache_limiter("nocache, must-revalidate");

ini_set("session.cache_expire", 300); // 세션 캐쉬 보관시간 (분)
ini_set("session.gc_maxlifetime", 3600); // session data의 garbage collection 존재 기간을 지정 (초)
ini_set("session.gc_probability", 1); // session.gc_probability는 session.gc_divisor와 연계하여 gc(쓰레기 수거) 루틴의 시작 확률을 관리합니다. 기본값은 1입니다. 자세한 내용은 session.gc_divisor를 참고하십시오.
ini_set("session.gc_divisor", 100); // session.gc_divisor는 session.gc_probability와 결합하여 각 세션 초기화 시에 gc(쓰레기 수거) 프로세스를 시작할 확률을 정의합니다. 확률은 gc_probability/gc_divisor를 사용하여 계산합니다. 즉, 1/100은 각 요청시에 GC 프로세스를 시작할 확률이 1%입니다. session.gc_divisor의 기본값은 100입니다.

session_set_cookie_params(0, '/');
ini_set("session.cookie_domain", DAELIM_COOKIE_DOMAIN);

session_start();

// common.php 파일을 수정할 필요가 없도록 확장합니다.
$extend_file = array();

$tmp[0] = dir(DAELIM_EXTEND_PATH);

while ($entry = $tmp[0]->read()) {
    // php 파일만 include 함
    if (preg_match("/(\.php)$/i", $entry))
        $extend_file[] = $entry;
}

if (!empty($extend_file) && is_array($extend_file)) {
    natsort($extend_file);

    foreach ($extend_file as $file) {
        include_once(DAELIM_EXTEND_PATH . '/' . $file);
    }

    unset($file);
}

unset($extend_file);

$is_admin = false;
$baseConfig = getConfig();

if ($_SESSION['df_admin_idx']) { // 로그인중이라면
    $chkAdmin = checkAdmin($_SESSION['df_admin_idx']);
    if ($chkAdmin['is_admin'] == 'Y') {
        $is_admin = true;
    }
}

// ==================================================================================
// 접속자의 ip주소를 리턴
// ==================================================================================
function get_client_ip()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } else if (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } else if (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else if (getenv('REMOTE_ADDR')) {
        $ip = getenv('REMOTE_ADDR');
    } else if ($_SERVER['REMOTE_ADDR']) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $error = true;
        $error_m = "IP주소를 확인할 수 없습니다.";

        $return_val = array(
            "error"   => $error,
            "error_m" => $error_m
        );

        exit($return_val);
    }

    return $ip;
}

function get_ip()
{
    $ip = "";
    $ip_regex = "/(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])/";

    if (getenv('HTTP_X_FORWARDED_FOR')) {
        if (preg_match($ip_regex, getenv('HTTP_X_FORWARDED_FOR'))) {
            $ip .= " |proxy| " . getenv('HTTP_X_FORWARDED_FOR');
        }
    }

    if (getenv('REMOTE_ADDR')) {
        if (preg_match($ip_regex, getenv('REMOTE_ADDR'))) {
            $ip .= " |ordinary| " . getenv('REMOTE_ADDR');
        }
    }

    if (!$ip) {
        $response = "error";
        $msg = "IP주소를 확인할 수 없습니다.";

        $return_val = array(
            "response" => $response,
            "msg" => $msg
        );

        exit($return_val);
    }

    return $ip;
}

// ==================================================================================
// 접속자가 모바일인지 PC인지 리턴
// ==================================================================================
function get_client_device()
{
    if (preg_match(DAELIM_AGENT_REGEXP, $_SERVER["HTTP_USER_AGENT"])) {
        $desktop_mobile = "mobile";
    } else {
        $desktop_mobile = "web";
    }

    return $desktop_mobile;
}

// ==================================================================================
// 접속자의 접속브라우저를 리턴
// ==================================================================================
function get_client_browser()
{
    $agent = $_SERVER["HTTP_USER_AGENT"];

    if (preg_match('/MSIE/i', $agent) && !preg_match('/Opera/i', $agent)) {
        $browser = 'Internet Explorer';
    } else if (preg_match('/Firefox/i', $agent)) {
        $browser = 'Mozilla Firefox';
    } elseif (preg_match('/Edg/i', $agent)) {
        $browser = 'Edge';
    } else if (preg_match('/Chrome/i', $agent)) {
        $browser = 'Google Chrome';
    } else if (preg_match('/Safari/i', $agent)) {
        $browser = 'Apple Safari';
    } elseif (preg_match('/Opera/i', $agent)) {
        $browser = 'Opera';
    } elseif (preg_match('/Netscape/i', $agent)) {
        $browser = 'Netscape';
    } else {
        $browser = "Other";
    }

    return $browser;
}
