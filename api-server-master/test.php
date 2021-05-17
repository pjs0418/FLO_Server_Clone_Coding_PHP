<?php

require './pdos/DatabasePdo.php';

function chartShuffle()
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE SongInChart
SET isDeleted = 'Y'
where chartIdx = 1;";

    $st = $pdo->prepare($query);
    $st->execute();

    $st = null;
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query = "select idx from Song order by listeningCount desc;";

    $st = $pdo->prepare($query);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    for($i = 0;$i < count($res);$i++)
    {
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO SongInChart (chartIdx, songIdx)
VALUES (1, ?);";

        $st = $pdo->prepare($query);
        $st->execute([$res[$i]['idx']]);

        $st = null;
        $pdo = null;
    }
}

chartShuffle();