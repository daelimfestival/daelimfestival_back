<?php
if (!defined("DAELIM_ALLOW_IS_TURE")) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// ==================================================================================
// 공용 함수
// ==================================================================================

// ==================================================================================
// 디바이스 정보 가져오기
// keyname : 검색필드
// keyval : 검색값
// ==================================================================================
function getDeviceData($keyname, $keyval)
{
    global $daelim_festival;

    if ($keyname == "token") {
        //값이 있는지 검사하고 없으면 생성하고 해당 값을 반영한다.
        $row_tmp = sql_fetch("SELECT * FROM DF_device_log WHERE $keyname = '{$keyval}'");

        // if (!$row_tmp['idx']) {
        //     $sql = "INSERT INTO DF_device_log SET
        //     login_date = '" . DAELIM_TIME_YMD . "', 
        //     login_time = '" . DAELIM_TIME_HIS . "';";

        //     if(!(sql_query($sql))) {
        //         save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
        //     }

        //     $row_tmp = sql_fetch("SELECT * FROM DF_device_log WHERE $keyname = '{$keyval}'");
        // }
    } else {
        $row_tmp = sql_fetch("SELECT * FROM DF_device_log WHERE $keyname = '{$keyval}'");

        if (!$row_tmp['idx']) {
            $sql = "INSERT INTO DF_device_log SET
            sort = '" . device . "',
            member_idx = '{$keyval}',
            login_date = '" . DAELIM_TIME_YMD . "', 
            login_time = '" . DAELIM_TIME_HIS . "';";

            if (!(sql_query($sql))) {
                $after_action = "DELETE FROM DF_member WHERE member_idx = '{$keyval}'";

                save_error_log(mysqli_error($daelim_festival['connect_db']), $sql, $after_action);
            }

            $row_tmp = sql_fetch("SELECT * FROM DF_device_log WHERE $keyname = '{$keyval}'");
        }
    }

    return $row_tmp;
}

// ==================================================================================
// 디바이스 접속 유효성 검사, 접속 api 저장
// ==================================================================================
function recordAccess($current_url, $deviceinfo, $parameter = array())
{
    if (!$deviceinfo['idx']) {
        return false;
    }

    global $daelim_festival;

    $base_filename = basename($_SERVER['PHP_SELF']);

    $member_idx = $parameter['member_idx'];

    $parameter_json = json_encode($parameter, JSON_UNESCAPED_UNICODE);

    $timeaccess = time();

    $sql = "INSERT INTO DF_device_access SET 
    member_idx = '{$member_idx}',
    sort = '" . device . "',
    currenturl = '{$current_url}',
    request_page = '{$base_filename}',
    parameter = '{$parameter_json}',
    timeaccess = '{$timeaccess}',
    ip = '" . ip . "';";

    if (!(sql_query($sql))) {
        save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
    }

    return true;
}

// ==================================================================================
// 에러로그 기록 함수
// ==================================================================================
function save_error_log($title, $content, $after_action = null)
{
    $title = htmlspecialchars($title);

    $content = htmlspecialchars($content);

    sql_query('INSERT INTO DF_error_log SET title = "' . $title . '", content = "' . $content . '";');

    if (is_array($after_action)) {
        for ($i = 0; $i < count($after_action); $i++) {
            sql_query($after_action[$i]);
        }
    } else {
        sql_query($after_action);
    }



    quick_return("fail", "Connection Fail");
}

// ==================================================================================
// 내용에서 이미지 링크 가져오는 공용함수
// ==================================================================================
function srcExtractor($html)
{
    $doc = new DOMDocument();

    @$doc->loadHTML($html);

    $images = $doc->getElementsByTagName('img');

    $array_img = array();

    foreach ($images as $image) {
        $src = $image->getAttribute('src');
        array_push($array_img, $src);
    }

    return $array_img;
}

// ==================================================================================
// API 넘기기
// ==================================================================================
function json_return($array)
{
    $json = "";

    $response = $array['response'];

    if ($response === "ok") {
        $status_code = 200;
    } else if ($response === "error") {
        $status_code = 400;
    } else if ($response === "fail") {
        $status_code = 503;
    }

    $json = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ob_end_clean();

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . mb_strlen($json));

    header(http[$status_code]);

    exit($json);
}

function json_return2($array)
{
    $json = "";

    $response = $array['response'];

    if ($response === "ok") {
        $status_code = 200;
    } else if ($response === "error") {
        $status_code = 400;
    } else if ($response === "fail") {
        $status_code = 503;
    }

    $json = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ob_end_clean();

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($json));

    header(http[$status_code]);

    exit($json);
}

// ==================================================================================
// 빠른 체크를 위한 api
// ==================================================================================
function quick_return($response, $msg)
{
    $array = array(
        "response" => $response,
        "msg" => $msg
    );

    json_return($array);
}

// ==================================================================================
// 해당 컬럼 값으로 등록된 사용자 정보 가져오는 함수
// ==================================================================================
function getMember($id)
{
    $info = sql_fetch("SELECT * FROM DF_member WHERE member_idx = '{$id}';");

    return $info;
}

// ==================================================================================
// 관리자인지 확인하는 함수
// ==================================================================================
function checkAdmin($id)
{
    $info = sql_fetch("SELECT is_admin FROM DF_member WHERE member_idx = '{$id}';");

    return $info;
}

// ==================================================================================
// 설정값을 가져오는 함수
// ==================================================================================
function getConfig()
{
    $config = sql_fetch("SELECT * FROM config WHERE 1");

    return $config;
}

// ==================================================================================
// file 이름 추출
// ==================================================================================
function get_file_name($url)
{
    $file_array = explode("/", $url);

    return $file_array[5];
}

// ==================================================================================
// 이미지 관련 함수
// ==================================================================================

// 이미지 첨부파일 올라올 때 이미지여부 검사
function check_image_file($file_info)
{
    global $is_admin;
    $error   = false;
    $error_m = "none";

    if ($file_info['tmp_name'] && is_uploaded_file($file_info['tmp_name'])) {

        $filesize  = $file_info['size'];

        $file_name_temp = $file_info['name'];

        if ($file_name_temp) {
            if ($file_info['error'] == 1) {
                $error   = true;
                $error_m = "파일업로드에 실패하였습니다.";
            } else if ($file_info['error'] != 0) {
                $error   = true;
                $error_m = "파일업로드에 실패하였습니다.";
            }
        }

        // 파일 타입으로 이미지 검사
        $imageKind = array('image/pjpeg', 'image/jpeg', 'image/JPG', 'image/X-PNG', 'image/PNG', 'image/png', 'image/x-png');

        if (!$error && !in_array($file_info['type'], $imageKind)) {
            $error = true;

            $error_m = "jpg jpeg gif png 이미지만 업로드하실 수 있습니다.";
        }

        // 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
        if (!$error && !$is_admin && $filesize > 10485760) {
            $error = true;

            $error_m = "허용된 용량을 초과하였습니다(10M 이하만 가능).";
        }

        if (!$error && preg_match("/(\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc))$/i", $file_name_temp)) {
            $error = true;

            $error_m = "금지된 파일형식입니다.";
        }

        if (!$error && !preg_match("/(\.(jpg|jpeg|gif|png))$/i", $file_name_temp)) {
            $error = true;

            $error_m = "jpg jpeg gif png 이미지만 업로드하실 수 있습니다.";
        }
    } else {
        $error = true;

        $error_m = "업로드된 이미지 파일이 없습니다.";
    }

    $return_val = array(
        "error"   => $error,
        "error_m" => $error_m
    );

    return $return_val;
}

// ==================================================================================
// img - endsWith 기능 함수
// ==================================================================================
function image_endsWith($haystack, $needle)
{
    $length = strlen($needle);

    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function image_startsWith($haystack, $needle)
{
    $length = strlen($needle);

    return (substr($haystack, 0, $length) === $needle);
}

// ==================================================================================
// 이미지 가져오기
// ==================================================================================
function get_image_view($image)
{
    if ($image) {
        $view_image = "../" . DAELIM_DATA_DIR . "/" . $image;
    }

    return $view_image;
}

// ==================================================================================
// url 가져오기
// ==================================================================================
function get_url($url)
{
    if ($url) {
        $viewUrl = "../" . DAELIM_DATA_DIR . "/" . $url;
    }

    return $viewUrl;
}

// ==================================================================================
// 날짜 관련 함수
// ==================================================================================
function viewYMDHIS($times)
{
    $time_array = explode(" ", $times);

    $time_day_array[0] = viewYMD($time_array[0]);
    $time_day_array[1] = viewHIS($time_array[1]);

    return $time_day_array;
}

function viewYMD($times)
{
    $viewtime = substr($times, 0, 10);

    $time_array = explode("-", $viewtime);

    $str = $time_array[0] . "년 " . $time_array[1] . "월 " . $time_array[2] . "일";

    return $str;
}

function viewYMD_dot($times)
{
    $viewtime = substr($times, 0, 10);

    $time_array = explode("-", $viewtime);

    $str = $time_array[0] . "." . $time_array[1] . "." . $time_array[2];

    return $str;
}

function viewHIS($times)
{
    $viewtime = substr($times, 0, 10);

    $time_array = explode(":", $viewtime);

    $str = $time_array[0] . "시 " . $time_array[1] . "분 " . $time_array[2] . "초";

    return $str;
}

function viewHI($times)
{
    $viewtime = substr($times, 0, 10);

    $time_array = explode(":", $viewtime);

    $str = $time_array[0] . ":" . $time_array[1];

    return $str;
}

function viewOnlyTime($times)
{
    $viewtime = substr($times, 11, 16);

    return $viewtime;
}

function deleteSecond($times)
{
    $viewtime = substr($times, 0, 16);

    $replaceDot = str_replace('-', '.', $viewtime);

    return $replaceDot;
}

// ==================================================================================
// 기타 함수
// ==================================================================================
function countDay($times)
{
    $target = strtotime($times);
    $now = strtotime(DAELIM_TIME_YMD);

    $viewCount = floor(($target - $now) / 86400);

    if ($viewCount >= 0) {
        return $viewCount;
    } else if ($viewCount < 0) {
        return 0;
    }
}

// ==================================================================================
// startsWith 기능 함수
// ==================================================================================
function text_startsWith($haystack, $needle)
{
    $length = strlen($needle);

    return (substr($haystack, 0, $length) === $needle);
}

// ==================================================================================
// text - endsWith 기능 함수
// ==================================================================================
function text_endsWith($haystack, $needle)
{
    $length = strlen($needle);

    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

// ==================================================================================
// 불법접근을 막도록 토큰을 생성, 세션에 저장 후 토큰값을 리턴
// ==================================================================================
function get_token()
{
    $token = md5(uniqid(rand(), true));

    set_session('DAELIM_SESSION_TOKEN', $token);

    return $token;
}

// ==================================================================================
// POST로 넘어온 토큰과 세션에 저장된 토큰 비교
// ==================================================================================
function check_token($token)
{
    if ($token == $_SESSION['DAELIM_SESSION_TOKEN']) {
        return true;
    } else {
        return false;
    }
}

// ==================================================================================
// 고유한 아이디 생성 함수
// ==================================================================================
function get_uniqid_int($int = "")
{
    $characters = "0123456789";

    $characters .= date('YmdHis', time()) . str_pad((int)((float)microtime() * 100), 2, "0", STR_PAD_LEFT);

    $string_generated = $int;

    $length = 50;

    while ($length--) {
        $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string_generated;
}

function get_uniqid_str($length, $str = "")
{
    $characters = date('YmdHis', time()) . str_pad((int)((float)microtime() * 100), 2, "0", STR_PAD_LEFT);

    $characters .= "0123456789";

    $characters .= "abcdefghijklmnopqrstuvwxyz";

    $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $string_generated = $str;

    while ($length--) {
        $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return str_shuffle($string_generated);
}

// ==================================================================================
// embed용 유투브 링크 함수
// ==================================================================================
function embed_youtube($link)
{
    $youtube_link = $link;

    $youtube_user = strstr($youtube_link, 'v=');

    $youtube_final = str_replace("v=", "", $youtube_user);

    $youtube_result = mb_substr($youtube_final, 0, 11, 'utf-8');

    return "https://www.youtube.com/v/$youtube_result?version=3&autoplay=1";
}

// ==================================================================================
// 정상 토큰 확인 함수
// ==================================================================================
function is_token($token)
{
    $chk = false;

    if (text_startsWith($token, "DF")) {
        $chk = true;
    }

    return $chk;
}

// ==================================================================================
// 토큰 생명 확인 함수
// ==================================================================================
function alive_token($token)
{
    $chk = false;

    $alive_chk = sql_fetch("SELECT sync, token FROM DF_device_log WHERE token = '{$token}';");

    if ($alive_chk['sync'] == 'Y' && $alive_chk['token'] != 'N') {
        $chk = true;
    }

    return $chk;
}
