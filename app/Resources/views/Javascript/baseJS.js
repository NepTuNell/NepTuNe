//////////////////////////////////////////////
//      TRAITEMENT DE LA FENETRE MODALE     //
//////////////////////////////////////////////
var imgMod = document.getElementById("modalImage");
var winMod = document.getElementById("modalWindow");
var navBar = document.getElementById("myNavBar");

/*
* Test de la position du curseur pour fermeture de la fenêtre sur clic
*/
document.getElementsByTagName('body')[0].onclick = function (event) {

    if (window.event) {
        event = window.event;
    }
    
    /**
     * Obtention des coordonnées et nombres de pixel par élément
     * Largeur de la popup; 
     * Position de l'icone utilisateur
     * Hauteur de la barre de navigation
     */
    var winModPosition = winMod.getBoundingClientRect(); 
    var imgModPosition = imgMod.getBoundingClientRect();
    var navPosition    = navBar.getBoundingClientRect();

    console.log(navPosition);
    /**
     * Calcul de la position de la popup
     */
    var modalWidth = document.documentElement.clientWidth*15/100;
    var position = imgModPosition['right'] - modalWidth;

    /**
     * Positionnement de la fenêtre Popup sur la position de l'image Utilisateur
     */
    winMod.style.left = position+"px";
    winMod.style.top = navPosition['bottom']+1+"px";

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

