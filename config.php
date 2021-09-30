<?php
// ==================================================================================
// 상수 선언
// ==================================================================================

// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
define('DAELIM_ALLOW_IS_TURE', true);

if (PHP_VERSION >= '5.1.0') {
    date_default_timezone_set('Asia/Seoul');
}

if (isset($daelim_path['path'])) {
    define('DAELIMFESTIVAL', $daelim_path['path']);
} else {
    define('DAELIMFESTIVAL', '');
}

define('DAELIM_DOMAIN', $daelim_path['url'] . '/');
define('DAELIM_HTTP_DOMAIN', $daelim_path['url'] . '/');
define('DAELIM_FILE_DOMAIN', $daelim_path['url'] . '/' . 'data/');

define('DAELIM_DATA_DIR', 'data');
define('DAELIM_EXTEND_DIR', 'extend');

define('DAELIM_DATA_PATH', DAELIMFESTIVAL . '/' . DAELIM_DATA_DIR);
define('DAELIM_EXTEND_PATH', DAELIMFESTIVAL . '/' . DAELIM_EXTEND_DIR);

// ==================================================================================
// 시간, 날짜 상수
// ==================================================================================

define('DAELIM_TIME_YMDHIS', date('Y-m-d H:i:s'));
define('DAELIM_TIME_YMD', date('Y-m-d'));
define('DAELIM_TIME_HIS', date('H:i:s'));
define('DAELIM_MONTH_NO_ZERO', date('n'));
define('DAELIM_DAY_NO_ZERO', date('j'));

// 입력값 검사 상수 (숫자를 변경하시면 안됩니다.)
define('DAELIM_ALPHAUPPER', 1); // 영대문자
define('DAELIM_ALPHALOWER', 2); // 영소문자
define('DAELIM_ALPHABETIC', 4); // 영대,소문자
define('DAELIM_NUMERIC', 8); // 숫자
define('DAELIM_HANGUL', 16); // 한글
define('DAELIM_SPACE', 32); // 공백
define('DAELIM_SPECIAL', 64); // 특수문자

// 퍼미션
define('DAELIM_DIR_PERMISSION', 0707); // 디렉토리 생성시 퍼미션
define('DAELIM_FILE_PERMISSION', 0644); // 파일 생성시 퍼미션

// ==================================================================================
// 정규식 상수
// ==================================================================================
// 모바일 OR PC 구분 정규식
define('DAELIM_AGENT_REGEXP', '/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/i');

// 숫자, 영문 각 1자리 이상 특수문자 불가능 8~20글자(아이디) 정규식
define('DAELIM_ID_REGEXP1', '/^[a-z]{1}(?=.*[a-zA-z])(?=.*[0-9])(?!.*[$`~!@$!%*#^?&\\(\\)\-_=+])(?!.*[^a-zA-z0-9$`~!@$!%*#^?&\\(\\)\-_=+]).{7,18}/i');

// 숫자만 가능
define('DAELIM_ID_REGEXP2', '/^(?=.*[0-9]).{9}/');

// 숫자, 영문, 특수문자 각 1자리 이상 8~20글자(비밀번호) 정규식
define('DAELIM_PASSWORD_REGEXP', '/^[a-z]{1}(?=.*[a-zA-z])(?=.*[0-9])(?=.*[$`~!@$!%*#^?&\\(\\)\-_=+])(?!.*[^a-zA-z0-9$`~!@$!%*#^?&\\(\\)\-_=+]).{7,18}/i');

// 이메일 정규식
define('DAELIM_EMAIL_REGEXP', '/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/');

// URL 정규식
define('DAELIM_URL_REGEXP', '/^http|https:\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/');

// ==================================================================================
// 기타 상수
// ==================================================================================

// SQL 에러를 표시할 것인지 지정
// 에러를 표시하려면 TRUE 로 변경
define('DAELIM_DISPLAY_SQL_ERROR', FALSE);

// 게시판에서 링크의 기본개수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
define('DAELIM_LINK_COUNT', 2);

// 썸네일 jpg Quality 설정
define('DAELIM_THUMB_JPG_QUALITY', 90);

// 썸네일 png Compress 설정
define('DAELIM_THUMB_PNG_COMPRESS', 5);

// 모바일 기기에서 DHTML 에디터 사용여부를 설정합니다.
define('DAELIM_IS_MOBILE_DHTML_USE', false);

// MySQLi 사용여부를 설정합니다.
define('DAELIM_MYSQLI_USE', true);

// DB 연결 방식
define('CONNECT_ARR', array('PDO', 'SQLI'));

// Browscap 사용여부를 설정합니다.
define('DAELIM_BROWSCAP_USE', true);

// 접속자 기록 때 Browscap 사용여부를 설정합니다.
define('DAELIM_VISIT_BROWSCAP_USE', false);

// 접속자 정보
define('AGENT', $_SERVER['HTTP_USER_AGENT']);

// url 방식
define('METHOD', $_SERVER['REQUEST_METHOD']);

// 모바일 OR PC
define('device', get_client_device());

// 접속자 IP주소
define('ip', get_ip());

// 접속자 브라우저 정보
define('browser', get_client_browser());
