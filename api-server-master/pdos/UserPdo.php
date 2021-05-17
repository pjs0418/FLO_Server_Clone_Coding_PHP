<?php

function createUserBase($name, $phoneNo)
{
    try
    {
        $pdo = pdoSqlConnect();
        $query = "START TRANSACTION;";

        $st = $pdo->prepare($query);
        $st->execute();

        $st = null;
        $pdo = null;

        $pdo = pdoSqlConnect();
        $query = "INSERT INTO User (name, phoneNo) VALUES (?,?);";

        $st = $pdo->prepare($query);
        $st->execute([$name, $phoneNo]);

        $st = null;
        $pdo = null;

        $pdo = pdoSqlConnect();
        $query = "select idx as userIdx, email from User where name = ? AND phoneNo = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$name, $phoneNo]);
        //    $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        return $res;

        $pdo = pdoSqlConnect();
        $query = "COMMIT;";

        $st = $pdo->prepare($query);
        $st->execute();

        $st = null;
        $pdo = null;
    }
    catch (PDOException $e) {
        $pdo = pdoSqlConnect();
        $query = "ROLLBACK;";

        $st = $pdo->prepare($query);
        $st->execute();

        $st = null;
        $pdo = null;
        throw $e;
    }
}

function isExistUser($name, $phoneNo)
{
    $pdo = pdoSqlConnect();
    $query = "select count(1) as exist from UserInfo where name = ? AND phoneNo = ? AND isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$name, $phoneNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getExistUser($name, $phoneNo)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as userIdx, email from User where name = ? AND phoneNo = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$name, $phoneNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function nameValidation($name)
{
    $num = preg_match('/[0-9]/u', $name);
    $spe = preg_match('/[\!\@\#\$\%\^\&\*]/u', $name);
    $con = preg_match('/[\ㄱ\ㄴ\ㄷ\ㄹ\ㅁ\ㅂ\ㅅ\ㅇ\ㅈ\ㅊ\ㅋ\ㅌ\ㅍ\ㅎ]/u', $name);
    $col = preg_match('/[\ㅏ\ㅑ\ㅓ\ㅕ\ㅗ\ㅛ\ㅜ\ㅣ]/u', $name);

    if($num != 0 || $spe != 0 || $con != 0 || $col != 0)
    {
        return array(false, "올바른 이름 형식이 아닙니다.");
        exit;
    }

    if(mb_strlen($name, "UTF-8") > 50)
    {
        return array(false, "이름은 최대 50자까지만 입력이 가능합니다.");
        exit;
    }

    return array(true);
}

function phoneNoValidation($phoneNo)
{
    if(!preg_match("/^010[0-9]{8}$/", $phoneNo))
    {
        return array(false, "올바른 휴대폰 번호 형식이 아닙니다.");
        exit;
    }

    return array(true);
}

function createUser($name, $phoneNo, $email, $password, $dateOfBirth)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserInfo (name, phoneNo, email, password, dateOfBirth) VALUES (?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$name, $phoneNo, $email, $password, $dateOfBirth]);

    $st = null;
    $pdo = null;
}

function getUserIdx($email)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as userIdx from UserInfo where email = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}
function emailValidation($email)
{
    $checkEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

    if($checkEmail == true)
    {
        return array(true);
        exit;
    }

    return array(false, "올바른 이메일 형식이 아닙니다.");
}

function passwordValidation($password)
{
    $num = preg_match('/[0-9]/u', $password);
    $upp = preg_match('/[A-Z]/u', $password);
    $low = preg_match('/[a-z]/u', $password);
    $spe = preg_match('/[\!\@\#\$\%\^\&\*]/u', $password);

    if(strlen($password) < 6 || strlen($password) > 15)
    {
        return array(false, "비밀번호는 최소 6~15자리로 입력해주세요.");
        exit;
    }

    if(($num != 0 && $upp == 0 && $low == 0 && $spe == 0) || ($num == 0 && $upp != 0 && $low == 0 && $spe == 0) ||
        ($num == 0 && $upp == 0 && $low != 0 && $spe == 0) || ($num == 0 && $upp == 0 && $low == 0 && $spe != 0))
    {
        return array(false, "비밀번호는 영문 대문자/소문자/숫자/특수문자를 섞어 2가지 이상 조합으로 입력해주세요.");
        exit;
    }

    return array(true);
}

function dateOfBirthValidation($dateOfBirth)
{
    if(!preg_match('/([0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1,2][0-9]|3[0,1]))/', $dateOfBirth))
    {
        return array(false, "생년월일이 잘못 입력되었습니다.");
        exit;
    }

    return array(true);
}

function isExistUserEmail($email)
{
    $pdo = pdoSqlConnect();
    $query = "select count(1) as exist from UserInfo where email = ? AND isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function createCharacter($name, $profileImageUrl, $userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO CharacterInfo (name, profileImageUrl, userIdx) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$name, $profileImageUrl, $userIdx]);

    $st = null;
    $pdo = null;
}

function setCurrentCharacterIdx($userIdx, $characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE UserInfo SET currentCharacterIdx = ? where idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$characterIdx, $userIdx]);

    $st = null;
    $pdo = null;
}

function getCharacterIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as characterIdx from CharacterInfo where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['characterIdx'];
}

function getArtistIdx()
{
    $pdo = pdoSqlConnect();
    $query = "select idx as artistIdx from Artist where isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getGenreIdx()
{
    $pdo = pdoSqlConnect();
    $query = "select idx as genreIdx from Genre where isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getChartIdx()
{
    $pdo = pdoSqlConnect();
    $query = "select idx as chartIdx from Chart where isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getSongIdx()
{
    $pdo = pdoSqlConnect();
    $query = "select idx as songIdx from Song where isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function setArtistTaste($characterIdx, $artistIdx)
{
    for($i = 1;$i < count($artistIdx) + 1;$i++)
    {
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO CharacterArtistTaste (characterIdx, artistIdx, taste) VALUES (?, ?, 'N');";

        $st = $pdo->prepare($query);
        $st->execute([$characterIdx, $artistIdx[$i - 1]['artistIdx']]);

        $st = null;
        $pdo = null;
    }
}

function setGenreTaste($characterIdx, $genreIdx)
{
    for($i = 1;$i < count($genreIdx) + 1;$i++)
    {
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO CharacterGenreTaste (characterIdx, genreIdx, taste) VALUES (?, ?, 'N');";

        $st = $pdo->prepare($query);
        $st->execute([$characterIdx, $genreIdx[$i - 1]['genreIdx']]);

        $st = null;
        $pdo = null;
    }
}

function setChartTaste($characterIdx, $chartIdx)
{
    for($i = 1;$i < count($chartIdx) + 1;$i++)
    {
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO CharacterChartTaste (characterIdx, chartIdx, taste) VALUES (?, ?, 'N');";

        $st = $pdo->prepare($query);
        $st->execute([$characterIdx, $chartIdx[$i - 1]['chartIdx']]);

        $st = null;
        $pdo = null;
    }
}

function setSongLiked($characterIdx, $songIdx)
{
    for($i = 1;$i < count($songIdx) + 1;$i++)
    {
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO CharacterSongLiked (characterIdx, songIdx, isCharacterLikedSong) VALUES (?, ?, 'N');";

        $st = $pdo->prepare($query);
        $st->execute([$characterIdx, $songIdx[$i - 1]['songIdx']]);

        $st = null;
        $pdo = null;
    }
}

function getPlaylist($characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select songIdx, title as songTitle, imageUrl as songImageUrl, name as artistName
from Playlist
         inner join (select Song.idx, title, albumIdx, artistIdx, imageUrl, name
                     from Song
                              inner join (select idx, imageUrl from Album where isDeleted = 'N') AlbumInfo
                                         on AlbumInfo.idx = Song.albumIdx
                              inner join (select idx, name from Artist where isDeleted = 'N') ArtistInfo
                                         on ArtistInfo.idx = Song.artistIdx
                     where isDeleted = 'N') SongInfo
                    on SongInfo.idx = Playlist.songIdx
where characterIdx = ?
  AND isDeleted = 'N'
order by playlistOrder;";

    $st = $pdo->prepare($query);
    $st->execute([$characterIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function deleteUser($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE UserInfo SET isDeleted = 'Y' where idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st = null;
    $pdo = null;
}

function deleteCharacter($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE CharacterInfo SET isDeleted = 'Y' where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st = null;
    $pdo = null;
}

function isDeletedUser($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select count(1) as notExist from UserInfo where idx = ? AND isDeleted = 'Y';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['notExist'];
}

function createKakaoUser($tokenID)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserInfo (idx) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$tokenID]);

    $st = null;
    $pdo = null;
}

function createNaverUser($tokenID)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserInfo (idx) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$tokenID]);

    $st = null;
    $pdo = null;
}
//function getUserDetail($userIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select name, phoneNo from User where idx = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$userIdx]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
