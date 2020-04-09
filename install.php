<?php

include('./config.php');

// Create connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n". $conn -> error);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// sql to create games
$sql = "CREATE TABLE IF NOT EXISTS games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(255) NOT NULL,
    thumb_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    current_player INT,
    direction INT,
    last_update INT
)  ENGINE=INNODB;";

if ($conn->query($sql) === TRUE) {
    echo "Table games created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}


// sql to create decks
$sql = "CREATE TABLE IF NOT EXISTS decks (
    room_id VARCHAR(255) NOT NULL,
    position INT,
    strength VARCHAR(255),
    suit VARCHAR(255),
    sips INT,
    description VARCHAR(255),
    rule TEXT,
    visible INT
)  ENGINE=INNODB;";

if ($conn->query($sql) === TRUE) {
    echo "Table decks created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}



// sql to create users
$sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    username VARCHAR(255),
    password VARCHAR(255),
    current_room_id INT
)  ENGINE=INNODB;";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}


// sql to create players
$sql = "CREATE TABLE IF NOT EXISTS players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    room_id VARCHAR(255),
    name VARCHAR(255),
    role INT,
    thumb INT,
    position INT,
    current_sips INT,
    total_sips INT
)  ENGINE=INNODB;";

if ($conn->query($sql) === TRUE) {
    echo "Table players created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}



// sql to create updates
$sql = "CREATE TABLE IF NOT EXISTS updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255)
)  ENGINE=INNODB;";

if ($conn->query($sql) === TRUE) {
    echo "Table updates created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 


