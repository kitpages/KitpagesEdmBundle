var intervalId;
var startUpload = false;
var maxprogress = 250;
var nbrUploadStatus = 0;
var timeInterval=200;
$(document).ready(function() {
    function progressHandlingFunction(e){
        if(e.lengthComputable){
            $('progress').attr({value:e.loaded,max:e.total});
        }
    }

    function phpUploadStatus(){
        nbrUploadStatus = nbrUploadStatus+1;
        $.getJSON(urlUploadStatus, function(data){
            if(nbrUploadStatus<2) {
                clearInterval(intervalId);
                intervalId = setInterval(phpUploadStatus, timeInterval);
            }
            nbrUploadStatus = nbrUploadStatus-1;
            if(nbrUploadStatus>5) {
                clearInterval(intervalId);
            }
            if(data)
            {

                var progress = Math.round(maxprogress*data.bytes_processed / data.content_length);
                $('.kit-edm-upload-progress-indicator').css('width', progress);
                $('.progress_status').html('Uploading '+ Math.round((data.bytes_processed / data.content_length)*100) + '%');
                startUpload = true;
            }
            else
            {
                if (startUpload) {
                    $('.kit-edm-upload-progress-indicator').css('width', maxprogress);
                    $('.progress_status').html('Complete');
                    clearInterval(intervalId);
                }
            }
        });
    }
    //Lorsque vous cliquez sur un lien de la classe poplight et que le href commence par #
    $('a.poplight[href^=#]').click(function() {
        var kitpagesEdmFieldName = $(this).data('kitpages-edm-field-name');
        var kitpagesEdmFieldValue = $(this).data('kitpages-edm-field-value');
        $('#' + kitpagesEdmFieldName).val(kitpagesEdmFieldValue);

        var popID = $(this).attr('rel'); //Trouver la pop-up correspondante
        var popURL = $(this).attr('href'); //Retrouver la largeur dans le href

        var popWidth = 500; //La première valeur du lien

        $('#' + popID).remove();
        var cloneForm = $('.' + popID).clone().prependTo('body');
        $('body > .' + popID).attr('id', popID);

        //Faire apparaitre la pop-up et ajouter le bouton de fermeture
        $('#' + popID).fadeIn().css({
            'width': Number(popWidth)
        })
            .prepend('<a class="close">close</a>');



        //Récupération du margin, qui permettra de centrer la fenêtre - on ajuste de 80px en conformité avec le CSS
        var popMargTop = ($('#' + popID).height() + 80) / 2;
        var popMargLeft = ($('#' + popID).width() + 80) / 2;

        //On affecte le margin
        $('#' + popID).css({
            'margin-top' : -popMargTop,
            'margin-left' : -popMargLeft
        });

        //Effet fade-in du fond opaque
        $('body').append('<div id="fade"></div>'); //Ajout du fond opaque noir
        $('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn();


        //selection premier input
        $('#' + popID + ' form div:first input').focus();

        return false;
    });

    //Fermeture de la pop-up et du fond
    $('a.close, #fade').live('click', function() { //Au clic sur le bouton ou sur le calque...
        $('#fade , .popup_block').fadeOut(function() {
            $('#fade, a.close').remove();  //...ils disparaissent ensemble
        });
        return false;
    });

    $('.kit-edm-confirm').click(function(e) {
        var response = confirm($(this).attr('data-kitpages-edm-confirm'));
        if (!response) {
            e.preventDefault();
        }
    });
    $('.kitpages_edmbundle_nodefileversionform form :button').live('click', function() {

        if (test == 2) {
            $('.kit-edm-upload-progress')[0].style.visibility = 'visible';
            intervalId = setInterval(phpUploadStatus, timeInterval);
            setTimeout($(this).parents('form').submit(),100)
        } else {
            $('.kitpages_edmbundle_nodefileversionform button[type="submit"]').hide();
            $('.kitpages_edmbundle_nodefileversionform button[type="submit"]').parent().addClass('kit-edm-tree-form-load');
            $(this).parents('form').submit();
        }
//        intervalId = setInterval(phpUploadStatus, 200);
//        setTimeout($(this).parents('form').submit(),300);
    });

    $('.kitpages_edmbundle_nodefileform form :button').live('click', function() {
        if (test == 2) {
            $('.kit-edm-upload-progress')[0].style.visibility = 'visible';
            intervalId = setInterval(phpUploadStatus, timeInterval);
            setTimeout($(this).parents('form').submit(),100)
        } else {
            $('.kitpages_edmbundle_nodefileform button[type="submit"]').hide();
            $('.kitpages_edmbundle_nodefileform button[type="submit"]').parent().addClass('kit-edm-tree-form-load');
            $(this).parents('form').submit();
        }
        ;
    });

});