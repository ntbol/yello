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

//Pull uid from url
$profile_uid = $_GET['u'];
//Pull username from ID
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE uid = '$profile_uid'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Pulls the amount of rows in table yellos the uid occurs
    $yellos = $pdo->prepare("SELECT COUNT(*) FROM yellos WHERE user_uid = '$profile_uid'");
    $yellos->execute();
    $yellosAmount = $yellos->fetchALL(PDO::FETCH_ASSOC);

    //Pulls out who the logged in user is following for follow button
    $followingPull = $pdo->prepare("SELECT f.follow_user, u.username, u.uid  FROM follow AS f, users AS u WHERE f.follow_user = '$uid' AND f.user_uid = u.uid;");
    $followingPull->execute();
    $following = $followingPull->fetch(PDO::FETCH_ASSOC);

    //Counts the amount of followers a user has
    $followerCount = $pdo->prepare("SELECT  COUNT(*) FROM follow WHERE user_uid = '$profile_uid'");
    $followerCount->execute();
    $followerc = $followerCount->fetchALL(PDO::FETCH_ASSOC);

    //Counts how many users are following a user
    $followingCount = $pdo->prepare("SELECT  COUNT(*) FROM follow WHERE follow_user = '$profile_uid'");
    $followingCount->execute();
    $followingc = $followingCount->fetchALL(PDO::FETCH_ASSOC);

    //Pulls recent tweets from user
    $profileyellos = $pdo->prepare("SELECT t.*, u.* FROM yellos AS t, users AS u WHERE ( t.user_uid = '$profile_uid') AND t.user_uid = u.uid ORDER BY t.yello_time DESC;");
    $profileyellos->execute();
    $yellos = $profileyellos->fetchAll(PDO::FETCH_ASSOC);
}

    if(isset($_POST['follow'])){

        $follow = "INSERT INTO follow (follow_user, user_uid) VALUES (:loggeduser, :followeduser)";
        $stmt = $pdo->prepare($follow);
        //Bind varibles
        $stmt->bindValue(':followeduser', $profile_uid);
        $stmt->bindValue(':loggeduser', $uid);

        $result = $stmt->execute();

        //If follow was successful
        if($result) {
            header('Location: profile.php?u=' . $profile_uid .'');
        }
    }

    if(isset($_POST['unfollow'])){

        $follow = "DELETE FROM follow WHERE follow_user = :loggeduser AND user_uid = :followeduser";
        $stmt = $pdo->prepare($follow);
        //Bind varibles
        $stmt->bindValue(':followeduser', $profile_uid);
        $stmt->bindValue(':loggeduser', $uid);

        $result = $stmt->execute();

        //If follow was successful
        if($result) {
            header('Location: profile.php?u=' . $profile_uid .'');
        }
    }


    $createDate = new DateTime($user['user_date']);

    $strip = $createDate->format('F Y');
    ;
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
            <div class="row">
                <div class="col-lg-1">
                    <a href="index.php"><span class="fas fa-chevron-left fa-2x icon" style="padding-top: 15px"></span></a>
                </div>
                <div class="col-lg-11">
                    <h1 class="title" style="margin-bottom: 0px"><?=$user['display']?></h1>
                    <?php foreach ($yellosAmount[0] as $yelloCount) : ?>
                        <?php if($yelloCount != null) { ?>
                            <p class="light-text"><?=$yelloCount?> yellos</p>
                        <?php } else { ?>
                            <p class="light-text">No yellos</p>
                    <?php } endforeach; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="banner"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-2">
                    <div class="user-profile-pic"></div>
                </div>
                <div class="col-lg-6"></div>
                <!-- This button changes based on the user logged in and if you are following the users profile you are looking at -->
                <div class="col-lg-3" style="padding-top: 5px">
                    <?php if($_SESSION['user_id'] == $profile_uid){ ?>
                        <a href="" class="btn btn-theme-thin btn-block">Edit</a>
                    <?php } elseif($profile_uid == $following['uid']) { ?>
                        <form action="profile.php?u=<?=$profile_uid?>" method="post">
                            <input type="submit" name="unfollow" value="Following" class="btn btn-theme btn-block">
                        </form>
                    <?php } else { ?>
                        <form action="profile.php?u=<?=$profile_uid?>" method="post">
                            <input type="submit" name="follow" value="Follow" class="btn btn-theme-thin btn-block">
                        </form>
                    <?php } ?>
                </div>
            </div>
            <div class="row" style="padding-top: 15px">
                <div class="col-lg-1"></div>
                <div class="col-lg-11">
                    <h3 class="small-title" style="margin-bottom: 0px"><?=$user['display']?></h3>
                    <h4 class="yello-text" style="color: #f1c40f">@<?=$user['username']?></h4>
                    <p class="yello-text"><?=$user['bio']?></p>
                    <div class="row" style="padding-top: 15px">
                        <?php if($user['link'] != null) {?>
                            <div class="col-lg-6 light-text">
                                <span class="fas fa-link"></span> <a href="<?=$user['link']?>" class="yello-link"><?=$user['link']?></a>
                            </div>
                        <?php } else { ?>
                        <?php } ?>
                        <div class="col-lg-6 light-text">
                            <span class="fas fa-calendar-alt"></span> Joined <?=$strip?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <a href="followers.php?u=<?=$profile_uid?>" style="font-weight: 400">
                    <div class="col-lg-3">
                        <?php foreach ($followerc[0] as $followersCount) : ?>
                            <?php if($followersCount != null) { ?>
                                <h4 class="small-title-yello"><?=$followersCount?></h4>
                            <?php } else { ?>
                                <h4 class="small-title-yello">0</h4>
                        <?php } endforeach; ?>
                        <p class="yello-text">Followers</p>
                    </div>
                </a>
                <a href="following.php?u=<?=$profile_uid?>" style="font-weight: 400">
                    <div class="col-lg-3">
                        <?php foreach ($followingc[0] as $followingCount) : ?>
                            <?php if($followingCount != null) { ?>
                                <h4 class="small-title-yello"><?=$followingCount?></h4>
                            <?php } else { ?>
                                <h4 class="small-title-yello">0</h4>
                        <?php } endforeach; ?>
                        <p class="yello-text">Following</p>
                    </div>
                </a>
            </div>
            <hr>
            <div class="row">
                <div class="col-lg-6">
                    <a href="" class="btn btn-block btn-theme-air air-active">Yellos</a>
                </div>
                <div class="col-lg-6">
                    <a href="" class="btn btn-block btn-theme-air">Favorites</a>
                </div>
            </div>
            <div class="row" style="padding-top: 15px">
                <div class="col-lg-12">
                    <?php foreach ($yellos as $yello) : ?>
                    <div class="yello-float">
                        <div class="row">
                            <div class="col-lg-2">
                                <div class="profile-pic"></div>
                            </div>
                            <div class="col-lg-10" style="padding-left: 0px">
                                <h3 class="tiny-title" style="margin-bottom: 0px"><?=$yello['display']?>  <span class="yello-link">@<?=$yello['username']?></span></h3>
                                <p class="yello-text"><?=$yello['yello']?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;?>
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


