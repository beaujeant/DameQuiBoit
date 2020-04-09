$(function(){

    // Init
    $( "#players" ).load( "api.php?action=players" );
    $( ".playingCards .table" ).load( "api.php?action=deck" );
    $( "#stats" ).load( "api.php?action=stats" );


});




