<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$current_url = clean_xss_tags(htmlspecialchars(trim($json->current_url)), 1);
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

if (is_token($token)) {
    $log = sql_fetch("SELECT * FROM DF_device_log WHERE token = '{$token}';");

    if ($log) {
        $parameter = array(
            "current_url" => $current_url,
            "member_idx" => $log["member_idx"]
        );

        recordAccess($current_url, $log, $parameter);

        sql_query("UPDATE DF_device_log SET
        sync = 'N',
        token = 'N',
        login_date = '" . DAELIM_TIME_YMD . "', 
        login_time = '" . DAELIM_TIME_HIS . "' 
        WHERE token = '{$token}';");

        quick_return("ok", "로그아웃 되었습니다.");
    } else {
        quick_return("error", "정상적인 접근이 아닙니다.");
    }
} else {
    quick_return("error", "정상적인 접근이 아닙니다.");
}
