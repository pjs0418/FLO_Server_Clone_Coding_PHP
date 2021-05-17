<?php

function getCharts()
{
    try {
        $pdo = pdoSqlConnect();
        $query = "START TRANSACTION;";

        $st = $pdo->prepare($query);
        $st->execute();

        $st = null;
        $pdo = null;

        $pdo = pdoSqlConnect();
        $query = "select idx as chartIdx, name as chartName from Chart limit 3;";

        $st = $pdo->prepare($query);
        $st->execute();
        //    $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        for($i = 1;$i < 4;$i++)
        {
            $pdo = pdoSqlConnect();
            $query = "select songIdx,
       title    as songTitle,
       imageUrl as songImageUrl,
       name     as songArtist
from SongInChart
         inner join (select Song.idx,
                            title,
                            albumIdx,
                            artistIdx,
                            imageUrl,
                            name
                     from Song
                              inner join (select idx, imageUrl from Album where isDeleted = 'N') AlbumInfo
                                         on AlbumInfo.idx = Song.albumIdx
                              inner join (select idx, name from Artist where isDeleted = 'N') ArtistInfo
                                         on ArtistInfo.idx = Song.artistIdx
                     where isDeleted = 'N') SongInfo
                    on SongInfo.idx = SongInChart.songIdx
where chartIdx = ? AND isDeleted = 'N'
limit 20;";

            $st = $pdo->prepare($query);
            $st->execute([$i]);
            //    $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res1 = $st->fetchAll();

            $st = null;
            $pdo = null;

            $array = array($res1);
            $arrayKey = array("songInfo");
            $arr = array_combine($arrayKey, $array);

            $res[$i - 1] = array_merge($res[$i - 1], $arr);
        }

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

function getCharacterMostListenedSongClassificationIdx($characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select classificationIdx
from History
         inner join (select Song.idx, albumIdx, classificationIdx
                     from Song
                              inner join (select classificationIdx, idx from Album where isDeleted = 'N') ClassificationInfo
                                         on ClassificationInfo.idx = Song.albumIdx
                     where isDeleted = 'N') AlbumInfo
                    on AlbumInfo.idx = History.songIdx
where characterIdx = ?
order by listeningCount desc
limit 1;";

    $st = $pdo->prepare($query);
    $st->execute([$characterIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['classificationIdx'];
}

function getRecommendedSongsIdx($classificationIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select SongInfo.idx as songIdx
from Album
         inner join (select idx, albumIdx from Song where isDeleted = 'N') SongInfo
                    on SongInfo.albumIdx = Album.idx
where isDeleted = 'N'
  AND classificationIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$classificationIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isExistSongInChart($songIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select count(1) as exist from SongInChart where chartIdx = 1 AND songIdx = ? AND isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$songIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function addSongInMixedChart($songIdx, $characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO MixChart (chartIdx, songIdx, characterIdx) VALUES (1, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$songIdx, $characterIdx]);

    $st = null;
    $pdo = null;
}

function resetMixedChart($characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE MixChart
SET isDeleted = 'Y'
where chartIdx = 1 AND characterIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$characterIdx]);

    $st = null;
    $pdo = null;
}

function getSongsInMixedChart($characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select songIdx from MixChart where isDeleted = 'N' AND chartIdx = 1 AND characterIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$characterIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getSongsInChartIdx()
{
    $pdo = pdoSqlConnect();
    $query = "select songIdx from SongInChart where isDeleted = 'N' AND chartIdx = 1;";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isExistSongInMixedChart($songIdx, $characterIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select count(1) as exist from MixChart where chartIdx = 1 AND songIdx = ? AND isDeleted = 'N' AND characterIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$songIdx, $characterIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//function getArtistTaste($currentCharacterIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as classificationIdx, name as classificationName from Classification where isDeleted = 'N';";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    for($i = 0;$i < count($res);$i++) {
//        $pdo = pdoSqlConnect();
//        $query = "select idx as artistIdx, name as artistName, profileImageUrl as artistProfileImageUrl, taste as artistTaste
//from CharacterArtistTaste
//         inner join (select idx, name, profileImageUrl
//                     from Artist
//                     where classificationIdx = ?
//                       AND parentsArtistIdx = 0
//                       AND isDeleted = 'N') ArtistInfo
//                    on ArtistInfo.idx = CharacterArtistTaste.artistIdx
//where characterIdx = ?;";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$res[$i]['classificationIdx'], $currentCharacterIdx]);
//        //    $st->execute();
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res1 = $st->fetchAll();
//
//        $st = null;
//        $pdo = null;
//
//        $array = array($res1);
//        $arrayKey = array("mainArtist");
//        $arr = array_combine($arrayKey, $array);
//
//        $res[$i] = array_merge($res[$i], $arr);
//    }
//
//    return $res;
//}
//
//function modifyArtistTaste($currentCharacterIdx, $modifyTaste)
//{
//    for($i = 0;$i < count($modifyTaste);$i++) {
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE CharacterArtistTaste SET taste = ? where characterIdx = ? AND artistIdx = ?;";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$modifyTaste[$i]->artistTaste, $currentCharacterIdx, $modifyTaste[$i]->artistIdx]);
//
//        $st = null;
//        $pdo = null;
//    }
//}
//
//function isValidArtistIdx($artistIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select count(1) as exist from Artist where idx = ? AND isDeleted = 'N';";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$artistIdx]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['exist'];
//}
//READ
//function isValidUserIdx($userIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select EXISTS(select * from Users where userIdx = ?) exist;";
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
//    return $res[0]['exist'];
//}
//
//
//function createUserBase($name, $phoneNo)
//{
//    try
//    {
//        $pdo = pdoSqlConnect();
//        $query = "START TRANSACTION;";
//
//        $st = $pdo->prepare($query);
//        $st->execute();
//
//        $st = null;
//        $pdo = null;
//
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO User (name, phoneNo) VALUES (?,?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$name, $phoneNo]);
//
//        $st = null;
//        $pdo = null;
//
//        $pdo = pdoSqlConnect();
//        $query = "select idx as userIdx, email from User where name = ? AND phoneNo = ?;";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$name, $phoneNo]);
//        //    $st->execute();
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st = null;
//        $pdo = null;
//
//        return $res;
//
//        $pdo = pdoSqlConnect();
//        $query = "COMMIT;";
//
//        $st = $pdo->prepare($query);
//        $st->execute();
//
//        $st = null;
//        $pdo = null;
//    }
//    catch (PDOException $e) {
//        $pdo = pdoSqlConnect();
//        $query = "ROLLBACK;";
//
//        $st = $pdo->prepare($query);
//        $st->execute();
//
//        $st = null;
//        $pdo = null;
//        throw $e;
//    }
//}
//
//function isExistUser($name, $phoneNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select count(1) as exist from UserInfo where name = ? AND phoneNo = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name, $phoneNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['exist'];
//}
//
//function getExistUser($name, $phoneNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as userIdx, email from User where name = ? AND phoneNo = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name, $phoneNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function nameValidation($name)
//{
//    $num = preg_match('/[0-9]/u', $name);
//    $spe = preg_match('/[\!\@\#\$\%\^\&\*]/u', $name);
//    $con = preg_match('/[\ㄱ\ㄴ\ㄷ\ㄹ\ㅁ\ㅂ\ㅅ\ㅇ\ㅈ\ㅊ\ㅋ\ㅌ\ㅍ\ㅎ]/u', $name);
//    $col = preg_match('/[\ㅏ\ㅑ\ㅓ\ㅕ\ㅗ\ㅛ\ㅜ\ㅣ]/u', $name);
//
//    if($num != 0 || $spe != 0 || $con != 0 || $col != 0)
//    {
//        return array(false, "올바른 이름 형식이 아닙니다.");
//        exit;
//    }
//
//    if(mb_strlen($name, "UTF-8") > 50)
//    {
//        return array(false, "이름은 최대 50자까지만 입력이 가능합니다.");
//        exit;
//    }
//
//    return array(true);
//}
//
//function phoneNoValidation($phoneNo)
//{
//    if(!preg_match("/^010[0-9]{8}$/", $phoneNo))
//    {
//        return array(false, "올바른 휴대폰 번호 형식이 아닙니다.");
//        exit;
//    }
//
//    return array(true);
//}
//
//function createUser($name, $phoneNo, $email, $password, $dateOfBirth)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO UserInfo (name, phoneNo, email, password, dateOfBirth) VALUES (?, ?, ?, ?, ?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name, $phoneNo, $email, $password, $dateOfBirth]);
//
//    $st = null;
//    $pdo = null;
//}
//
//function getUserIdx($email)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as userIdx from UserInfo where email = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$email]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['userIdx'];
//}
//function emailValidation($email)
//{
//    $checkEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
//
//    if($checkEmail == true)
//    {
//        return array(true);
//        exit;
//    }
//
//    return array(false, "올바른 이메일 형식이 아닙니다.");
//}
//
//function passwordValidation($password)
//{
//    $num = preg_match('/[0-9]/u', $password);
//    $upp = preg_match('/[A-Z]/u', $password);
//    $low = preg_match('/[a-z]/u', $password);
//    $spe = preg_match('/[\!\@\#\$\%\^\&\*]/u', $password);
//
//    if(strlen($password) < 6 || strlen($password) > 15)
//    {
//        return array(false, "비밀번호는 최소 6~15자리로 입력해주세요.");
//        exit;
//    }
//
//    if(($num != 0 && $upp == 0 && $low == 0 && $spe == 0) || ($num == 0 && $upp != 0 && $low == 0 && $spe == 0) ||
//        ($num == 0 && $upp == 0 && $low != 0 && $spe == 0) || ($num == 0 && $upp == 0 && $low == 0 && $spe != 0))
//    {
//        return array(false, "비밀번호는 영문 대문자/소문자/숫자/특수문자를 섞어 2가지 이상 조합으로 입력해주세요.");
//        exit;
//    }
//
//    return array(true);
//}
//
//function dateOfBirthValidation($dateOfBirth)
//{
//    if(!preg_match('/([0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1,2][0-9]|3[0,1]))/', $dateOfBirth))
//    {
//        return array(false, "생년월일이 잘못 입력되었습니다.");
//        exit;
//    }
//
//    return array(true);
//}
//
//function isExistUserEmail($email)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select count(1) as exist from UserInfo where email = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$email]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['exist'];
//}
//
//function createCharacter($name, $profileImageUrl, $userIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO CharacterInfo (name, profileImageUrl, userIdx) VALUES (?, ?, ?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name, $profileImageUrl, $userIdx]);
//
//    $st = null;
//    $pdo = null;
//}
//
//function setCurrentCharacterIdx($userIdx, $characterIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "UPDATE UserInfo SET currentCharacterIdx = ? where idx = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$characterIdx, $userIdx]);
//
//    $st = null;
//    $pdo = null;
//}
//
//function getCharacterIdx($userIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as characterIdx from CharacterInfo where userIdx = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$userIdx]);
//    //    $st->execute();a
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['characterIdx'];
//}
//
//function getArtistIdx()
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as artistIdx from Artist where isDeleted = 'N';";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//    //    $st->execute();a
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function getGenreIdx()
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as genreIdx from Genre where isDeleted = 'N';";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//    //    $st->execute();a
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function getChartIdx()
//{
//    $pdo = pdoSqlConnect();
//    $query = "select idx as chartIdx from Chart where isDeleted = 'N';";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//    //    $st->execute();a
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function setArtistTaste($characterIdx, $artistIdx)
//{
//    for($i = 1;$i < count($artistIdx) + 1;$i++)
//    {
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO CharacterArtistTaste (characterIdx, artistIdx, taste) VALUES (?, ?, 'N');";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$characterIdx, $artistIdx[$i - 1]['artistIdx']]);
//
//        $st = null;
//        $pdo = null;
//    }
//}
//
//function setGenreTaste($characterIdx, $genreIdx)
//{
//    for($i = 1;$i < count($genreIdx) + 1;$i++)
//    {
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO CharacterGenreTaste (characterIdx, genreIdx, taste) VALUES (?, ?, 'N');";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$characterIdx, $genreIdx[$i - 1]['genreIdx']]);
//
//        $st = null;
//        $pdo = null;
//    }
//}
//
//function setChartTaste($characterIdx, $chartIdx)
//{
//    for($i = 1;$i < count($chartIdx) + 1;$i++)
//    {
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO CharacterChartTaste (characterIdx, chartIdx, taste) VALUES (?, ?, 'N');";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$characterIdx, $chartIdx[$i - 1]['chartIdx']]);
//
//        $st = null;
//        $pdo = null;
//    }
//}

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
