<?php

    include('./config.php');
    
    // Start the session
    session_start();





    if( isset($_REQUEST['action']) )
    {
        $action = $_REQUEST['action'];
    }
    else
    {
        die("No action");
    }


    // Function createModal
    function createModal( $content )
    {
        return '<div id="myModal" class="modal"><div class="modal-content"><span class="close">&times;</span>' . $content . '</div></div>';
    }


    // Function nextPlayer
    function nextPlayer( $conn, $position, $direction, $amount_player )
    {
        $position = $position + $direction;
        if( $position < 0 ) $position = $amount_player - 1;
        if( $position >= $amount_player ) $position = 0;

        $get_next_user = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id AND position = :position");
        $get_next_user->bindParam(':room_id', $_SESSION['room_id']);
        $get_next_user->bindParam(':position', $position);
        $get_next_user->execute();
        $next_player = $get_next_user->fetch();
        $next_player_id = $next_player['player_id'];

        $update_current_user = $conn->prepare("UPDATE games SET current_player = :player_id WHERE room_id = :room_id");
        $update_current_user->bindParam(':room_id', $_SESSION['room_id']);
        $update_current_user->bindParam(':player_id', $next_player_id);
        $update_current_user->execute();
    }


    $conn = new PDO("mysql:host=$dbhost;dbname=$db;charset=utf8", $dbuser, $dbpass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



    if( $action == "players")
    {
        // Games
        $stmt = $conn->prepare("SELECT * FROM games WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $game = $stmt->fetch();
        $current_player = $game['current_player'];

        // Players
        $players_div = "";
        $stmt = $conn->prepare("SELECT role FROM players WHERE room_id = :room_id AND user_id = :user_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $me = $stmt->fetch();
        $role = $me['role'];

        // Players
        $players_div = "";
        $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id ORDER BY position");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $players = $stmt->fetchAll();
        foreach( $players as $player )
        {
            $thumb = ( $player['thumb'] == 1 ) ? '👍' : '';
            $delete = ( $role == 1 ) ? ' <span style="font-size:8" onclick="deletePlayer('. $player['player_id'] .')">(x)</span>' : '';
            $current = ( $player['player_id'] == $current_player ) ? ' current' : '';
            $players_div .= '<div class="player' . $current . '">';
            $players_div .=  '<p><b>' . $player['name'] . $thumb . '</b>' . $delete . '</p>';
            $players_div .=  '<p style="font-size:10pt">Gorgés: ' . $player['current_sips'] . '</p>';
            $players_div .=  '<p style="font-size:10pt">Total: ' . $player['total_sips'] . '</p>';
            $players_div .=  '</div>';
        }

        echo $players_div . '<div class="player add" onclick="getCode()"><p>+</p></div>';
    }
    elseif( $action == "delete" && isset($_REQUEST['id']) )
    {
        $player_id = intval( $_REQUEST['id'] );
        
        $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = :player_id AND room_id = :room_id");
        $stmt->bindParam(':player_id', $player_id);
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $found = $stmt->rowCount();

        if( $found == 1)
        {
            $to_delete = $stmt->fetch();
            $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->execute();
            $amount_players = $stmt->rowCount();
            $players = $stmt->fetchAll();

            $behind = $amount_players - $to_delete['position'] - 1;

            for ($i = 0; $i < $behind; $i++)
            {
                $current_position = $to_delete['position'] + $i;
                $new_position = $to_delete['position'] + $i +1;
                $stmt = $conn->prepare("UPDATE players SET position = :new_position WHERE room_id = :room_id AND position = :current_position");
                $stmt->bindParam(':room_id', $_SESSION['room_id']);
                $stmt->bindParam(':new_position', $current_position);
                $stmt->bindParam(':current_position', $new_position);
                $stmt->execute();
            } 

            $stmt = $conn->prepare("DELETE FROM players WHERE player_id = :player_id");
            $stmt->bindParam(':player_id', $player_id);
            $stmt->execute();
        }
    }
    elseif( $action == "deck" )
    {
        // Get current player
        $stmt = $conn->prepare("SELECT * FROM games WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $game = $stmt->fetch();
        $current_player = $game['current_player'];
        
        $deck_div = "";

        // Get decks
        $stmt = $conn->prepare("SELECT * FROM decks WHERE room_id = :room_id ORDER BY position");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $deck = $stmt->fetchAll();
        foreach( $deck as $card)
        {
            if( $card['visible'] == 0 )
            {
                if( $_SESSION['player_id'] == $current_player )
                {
                    $deck_div .= '<li onclick="getCard(this)"><div class="card back active">*</div></li>';
                }
                else
                {
                    $deck_div .= '<li><div class="card back">*</div></li>';
                }
                
            }
            else
            {
                $deck_div .= '<li><div class="card rank-' . strtolower($card['strength']) . ' ' . $card['suit'] . '"><span class="rank">' . $card['strength'] . '</span><span class="suit">&' . $card['suit'] . ';</span></div></li>';
            }
        }

        echo $deck_div;
    }
    elseif( $action == "stats" )
    {
        $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = :player_id");
        $stmt->bindParam(':player_id', $_SESSION['player_id']);
        $stmt->execute();
        $player = $stmt->fetch();
        echo "<img src=\"assets/images/beer-glass.png\" alt=\"beer\" height=\"40\" width=\"40\">";
        echo "<span style=\"margin:5pt\">". $player['current_sips'] . " (". $player['total_sips'] . ")</span>";
    }
    elseif( $action == "purge" )
    {
        $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = :player_id");
        $stmt->bindParam(':player_id', $_SESSION['player_id']);
        $stmt->execute();
        $player = $stmt->fetch();
        $current_sips = $player['current_sips'];
        $total_sips = $player['total_sips'];
        $sips = $current_sips + $total_sips;

        $stmt = $conn->prepare("UPDATE players SET current_sips = 0, total_sips = :sips WHERE player_id = :player_id");
        $stmt->bindParam(':player_id', $_SESSION['player_id']);
        $stmt->bindParam(':sips', $sips);
        $stmt->execute();
    }
    elseif( $action == "thumb" )
    {
        $stmt = $conn->prepare("SELECT * FROM games WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $game = $stmt->fetch();
        $thumb_id = $game['thumb_id'];

        $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = :player_id AND thumb = 1");
        $stmt->bindParam(':player_id', $thumb_id);
        $stmt->execute();
        $player = $stmt->fetch();

        if( ($thumb_id == $_SESSION['player_id']) || ($stmt->rowCount() > 0) )
        {
            $stmt = $conn->prepare("UPDATE players SET thumb = 1 WHERE player_id = :player_id");
            $stmt->bindParam(':player_id', $_SESSION['player_id']);
            $stmt->execute();

            $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->execute();
            $amount_player = $stmt->rowCount();

            $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id AND thumb = 1");
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->execute();
            $amount_thumb = $stmt->rowCount();

            if( $amount_thumb == $amount_player - 1)
            {
                $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id AND thumb = 0");
                $stmt->bindParam(':room_id', $_SESSION['room_id']);
                $stmt->execute();
                $player = $stmt->fetch();
                $sips = 5 + $player['current_sips'];
                $player_id = $player['player_id'];

                $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                $stmt->bindParam(':player_id', $player_id);
                $stmt->bindParam(':sips', $sips);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE players SET thumb = 0 WHERE room_id = :room_id");
                $stmt->bindParam(':room_id', $_SESSION['room_id']);
                $stmt->execute();
            }
        } 
        else
        {
            $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = :player_id");
            $stmt->bindParam(':player_id', $_SESSION['player_id']);
            $stmt->execute();
            $player = $stmt->fetch();

            $sips = 5 + $player['current_sips'];
            $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
            $stmt->bindParam(':player_id', $_SESSION['player_id']);
            $stmt->bindParam(':sips', $sips);
            $stmt->execute();
            die(createModal("Mais d'où ? La dame n'a toujours pas mis son pouce. Allez, t'affones !"));
        }  
    }
    elseif( $action == "ping" )
    {
        $stmt = $conn->prepare("SELECT * FROM games WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $game = $stmt->fetch();
        $last_update = $game['last_update'];

        if( $last_update != $_SESSION['update'])
        {
            $stmt = $conn->prepare("SELECT * FROM updates WHERE update_id = :update_id");
            $stmt->bindParam(':update_id', $last_update);
            $stmt->execute();
            $update = $stmt->fetch();
            $message = $update['message'];

            $obj['modal'] = createModal($message);
            echo json_encode($obj);

            $_SESSION['update'] = $last_update;
        }
        else
        {
            echo '{}';
        }

    }
    elseif( $action == "pick" && isset($_REQUEST['position']) )
    {
        // CHECK IF CURRENT USER
        $stmt = $conn->prepare("SELECT * FROM games WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $game = $stmt->fetch();
        $current_player = $game['current_player'];
        $direction = $game['direction'];

        $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->execute();
        $amount_player = $stmt->rowCount();

        if( $current_player != $_SESSION['player_id'] )
        {
            die(createModal("Error: Not your turn!"));
        }

        $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = :player_id");
        $stmt->bindParam(':player_id', $_SESSION['player_id']);
        $stmt->execute();
        $player = $stmt->fetch();
        $name = $player['name'];
        $position = $player['position'];
        $current_sips = $player['current_sips'];

        $index = intval($_REQUEST['position']);

        if( $current_sips > 0 )
        {
            die(createModal("Hey la, hey la, on se calme, termines ton verre avant !"));
        }

        // Players
        $modal = "";
        $stmt = $conn->prepare("SELECT * FROM decks WHERE room_id = :room_id AND position = :position");
        $stmt->bindParam(':room_id', $_SESSION['room_id']);
        $stmt->bindParam(':position', $index);
        $stmt->execute();
        $card = $stmt->fetch();

        // CHECK IF NOT VISIBLE
        if( $card['visible'] == 0 )
        {

            $strength = $card['strength'];
            $suit = $card['suit'];
            echo createModal($card['description']);

            $message = $name . ' a pioché ' . $strength.' &'.$suit. ';: ' . $card['description'];
            $stmt = $conn->prepare("INSERT INTO updates (message) VALUES (:message)");
            $stmt->bindParam(':message', $message);
            $stmt->execute();
            $update_id = $conn->lastInsertId();

            $stmt = $conn->prepare("UPDATE games SET last_update = :update_id WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->bindParam(':update_id', $update_id);
            $stmt->execute();

            $_SESSION['update'] = $update_id;

            switch( $strength )
            {
                case 'A':
                    $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id");
                    $stmt->bindParam(':room_id', $_SESSION['room_id']);
                    $stmt->execute();
                    $players_tmp = $stmt->fetchAll();
                    foreach( $players_tmp as $player_tmp )
                    {
                        $sips = 5 + $player_tmp['current_sips'];
                        $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                        $stmt->bindParam(':player_id', $player_tmp['player_id']);
                        $stmt->bindParam(':sips', $sips);
                        $stmt->execute();
                    }

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '2':
                    $sips = 2 + $current_sips;
                    $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                    $stmt->bindParam(':player_id', $_SESSION['player_id']);
                    $stmt->bindParam(':sips', $sips);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '3':
                    $sips = 3 + $current_sips;
                    $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                    $stmt->bindParam(':player_id', $_SESSION['player_id']);
                    $stmt->bindParam(':sips', $sips);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '4':
                    $sips = 4 + $current_sips;
                    $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                    $stmt->bindParam(':player_id', $_SESSION['player_id']);
                    $stmt->bindParam(':sips', $sips);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '5':
                    $sips = 5 + $current_sips;
                    $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                    $stmt->bindParam(':player_id', $_SESSION['player_id']);
                    $stmt->bindParam(':sips', $sips);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '6':
                    $direction = -$direction;
                    $stmt = $conn->prepare("UPDATE games SET direction = :direction WHERE room_id = :room_id");
                    $stmt->bindParam(':room_id', $_SESSION['room_id']);
                    $stmt->bindParam(':direction', $direction);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '7':
                    break;
                case '8':
                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '9':
                    // Get next player
                    $sips_position = $position + 1;
                    if( $sips_position < 0 ) $sips_position = $amount_player - 1;
                    if( $sips_position >= $amount_player ) $sips_position = 0;
                    
                    $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id AND position = :position");
                    $stmt->bindParam(':room_id', $_SESSION['room_id']);
                    $stmt->bindParam(':position', $sips_position);
                    $stmt->execute();
                    $next_player = $stmt->fetch();
                    $next_player_id = $next_player['player_id'];
                    $current_sips = $next_player['current_sips'];

                    $sips = 5 + $current_sips;

                    $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                    $stmt->bindParam(':player_id', $next_player_id);
                    $stmt->bindParam(':sips', $sips);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case '10':
                    // Get previous player
                    $sips_position = $position - 1;
                    if( $sips_position < 0 ) $sips_position = $amount_player - 1;
                    if( $sips_position >= $amount_player ) $sips_position = 0;

                    $stmt = $conn->prepare("SELECT * FROM players WHERE room_id = :room_id AND position = :position");
                    $stmt->bindParam(':room_id', $_SESSION['room_id']);
                    $stmt->bindParam(':position', $sips_position);
                    $stmt->execute();
                    $previous_player = $stmt->fetch();
                    $previous_player_id = $previous_player['player_id'];
                    $current_sips = $previous_player['current_sips'];

                    $sips = 5 + $current_sips;

                    $stmt = $conn->prepare("UPDATE players SET current_sips = :sips WHERE player_id = :player_id");
                    $stmt->bindParam(':player_id', $previous_player_id);
                    $stmt->bindParam(':sips', $sips);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case 'J':
                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case 'Q':
                    $stmt = $conn->prepare("SELECT player_id FROM players WHERE room_id = :room_id AND thumb = 1");
                    $stmt->bindParam(':room_id', $_SESSION['room_id']);
                    $stmt->execute();
                    $thumb_player = $stmt->fetch(); 

                    $stmt = $conn->prepare("UPDATE games SET thumb_id = :player_id WHERE room_id = :room_id");
                    $stmt->bindParam(':room_id', $_SESSION['room_id']);
                    $stmt->bindParam(':player_id', $_SESSION['player_id']);
                    $stmt->execute();

                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                case 'K':
                    // Update next player
                    nextPlayer( $conn, $position, $direction, $amount_player );
                    break;
                default:
                    $sips = 0;
                    $description = "";
                    break;
            }

            // Flip the card
            $stmt = $conn->prepare("UPDATE decks SET visible = 1 WHERE room_id = :room_id AND position = :position");
            $stmt->bindParam(':room_id', $_SESSION['room_id']);
            $stmt->bindParam(':position', $index);
            $stmt->execute();

        }
        else
        {
            die(createModal("Error: Card already flipped!"));
        }

    }

?>