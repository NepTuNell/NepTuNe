
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
            if ( event.clientY < imgModPosition['bottom'] && event.clientY > imgModPosition['top'] &&
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
         *        Traitement du clic hors navigation    
         *****************************************************/

        var containerPos = $('.dropdown-menu')[0].getBoundingClientRect();

        // Test de la position du curseur Fermeture des listes  
        if ( event.clientY < containerPos['bottom'] || event.clientY > containerPos['top'] ||
             event.clientX < containerPos['left'] || event.clientX > containerPos['right'] ) {
            
            // Fermeture de toutes les fenêtres ouvertes
            $('.dropdown-submenu > a').next('ul').each( function() {

                $(this).hide();

            })

        }

        /****************************************************************
         *    Traitement des submenus de la barre de navigation
         ****************************************************************/

        $('.dropdown-submenu > a').on("click", function (event) {
          
            // Fermeture de toutes les fenêtres ouverte
            console.log(this)
            if ( $(this).next('ul').css("display") === "none" ) {

                $('.dropdown-submenu > a').next('ul').each( function() {

                    $(this).hide();

                })

                $(this).next('ul').show(500, "swing");

            } else {

                
                $(this).next('ul').hide(500, "swing");

            }
                
            console.log('test')

            // Attention utiliser stopImmediatePropagation pour éviter d'effectuer la fonction autant de fois qu'il y a de .dropdown-submenu > a !
            //event.stopPropagation();
            event.stopImmediatePropagation();
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
