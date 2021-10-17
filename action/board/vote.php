<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$current_url = clean_xss_tags(htmlspecialchars(trim($json->current_url)), 1);
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

// input data
$vote = clean_xss_tags(htmlspecialchars(trim($json->vote)), 1);

if (is_token($token)) {
    $log = getDeviceData("token", $token);

    if ($log) {
        $sql = "SELECT * FROM DF_vote WHERE member_idx = '{$log["member_idx"]}';";

        $chk_vote = sql_fetch($sql);

        if (!$chk_vote) {
            $parameter = array(
                "current_url" => $current_url,
                "member_idx" => $log["member_idx"],
                "vote" => $vote
            );

            recordAccess($current_url, $log, $parameter);

            $sql = "INSERT INTO DF_vote SET
            target_idx = '{$vote}',
            member_idx  = '{$log["member_idx"]}';";

            if (!(sql_query($sql))) {
                save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
            }

            $sql = "UPDATE DF_member SET
            stamp = stamp + 1
            WHERE member_idx = '{$log["member_idx"]}';";

            if (!(sql_query($sql))) {
                save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
            }

            $response = "ok";
            $msg = "투표가 완료 되었습니다.";
        } else {
            $response = "error";
            $msg = "이미 투표 하셨습니다.";
        }
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
