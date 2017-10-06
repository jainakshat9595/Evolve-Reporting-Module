<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();


//die();
    
$username = $_POST['username'];
$password = $_POST['password'];

if($username == "" || $password == "") {
    echo "Invalid Arguments";
    die();
}

if($username == "admin" && $password == "Reporting#2017_evolve") {

    $_SESSION["username"] = $username;
    $_SESSION["password"] = $password;
    // echo "Same: <br>";
    // print_r($_SESSION);
    header('Location: export-form.php');
} else {
    
    $_SESSION["username"] = '';
    $_SESSION["password"] = '';
    $_SESSION["message"] = "Invalid Username or Password";
    // echo "Wrong: <br>";
    // print_r($_SESSION);
    header('Location: index.php');
}


?>