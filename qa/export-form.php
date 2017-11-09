<?php 
  session_start();
  /*$_SESSION['test']="safsf";
  print_r($_SESSION);
  if(!isset($_SESSION['username'])||!isset($_SESSION['password'])||$_SESSION['username']==""||$_SESSION['password']=="") {
    session_destroy();
    echo "redirecting";*/
    //header('Location: index.php');
  //} 
  //print_r($_POST);
  $username = $_POST['username'];
$password = $_POST['password'];

if($username == "" || $password == "") {
    echo "Invalid Arguments";
    die();
}

if($username == "admin" && $password == "Evolve@123#") {

    $_SESSION["username"] = $username;
    $_SESSION["password"] = $password;
    // echo "Same: <br>";
    // print_r($_SESSION);
    //header('Location: export-form.php');
} else {
    
    $_SESSION["username"] = '';
    $_SESSION["password"] = '';
    $_SESSION["message"] = "Invalid Username or Password";
    // echo "Wrong: <br>";
    // print_r($_SESSION);
    header('Location: index.php');
}
  ?>
<!DOCTYPE html>
<html>
  <head>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
    
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
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <img src="whyevolve.jpg" alt="" class=" img-responsive banner_image" />

    <h2> Export Order Sheet </h2>
    
    <form action="export.php" method="POST" class="main-form">
      <div class="container-fluid main-cont">
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label for="start_date">Start Date</label>
              <input readonly size="16" type="text" data-date="" data-link-format="yyyy-mm-dd hh:ii" class="form_datetime control" required name="start_date" id="start_date">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="end_date">End Date</label>
              <input readonly   size="16" type="text" data-date="" data-link-format="yyyy-mm-dd hh:ii" class="form_datetime control" required name="end_date" id="end_date">
            </div>
          </div>
          <div class="col-sm-4">
            <button type="submit" class="btn btn-default">Export</button>
          </div>
        </div>
      </div>  
    </form>

    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script type="text/javascript" src="bootstrap-datetimepicker.js" charset="UTF-8"></script>
    <script type="text/javascript">
      $('.form_datetime').datetimepicker({
        weekStart: 1,
        todayBtn:  1,
		    autoclose: 1,
		    todayHighlight: 1,
		    format: 'yyyy-mm-dd hh:ii'
    });
    </script> 
    <script>
      var isOpen_1 = false;
      $('#start_date').click(function() {
        if(isOpen_1) {
          $('#start_date').blur();
        }
        isOpen_1 = !isOpen_1;
      });

      var isOpen_2 = false;
      $('#end_date').click(function() {
        if(isOpen_2) {
          $('#end_date').blur();
        }
        isOpen_2 = !isOpen_2;
      });
    </script>
  </body>
</html>
