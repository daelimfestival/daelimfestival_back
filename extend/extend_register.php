<?php
if (!defined("DAELIM_ALLOW_IS_TURE")) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// ==================================================================================
// ID 유효성 확인 함수
// ==================================================================================

// 빈값 확인
function empty_mb_id($reg_mb_id)
{
    if (trim($reg_mb_id) == '') {
        return "회원아이디를 입력해 주십시오.";
    } else {
        return "";
    }
}

// 지정한 형식에 맞는지 확인
function valid_mb_id1($reg_mb_id)
{
    if (!preg_match(DAELIM_ID_REGEXP1, $reg_mb_id)) {
        return "회원아이디는 영문자, 숫자만 입력하세요.";
    } else {
        return "";
    }
}

function valid_mb_id2($reg_mb_id)
{
    if (!preg_match(DAELIM_ID_REGEXP2, $reg_mb_id)) {
        return "학번은 숫자만 입력하세요.";
    } else {
        return "";
    }
}

// 글자 수 확인
function count_mb_id($reg_mb_id)
{
    if (strlen($reg_mb_id) < 8) {
        return "회원아이디는 최소 8글자 이상 입력하세요.";
    } else if (strlen($reg_mb_id) > 20) {
        return "회원아이디는 최대 20글자 이상 입력하세요.";
    } else {
        return "";
    }
}

// DB에 존재하는지 확인
function exist_mb_id($reg_mb_id)
{
    $reg_mb_id = trim($reg_mb_id);
    if ($reg_mb_id == "") {
        return "";
    }

    $sql = " SELECT count(*) AS cnt FROM member WHERE member_id = '$reg_mb_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt']) {
        return "이미 사용중인 아이디 입니다.";
    } else {
        return "";
    }
}

// ==================================================================================
// EMAIL 유효성 확인 함수
// ==================================================================================

// 빈값 확인
function empty_mb_email($reg_mb_email)
{
    if (!trim($reg_mb_email)) {
        return "E-mail 주소를 입력해 주십시오.";
    } else {
        return "";
    }
}

// 지정한 형식에 맞는지 확인
function valid_mb_email($reg_mb_email)
{
    if (!preg_match(DAELIM_EMAIL_REGEXP, $reg_mb_email)) {
        return "E-mail 주소가 형식에 맞지 않습니다.";
    } else {
        return "";
    }
}

// DB에 존재하는지 확인
function exist_mb_email($reg_mb_email)
{
    $row = sql_fetch("SELECT count(*) AS cnt FROM member WHERE id = '$reg_mb_email'");
    if ($row['cnt']) {
        return "이미 사용중인 E-mail 주소입니다.";
    } else {
        return "";
    }
}

// ==================================================================================
// NAME 유효성 확인 함수
// ==================================================================================

// 빈값 확인
function empty_mb_name($reg_mb_name)
{
    if (!trim($reg_mb_name)) {
        return "이름을 입력해 주십시오.";
    } else {
        return "";
    }
}

// 지정한 형식에 맞는지 확인
function valid_mb_name($mb_name)
{
    if (!check_string($mb_name, DAELIM_HANGUL)) {
        return "이름은 공백없이 한글만 입력 가능합니다.";
    } else {
        return "";
    }
}

// ==================================================================================
// PASSWORD 유효성 확인 함수
// ==================================================================================

// DB에 INSERT시 암호화 되므로 전달받은 비밀번호를 암호화하여 비교 가능하게 해주는 함수
function check_password($pass, $hash)
{
    $password = get_encrypt_string($pass);
    return ($password === $hash);
}

// 빈값 확인
function empty_mb_pass($reg_mb_pass)
{
    if (trim($reg_mb_pass) == '') {
        return "비밀번호를 입력해 주십시오.";
    } else {
        return "";
    }
}

// 지정한 형식에 맞는지 확인
function valid_mb_pass($reg_mb_pass)
{
    if (!preg_match(DAELIM_PASSWORD_REGEXP, $reg_mb_pass)) {
        return "비밀번호는 영문자, 숫자, 특수문자만 입력하세요.";
    } else {
        return "";
    }
}

// 글자 수 확인
function count_mb_pass($reg_mb_pass)
{
    if (strlen($reg_mb_pass) < 8) {
        return "비밀번호는 최소 8글자 이상 입력하세요.";
    } else if (strlen($reg_mb_pass) > 20) {
        return "비밀번호는 최대 20글자 이상 입력하세요.";
    } else {
        return "";
    }
}

// ==================================================================================
// URL 유효성 확인 함수
// ==================================================================================

// 지정한 형식에 맞는지 확인
function valid_mb_url($reg_mb_url)
{
    if (!preg_match(DAELIM_URL_REGEXP, $reg_mb_url)) {
        return "URL이 형식에 맞지 않습니다.";
    } else {
        return "";
    }
}
