<?php
if (!defined("DAELIM_ALLOW_IS_TURE")) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// ==================================================================================
// SQL 관련 함수 모음
// ==================================================================================

// ==================================================================================
// DB 연결
// ==================================================================================
function sql_connect($host, $user, $pass, $pdo = false)
{
    $db = DAELIM_DATABASE;
    if ($pdo == true) {
        $dsn = 'mysql:host=' . DAELIM_HOSTNAME . '; dbname=' . DAELIM_DATABASE . '; port=' . DAELIM_PORT . '; charset=' . DAELIM_CHARSET;
        try {
            $link = new PDO($dsn, DAELIM_USERNAME, DAELIM_PASSWORD);
        } catch (PDOException $Exception) {
            die("connet faile : " . $Exception->getMessage());
        }
    } else {
        if (function_exists('mysqli_connect') && DAELIM_MYSQLI_USE) {

            $link = mysqli_connect($host, $user, $pass, $db);

            if (mysqli_connect_errno()) {
                die('Connect Error: ' . mysqli_connect_error());
            }
        } else {
            $link = mysql_connect($host, $user, $pass);
        }
    }
    return $link;
}

// ==================================================================================
// DB 선택
// ==================================================================================
function sql_select_db($db, $connect)
{
    if (function_exists('mysqli_select_db') && DAELIM_MYSQLI_USE)
        return @mysqli_select_db($connect, $db);
    else
        return @mysql_select_db($db, $connect);
}

function sql_set_charset($charset, $link = null)
{
    global $daelim_festival;

    if (!$link)
        $link = $daelim_festival['connect_db'];

    if (function_exists('mysqli_set_charset') && DAELIM_MYSQLI_USE)
        mysqli_set_charset($link, $charset);
    else
        mysql_query(" set names {$charset} ", $link);
}

function sql_password($value)
{
    // mysql 4.0x 이하 버전에서는 password() 함수의 결과가 16bytes
    // mysql 4.1x 이상 버전에서는 password() 함수의 결과가 41bytes
    $row = sql_fetch(" select password('$value') as pass ");

    return $row['pass'];
}

// ==================================================================================
// mysqli_query 와 mysqli_error 를 한꺼번에 처리
// mysql connect resource 지정 - 명랑폐인님 제안
// ==================================================================================
function sql_query($sql, $error = DAELIM_DISPLAY_SQL_ERROR, $link = null)
{
    global $daelim_festival;

    if (!$link) {
        $link = $daelim_festival['connect_db'];
    }

    // Blind SQL Injection 취약점 해결
    $sql = trim($sql);
    // union의 사용을 허락하지 않습니다.
    //$sql = preg_replace("#^select.*from.*union.*#i", "select 1", $sql);
    $sql = preg_replace("#^select.*from.*[\s\(]+union[\s\)]+.*#i ", "select 1", $sql);
    // `information_schema` DB로의 접근을 허락하지 않습니다.
    $sql = preg_replace("#^select.*from.*where.*`?information_schema`?.*#i", "select 1", $sql);

    if (function_exists('mysqli_query') && DAELIM_MYSQLI_USE) {
        if ($error) {
            $result = @mysqli_query($link, $sql) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
        } else {
            $result = @mysqli_query($link, $sql);
        }
    } else {
        if ($error) {
            $result = @mysql_query($sql, $link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
        } else {
            $result = @mysql_query($sql, $link);
        }
    }

    return $result;
}

// ==================================================================================
// 쿼리를 실행한 후 결과값에서 한행을 얻는다
// ==================================================================================
function sql_fetch($sql, $error = DAELIM_DISPLAY_SQL_ERROR, $link = null)
{
    global $daelim_festival;

    if (!$link) {
        $link = $daelim_festival['connect_db'];
    }

    $result = sql_query($sql, $error, $link);
    $row = sql_fetch_array($result);
    return $row;
}

// ==================================================================================
// 결과값에서 한행 연관배열(이름으로)로 얻는다
// ==================================================================================
function sql_fetch_array($result)
{
    if (function_exists('mysqli_fetch_assoc') && DAELIM_MYSQLI_USE) {
        $row = @mysqli_fetch_assoc($result);
    } else {
        $row = @mysql_fetch_assoc($result);
    }

    return $row;
}


function sql_insert_id($link = null)
{
    global $daelim_festival;

    if (!$link) {
        $link = $daelim_festival['connect_db'];
    }
    if (function_exists('mysqli_insert_id') && DAELIM_MYSQLI_USE) {
        return mysqli_insert_id($link);
    } else {
        return mysql_insert_id($link);
    }
}

// ==================================================================================
// 문자열 암호화
// ==================================================================================
function get_encrypt_string($str)
{
    if (defined('DAELIM_STRING_ENCTYPE_FUNCTION') && DAELIM_STRING_ENCTYPE_FUNCTION) {
        $encrypt = call_user_func(DAELIM_STRING_ENCTYPE_FUNCTION, $str);
    } else {
        $encrypt = sql_password($str);
    }

    return $encrypt;
}

// ==================================================================================
// 세션변수 생성
// ==================================================================================
function set_session($session_name, $value)
{
    $session_name = $_SESSION[$session_name] = $value;
}
// ==================================================================================
// 세션변수값 얻음
// ==================================================================================
function get_session($session_name)
{
    return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : '';
}

// ==================================================================================
// 쿠키변수 생성
// ==================================================================================
function set_cookie($cookie_name, $value, $expire)
{
    setcookie(md5($cookie_name), base64_encode($value), time() + $expire, '/', '');
}

function get_real_client_ip()
{
    $real_ip = $_SERVER['REMOTE_ADDR'];

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $real_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return preg_replace('/[^0-9.]/', '', $real_ip);
}

// ==================================================================================
// 쿠키변수값 얻음
// ==================================================================================
function get_cookie($cookie_name)
{
    $cookie = md5($cookie_name);
    if (array_key_exists($cookie, $_COOKIE))
        return base64_decode($_COOKIE[$cookie]);
    else
        return "";
}

// ==================================================================================
// 휴대폰번호의 숫자만 취한 후 중간에 하이픈(-)을 넣는다
// ==================================================================================
function hyphen_hp_number($hp)
{
    $hp = preg_replace("/[^0-9]/", "", $hp);
    return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $hp);
}

// ==================================================================================
// 3.31
// HTML SYMBOL 변환
// &nbsp; &amp; &middot; 등을 정상으로 출력
// ==================================================================================
function html_symbol($str)
{
    return preg_replace("/\&([a-z0-9]{1,20}|\#[0-9]{0,3});/i", "&#038;\\1;", $str);
}

// ==================================================================================
// XSS 관련 태그 제거
// ==================================================================================
function clean_xss_tags($str, $check_entities = 0)
{
    $str_len = strlen($str);

    $i = 0;
    while ($i <= $str_len) {
        $result = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $str);

        if ($check_entities) {
            $result = str_replace(array('&colon;', '&lpar;', '&rpar;', '&NewLine;', '&Tab;'), '', $result);
        }

        if ((string)$result === (string)$str) {
            break;
        }

        $str = $result;
        $i++;
    }
    return $str;
}

// ==================================================================================
// 메타태그를 이용한 URL 이동
// header("location:URL") 을 대체
// ==================================================================================
function goto_url($url)
{
    $url = str_replace("&amp;", "&", $url);

    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script>';
        echo 'location.replace("' . $url . '");';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
    }
    exit;
}

// ==================================================================================
// 로그인 후 이동할 URL
// ==================================================================================
function login_url($url = '')
{
    if (!$url) {
        $url = DAELIM_DOMAIN;
    }

    return urlencode(clean_xss_tags(urldecode($url)));
}

if (!function_exists('json_encode')) {
    function json_encode($a = false)
    {
        // Some basic debugging to ensure we have something returned
        if (is_null($a)) {
            return 'null';
        }

        if ($a === false) {
            return 'false';
        }

        if ($a === true) {
            return 'true';
        }

        if (is_scalar($a)) {
            if (is_float($a)) {
                // Always use '.' for floats.
                return floatval(str_replace(',', '.', strval($a)));
            }

            if (is_string($a)) {
                static $jsonReplaces = array(array('\\', '/', "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            } else {
                return $a;
            }
        }
        $isList = true;
        for ($i = 0, reset($a); true; $i++) {
            if (key($a) !== $i) {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList) {
            foreach ($a as $v) {
                $result[] = json_encode($v);
            }

            return '[' . join(',', $result) . ']';
        } else {
            foreach ($a as $k => $v) {
                $result[] = json_encode($k) . ':' . json_encode($v);
            }

            return '{' . join(',', $result) . '}';
        }
    }
}

if (!function_exists('json_decode')) {
    function json_decode($json)
    {
        $comment = false;
        $out = '$x=';

        for ($i = 0; $i < strlen($json); $i++) {
            if (!$comment) {
                if (($json[$i] == '{') || ($json[$i] == '[')) {
                    $out .= ' array(';
                } else if (($json[$i] == '}') || ($json[$i] == ']')) {
                    $out .= ')';
                } else if ($json[$i] == ':') {
                    $out .= '=>';
                } else {
                    $out .= $json[$i];
                }
            } else {
                $out .= $json[$i];
            }
            if ($json[$i] == '"' && $json[($i - 1)] != "\\") {
                $comment = !$comment;
            }
        }

        eval($out . ';');

        return $x;
    }
}

function GetValue($str, $name)
{
    $pos1 = 0;
    $pos2 = 0;
    while ($pos1 <= strlen($str)) {
        $pos2 = strpos($str, ":", $pos1);
        $len = substr($str, $pos1, $pos2 - $pos1);
        $key = substr($str, $pos2 + 1, (int)$len);
        $pos1 = $pos2 + (int)$len + 1;
        if ($key == $name) {
            $pos2 = strpos($str, ":", $pos1);
            $len = substr($str, $pos1, $pos2 - $pos1);
            $value = substr($str, $pos2 + 1, (int)$len);
            return $value;
        } else {
            $pos2 = strpos($str, ":", $pos1);
            $len = substr($str, $pos1, $pos2 - $pos1);
            $pos1 = $pos2 + (int)$len + 1;
        }
    }
}

// 문자열이 한글, 영문, 숫자, 특수문자로 구성되어 있는지 검사
function check_string($str, $options)
{
    $s = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $c = $str[$i];
        $oc = ord($c);

        // 한글
        if ($oc >= 0xA0 && $oc <= 0xFF) {
            if ($options & DAELIM_HANGUL) {
                $s .= $c . $str[$i + 1] . $str[$i + 2];
            }
            $i += 2;
        }
        // 숫자
        else if ($oc >= 0x30 && $oc <= 0x39) {
            if ($options & DAELIM_NUMERIC) {
                $s .= $c;
            }
        }
        // 영대문자
        else if ($oc >= 0x41 && $oc <= 0x5A) {
            if (($options & DAELIM_ALPHABETIC) || ($options & DAELIM_ALPHAUPPER)) {
                $s .= $c;
            }
        }
        // 영소문자
        else if ($oc >= 0x61 && $oc <= 0x7A) {
            if (($options & DAELIM_ALPHABETIC) || ($options & DAELIM_ALPHALOWER)) {
                $s .= $c;
            }
        }
        // 공백
        else if ($oc == 0x20) {
            if ($options & DAELIM_SPACE) {
                $s .= $c;
            }
        } else {
            if ($options & DAELIM_SPECIAL) {
                $s .= $c;
            }
        }
    }

    // 넘어온 값과 비교하여 같으면 참, 틀리면 거짓
    return ($str == $s);
}

//해당 메시지띄우고 url로 이동
function alert($msg, $url = NULL)
{
    $str = "<script>alert('{$msg}');";

    isset($url) && !empty($url) ? $str .= "window.location.replace('{$url}')</script>" : $str .= "</script>";
    echo ("$str");
    exit;
}
