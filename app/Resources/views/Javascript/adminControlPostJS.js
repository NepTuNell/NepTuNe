
/******************************************* 
 *     Déclaration de l'instance de Vue 
 *******************************************/
vuePost = new Vue({
                    
    el: '#app',
    delimiters: ['[[' , ']]'],
    data: {

        sujet:                 0,
        data:                 [],
        comments:             [],
        editors:              [],
        editor:             null,
        pages:                [],
        selector:              1,
        nbPages:               0,
        interval:              0,
        stop:               false

    },
    methods: {

        /*****************************************************
        *   Raffraichissement de tous les commentaires
        *****************************************************/
        refreshComment: function() {
  
            var $url = Routing.generate('admin_post_list');
                   
            axios ({

                method: "get",
                url:    $url,
                responseType: 'json'

            })            
            .then( (response) => {
                
                console.log(response.data);
                // Initialisation variables globales à Vue
                this.data  = [];
                this.nbPages = 0;

                // Initialisation variables locales
                var $index = 0;
                var $array = [];
                 
                // Création et placement des commentaires dans un tableau pour la pagination
                for ( $i = 1; $i <= response.data.length; $i++ ) {

                    if ( ($i % 10) === 0 ) {

                        this.nbPages++;
                        $array = [];

                        while ( $index <= $i-1 ) {

                            $array.push(response.data[$index]);
                            $index++;

                        }
                        
                        if ( $array.length > 0 ) {
 
                            this.data.push($array);
                        
                        }

                    }

                };
       
                // Traitement des derniers commentaires que le modulo n'a pas traité
                if ( $index < response.data.length ) {

                    $array = [];

                    while ( $index < response.data.length ) {
                        
                        $array.push(response.data[$index]);
                        $index++;

                    }
                    
                    this.data.push($array);
                    this.nbPages++;

                }
                
                // Initialisation des commentaires à voir selon l'index sur lequel nous sommes placés dans le tableau "data" pour la pagination
                this.viewComment();

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })

        },

        /*******************************************************************************
         *   Affichage des commentaires relatifs à la page sélectionnée + pagination
         ******************************************************************************/
        viewComment: function ( $page = null ) {
            
            // Tableau pour sauvegarder les numéros de pagination
            $array     = [];
            $nbPages   = this.nbPages;
            this.pages = [];

            // Recherche de la plage de commentaires à voir dans data selon l'index (la page) en cours 
            if ( null === $page ) {

                $index = 0;

            } else {

                $index = $page[0]-1;

            }
 
            // Création du premier élément (statique)
            $array.push(1);
             
            // Boucle sur le nombre de pages total
            if ( $index+1 > this.nbPages - 4 ) {
                
                for ( $i = this.nbPages - 4; $i <= this.nbPages; $i++ ) {

                    if ( $array.length <= 4 && $i > 0 && $i !== 1 && $i !== this.nbPages) {
                        $array.push($i);
                    } 

                }
            
            } else {
    
                for ( $i = $index; $i < this.nbPages; $i++ ) {
            
                    if ( $array.length <= 4 && $i !== 0 &&
                         $i !== 1 && $i !== this.nbPages ) {

                        $array.push($i);

                    } 
            
                }           
            
            } 
            
            // Dernière page (statique)
            if ( this.nbPages > 1 ) {    
                $array.push( this.nbPages );
            }
            
            // Affichage des commentaires via v-for et sauvegardes du nombre de page
            this.comments = this.data[$index];
            this.pages = $array;

            // Sauvegarde du numéro de page sélectionné
            this.selector = $index+1;
        
        },

        /*******************************************
         *      Suppression d'un commentaire
         *******************************************/
        deleteComment: function ($commentId) {

            // Si la fonction est déjà en exécution alors retour
            if ( true === this.stop ) {
                return;
            } 

            this.stop = true;
            $id = $commentId[0][0]
        
            if ( null === $id ) {
                return;
            }

            // Curseur de chargement
            $("body").css('cursor', 'wait'); 

            $url = Routing.generate('post_delete');
            var $data = new FormData();
            $data.set('postID', $id);

            axios ({
                        
                method: 'post',
                url: $url,
                data: $data

            })            
            .then( (response) => {

                // Actualisation des commentaires et updated si status HTTP OK ou travail sur serveur local
                if ( response.status === 200 || response.status === 0 ) {

                    this.refreshComment();

                }

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })
                    

        },
        
    },

    mounted() {
        
        // Set le sujet dès le onload
        this.sujet = $("span[name='sujet']").attr("id");
         
        // Affichage des commentaires au chargement de l'instance de vue et donc de la page
        this.refreshComment();

    }, 

    updated() {
        
        // Mise à blanc de la page en surbrillance et mise en surbrillance de la page sélectionnée 
        $("a").css('background-color', 'transparent');
        $("#Selector_"+this.selector).css('background-color', 'grey');
        
        // Curseur par défaut
        $("body").css('cursor', 'default');

        // Le programme peut reprendre
        this.stop = false;

    }

});


