<?php

    include('./config.php');
    // Start the session
    session_start();
    
    $msg = '';

    if( isset($_POST['username']) && !empty($_POST['username']) &&
        isset($_POST['password']) && !empty($_POST['password']) )
    {

        $username = htmlentities($_POST['username']);
        $password = $_POST['password'];

        try {
            $conn = new PDO("mysql:host=$dbhost;dbname=$db;charset=utf8", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // REGISTER
            if( isset($_POST['name']) && !empty($_POST['name']) )
            {
                
                $name = htmlentities($_POST['name']);
                
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $same_username = $stmt->rowCount();

                if( $same_username > 0 )
                {
                    $msg = '<div class="alert alert-danger" role="alert">Username déjà pris !</div>';
                }
                else
                {
                    $options = array("cost"=>4);
                    $hashPassword = password_hash($password,PASSWORD_BCRYPT,$options);
                    // Create player
                    $stmt = $conn->prepare("INSERT INTO users (name, username, password) VALUES (:name, :username, :password)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $hashPassword);
                    $stmt->execute();
                    $user_id = $conn->lastInsertId();

                    $_SESSION['user_id'] = $user_id;
                }
            }
            // LOGIN
            else
            {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $same_username = $stmt->rowCount();
                $user = $stmt->fetch();

                if( $same_username == 1 && password_verify($password, $user['password']))
                {
                    $_SESSION['user_id'] = $user['user_id'];
                }
                else
                {
                    $msg = '<div class="alert alert-danger" role="alert">Mauvais mot de passe !</div>';
                }
            }

        
        }
        catch(PDOException $e)
        {
            echo "Error: " . $e->getMessage();
            //header('Location: ' . $ROOT_DIR . '/');
        }
    }

?><!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>La Sketting</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link href="assets/css/dqb.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
    <script src="assets/js/main.js"></script>
  </head>
  <body>
    
    
  <div class="site-wrapper">

    <div class="site-wrapper-inner">

      <div class="cover-container">

        <div class="inner cover">
          <h1 class="cover-heading">Bienvenue dans la sketting !</h1>

          <?php
          echo $msg;
          if( isset($_SESSION['user_id']) )
          {
          ?>
          
          <div id="menu">
          <p class="lead">
            <button id="create" class="btn btn-lg btn-default">Nouvelle partie</button>
          </p>
          <p class="lead">
            <button id="join" class="btn btn-lg btn-default">Rejoindre une partie</button>
          </p>
          <p class="lead">
            <button id="last" class="btn btn-lg btn-default">Rejoindre derniere partie</button>
          </p>
          <p class="lead">
            <button id="logout" class="btn btn-lg btn-default">Se déconnecter</button>
          </p>
          </div>

          <?php
          }
          else
          {
          ?>
          
          <div id="menu">
          <form class="form" action="index.php" method="POST">
              <div class="form-group mb-2">
                  <input type="text" class="form-control" name="username" placeholder="Username" required>
              </div>
              <div class="form-group mb-2">
                  <input type="password" class="form-control" name="password" placeholder="password" required>
              </div>
              <button type="submit" class="btn btn-primary mb-2">Se connecter</button>
            </form>
              <br/>
              <a id="register" href="#" class="btn btn-primary">S'enregistrer</a>
          </div>

          <?php
          }
          ?>
          
        </div>

        <div class="mastfoot">
          <div class="inner">
            <p>Made in <a href="#">confinement</a>.</p>
            <p><?php if( isset($_SESSION['message']) ) echo $_SESSION['message']; ?></p>
          </div>
        </div>

      </div>

    </div>

  </div>

  <script>
    $(function(){

    $( "#create" ).click(function() {
      window.location.href = "create.php";
    });

    $( "#join" ).click(function() {
      $( "#menu" ).replaceWith( '<form class="form" action="room.php" method="post"><div class="form-group mb-2"><input type="text" class="form-control" name="id" placeholder="Code" required></div><button type="submit" class="btn btn-primary mb-2">Rejoindre !</button></form>' );
    });

    $( "#register").click(function() {
      $( "#menu" ).replaceWith( '<form class="form" action="index.php" method="post"><div class="form-group mb-2"><input type="text" class="form-control" name="name" placeholder="Nom" required></div><div class="form-group mb-2"><input type="text" class="form-control" name="username" placeholder="Username" required></div><div class="form-group mb-2"><input type="password" class="form-control" name="password" placeholder="password" required></div><button type="submit" class="btn btn-primary mb-2">S\'enregistrer</button></form>' );
    });

    $( "#logout").click(function() {
        window.location.href = "logout.php";
    });

    <?php if( isset($_SESSION['room_id']) && isset($_SESSION['player_id']) ) { ?>
    $( "#last" ).click(function() {
      window.location.href = "room.php";
    });
  <?php } ?>

    });
  </script>

  </body>
</html>