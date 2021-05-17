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
        case "getTastes":
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
            $currentCharacterIdx = getCurrentCharacterIdx($userIdx);

            $res->result = getArtistTaste($currentCharacterIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "캐릭터 취향 정보 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "modifyTastes":
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

            if($req->modifyTaste == null)
            {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "변경 정보 없음";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $modifyTaste = $req->modifyTaste;

            for($i = 0;$i < count($modifyTaste);$i++)
            {
                if(!isValidArtistIdx($modifyTaste[$i]->artistIdx))
                {
                    $res->isSuccess = FALSE;
                    $res->code = 2002;
                    $res->message = "존재하지 않는 아티스트입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            for($i = 0;$i < count($modifyTaste);$i++)
            {
                if($modifyTaste[$i]->artistTaste != 'N' && $modifyTaste[$i]->artistTaste != 'Y')
                {
                    $res->isSuccess = FALSE;
                    $res->code = 2003;
                    $res->message = "잘못된 취향 선택 정보입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $currentCharacterIdx = getCurrentCharacterIdx($userIdx);

            modifyArtistTaste($currentCharacterIdx, $modifyTaste);

            $characterTasteArtistClassificationIdxInfo = getCharacterTasteArtistClassificationIdxInfo($currentCharacterIdx);
            $characterTasteArtistInfo = getCharacterTasteArtistInfo($characterTasteArtistClassificationIdxInfo);
            $mixedListName = getMixedListName($characterTasteArtistInfo);
            makeMixedList($mixedListName, $currentCharacterIdx);

            $mixedListIdx = getMixedListIdx($currentCharacterIdx);
            $mixedListSongIdx = getMixedListSongIdx($characterTasteArtistInfo);

            addSongInMixedList($mixedListIdx, $mixedListSongIdx);
            $res->code = 1000;
            $res->message = "캐릭터 취향 정보 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
//
//        case "createUser":
//            http_response_code(200);
//
//            $name = $req->name;
//            $phoneNo = $req->phoneNo;
//            $email = $req->email;
//            $pwd_hash = password_hash($req->password, PASSWORD_DEFAULT);
//            $dateOfBirth = $req->dateOfBirth;
//
//            if(nameValidation($name)[0] == false)
//            {
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = nameValidation($name)[1];
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            if(phoneNoValidation($phoneNo)[0] == false)
//            {
//                $res->isSuccess = FALSE;
//                $res->code = 2001;
//                $res->message = phoneNoValidation($phoneNo)[1];
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            if(emailValidation($email)[0] == false)
//            {
//                $res->isSuccess = FALSE;
//                $res->code = 2002;
//                $res->message = emailValidation($email)[1];
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            if(passwordValidation($req->password)[0] == false)
//            {
//                $res->isSuccess = FALSE;
//                $res->code = 2003;
//                $res->message = passwordValidation($req->password)[1];
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            if(dateOfBirthValidation($dateOfBirth)[0] == false)
//            {
//                $res->isSuccess = FALSE;
//                $res->code = 2004;
//                $res->message = dateOfBirthValidation($dateOfBirth)[1];
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            if(isExistUserEmail($email))
//            {
//                $res->isSuccess = FALSE;
//                $res->code = 2005;
//                $res->message = "이미 사용중인 아이디입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            createUser($name, $phoneNo, $email, $pwd_hash, $dateOfBirth);
//
//            $userIdx = getUserIdx($email);
//            createCharacter('캐릭터1', '123', $userIdx);
//
//            $characterIdx = getCharacterIdx($userIdx);
//            setCurrentCharacterIdx($userIdx, $characterIdx);
//
//            $artistIdx = getArtistIdx();
//            setArtistTaste($characterIdx, $artistIdx);
//
//            $genreIdx = getGenreIdx();
//            setGenreTaste($characterIdx, $genreIdx);
//
//            $chartIdx = getChartIdx();
//            setchartTaste($characterIdx, $chartIdx);
//
//            $res->result->characterIdx = $characterIdx;
//            $res->isSuccess = TRUE;
//            $res->code = 1000;
//            $res->message = "유저 생성 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
