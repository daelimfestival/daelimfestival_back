<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

if (is_token($token) && alive_token($token)) {
    $log = getDeviceData("token", $token);

    if ($log) {
        $member = getMember($log["member_idx"]);

        $vote_sql = sql_fetch("SELECT target_idx FROM DF_vote WHERE member_idx = {$log["member_idx"]}");

        $response = "ok";
    }
}

$result = array(
    "response" => $response,
    "vote" => $vote_sql
);

json_return($result);
