<?php
include_once('../../common.php');
include_once('../_common.php');

$guest_book_data_sql = sql_query("SELECT nickname, content, like_num, write_date FROM DF_guest_book WHERE is_delete = 'N';");

for ($i = 0; $res = $guest_book_data_sql->fetch_array(MYSQLI_ASSOC); $i++) {
    $guest_book_data_arr[$i] = array(
        "nickname" => $res["nickname"],
        "content" => $res["content"],
        "like_num" => $res["like_num"],
        "write_date" => viewYMD_dot($res["write_date"])
    );
}

json_return2($guest_book_data_arr);
