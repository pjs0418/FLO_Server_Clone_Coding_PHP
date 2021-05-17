<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 4
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "checkOverlapUser":
            http_response_code(200);

            $name = $_GET["name"];
            $phoneNo = $_GET["phoneNo"];

            if(nameValidation($name)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = nameValidation($name)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(phoneNoValidation($phoneNo)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = phoneNoValidation($phoneNo)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(isExistUser($name, $phoneNo))
            {
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "이미 존재하는 유저";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "유저 생성 가능";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createUser":
            http_response_code(200);

            $name = $req->name;
            $phoneNo = $req->phoneNo;
            $email = $req->email;
            $pwd_hash = password_hash($req->password, PASSWORD_DEFAULT);
            $dateOfBirth = $req->dateOfBirth;

            if(nameValidation($name)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = nameValidation($name)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(phoneNoValidation($phoneNo)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = phoneNoValidation($phoneNo)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(emailValidation($email)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = emailValidation($email)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(passwordValidation($req->password)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = passwordValidation($req->password)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(dateOfBirthValidation($dateOfBirth)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = dateOfBirthValidation($dateOfBirth)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(isExistUserEmail($email))
            {
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "이미 사용중인 아이디입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            createUser($name, $phoneNo, $email, $pwd_hash, $dateOfBirth);

            $userIdx = getUserIdx($email);
            createCharacter('캐릭터1', "https://product.jun-softsquared.shop/c-d-x-PDX_a_82obo-unsplash.jpg", $userIdx);

            $characterIdx = getCharacterIdx($userIdx);
            setCurrentCharacterIdx($userIdx, $characterIdx);

            $artistIdx = getArtistIdx();
            setArtistTaste($characterIdx, $artistIdx);

            $genreIdx = getGenreIdx();
            setGenreTaste($characterIdx, $genreIdx);

            $chartIdx = getChartIdx();
            setchartTaste($characterIdx, $chartIdx);

            $songIdx = getSongIdx();
            setSongLiked($characterIdx, $songIdx);

            $jwt = getJWT($userIdx, JWT_SECRET_KEY);

            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "유저 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "userLogin":
            http_response_code(200);

            if($req->email == null && $req->password == null)
            {
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "로그인 정보 없음";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $email = $req->email;
            $password = $req->password;

            if(emailValidation($email)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = emailValidation($email)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(passwordValidation($password)[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = passwordValidation($req->password)[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(!isExistUserEmail($email))
            {
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "존재하지 않는 이메일 정보입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            // 1) 로그인 시 email, password 받기
            if (!isValidUser($email, $password)) { // JWTPdo.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "비밀번호 정보가 잘못되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT 발급
            // Payload에 맞게 다시 설정 요함, 아래는 Payload에 userIdx를 넣기 위한 과정
            $userIdx = getUserIdx($email);  // JWTPdo.php 에 구현
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $currentCharacterIdx = getCurrentCharacterIdx($userIdx);

            $res->result->jwt = $jwt;
            $res->result->playlist = getPlaylist($currentCharacterIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "유저 로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteUser":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if (isDeletedUser($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "이미 탈퇴한 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            deleteUser($userIdx);
            deleteCharacter($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "회원 탈퇴 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "autoLogin":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // 1) JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $currentCharacterIdx = getCurrentCharacterIdx($userIdx);

            if (isDeletedUser($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "이미 탈퇴한 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT Payload 반환
            http_response_code(200);
            //$res->result = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $res->result->playlist = getPlaylist($currentCharacterIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "자동 로그인 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "userKakaoLogin":

            $TOKEN_API_URL = "https://kapi.kakao.com/v2/user/me";
            $token = $_SERVER["HTTP_AUTHORIZATION"];

            $opts = array(
                CURLOPT_URL => $TOKEN_API_URL,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSLVERSION => 1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $token
                )
            );

            $curlSession = curl_init();
            curl_setopt_array($curlSession, $opts);
            $accessTokenJson = json_decode(curl_exec($curlSession));
            curl_close($curlSession);
            $tokenArray = array($accessTokenJson);

            // 2) JWT Payload 반환
            http_response_code(200);
            createKakaoUser($tokenArray[0]->id);
            $jwt = getJWT($tokenArray[0]->id, JWT_SECRET_KEY);
            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "카카오 로그인 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "userNaverLogin":

            $TOKEN_API_URL = "https://openapi.naver.com/v1/nid/me";
            $token = $_SERVER["HTTP_AUTHORIZATION"];

            $opts = array(
                CURLOPT_URL => $TOKEN_API_URL,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSLVERSION => 1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $token
                )
            );

            $curlSession = curl_init();
            curl_setopt_array($curlSession, $opts);
            $accessTokenJson = json_decode(curl_exec($curlSession));
            curl_close($curlSession);
            $tokenArray = array($accessTokenJson);

            // 2) JWT Payload 반환
            http_response_code(200);
            createNaverUser($tokenArray[0]->response->id);
            $jwt = getJWT($tokenArray[0]->response->id, JWT_SECRET_KEY);
            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "네이버 로그인 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
