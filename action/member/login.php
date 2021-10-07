<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$current_url = clean_xss_tags(htmlspecialchars(trim($json->current_url)), 1);

// input data
$member_idx = clean_xss_tags(htmlspecialchars(trim($json->id)), 1);
$password = clean_xss_tags(htmlspecialchars(trim($json->password)), 1);

$token = "N";

if ($msg = empty_mb_id($member_idx)) {
    quick_return("error", $msg);
}

if ($msg = valid_mb_id2($member_idx)) {
    quick_return("error", $msg);
}

if ($msg = empty_mb_pass($password)) {
    quick_return("error", $msg);
}

if ($msg = valid_mb_pass($password)) {
    quick_return("error", $msg);
}

if ($msg = count_mb_pass($password)) {
    quick_return("error", $msg);
}

$parameter = array(
    "current_url" => $current_url,
    "device" => device,
    "member_idx" => $member_idx,
    "password" => $password
);

if (student_login_check_curl($member_idx, $password) === "Y") {

    $member = getMember($member_idx);

    if (!$member) {
        sql_query("INSERT INTO DF_member SET 
        member_idx = '{$member_idx}', 
        password = '" . get_encrypt_string($password) . "';");

        $member = getMember($member_idx);
    }

    $log = getDeviceData("member_idx", $member_idx);

    if ($log) {
        // 이미 로그인 한 상태
        if ($log['sync'] == 'Y' && $log['token'] != 'N') {
            // update login log
            sql_query("UPDATE DF_device_log SET 
            sort = '" . device . "', 
            login_date = '" . DAELIM_TIME_YMD . "', 
            login_time = '" . DAELIM_TIME_HIS . "' 
            WHERE member_idx = '{$log['member_idx']}' AND token = '{$log['token']}';");

            $response = "ok";
            $msg = "이미 로그인하셨습니다.";
            $token = $log['token'];
        } else {
            // 처음 로그인하거나 로그아웃 상태일 경우
            $token = "DF" . get_uniqid_str(400, $member_idx);

            // update login log
            sql_query("UPDATE DF_device_log SET 
            sort = '" . device . "', 
            sync = 'Y', 
            token = '{$token}', 
            login_date = '" . DAELIM_TIME_YMD . "', 
            login_time = '" . DAELIM_TIME_HIS . "' 
            WHERE member_idx = '{$log['member_idx']}' AND token = '{$log['token']}';");

            $log['token'] = $token;

            $response = "ok";
            $msg = "success";

            recordAccess($current_url, $log, $parameter);
        }
    }
} else {
    $response = "error";
    $msg = "아이디 또는 비밀번호가 틀립니다.";
}

$result = array(
    "response" => $response,
    "msg" => $msg,
    "token" => $token
);

json_return($result);
