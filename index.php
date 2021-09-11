<?php
include_once('./common.php');

if (!$is_member) {
    goto_url(DAELIM_DOMAIN . 'admin/login.php');
}
