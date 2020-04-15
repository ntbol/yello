<?php
session_start();
require 'php/connect.php';
require 'lib/password.php';

//Check if user is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

//Assigns user id to a varible
$uid = $_SESSION['user_id'];

//Default values for errors so no php errors
$error = '';
$errorusername = '';
$errorpassword = '';
$passwordsuccess = '';
//Pull username from ID
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE uid = '$uid'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

//Allow users to change email
if(isset($_POST['change-email'])){
    $email = !empty($_POST['new-email']) ? trim($_POST['new-email']) : null;

    //Checking if the supplied email already exists
    //Preparing SQL statement
    $sql = "SELECT COUNT(email) AS num FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    //Fetch the row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    //Username already exists error
    if($row['num'] > 0){
        $error = 'That email already exists!';
    } else {

        //Preparing insert statement
        $sql = "UPDATE users SET email = :email WHERE uid ='$uid'";
        $stmt = $pdo->prepare($sql);
        //Bind varibles
        $stmt->bindValue(':email', $email);

        //Execute the statement
        $result = $stmt->execute();

        //If signup was successful
        if ($result) {
            header('Location: settings.php');
        }
    }

}

//Allows user to change username
if(isset($_POST['change-username'])){
    $username = !empty($_POST['new-username']) ? trim($_POST['new-username']) : null;

    //Checking if the supplied username already exists
    //Preparing SQL statement
    $sql = "SELECT COUNT(username) AS num FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    //Fetch the row
    $usernamerow = $stmt->fetch(PDO::FETCH_ASSOC);

    //Username already exists error
    if($usernamerow['num'] > 0){
        $errorusername = 'That username already exists!';
    } else {

        //Preparing insert statement
        $sql = "UPDATE users SET username = :username WHERE uid ='$uid'";
        $stmt = $pdo->prepare($sql);
        //Bind varibles
        $stmt->bindValue(':username', $username);

        //Execute the statement
        $result = $stmt->execute();

        //If signup was successful
        if ($result) {
            header('Location: settings.php');
        }
    }

}

//Allows user to change password
//Checks to see if passwords match
if(isset($_POST["change-password"])){
    if ($_POST['new-password'] == $_POST['confirm-password']){
        $password = addslashes(htmlspecialchars($_POST['confirm-password']));
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 12));
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // <== add this line
            $sql = "UPDATE users SET userpass = '$passwordHash' WHERE uid = '$uid'";
            if ($pdo->query($sql)) {
                $passwordsuccess = "Password updated successfully";
            }
            else{
                $errorpassword = "Already using this password";
            }
            $pdo = null;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    } else {
        $errorpassword = "Passwords do not match";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title><?=ucwords($user['username'])?>'s Settings</title>
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
                        <div class="section active">
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
                    <h1 class="title" style="margin-bottom: 25px;margin-top: 14px">Settings</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="yello-float">
                        <div class="row">
                            <div class="col-lg-12" >
                                <h4 class="tiny-title" style="margin-bottom: 15px">Change Email Address</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12" >
                                <p class="error"><?=$error?></p>
                                <form method="post" action="settings.php">
                                    <div class="form-group">
                                        <input type="email" name="current-email" class="form-control" value="<?=$user['email']?>"  disabled>
                                    </div>
                                    <div class="form-group">
                                        <input type="email" name="new-email" class="form-control" placeholder="New Email Address" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0px">
                                        <input type="submit" name="change-email" class="btn btn-theme btn-block" value="Update">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="yello-float">
                        <div class="row">
                            <div class="col-lg-12" >
                                <h4 class="tiny-title" style="margin-bottom: 15px">Change Username</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12" >
                                <p class="error"><?=$errorusername?></p>
                                <form method="post" action="settings.php">
                                    <div class="form-group">
                                        <input type="text" name="current-username" class="form-control" value="<?=$user['username']?>"  disabled>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="new-username" class="form-control" placeholder="New Username" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0px">
                                        <input type="submit" name="change-username" class="btn btn-theme btn-block" value="Update">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="yello-float">
                        <div class="row">
                            <div class="col-lg-12" >
                                <h4 class="tiny-title" style="margin-bottom: 15px">Change Password</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12" >
                                <p class="error"><?=$errorpassword?></p>
                                <p class="success"><?=$passwordsuccess?></p>
                                <form method="post" action="settings.php">
                                    <div class="form-group">
                                        <input type="password" name="new-password" class="form-control" placeholder="New Password" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="confirm-password" class="form-control" placeholder="Confirm New Password" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0px">
                                        <input type="submit" name="change-password" class="btn btn-theme btn-block" value="Update">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="yello-float">
                        <div class="row">
                            <div class="col-lg-12" >
                                <h4 class="tiny-title" style="margin-bottom: 0px">Credits</h4>
                                <p style="margin-bottom: 5px">Made by : Nathan Boland</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <a href="https://www.nateboland.com/" target="_blank" class="btn btn-theme btn-block">Website</a>
                            </div>
                            <div class="col-lg-6">
                                <a href="https://twitter.com/ntbol" target="_blank" class="btn btn-theme btn-block">Twitter</a>
                            </div>
                        </div>
                    </div>
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


