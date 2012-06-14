$(document).ready(function() {


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
        return false;
    });

    //Fermeture de la pop-up et du fond
    $('a.close, #fade').live('click', function() { //Au clic sur le bouton ou sur le calque...
        $('#fade , .popup_block').fadeOut(function() {
            $('#fade, a.close').remove();  //...ils disparaissent ensemble
        });
        return false;
    });

    $('.kit-edm-delete-node').click(function(e) {
        var response = confirm("Do you confirm you want to " + $(this).attr('title') + " ?");
        if (!response) {
            e.preventDefault();
        }
    });

});