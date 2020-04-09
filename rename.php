<?php
/*************************************************************
 * Here is an example of dealing cards for Texas holdem poker
 * for 4 people plus flop, turn and river
 * More information about the game:
 * http://en.wikipedia.org/wiki/Texas_hold_'em
 *************************************************************/
 //car strength
$strength = array("2","3","4","5","6","7","8","9","10","J","Q","K","A");
//suit of cards
$suit = array("Carreaux","Treffle","Coeur","Pique");

//inlcude class and create card deck
include("./card_deck.php");
$deck = new card_deck();

//add type with strength property and values from array
//and get id of type
$id = $deck->add_type("strength", $strength);

//add suit property to same type by providing id
$deck->add_type("suit", $suit, 1, $id);

//shuffle cards
$deck->shuffle();


//deal cards for 4 people, 2 cards for each
echo "<p>Player 1: ";
$arr = $deck->deal(2);
foreach($arr as $position => $card)
{
    echo $card['strength'];
    echo $card["suit"];
    //$arr[$key] = implode("", $val);
}
//echo implode(" ", $arr);
echo "</p>";

echo "<p>Player 2: ";
$arr = $deck->deal(2);
foreach($arr as $key => $val)
{
    $arr[$key] = implode("", $val);
}
echo implode(" ", $arr);
echo "</p>";

echo "<p>Player 3: ";
$arr = $deck->deal(2);
foreach($arr as $key => $val)
{
    $arr[$key] = implode("", $val);
}
echo implode(" ", $arr);
echo "</p>";

echo "<p>Player 4: ";
$arr = $deck->deal(2);
foreach($arr as $key => $val)
{
    $arr[$key] = implode("", $val);
}
echo implode(" ", $arr);
echo "<p>";

//deal flop, turn and river
echo "<p>Flop: ";
$arr = $deck->deal(3);
foreach($arr as $key => $val)
{
    $arr[$key] = implode("", $val);
}
echo implode(" ", $arr);
echo "<p>";

echo "<p>Turn: ";
$arr = $deck->deal(1);
echo implode("", current($arr));
echo "</p>";

echo "<p>River: ";
$arr = $deck->deal(1);
echo implode("", current($arr));
echo "</p>";

?>