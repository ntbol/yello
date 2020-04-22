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
    $followingPull = $pdo->prepare("SELECT f.follow_user, f.user_uid  FROM follow AS f WHERE f.follow_user = '$uid' AND f.user_uid = '$profile_uid';");
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

    //Pulls the post the user has favorited
    $userfavorites = $pdo->prepare("SELECT f.favorite_user, t.*, u.* FROM favorite AS f, yellos AS t, users AS u WHERE f.favorite_user = '$profile_uid' AND f.yello_id = t.yello_id AND t.user_uid = u.uid;");
    $userfavorites->execute();
    $favorites = $userfavorites->fetchAll(PDO::FETCH_ASSOC);

    //Pulls favorite data
    $favoriteyellos = $pdo->prepare("SELECT * FROM favorite WHERE favorite_user = '$uid'");
    $favoriteyellos->execute();
    $favoritedata = $favoriteyellos->fetchAll(PDO::FETCH_ASSOC);

    //Pulls reyello data
    $reyellos = $pdo->prepare("SELECT * FROM reyello WHERE user_uid = '$uid'");
    $reyellos->execute();
    $reyello = $reyellos->fetchAll(PDO::FETCH_ASSOC);

    //Pulls theme color data
    $theme = $pdo->prepare("SELECT  u.uid, c.scheme_color FROM customize AS c, users AS u WHERE u.uid = '$profile_uid' AND c.scheme_id = u.user_scheme_id");
    $theme->execute();
    $themecolor = $theme->fetch(PDO::FETCH_ASSOC);

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
        $profile_uid = $_GET['u'];
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

    //If save is clicked on edit profile modal
    if(isset($_POST['editProfile'])){
        //Sanitizes data
        $bio = !empty($_POST['bio']) ? trim($_POST['bio']) : null;
        $link = !empty($_POST['link']) ? trim($_POST['link']) : null;
        $display = !empty($_POST['display']) ? trim($_POST['display']) : null;
        $color = !empty($_POST['color']) ? trim($_POST['color']) : null;

        //Updates table
        $edit = "UPDATE users SET link ='$link', bio ='$bio', display ='$display', user_scheme_id = '$color' WHERE uid ='$uid'";
        $stmt = $pdo->prepare($edit);

        $result = $stmt->execute();

        //If successful, returns to user profile
        if($result) {
            header('Location: profile.php?u=' . $uid .'');
        }

    }

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
        header ('Location: profile.php?u=' . $profile_uid .'');
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
        header ('Location: profile.php?u=' . $profile_uid .'');
    }
}

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
        header ('Location: profile.php?u=' . $profile_uid .'');
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
        header ('Location: profile.php?u=' . $profile_uid .'');
    }
}

    //Adjusts SQL DATETIME and reformats to easy to read format
    $createDate = new DateTime($user['user_date']);
    $strip = $createDate->format('F Y');

    // Remove the http://, www., and slash(/) from the URL
    $input = $user['link'];
    // If URI is like, eg. www.way2tutorial.com/
    $input = trim($input, '/');
    // If not have http:// or https:// then prepend it
    if (!preg_match('#^http(s)?://#', $input)) {
        $input = 'http://' . $input;
    }
    $urlParts = parse_url($input);
    // Remove www.
    $domain_name = preg_replace('/^www\./', '', $urlParts['host']);

    //Assigns the profile users color scheme
    $bannerColor = $themecolor['scheme_color'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title><?=ucwords($user['username'])?>'s Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">

    <!-- Sets up theme color to work with buttons on the profile -->
    <style>
        .btn-theme-air:hover{
            border-bottom: <?=$bannerColor?> 5px solid;
        }
        .btn-theme{
            background: <?=$bannerColor?>;
            border: <?=$bannerColor?> 3px solid;
        }
        .btn-theme:hover{
            color: <?=$bannerColor?>;
            border: <?=$bannerColor?> 3px solid;
        }
        .btn-theme-thin{
            color: <?=$bannerColor?>;
            border: <?=$bannerColor?> 3px solid;
        }
        .btn-theme-thin:hover{
            background: <?=$bannerColor?>;
            color: white;
            border: <?=$bannerColor?> 3px solid;
        }
        .yelloicon:hover{
            color: <?=$bannerColor?>;
        }
        .yelloiconactive{
            color: <?=$bannerColor?>;
        }
    </style>

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
                    <a href="index.php"><span class="fas fa-chevron-left fa-2x icon" style="padding-top: 15px; color: <?=$bannerColor?>"></span></a>
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
                    <div class="banner" style="background: <?=$bannerColor?>"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-2">
                    <div class="user-profile-pic"  style="background: <?=$bannerColor?>"></div>
                </div>
                <div class="col-lg-6"></div>
                <!-- This button changes based on the user logged in and if you are following the users profile you are looking at -->
                <div class="col-lg-3" style="padding-top: 5px">
                    <?php if($_SESSION['user_id'] == $profile_uid){ ?>
                        <button class="btn btn-theme-thin btn-block" data-toggle="modal" data-target="#editProfile">Edit</button>
                    <?php } elseif($profile_uid == $following['user_uid']) { ?>
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
                    <h4 class="yello-text" style="color: <?=$bannerColor?>">@<?=$user['username']?></h4>
                    <p class="yello-text" style="padding-top: 10px"><?=$user['bio']?></p>
                    <div class="row" style="padding-top: 15px">
                        <?php if($user['link'] != null) {?>
                            <div class="col-lg-6 light-text">
                                <span class="fas fa-link"></span> <a href="<?=$user['link']?>" class="yello-link" style="color: <?=$bannerColor?>"><?=$domain_name?></a>
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
                                <h4 class="small-title-yello" style="color : <?=$bannerColor?>"><?=$followersCount?></h4>
                            <?php } else { ?>
                                <h4 class="small-title-yello" style="color: <?=$bannerColor?>">0</h4>
                        <?php } endforeach; ?>
                        <p class="yello-text">Followers</p>
                    </div>
                </a>
                <a href="following.php?u=<?=$profile_uid?>" style="font-weight: 400">
                    <div class="col-lg-3">
                        <?php foreach ($followingc[0] as $followingCount) : ?>
                            <?php if($followingCount != null) { ?>
                                <h4 class="small-title-yello" style="color: <?=$bannerColor?>"><?=$followingCount?></h4>
                            <?php } else { ?>
                                <h4 class="small-title-yello" style="color: <?=$bannerColor?>">0</h4>
                        <?php } endforeach; ?>
                        <p class="yello-text">Following</p>
                    </div>
                </a>
            </div>
            <hr>
            <?php if(isset($_POST['favorites'])) { ?>
            <div class="row">
                <div class="col-lg-6">
                    <form method="post" action="profile.php?u=<?=$profile_uid?>">
                        <button name="yello" class="btn btn-block btn-theme-air">Yellos</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <form method="post" action="profile.php?u=<?=$profile_uid?>">
                        <button name="favorites" class="btn btn-block btn-theme-air air-active" style="border-bottom-color: <?=$bannerColor?>">Favorites</button>
                    </form>
                </div>
            </div>
            <div class="row" style="padding-top: 15px">
                <div class="col-lg-12">
                    <?php
                    if($favorites == null){
                        echo "<div class='yello-float'><i>No favorited yellos</i></div>";
                    } else {
                        ?>
                    <?php foreach ($favorites as $fav) : ?>
                        <div class="yello-float">
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="profile-pic" style="background: <?=$bannerColor?>"></div>
                                </div>
                                <div class="col-lg-10" style="padding-left: 0px">
                                    <h3 class="tiny-title" style="margin-bottom: 0px"><?=$fav['display']?>  <span class="yello-link" style="color: <?=$bannerColor?>">@<?=$fav['username']?></span></h3>
                                    <p class="yello-text"><?=$fav['yello']?></p>
                                    <div class="row" style="padding-top: 15px">
                                        <!-- Checks to see if the post was already favorited/reyello by logged in user -->
                                        <?php
                                        //Sets default value to blank to stop php errors of unassigned varibles
                                        $yelloid = '';
                                        $checkyello = '';
                                        $reyelloid = '';
                                        $checkreyello = '';
                                        //Checks logged in users favorites and checks which timeline post are in the table. To prevent double favoriting
                                        foreach ($favoritedata as $favorite){
                                            $yelloid = $favorite['yello_id'];
                                            if ($yelloid == $fav['yello_id']){
                                                $checkyello = $fav['yello_id'];
                                            }
                                        }
                                        //Checks logged in users reyellos and checks which timeline post are in the table. To prevent double reyellos
                                        foreach ($reyello as $ry){
                                            $reyelloid = $ry['yello_id'];
                                            if ($reyelloid == $fav['yello_id']){
                                                $checkreyello = $fav['yello_id'];
                                            }
                                        }
                                        //Counts the amount of favorites a post has
                                        $countid = $fav['yello_id'];
                                        $favoritecount = $pdo->prepare("SELECT COUNT(*) FROM favorite WHERE yello_id = '$countid'");
                                        $favoritecount->execute();
                                        $fcount = $favoritecount->fetch(PDO::FETCH_ASSOC);

                                        //Counts the amount of comments a post has
                                        $commentcount = $pdo->prepare("SELECT COUNT(*) FROM comment WHERE yello_id = '$countid'");
                                        $commentcount->execute();
                                        $ccount = $commentcount->fetch(PDO::FETCH_ASSOC);

                                        //Counts the amount of reyellos a post has
                                        $reyellocount = $pdo->prepare("SELECT COUNT(*) FROM reyello WHERE yello_id = '$countid'");
                                        $reyellocount->execute();
                                        $rcount = $reyellocount->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <div class="col-lg-2">
                                            <!-- If post hasnt been favorited by logged in user -->
                                            <?php if($checkyello != $fav['yello_id']) { ?>
                                                <form action="profile.php?u=<?=$uid?>" method="post">
                                                    <input type="hidden" name="yelloid" value="<?=$fav['yello_id']?>">
                                                    <input type="hidden" name="uid" value="<?=$uid?>">
                                                    <button type="submit" name="favorite" class="yelloicon fas fa-heart" >
                                                        <?php foreach ($fcount as $fc) : ?>
                                                            <h5 class="light-text"><?=$fc?></h5>
                                                        <?php endforeach; ?>
                                                    </button>
                                                </form>
                                                <!-- If post has been favorited by logged in user -->
                                            <?php } else { ?>
                                                <form action="profile.php?u=<?=$uid?>" method="post">
                                                    <input type="hidden" name="yelloid" value="<?=$fav['yello_id']?>">
                                                    <input type="hidden" name="uid" value="<?=$uid?>">
                                                    <button type="submit" name="unfavorite" class="yelloiconactive fas fa-heart" >
                                                        <?php foreach ($fcount as $fc) : ?>
                                                            <h5 class="light-text"><?=$fc?></h5>
                                                        <?php endforeach; ?>
                                                    </button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                        <div class="col-lg-2">
                                            <form action="yello.php?y=<?=$fav['yello_id']?>" method="post">
                                                <button type="submit" name="comment" class="yelloicon fas fa-comment-alt">
                                                    <?php foreach ($ccount as $cc) : ?>
                                                        <h5 class="light-text"><?=$cc?></h5>
                                                    <?php endforeach; ?>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-lg-2">
                                            <!-- If post hasnt been reyello by logged in user -->
                                            <?php if($checkreyello != $fav['yello_id']) { ?>
                                                <form action="profile.php?u=<?=$uid?>" method="post">
                                                    <input type="hidden" name="yelloid" value="<?=$fav['yello_id']?>">
                                                    <input type="hidden" name="uid" value="<?=$uid?>">
                                                    <button type="submit" name="retweet" class="yelloicon fas fa-retweet" >
                                                        <?php foreach ($rcount as $rc) : ?>
                                                            <h5 class="light-text"><?=$rc?></h5>
                                                        <?php endforeach; ?>
                                                    </button>
                                                </form>
                                                <!-- If post has been reyello by logged in user -->
                                            <?php } else { ?>
                                                <form action="profile.php?u=<?=$uid?>" method="post">
                                                    <input type="hidden" name="yelloid" value="<?=$fav['yello_id']?>">
                                                    <input type="hidden" name="uid" value="<?=$uid?>">
                                                    <button type="submit" name="unretweet" class="yelloiconactive fas fa-retweet" >
                                                        <?php foreach ($rcount as $rc) : ?>
                                                            <h5 class="light-text"><?=$rc?></h5>
                                                        <?php endforeach; ?>
                                                    </button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; } ?>
                </div>
            </div>
            <?php } else { ?>
            <div class="row">
                <div class="col-lg-6">
                    <form method="post" action="profile.php?u=<?=$profile_uid?>">
                        <button name="yello" class="btn btn-block btn-theme-air air-active" style="border-bottom-color: <?=$bannerColor?>">Yellos</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <form method="post" action="profile.php?u=<?=$profile_uid?>">
                        <button name="favorites" class="btn btn-block btn-theme-air">Favorites</button>
                    </form>
                </div>
            </div>
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
                                        <div class="profile-pic" style="background: <?=$bannerColor?>"></div>
                                    </div>
                                    <div class="col-lg-10" style="padding-left: 0px">
                                        <h3 class="tiny-title" style="margin-bottom: 0px"><?=$yello['display']?>  <a href="profile.php?u=<?=$yello['uid']?>" class="yello-link" style="color: <?=$bannerColor?>">@<?=$yello['username']?></a></h3>
                                        <p class="yello-text"><?=$yello['yello']?></p>
                                        <div class="row" style="padding-top: 15px">
                                            <!-- Checks to see if the post was already favorited/reyello by logged in user -->
                                            <?php
                                            //Sets default value to blank to stop php errors of unassigned varibles
                                            $yelloid = '';
                                            $checkyello = '';
                                            $reyelloid = '';
                                            $checkreyello = '';
                                            //Checks logged in users favorites and checks which timeline post are in the table. To prevent double favoriting
                                            foreach ($favoritedata as $favorite){
                                                $yelloid = $favorite['yello_id'];
                                                if ($yelloid == $yello['yello_id']){
                                                    $checkyello = $yello['yello_id'];
                                                }
                                            }
                                            //Checks logged in users reyellos and checks which timeline post are in the table. To prevent double reyellos
                                            foreach ($reyello as $ry){
                                                $reyelloid = $ry['yello_id'];
                                                if ($reyelloid == $yello['yello_id']){
                                                    $checkreyello = $yello['yello_id'];
                                                }
                                            }
                                            //Counts the amount of favorites a post has
                                            $countid = $yello['yello_id'];
                                            $favoritecount = $pdo->prepare("SELECT COUNT(*) FROM favorite WHERE yello_id = '$countid'");
                                            $favoritecount->execute();
                                            $fcount = $favoritecount->fetch(PDO::FETCH_ASSOC);

                                            //Counts the amount of comments a post has
                                            $commentcount = $pdo->prepare("SELECT COUNT(*) FROM comment WHERE yello_id = '$countid'");
                                            $commentcount->execute();
                                            $ccount = $commentcount->fetch(PDO::FETCH_ASSOC);

                                            //Counts the amount of reyellos a post has
                                            $reyellocount = $pdo->prepare("SELECT COUNT(*) FROM reyello WHERE yello_id = '$countid'");
                                            $reyellocount->execute();
                                            $rcount = $reyellocount->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <div class="col-lg-2">
                                                <!-- If post hasnt been favorited by logged in user -->
                                                <?php if($checkyello != $yello['yello_id']) { ?>
                                                    <form action="profile.php?u=<?=$uid?>" method="post">
                                                        <input type="hidden" name="yelloid" value="<?=$yello['yello_id']?>">
                                                        <input type="hidden" name="uid" value="<?=$uid?>">
                                                        <button type="submit" name="favorite" class="yelloicon fas fa-heart" >
                                                            <?php foreach ($fcount as $fc) : ?>
                                                                <h5 class="light-text"><?=$fc?></h5>
                                                            <?php endforeach; ?>
                                                        </button>
                                                    </form>
                                                    <!-- If post has been favorited by logged in user -->
                                                <?php } else { ?>
                                                    <form action="profile.php?u=<?=$uid?>" method="post">
                                                        <input type="hidden" name="yelloid" value="<?=$yello['yello_id']?>">
                                                        <input type="hidden" name="uid" value="<?=$uid?>">
                                                        <button type="submit" name="unfavorite" class="yelloiconactive fas fa-heart" >
                                                            <?php foreach ($fcount as $fc) : ?>
                                                                <h5 class="light-text"><?=$fc?></h5>
                                                            <?php endforeach; ?>
                                                        </button>
                                                    </form>
                                                <?php } ?>
                                            </div>
                                            <div class="col-lg-2">
                                                <form action="yello.php?y=<?=$yello['yello_id']?>" method="post">
                                                    <button type="submit" name="comment" class="yelloicon fas fa-comment-alt">
                                                        <?php foreach ($ccount as $cc) : ?>
                                                            <h5 class="light-text"><?=$cc?></h5>
                                                        <?php endforeach; ?>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="col-lg-2">
                                                <!-- If post hasnt been reyello by logged in user -->
                                                <?php if($checkreyello != $yello['yello_id']) { ?>
                                                    <form action="profile.php?u=<?=$uid?>" method="post">
                                                        <input type="hidden" name="yelloid" value="<?=$yello['yello_id']?>">
                                                        <input type="hidden" name="uid" value="<?=$uid?>">
                                                        <button type="submit" name="retweet" class="yelloicon fas fa-retweet" >
                                                            <?php foreach ($rcount as $rc) : ?>
                                                                <h5 class="light-text"><?=$rc?></h5>
                                                            <?php endforeach; ?>
                                                        </button>
                                                    </form>
                                                    <!-- If post has been reyello by logged in user -->
                                                <?php } else { ?>
                                                    <form action="profile.php?u=<?=$uid?>" method="post">
                                                        <input type="hidden" name="yelloid" value="<?=$yello['yello_id']?>">
                                                        <input type="hidden" name="uid" value="<?=$uid?>">
                                                        <button type="submit" name="unretweet" class="yelloiconactive fas fa-retweet" >
                                                            <?php foreach ($rcount as $rc) : ?>
                                                                <h5 class="light-text"><?=$rc?></h5>
                                                            <?php endforeach; ?>
                                                        </button>
                                                    </form>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; }?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>


<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfile" tabindex="-1" role="dialog" aria-labelledby="editProfileLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 550px">
        <div class="modal-content">
            <div class="modal-header" style="padding-top: 25px; padding-bottom: 10px">
                <h4 class="title" id="editProfileLabel">Edit Profile</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="profile.php?u=<?=$uid?>">
                    <div class="form-group">
                        <h5 class="tiny-title">Theme Color</h5>
                        <label>
                            <input type="radio" name="color" value="1">
                            <div id="colorSelect" class="yello-theme"></div>
                        </label>
                        <label>
                            <input type="radio" name="color" value="2">
                            <div  id="colorSelect" class="green-theme"></div>
                        </label>
                        <label>
                            <input type="radio" name="color" value="3">
                            <div  id="colorSelect" class="blue-theme"></div>
                        </label>
                        <label>
                            <input type="radio" name="color" value="4">
                            <div  id="colorSelect" class="purple-theme"></div>
                        </label>
                        <label>
                            <input type="radio" name="color" value="5">
                            <div  id="colorSelect" class="pink-theme"></div>
                        </label>
                        <label>
                            <input type="radio" name="color" value="6">
                            <div  id="colorSelect" class="orange-theme"></div>
                        </label>
                    </div>
                    <div class="form-group">
                        <h5 class="tiny-title">Display Name</h5>
                        <input type="text" name="display" class="form-control" value="<?=$user['display']?>">
                    </div>
                    <div class="form-group">
                        <h5 class="tiny-title">Bio</h5>
                        <textarea id="bioText" name="bio" class="yello-post yello-text" maxlength="150" onkeyup="countChar(this)"><?=$user['bio']?>
                        </textarea>
                        <div id="charNum" align="right" class="tiny-text" style="margin-bottom: 5px; margin-top: -25px; margin-right: 5px">150</div>
                    </div>
                    <div class="form-group">
                        <h5 class="tiny-title">Link</h5>
                        <input type="text" name="link" class="form-control" value="<?=$user['link']?>"
                    </div>
                    <div class="form-group" style="padding-top: 25px">
                        <input type="submit" name="editProfile" class="btn btn-theme btn-block" value="Save">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>


