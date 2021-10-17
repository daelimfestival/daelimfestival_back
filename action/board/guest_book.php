<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$current_url = clean_xss_tags(htmlspecialchars(trim($json->current_url)), 1);
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

// input data
$content = clean_xss_tags(htmlspecialchars(trim($json->content)), 1);

if (!empty_mb($content)) {
    quick_return("error", "내용을 입력해주세요.");
} else if ((mb_strlen($content, "UTF-8") > 100) && (strlen($content) > 100)) {
    quick_return("error", "최대 100글자까지 입력이 가능합니다.");
}

if (is_token($token)) {
    $log = getDeviceData("token", $token);

    if ($log) {
        $member_idx = $log["member_idx"];

        $parameter = array(
            "current_url" => $current_url,
            "member_idx" => $member_idx,
            "content" => $content
        );

        recordAccess($current_url, $log, $parameter);

        $cnt_guest_book = sql_fetch("SELECT COUNT(idx) AS cnt FROM DF_guest_book WHERE member_idx = '{$member_idx}';")['cnt'];

        if ($cnt_guest_book === "0") {
            $sql = "UPDATE DF_member SET
            stamp = stamp + 1
            WHERE member_idx = '{$member_idx}';";

            if (!(sql_query($sql))) {
                save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
            }
        }

        $sql = "INSERT INTO DF_guest_book SET 
        member_idx = $member_idx, 
        content = '{$content}', 
        write_date = '" . DAELIM_TIME_YMD . "', 
        write_time = '" . DAELIM_TIME_HIS . "';";

        if (!(sql_query($sql))) {
            save_error_log(mysqli_error($daelim_festival['connect_db']), $sql);
        }

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
