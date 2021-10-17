<?php
include_once('../../common.php');
include_once('../_common.php');

// base data
$token = clean_xss_tags(htmlspecialchars(trim($json->token)), 1);

// pagination
$page = clean_xss_tags(htmlspecialchars(trim($json->page)), 1);

if (!$page) {
    $page = 1;
}

$limit = ($page - 1) * 25;

$guest_book_list = array();

$response = "ok";
$msg = "리스트 출력 완료";

$sql = "SELECT COUNT(idx) AS cnt FROM DF_guest_book WHERE is_delete = 'N';";

$guest_book_list_sql = sql_query("SELECT DFg.idx, DFg.content, DFg.write_date FROM DF_guest_book AS DFg INNER JOIN DF_member AS DFm ON DFg.member_idx = DFm.member_idx WHERE DFg.is_delete = 'N' ORDER BY DFg.idx DESC LIMIT $limit, 25;");

for ($i = 0; $res = sql_fetch_array($guest_book_list_sql); $i++) {
    $guest_book_list_data = array(
        "idx" => $res['idx'],
        "content" => $res["content"],
        "write_date" => viewYMD_dot($res["write_date"])
    );

    array_push($guest_book_list, $guest_book_list_data);
}

$total = sql_fetch("SELECT COUNT(idx) AS cnt FROM DF_guest_book WHERE is_delete = 'N';")['cnt'];

$result = array(
    "response" => $response,
    "msg" => $msg,
    "total" => $total,
    "list" => $guest_book_list
);

json_return2($result);
