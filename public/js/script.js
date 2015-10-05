'use strict';

$(function() {
    $('.tablesorter').tablesorter();
    
    $('#get-players').click(function(event) {
        event.preventDefault();

        $.get(basepath + '/server/getData', function(data) {
            for (var i in data) {
                document.write(data[i].name);
            }
        });
    });

});
