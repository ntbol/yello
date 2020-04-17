<?php

//repost post on logged in users timeline
if(isset($_POST['retweet'])) {
    $useruid = !empty($_POST['uid']) ? trim($_POST['uid']) : null;
    $yelloid = !empty($_POST['yelloid']) ? trim($_POST['yelloid']) : null;

    $sql = "INSERT INTO reyello (user_uid, yello_id) VALUES (:useruid, :yelloid)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':useruid', $useruid);
    $stmt->bindValue(':yelloid', $yelloid);

    $result = $stmt->execute();

    //If post was successful
    if($result) {
        header ('Location: index.php');
    }
}

//unpost repost on logged in users timeline
if(isset($_POST['unretweet'])) {
    $useruid = !empty($_POST['uid']) ? trim($_POST['uid']) : null;
    $yelloid = !empty($_POST['yelloid']) ? trim($_POST['yelloid']) : null;

    $sql = "DELETE FROM reyello WHERE user_uid = '$useruid' AND yello_id = '$yelloid'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':useruid', $useruid);
    $stmt->bindValue(':yelloid', $yelloid);

    $result = $stmt->execute();

    //If post was successful
    if($result) {
        header ('Location: index.php');
    }
}

?>