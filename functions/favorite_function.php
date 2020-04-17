<?php

//Adds post to logged in users favorites
if(isset($_POST['favorite'])) {
    $useruid = !empty($_POST['uid']) ? trim($_POST['uid']) : null;
    $yelloid = !empty($_POST['yelloid']) ? trim($_POST['yelloid']) : null;

    $sql = "INSERT INTO favorite (favorite_user, yello_id) VALUES (:useruid, :yelloid)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':useruid', $useruid);
    $stmt->bindValue(':yelloid', $yelloid);

    $result = $stmt->execute();

    //If post was successful
    if($result) {
        header ('Location: index.php');
    }
}

//Removes post to logged in users favorites
if(isset($_POST['unfavorite'])) {
    $useruid = !empty($_POST['uid']) ? trim($_POST['uid']) : null;
    $yelloid = !empty($_POST['yelloid']) ? trim($_POST['yelloid']) : null;

    $sql = "DELETE FROM favorite WHERE favorite_user = '$useruid' AND yello_id = '$yelloid'";
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