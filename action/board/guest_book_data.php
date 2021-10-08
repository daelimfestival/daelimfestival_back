<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

$guest_book_data_sql = sql_query("SELECT DFm.nickname, DFg.content, DFg.like_num, DFg.write_date FROM DF_guest_book AS DFg LEFT JOIN DF_member AS DFm ON DFg.member_idx = DFm.member_idx WHERE DFg.is_delete = 'N';");

if (is_token($token)) {
    $log = getDeviceData("token", $token);

    if ($log) {
        $member = getMember($log["member_idx"]);
    }
}

for ($i = 0; $res = $guest_book_data_sql->fetch_array(MYSQLI_ASSOC); $i++) {
    if ($response != "ok") {
        $response = "ok";
    }

    $guest_book_data_arr[$i] = array(
        "nickname" => $res["nickname"],
        "content" => $res["content"],
        "like_num" => $res["like_num"],
        "write_date" => viewYMD_dot($res["write_date"])
    );
}

$result = array(
    "response" => $response,
    "user_nickname" => $member['nickname'],
    "data" => $guest_book_data_arr
);

json_return2($result);
