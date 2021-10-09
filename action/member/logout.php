<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$current_url = clean_xss_tags(htmlspecialchars(trim($json->current_url)), 1);
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

if (is_token($token)) {
    $log = getDeviceData("token", $token);

    if ($log) {
        $parameter = array(
            "current_url" => $current_url,
            "member_idx" => $log["member_idx"]
        );

        recordAccess($current_url, $log, $parameter);

        $sql = "UPDATE DF_device_log SET
        sync = 'N',
        token = 'N',
        login_date = '" . DAELIM_TIME_YMD . "', 
        login_time = '" . DAELIM_TIME_HIS . "' 
        WHERE token = '{$token}';";

        if (!(sql_query($sql))) {
            save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
        }

        $response = "ok";
        $msg = "로그아웃 되었습니다.";
        $status_code = 200;
    } else {
        $response = "error";
        $msg = "이미 로그아웃 되어있습니다.";
        $status_code = 400;
    }
} else {
    $response = "error";
    $msg = "정상적인 접근이 아닙니다.";
    $status_code = 400;
}

$result = array(
    "response" => $response,
    "msg" => $msg
);

json_return($result, $status_code);
