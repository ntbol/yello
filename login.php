<?php
session_start();
require 'php/connect.php';
require 'lib/password.php';

$error = "";
if(isset($_POST['login'])){

    //Retrieve the field values from our login form.
    $username = !empty($_POST['username']) ? trim($_POST['username']) : null;
    $passwordAttempt = !empty($_POST['password']) ? trim($_POST['password']) : null;

    //Retrieve the user account information for the given username.
    $sql = "SELECT uid, username, userpass FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);

    //Bind value.
    $stmt->bindValue(':username', $username);

    //Execute.
    $stmt->execute();

    //Fetch row.
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //If $row is FALSE.
    if($user === false){
        //Could not find a user with that username!
        //PS: You might want to handle this error in a more user-friendly manner!
        $error = "User doesnt exists!";
    } else{
        //User account found. Check to see if the given password matches the
        //password hash that we stored in our users table.

        //Compare the passwords.
        $validPassword = password_verify($passwordAttempt, addslashes(htmlspecialchars($user['userpass'])));

        //If $validPassword is TRUE, the login has been successful.
        if($validPassword){

            //Provide the user with a login session.
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['logged_in'] = time();

            //Redirect to our protected page, which we called home.php
            header('Location: index.php');
            exit;

        } else{
            //$validPassword was FALSE. Passwords do not match.
            $error = "Passwords do not match!";
        }
    }

}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title>Login - Yello</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body class="login">
    <div class="topbar"></div>
    <div class="container">
        <div class="row logo-bar">
            <div class="col-lg-1 col-sm-2 col-xs-3 col-3">
                <img src="img/yello-dark.gif" width="100%">
            </div>
        </div>
    </div>
        <div class="container d-flex align-items-center flex-column justify-content-center h-100 float-fix">
            <div class="row">
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
                <div class="col-md-4 login-float">
                    <div class="float-home-bar"></div>
                    <div class="float-home">
                        <h1 class="title">Login</h1>
                        <p class="tiny-text">Don't have an account? <a href="register.php">Register here</a></p>
                        <form action="login.php" method="post">
                            <p class="error"><?=$error?></p>
                            <div class="form-group">
                                <input type="text" name="username" id="username" placeholder="username" placeholder="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <input type="password" id="password" name="password" class="form-control" placeholder="password" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0px">
                                <input type="submit" name="login" value="Login" class="btn btn-theme btn-block">
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