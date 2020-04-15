<?php
session_start();
require 'php/connect.php';

//Check if user is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

//Assigns user id to a varible
$uid = $_SESSION['user_id'];

//Pull username from ID
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE uid = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Pulls recent tweets from you and people who you follow
    $recentyellos = $pdo->prepare("SELECT t.*, u.* FROM yellos AS t, users AS u WHERE ( t.user_uid = '$uid' OR t.user_uid IN (SELECT f.user_uid FROM follow AS f WHERE follow_user = '$uid')) AND t.user_uid = u.uid ORDER BY t.yello_time DESC;");
    $recentyellos->execute();
    $yellos = $recentyellos->fetchAll(PDO::FETCH_ASSOC);

}

if(isset($_POST['yello'])) {
    $yello = !empty($_POST['yelloText']) ? trim($_POST['yelloText']) : null;

    //Preparing insert statement
    $sql = "INSERT INTO yellos (yello, user_uid) VALUES (:yello, :user_uid)";
    $stmt = $pdo->prepare($sql);
    //Bind varibles
    $stmt->bindValue(':yello', $yello);
    $stmt->bindValue(':user_uid', $uid);

    //Execute the statement
    $result = $stmt->execute();

    //If post was successful
    if($result) {
        header ('Location: index.php');
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title><?=ucwords($user['username'])?>'s Timeline</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">

    <script>
        function countChar(val) {
            var len = val.value.length;
            if (len >= 150) {
                val.value = val.value.substring(0,150);
            } else {
                $('#charNum').text(150 - len);
            }
        };
    </script>
</head>
<body>
<div class="topbar"></div>
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <nav class="row side-nav">
                <div class="col-lg-5 nav-logo">
                    <img src="img/yello-dark.gif" width="100%">
                </div>
            </nav>
            <nav class="row">
                <div class="col-lg-12 navigation">
                    <a href="">
                        <div class="section active">
                            <h2 class="tiny-title"><span class="fas fa-home icon"></span> Home</h2>
                        </div>
                    </a>
                    <a href="profile.php?u=<?=$user['uid']?>">
                        <div class="section">
                            <h2 class="tiny-title"><span class="fas fa-circle icon"></span> Profile</h2>
                        </div>
                    </a>
                    <a href="">
                        <div class="section">
                            <h2 class="tiny-title"><span class="fas fa-search icon"></span> Explore</h2>
                        </div>
                    </a>
                    <a href="">
                        <div class="section">
                            <h2 class="tiny-title"><span class="fas fa-cog icon"></span> Settings</h2>
                        </div>
                    </a>
                    <a href="php/logout.php">
                        <div class="section">
                            <h2 class="tiny-title"><span class="fas fa-sign-out-alt icon"></span> Logout</h2>
                        </div>
                    </a>
                </div>
            </nav>
        </div>
        <div class="col-lg-6 content">
            <h1 class="title">Home</h1>
            <form action="index.php" method="post">
                <div class="form-group" style="margin-bottom: 10px">
                    <textarea id="yelloText" name="yelloText"  class="yello-post" maxlength="150" onkeyup="countChar(this)" placeholder="Whats happening?" required></textarea>
                    <div id="charNum" align="right" class="tiny-text" style="margin-bottom: 5px; margin-top: -25px; margin-right: 5px">150</div>
                </div>
                <div class="form-group" style="margin-bottom: 0px">
                    <input type="submit" name="yello" value="Yello!" class="btn btn-theme btn-block">
                </div>
            </form>
            <div class="row" style="padding-top: 15px">
                <div class="col-lg-12">
                    <?php
                    if($yellos == null){
                        echo "<div class='yello-float'><i>No yellos</i></div>";
                    } else {
                    ?>
                    <?php foreach ($yellos as $yello) : ?>
                    <div class="yello-float">
                        <div class="row">
                            <div class="col-lg-2">
                                <div class="profile-pic"></div>
                            </div>
                            <div class="col-lg-10" style="padding-left: 0px">
                                <h3 class="tiny-title" style="margin-bottom: 0px"><?=$yello['display']?>  <a href="profile.php?u=<?=$yello['uid']?>" class="yello-link">@<?=$yello['username']?></a></h3>
                                <p class="yello-text"><?=$yello['yello']?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; }?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>


<!--
//Pulls out who the logged in users followers are
    $followerPull = $pdo->prepare("SELECT f.user_uid, u.username, u.uid  FROM follow AS f, users AS u WHERE f.user_uid = '$uid' AND f.follow_user = u.uid;");
    $followerPull->execute();
    $follower = $followerPull->fetchAll(PDO::FETCH_ASSOC);

    //Pulls out who the logged in user is following
    $followingPull = $pdo->prepare("SELECT f.follow_user, u.username, u.uid  FROM follow AS f, users AS u WHERE f.follow_user = '$uid' AND f.user_uid = u.uid;");
    $followingPull->execute();
    $following = $followingPull->fetchAll(PDO::FETCH_ASSOC);





echo '<h1>Hello' . ', ' . $user['username'] . '</h1>';


$follower_num = 0;
foreach ($follower as $followeruser) {
    $follower_num++;
}
echo '<h3>Followers - ' . $follower_num . '</h3>';
if($follower == null){
    echo "<i>You have no followers #loser</i>";
} else {
    $follower_num = 0;
    foreach ($follower as $followeruser) {
        echo $followeruser['uid'] . ' - ' . $followeruser['username'] . '<br>';
    }
}

$following_num = 0;
foreach ($following as $followinguser) {
    $following_num++;
}
echo '<h3>Following - ' . $following_num . '</h3>';
if($following == null){
    echo "<i>You are following no one :C</i>";
} else {
    foreach ($following as $followinguser){
        echo $followinguser['uid'] . ' - ' . $followinguser['username'] . '<br>';
    }
}
-->


