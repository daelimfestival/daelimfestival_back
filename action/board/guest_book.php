<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$current_url = clean_xss_tags(htmlspecialchars(trim($json->current_url)), 1);
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

if (is_token($token)) {
    $log = getDeviceData("token", $token);

    if ($log) {
        // input data
        $content = clean_xss_tags(htmlspecialchars(trim($json->content)), 1);

        $member_idx = $log["member_idx"];
        $parameter = array(
            "current_url" => $current_url,
            "member_idx" => $member_idx,
            "content" => $content
        );

        recordAccess($current_url, $log, $parameter);

        sql_query("INSERT INTO DF_guest_book SET 
        member_idx = $member_idx, 
        nickname = $nickname, 
        content = '{$content}', 
        write_date = '" . DAELIM_TIME_YMD . "', 
        write_time = '" . DAELIM_TIME_HIS . "';");

        $response = "ok";
        $msg = "방명록 등록이 정상적으로 처리 되었습니다.";
    } else {
        $response = "error";
        $msg = "로그아웃 되어있습니다.";
    }
} else {
    $response = "error";
    $msg = "정상적인 접근이 아닙니다.";
}

$result = array(
    "response" => $response,
    "msg" => $msg
);

json_return($result);
