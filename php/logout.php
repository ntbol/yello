<?php
session_start();

//Breaks current session
session_destroy();

//Returns user to login page
header('Location: ../login.php');

?>