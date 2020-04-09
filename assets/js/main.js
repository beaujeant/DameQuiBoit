$(function(){

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == document.getElementById("myModal")) {
            document.getElementById("myModal").remove();
        }
        if (event.target == document.getElementsByClassName("close")[0]) {
            document.getElementById("myModal").remove();
        }
    }

    /*
    $( "#create" ).click(function() {
        $( "#menu" ).replaceWith( '<form class="form-inline" action="create.php" method="post"><div class="form-group mb-2"><input type="text" class="form-control" name="name" placeholder="Nom" required></div><button type="submit" class="btn btn-primary mb-2">C\'est partie !</button></form>' );
    });

    $( "#join" ).click(function() {
        $( this ).replaceWith( "<div>" + $( this ).text() + "</div>" );
    });
    */

});