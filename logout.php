<?php
    session_start();

    $_SESSION["username"] = "";
    $_SESSION["password"] = "";
    $_SESSION["message"] = "";
    if(isset($_SESSION)) {
        session_destroy();
        
    }
    header('Location: index.php');

?>