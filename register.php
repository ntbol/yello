<?php
require 'php/connect.php';
require 'lib/password.php';

// Default values
$error = "";
$success = "";

//When register from is submitted
if(isset($_POST['register'])){
    //Retriving feild values
    $display = !empty($_POST['display']) ? trim($_POST['display']) : null;
    $username = !empty($_POST['username']) ? trim($_POST['username']) : null;
    $password = !empty($_POST['password']) ? trim($_POST['password']) : null;
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;

    //Checking if the supplied username already exists
    //Preparing SQL statement
    $sql = "SELECT COUNT(username) AS num FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    //Fetch the row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    //Username already exists error
    if($row['num'] > 0){
        $error = 'That username already exists!';
    }

    //Hasing the password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 12));

    //Preparing insert statement
    $sql = "INSERT INTO users (username, userpass, email, display) VALUES (:username, :password, :email, :display)";
    $stmt = $pdo->prepare($sql);
    //Bind varibles
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $passwordHash);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':display', $display);

    //Execute the statement
    $result = $stmt->execute();

    //If signup was successful
    if($result) {
        $success =  "Thank you for signing up :D";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title>Register - Yello</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body class="login">
<div class="topbar"></div>
<div class="container">
    <div class="row logo-bar">
        <div class="col-md-1 col-sm-3 col-xs-3 col-3">
            <img src="img/yello-dark.gif" width="100%">
        </div>
    </div>
</div>
<div class="container d-flex align-items-center flex-column justify-content-center h-100 float-fix">
    <div class="row ">
        <div class="col-md-8">
            <div class="fat-bar"></div>
            <h1 class="big-title">Say Hello To Yello</h1>
            <div class="row">
                <div class="col-md-10">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus venenatis erat at ultrices
                        ultricies. Mauris sed libero hendrerit, condimentum metus at, porttitor mauris. Donec vehicula
                        pretium erat id ullamcorper.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 register-float">
            <div class="float-home-bar"></div>
            <div class="float-home">
                <h1 class="title">Register</h1>
                <p class="tiny-text">Already have an account? <a href="login.php">Login here</a></p>
                <form action="register.php" method="post">
                    <p class="error"><?=$error?></p>
                    <p class="success"><?=$success?></p>
                    <div class="form-group">
                        <input type="text"  id="email" name="email" placeholder="email address" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="text"  id="display" name="display" placeholder="display name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="text"  id="username" name="username" placeholder="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" placeholder="password" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0px">
                        <input type="submit" name="register" value="Register" class="btn btn-theme btn-block">
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