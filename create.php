<?php

    include('./config.php');
    include("./card_deck.php");
    
    // Start the session
    session_start();

    if( !isset($_SESSION['user_id']))
    {
        $_SESSION['message'] = 'No user_id';
        header('Location: ' . $ROOT_DIR . '/');
    }

    $alphabet = "0123456789";
    for ($i = 0; $i < 4; $i++) {
        $n = rand(0, strlen($alphabet)-1);
        $room[$i] = $alphabet[$n];
    }

    $room_id = implode($room);


    try {
        $conn = new PDO("mysql:host=$dbhost;dbname=$db;charset=utf8", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get name
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->fetch();
    
        // Create player
        $stmt = $conn->prepare("INSERT INTO players (user_id, room_id, name, role, thumb, position, current_sips, total_sips) VALUES (:user_id, :room_id, :name, 1, 0, 0, 0, 0)");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':name', $user['name']);
        $stmt->execute();
        $player_id = $conn->lastInsertId();

        echo "New player created successfully\n";

        $_SESSION['player_id'] = $player_id;
        $_SESSION['room_id'] = $room_id;


        // Create deck

        $strength_array = array("2","3","4","5","6","7","8","9","10","J","Q","K","A");
        $suit_array = array("hearts", "diams", "clubs", "spades");
        $deck = new card_deck();
        $id = $deck->add_type("strength", $strength_array);
        $deck->add_type("suit", $suit_array, 1, $id);
        $deck->shuffle();
        $arr = $deck->deal(52);

        $stmt = $conn->prepare("INSERT INTO decks (room_id, position, strength, suit, sips, description, visible) VALUES (:room_id, :position, :strength, :suit, :sips, :description, 0)");
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':strength', $strength);
        $stmt->bindParam(':suit', $suit);
        $stmt->bindParam(':sips', $sips);
        $stmt->bindParam(':description', $description);

        foreach($arr as $position => $card)
        {
            $strength = $card['strength'];
            $suit = $card['suit'];

            switch( $strength )
            {
                case 'A':
                    $sips = 5;
                    $description = "Tout le monde affone son verre.";
                    break;
                case '2':
                    $sips = 2;
                    $description = "Le joueur boit 2 gorgées de son verre.";
                    break;
                case '3':
                    $sips = 3;
                    $description = "Le joueur boit 3 gorgées de son verre.";
                    break;
                case '4':
                    $sips = 4;
                    $description = "Le joueur boit 4 gorgées de son verre.";
                    break;
                case '5':
                    $sips = 5;
                    $description = "Le joueur affone son verre.";
                    break;
                case '6':
                    $description = "Changement de sens.";
                    break;
                case '7':
                    $description = "Le joueur rejoue.";
                    break;
                case '8':
                    $description = "Le joueur reçoit un gage.";
                    break;
                case '9':
                    $sips = 5;
                    $description = "Le joueur à droite affone son verre.";
                    break;
                case '10':
                    $sips = 5;
                    $description = "Le joueur à gauche affone son verre.";
                    break;
                case 'J':
                    $description = "Le joueur devient le serveur de la soirée (ressert à boire aux autres joueurs) jusqu'à ce qu'un autre joueur pioche un valet.";
                    break;
                case 'Q':
                    $description = "Le joueur devient la reine des pouces. Dès qu'il le souhaite, il pose son pouce sur la table (&#128077;). Le dernier joueur à le faire affone son verre. Le pouvoir passe au prochain joueur piochant une reine.";
                    break;
                case 'K':
                    $description = "Le joueur devient le roi, il invente une règle.";
                    break;
                default:
                    $sips = 0;
                    $description = "";
                    break;
            }

            $stmt->execute();
        }
        

        // prepare sql and bind parameters
        $stmt = $conn->prepare("INSERT INTO games (room_id, current_player, last_update, direction) VALUES (:room_id, :current_player, 0, 1)");
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':current_player', $_SESSION['player_id']);
        $stmt->execute();

        echo "New game created successfully";

        header('Location: ' . $ROOT_DIR . '/room.php');
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
        //header('Location: ' . $ROOT_DIR . '/');
    }



    


?>