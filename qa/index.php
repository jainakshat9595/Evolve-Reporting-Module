<?php 
    session_start();
    if(isset($_SESSION)) {
        if(isset($_SESSION["username"])&&isset($_SESSION["password"])&&$_SESSION["username"]!=""&&$_SESSION["password"]!="") {
            header('Location: export-form.php');
        }
    }
?>
<!DOCTYPE html>
<html>
  <head>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    <style>
      body {
        text-align: center;
        min-height: 800px;
      }
      .banner_image {
        margin: auto;
        width: 100%;
        height: auto;
      }
      .main-form {
        text-align: center;
      }
      .navbar {
        min-height: 95px;
      }
      .navbar-brand img {
        width: 74%;
      }
      .form_datetime {
        border-color: green;
        padding: 0.5% 1%;
      }
      .btn-default {
        color: white;
        background-color: #015601;
        padding: 6px 25px;
      }
      .main-cont {
        margin-top: 3%;
        padding: 1% 25%;
      }
      input {
        cursor: pointer;
      }
    </style>
  </head>
  <body>

    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">
            <img alt="Brand" src="ev.png">
          </a>
        </div>
        </div>
      </div>
    </nav>

    <img src="whyevolve.jpg" alt="" class=" img-responsive banner_image" />

    <h2> Enter Your Login Credentials </h2>
    
    <form action="export-form.php" method="POST" class="main-form">
      <div class="container-fluid main-cont">
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label for="username">Username</label>
              <input size="16" type="text" class="form_datetime control" required name="username" id="username">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="password">Password</label>
              <input size="16" type="password" class="form_datetime control" required name="password" id="password">
            </div>
          </div>
          <div class="col-sm-4">
            <button type="submit" class="btn btn-default">Login</button>
          </div>
        </div>
      </div>
      <h4><?php if(isset($_SESSION['message']) && $_SESSION['message']!="") { echo $_SESSION['message']; session_destroy(); } ?></h4>
    </form>

    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
