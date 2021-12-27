'use strict'
function mostrarvideo(nombrevideo, titulovideo){
    $("#titulovideoayuda").html(titulovideo);
    $('#divvideosayuda').attr('src', urlvideos + nombrevideo);
    $("#divprincipalvideosayuda video")[0].load();
}