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
$yello_id = $_GET['y'];

//Pull username from ID
if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare('SELECT * FROM users WHERE uid = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Pulls recent tweets from you and people who you follow
    $commentyellos = $pdo->prepare("SELECT t.*, u.* FROM yellos AS t, users AS u WHERE t.yello_id = '$yello_id' AND t.user_uid = u.uid ORDER BY t.yello_time DESC;");
    $commentyellos->execute();
    $yellos = $commentyellos->fetchAll(PDO::FETCH_ASSOC);

    //Pulls recent tweets from you and people who you follow
    $comments = $pdo->prepare("SELECT t.*, u.* FROM comment AS t, users AS u WHERE t.yello_id = '$yello_id' AND t.user_uid = u.uid ORDER BY t.comment_date DESC;");
    $comments->execute();
    $comment = $comments->fetchAll(PDO::FETCH_ASSOC);

    //Pulls favorite data
    $favoriteyellos = $pdo->prepare("SELECT * FROM favorite WHERE favorite_user = '$uid'");
    $favoriteyellos->execute();
    $favorites = $favoriteyellos->fetchAll(PDO::FETCH_ASSOC);

    //Pulls reyello data
    $reyellos = $pdo->prepare("SELECT * FROM reyello WHERE user_uid = '$uid'");
    $reyellos->execute();
    $reyello = $reyellos->fetchAll(PDO::FETCH_ASSOC);
}


if(isset($_POST['commentPost'])) {
    $comment = !empty($_POST['commenttext']) ? trim($_POST['commenttext']) : null;
    $user_uid = !empty($_POST['user_uid']) ? trim($_POST['user_uid']) : null;
    $yelloids = !empty($_POST['yelloid']) ? trim($_POST['yelloid']) : null;

    //Preparing insert statement
    $sql = "INSERT INTO comment (yello_id, user_uid, comment) VALUES (:yelloids, :useruid, :comment)";
    $stmt = $pdo->prepare($sql);
    //Bind varibles
    $stmt->bindValue(':yelloids', $yelloids);
    $stmt->bindValue(':useruid', $user_uid);
    $stmt->bindValue(':comment', $comment);

    //Execute the statement
    $result = $stmt->execute();

    //If post was successful
    if($result) {
        header ('Location: yello.php?y=' . $yello_id .'');
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
        header ('Location: yello.php?y=' . $yello_id .'');
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
        header ('Location: yello.php?y=' . $yello_id .'');
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
        header ('Location: yello.php?y=' . $yello_id .'');
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
        header ('Location: yello.php?y=' . $yello_id .'');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title>Yello Thread</title>
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
                    <a href="index.php">
                        <div class="section active">
                            <h2 class="tiny-title"><span class="fas fa-home icon"></span> Home</h2>
                        </div>
                    </a>
                    <a href="profile.php?u=<?=$user['uid']?>">
                        <div class="section">
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
                    <h1 class="title" style="margin-bottom: 25px;margin-top: 14px">Thread</h1>
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
                                        <div class="profile-pic"></div>
                                    </div>
                                    <div class="col-lg-10" style="padding-left: 0px">
                                        <h3 class="tiny-title" style="margin-bottom: 0px"><?=$yello['display']?>  <a href="profile.php?u=<?=$yello['uid']?>" class="yello-link">@<?=$yello['username']?></a></h3>
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
                                            foreach ($favorites as $favorite){
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
                                                    <form action="yello.php?y=<?=$yello_id?>" method="post">
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
                                                    <form action="yello.php?y=<?=$yello_id?>" method="post">
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
                                                    <button name="comment" class="yelloicon fas fa-comment-alt" data-toggle="modal" data-target="#comment">
                                                        <?php foreach ($ccount as $cc) : ?>
                                                            <h5 class="light-text"><?=$cc?></h5>
                                                        <?php endforeach; ?>
                                                    </button>
                                            </div>
                                            <div class="col-lg-2">
                                                <!-- If post hasnt been reyello by logged in user -->
                                                <?php if($checkreyello != $yello['yello_id']) { ?>
                                                    <form action="yello.php?y=<?=$yello_id?>" method="post">
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
                                                    <form action="yello.php?y=<?=$yello_id?>" method="post">
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

                        <!-- Pull comments for a specific post -->
                        <?php foreach ($comment as $userc) : ?>
                            <?php

                            $date = date_create($userc['comment_date']);
                            ?>
                        <div class="yello-float">
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="profile-pic"></div>
                                </div>
                                <div class="col-lg-10" style="padding-left: 0px">
                                    <h3 class="tiny-title" style="margin-bottom: 0px"><?=$userc['display']?>  <a href="profile.php?u=<?=$userc['uid']?>" class="yello-link">@<?=$userc['username']?></a> · <a class="yello-link" style="color: #BBBBBB;"><?=date_format($date, 'd/m/Y H:i:s');?></a></h3>
                                    <p class="yello-text"><?=$userc['comment']?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="comment" tabindex="-1" role="dialog" aria-labelledby="commentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 550px">
        <div class="modal-content">
            <div class="modal-header" style="padding-top: 25px; padding-bottom: 10px">
                <h4 class="title" id="commentLabel">Write a comment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="yello.php?y=<?=$yello_id?>" method="post">
                    <div class="form-group" style="margin-bottom: 10px">
                        <input type="hidden" name="user_uid" value="<?=$uid?>">
                        <input type="hidden" name="yelloid" value="<?=$yello_id?>">
                        <textarea id="commenttext" name="commenttext"  class="yello-post" maxlength="150" onkeyup="countChar(this)" placeholder="Write a comment..." required></textarea>
                        <div id="charNum" align="right" class="tiny-text" style="margin-bottom: 5px; margin-top: -25px; margin-right: 5px">150</div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0px">
                        <input type="submit" name="commentPost" value="Comment" class="btn btn-theme btn-block">
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


