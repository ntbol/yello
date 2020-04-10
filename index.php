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

    //Pulls out who the logged in users followers are
    $followerPull = $pdo->prepare("SELECT f.user_uid, u.username, u.uid  FROM follow AS f, users AS u WHERE f.user_uid = '$uid' AND f.follow_user = u.uid;");
    $followerPull->execute();
    $follower = $followerPull->fetchAll(PDO::FETCH_ASSOC);

    //Pulls out who the logged in user is following
    $followingPull = $pdo->prepare("SELECT f.follow_user, u.username, u.uid  FROM follow AS f, users AS u WHERE f.follow_user = '$uid' AND f.user_uid = u.uid;");
    $followingPull->execute();
    $following = $followingPull->fetchAll(PDO::FETCH_ASSOC);

    //Pulls recent tweets from you and people who you follow
    $recenttweets = $pdo->prepare("SELECT t.*, u.username FROM tweet AS t, users AS u WHERE ( t.user_uid = '$uid' OR t.user_uid IN (SELECT f.user_uid FROM follow AS f WHERE follow_user = '$uid')) AND t.user_uid = u.uid;");
    $recenttweets->execute();
    $tweets = $recenttweets->fetchAll(PDO::FETCH_ASSOC);
}

echo '<h1>Hello' . ', ' . $user['username'] . '</h1>';


echo '<h3>Followers</h3>';
if($follower == null){
    echo "<i>You have no followers #loser</i>";
} else {
    foreach ($follower as $followeruser) {
        echo $followeruser['uid'] . ' - ' . $followeruser['username'] . '<br>';
    }
}

echo '<h3>Following</h3>';
if($following == null){
    echo "<i>You are following no one :C</i>";
} else {
    foreach ($following as $followinguser){
        echo $followinguser['uid'] . ' - ' . $followinguser['username'] . '<br>';
    }
}

echo '<h3>Tweets</h3>';
if($tweets == null){
    echo "<i>No tweets</i>";
} else {
    foreach ($tweets as $tweet){
        echo $tweet['tweet'] . ' <br> - ' . $tweet['username'] . '<br>' . $tweet['tweet_time'] . '<br><hr>';
    }
}


?>


