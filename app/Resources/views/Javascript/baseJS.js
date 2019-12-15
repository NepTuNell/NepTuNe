
//////////////////////////////////////////////
//            FONCTIONS GLOBALES            //
//////////////////////////////////////////////
$(document).ready(function () {

    //////////////////////////////////////////////
    //      GESTION DES COLLISIONS GLOBALES     //
    //////////////////////////////////////////////
    $('body').on("click", function (event) {

        if (window.event) {
            event = window.event;
        }

        //////////////////////////////////////////////////
        //      TRAITEMENT DE LA POPUP UTILISATEUR      //
        //////////////////////////////////////////////////
        var imgMod = document.getElementById("modalImage");
        var winMod = document.getElementById("modalWindow");
        var navBar = document.getElementById("my1stNavBar");

        /**
         * Valable si utilisateur connecté
         */
        if ( null !== winMod || null !== imgMod ) {

            /**
             * Position de la liste de type dropdown 
             * Position de la popup utilisateur
             * Position de l'icone utilisateur
             * Position de la barre de navigation principale
             */
            var winModPosition = winMod.getBoundingClientRect(); 
            var imgModPosition = imgMod.getBoundingClientRect();
            var navPosition    = navBar.getBoundingClientRect();

            /**
             * Calcul de la position de la popup utilisateur
             */
            var modalWidth = document.documentElement.clientWidth*15/100;
            var position = imgModPosition['right'] - modalWidth;

            /**
             * Positionnement de la fenêtre Popup sur la position de l'image Utilisateur
             */
            winMod.style.left = position+"px";
            winMod.style.top  = navPosition['bottom']+1+"px";

            /**
             * Test des positions du curseur pour affichage ou fermeture de la popup
             */
            if (event.clientY < imgModPosition['bottom'] && event.clientY > imgModPosition['top'] &&
                event.clientX > imgModPosition['left'] && event.clientX < imgModPosition['right']) {

                winMod.style.display = "flex";

            } else {

                if (event.clientY < winModPosition['bottom'] && event.clientY > winModPosition['top'] &&
                    event.clientX > winModPosition['left'] && event.clientX < winModPosition['right']) {

                    winMod.style.display = "flex";

                } else {

                    winMod.style.display = "none";

                }

            }

        }
        

        ////////////////////////////////////////////////////
        //      TRAITEMENT DE LA BARRE DE NAVIGATION      //
        ////////////////////////////////////////////////////

        /**
         * Position de la liste de type dropdown 
         * Position du lien pour accéder à la liste
         */
        var containerPos   = $('.dropdown-submenu ul.dropdown-menu')[0].getBoundingClientRect();
        var dropdownLiPos  = $('.dropdown-submenu > a')[0].getBoundingClientRect();

        /**
         * Test de la position du curseur
         * Fermeture des listes 
         */
        if ( event.clientY > containerPos['bottom'] && event.clientY < containerPos['top'] &&
             event.clientX > containerPos['left'] && event.clientX < containerPos['right'] ) {

            return;

        } else {

            /**
             * Fermeture de toutes les fenêtres ouvertes
             */
            $('.dropdown-submenu > a').next('ul').css("display", "none");

        }

    });

    ///////////////////////////////////////////////////////////
    //      AFFICHAGE LA BARRE DE NAVIGATION PRINCIPALE      //
    ///////////////////////////////////////////////////////////

    /**
     * Affichage des univers, des thèmes et des sections si elles existent
     */
    $('.dropdown-submenu > a').on("click", function (event) {
        
        /**
         * Fermeture de toutes les fenêtres ouvertes
         */
        $('.dropdown-submenu > a').next('ul').css("display", "none");

        /**
         * Ouverture de celle sur laquelle l'utilisateur a cliqué
         */
        $(this).next('ul').toggle();
        event.stopPropagation();
        event.preventDefault();

    });

});