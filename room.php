<?php

    include('./config.php');
    
    // Start the session
    session_start();

    try {
        $conn = new PDO("mysql:host=$dbhost;dbname=$db;charset=utf8", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if( isset($_REQUEST ['id']) )
        {
            $_SESSION['room_id'] = trim($_REQUEST ['id']);
        }
        
        if( !isset( $_SESSION['room_id'] ) || !isset( $_SESSION['user_id'] ) )
        {
            $_SESSION['message'] = "You need to authenticate first!";
            header('Location: ' . $ROOT_DIR . '/' );
            die("Redirection...");
        }

        // Check if room exist
        // Get last update id
        $stmt = $conn->prepare("SELECT last_update FROM games WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        
        if( $stmt->rowCount() == 0 )
        {
            $_SESSION['room_id'] = NULL;
            $_SESSION['message'] = "Room number invalid!";
            header('Location: ' . $ROOT_DIR . '/' );
            die("Redirection...");
        }

        $game = $stmt->fetch();
        $_SESSION['update'] = $game['last_update'];
        

        // Get amount player
        $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $amount_player = $stmt->rowCount();


        // Update user
        $stmt = $conn->prepare("UPDATE users SET current_room_id = :room_id WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();


        // Update player
        // Update session player_id
        $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id AND user_id = :user_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $player_exist = $stmt->rowCount();

        if( $player_exist == 0 )
        {
            // Get name
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch();

            $stmt = $conn->prepare("INSERT INTO players (user_id, room_id, role, name, thumb, position, current_sips, total_sips) VALUES (:user_id, :room_id, 0, :name, 0, :position, 0, 0)");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->bindParam(':name', $user['name']);
            $stmt->bindParam(':position', $amount_player);
            $stmt->execute();
            $_SESSION['player_id'] = $conn->lastInsertId();


            // Announce new player
            $message = $user['name'] . ' a rejoint la partie';
            $stmt = $conn->prepare("INSERT INTO updates (message) VALUES (:message)");
            $stmt->bindParam(':message', $message);
            $stmt->execute();
            $update_id = $conn->lastInsertId();

            $stmt = $conn->prepare("UPDATE games SET last_update = :update_id WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->bindParam(':update_id', $update_id);
            $stmt->execute();

        }
        else
        {
            $my_player = $stmt->fetch();
            $_SESSION['player_id'] = $my_player['player_id'];
        }
        
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
        //header('Location: ' . $ROOT_DIR . '/');
    }

?><!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>La Sketting - DQB</title>

        <link href="assets/css/dqb.css" rel="stylesheet">

        <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
        <script src="assets/js/main.js"></script>
        <script src="assets/js/game.js"></script>
    </head>
    <body>

        <div id="players" class="noselect">
        </div>

        <div class="playingCards fourColours rotateHand noselect">
            <ul class="table">
            </ul>
        </div>

        <div style="bottom:0;heigth: 100px;"></div>

        <div id="userMenu" class="noselect">
            <div id="stats" style="font-size:10pt">
            </div> 
            <div id="purge" onclick="purge()" style="font-size:25pt; color: green;">
                &#x2713;
            </div>
            <div id="thumb" onclick="thumb()" style="font-size:25pt">
            👍
            </div>
            <div id="logout" onclick="logout()">
                <img src="assets/images/home.png" alt="home" height="35" width="35">
            </div>

        </div>

        <script>

            function createModal( content )
            {
                $("#modalContainer").append( '<div id="myModal" class="modal"><div class="modal-content"><span class="close">&times;</span>' + content + '</div></div>' );
                document.getElementById("myModal").style.display = "block";
            }

            function getCode()
            {
                createModal("<p>Code</p><pre><?php echo htmlentities($_SESSION['room_id']); ?></pre><p>Link</p><pre>http://sketting.be/room.php?id=<?php echo urlencode($_SESSION['room_id']); ?></pre>");
            }

            function getCard( element )
            {
                $("#modalContainer").load( "api.php?action=pick&position=" + $( "li" ).index( element ) );
                $( "#players" ).load( "api.php?action=players" );
                $( ".playingCards .table" ).load( "api.php?action=deck" );
                $( "#stats" ).load( "api.php?action=stats" );
            }

            function deletePlayer( player_id ) 
            {
                $("#modalContainer").load( "api.php?action=delete&id=" + player_id );
                $( "#players" ).load( "api.php?action=players" );
                $( ".playingCards .table" ).load( "api.php?action=deck" );
                $( "#stats" ).load( "api.php?action=stats" );
            }

            function purge() 
            {
                $("#modalContainer").load( "api.php?action=purge" );
                $( "#players" ).load( "api.php?action=players" );
                $( ".playingCards .table" ).load( "api.php?action=deck" );
                $( "#stats" ).load( "api.php?action=stats" );
            }

            function thumb() 
            {
                $("#modalContainer").load( "api.php?action=thumb" );
                $( "#players" ).load( "api.php?action=players" );
                $( ".playingCards .table" ).load( "api.php?action=deck" );
                $( "#stats" ).load( "api.php?action=stats" );
            }

            function logout() 
            {
                window.location.href = "index.php";
            }



            (function worker() {
                $.get('api.php?action=ping', function(data) {
                    console.log(data);
                    try
                    {
                        var obj = JSON.parse(data); 
                        if( typeof obj['modal'] !== 'undefined')
                        {
                            $("#modalContainer").html(obj['modal']);
                            $( "#players" ).load( "api.php?action=players" );
                            $( ".playingCards .table" ).load( "api.php?action=deck" );
                            $( "#stats" ).load( "api.php?action=stats" );
                        }
                    }
                    catch(err)
                    {
                        console.log(err);
                    }
                    setTimeout(worker, 5000);
                });
            })();

        </script>

        <div id="modalContainer">
        </div>
    </body>
</html>