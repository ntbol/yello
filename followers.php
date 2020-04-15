<?php
error_reporting(E_ERROR);
session_start();
require 'php/connect.php';

//Check if user is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

//Assigns user id to a varible
$uid = $_SESSION['user_id'];

//Pull uid from url
$profile_uid = $_GET['u'];
//Pull username from ID
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE uid = '$profile_uid'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Pulls out who the logged in users followers are
    $followerPull = $pdo->prepare("SELECT f.*, u.*  FROM follow AS f, users AS u WHERE f.user_uid = '$profile_uid' AND f.follow_user = u.uid;");
    $followerPull->execute();
    $follower = $followerPull->fetchAll(PDO::FETCH_ASSOC);

    //Pulls out who the logged in user is following for follow button
    $followingPull = $pdo->prepare("SELECT f.follow_user, u.username, u.uid  FROM follow AS f, users AS u WHERE f.follow_user = '$uid' AND f.user_uid = u.uid;");
    $followingPull->execute();
    $following = $followingPull->fetchall(PDO::FETCH_ASSOC);

}




    if(isset($_POST['follow'])){

        $uuid = $_POST['user'];

        $follow = "INSERT INTO follow (follow_user, user_uid) VALUES (:loggeduser, :followeduser)";
        $stmt = $pdo->prepare($follow);
        //Bind varibles
        $stmt->bindValue(':followeduser', $uuid);
        $stmt->bindValue(':loggeduser', $uid);

        $result = $stmt->execute();

        //If follow was successful
        if($result) {
            header('Location: followers.php?u=' . $profile_uid .'');
        }
    }

    if(isset($_POST['unfollow'])){

        $uuid = $_POST['user'];

        $follow = "DELETE FROM follow WHERE follow_user = :loggeduser AND user_uid = :followeduser";
        $stmt = $pdo->prepare($follow);
        //Bind varibles
        $stmt->bindValue(':followeduser', $uuid);
        $stmt->bindValue(':loggeduser', $uid);

        $result = $stmt->execute();

        //If follow was successful
        if($result) {
            header('Location: followers.php?u=' . $profile_uid .'');
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title><?=ucwords($user['username'])?>'s Followers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
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
                    <a href="index.php">
                        <div class="section">
                            <h2 class="tiny-title"><span class="fas fa-home icon"></span> Home</h2>
                        </div>
                    </a>
                    <a href="profile.php?u=<?=$uid?>">
                        <div class="section active">
                            <h2 class="tiny-title"><span class="fas fa-circle icon"></span> Profile</h2>
                        </div>
                    </a>
                    <a href="explore.php">
                        <div class="section">
                            <h2 class="tiny-title"><span class="fas fa-search icon"></span> Explore</h2>
                        </div>
                    </a>
                    <a href="settings.php">
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
            <div class="row">
                <div class="col-lg-1">
                    <a href="index.php"><span class="fas fa-chevron-left fa-2x icon" style="padding-top: 15px"></span></a>
                </div>
                <div class="col-lg-11">
                    <h1 class="title" style="margin-bottom: 0px"><?=$user['display']?></h1>
                    <p class="light-text">@<?=$user['username']?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <a href="followers.php?u=<?=$profile_uid?>" class="btn btn-block btn-theme-air air-active">Followers</a>
                </div>
                <div class="col-lg-6">
                    <a href="following.php?u=<?=$profile_uid?>" class="btn btn-block btn-theme-air">Following</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                <?php foreach ($follower as $followeruser) : ?>
                    <?php
                        foreach($following as $f) {
                            $result = $f['uid'];
                            if($result == $followeruser['follow_user']){
                                $userF = $followeruser['follow_user'];
                            } else {

                            }
                        }
                    ?>
                    <div class="row" style="padding-top: 15px">
                        <div class="col-lg-2">
                            <div class="profile-pic"></div>
                        </div>
                        <div class="col-lg-7" style="padding-left: 0px; padding-top: 7px">
                            <h3 class="tiny-title" style="margin-bottom: 0px"><?=$followeruser['display']?></h3>
                            <a href="profile.php?u=<?=$followeruser['uid']?>"><h4 class="yello-text" style="color: #f1c40f">@<?=$followeruser['username']?></h4></a>
                        </div>
                        <div class="col-lg-3" style="padding-top: 7px">
                            <?php if($_SESSION['user_id'] == $followeruser['uid']){ ?>
                            <?php } elseif($followeruser['follow_user'] == $userF) { ?>
                            <form action="followers.php?u=<?=$profile_uid?>" method="post">
                                <input type="hidden" name="user" value="<?=$followeruser['uid']?>">
                                <input type="submit" name="unfollow" value="Following" class="btn btn-theme btn-block">
                            </form>
                            <?php } else { ?>
                            <form action="followers.php?u=<?=$profile_uid?>" method="post">
                                <input type="hidden" name="user" value="<?=$followeruser['uid']?>">
                                <input type="submit" name="follow" value="Follow" class="btn btn-theme-thin btn-block">
                            </form>
                            <?php } ?>
                        </div>
                    </div>
                <hr>
                <?php endforeach; ?>
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


