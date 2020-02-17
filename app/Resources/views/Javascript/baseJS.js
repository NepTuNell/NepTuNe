
$(document).ready(function () {

    /********************************************* 
     *          Gestion des collisions   
     ********************************************/
    $('body').on("click", function (event) {

        if (window.event) {
            event = window.event;
        }

        /******************************************
         *   Traitement de la popup utilisateur
         *****************************************/

        var imgMod          = document.getElementById("modalImage");
        var winMod          = document.getElementById("modalWindow");
        var firstNavBar     = document.getElementById("my1stNavBar");
        var secondNavBar    = document.getElementById("mainNav");

        /**
         * Valable si utilisateur connecté
         */
        if ( null !== winMod || null !== imgMod ) {

            // Position des éléments
            var winModPosition = winMod.getBoundingClientRect(); 
            var imgModPosition = imgMod.getBoundingClientRect();
            var navPosition    = firstNavBar.getBoundingClientRect();
            var nav2Position   = secondNavBar.getBoundingClientRect();

            // Calcul de la position de la popup utilisateur

            if (window.matchMedia("(min-width: 769px)").matches) {
                var modalWidth = document.documentElement.clientWidth*17/100;
            } else {
                var modalWidth = document.documentElement.clientWidth*50/100;
            }

            var position = imgModPosition['right'] - modalWidth;

            // Positionnement de la fenêtre Popup sur la position de l'image Utilisateur
            winMod.style.left = position+"px";
            winMod.style.top  = navPosition['bottom']+nav2Position['height']+1+"px";

            // Test des positions du curseur pour affichage ou fermeture de la popup 
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
        
        /******************************************************
         *        Traitement de la barre de navigation      
         *****************************************************/

        var containerPos   = $('.dropdown-submenu ul.dropdown-menu')[0].getBoundingClientRect();

        // Test de la position du curseur Fermeture des listes  
        if ( event.clientY > containerPos['bottom'] && event.clientY < containerPos['top'] &&
             event.clientX > containerPos['left'] && event.clientX < containerPos['right'] ) {
            
            return;

        } else {

            // Fermeture de toutes les fenêtres ouvertes
            $('.dropdown-submenu > a').next('ul').css("display", "none");

        }

        /****************************************************************
         *    Traitement des submenus de la barre de navigation
         ****************************************************************/

        $('.dropdown-submenu > a').on("click", function (event) {
            
            // Fermeture de toutes les fenêtres ouvertes
            $('.dropdown-submenu > a').next('ul').css("display", "none");

            // Ouverture de celle sur laquelle l'utilisateur a cliqué
            $(this).next('ul').toggle();
            event.stopPropagation();
            event.preventDefault();

        });

    });

    /******************************************* 
     *      Déclaration des filtres Vue 
     *******************************************/
    Vue.filter('dateFR', function (value) {
                        
        if ( !value ) {

            return ''
                            
        }

        var date = value.date.split(" ", 1).join();
        return date.split("-").reverse().join('/');
                    
    });

});