
/**********************************************************
 *  Options globales utilisées pour les formulaires Quill
 **********************************************************/
var options = {

    placeholder: 'Le commentaire ici tu saisiras... et courtois tu resteras...',
    theme: 'snow',
    modules: {
        
        toolbar:  [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],  
            ['bold', 'italic', 'underline', 'strike'],  
            [{ 'color': [] }, { 'background': [] }],     
            [{ 'align': [] }],  
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ]

    }

};

/******************************************* 
 *     Déclaration de l'instance de Vue 
 *******************************************/
vuePost = new Vue({
                    
    el: '#app',
    delimiters: ['[[' , ']]'],
    data: {

        sujet:                 0,
        input:              null,
        user:               null,
        userAuthorised:    false,
        userID:                0,
        data:                 [],
        comments:             [],
        editors:              [],
        editor:             null,
        pages:                [],
        selector:              1,
        nbPages:               0,
        interval:              0,
        nbComment:             0,
        stop:               false

    },
    methods: {

        /*******************************************************************
         *      Affichage du formulaire d'édition sur un commentaire 
         ******************************************************************/
        editComment: function ($post) {

            // Creation de l'éditeur de message via un tableau de ces objets    
            const $key     = $('#'+$post).attr('name');
            const $postObj = $('#'+$post);
                    
            if ( false === this.editors.hasOwnProperty($key) ) {

                this.editors[$key] = new Quill("#"+$('#'+$post).attr('name'), options);

            }
             
            // Si le commentaire est déjà en édition 
            if ( $('#'+$post).hasClass('PostShown') ) {
                
                this.deletePostShown()

            // Si le commentaire n'est pas en édition alors on le passe
            } else {

                this.deletePostShown()

                // Passage du commentaire à éditer à visible
                $('#'+$post).addClass('PostShown');
                $('#'+$post).css('display', 'block');

            }

            // Modification du css des rich text editor
            this.editorCssRebuilt()

            // Calcul du nombre d'éléments
            this.countElem($postObj, 'Edit');
                         
        },

        /*******************************************
         *  Création ou édition du commentaire
         *******************************************/
        postComment: function($post = null) {

            // Si l'utilisateur n'est pas connecté alors retour
            if ( this.userID === 0 ) {
                alert("Veuillez vous connecter !");
                return;
            }
         
            // Si la fonction est déjà en exécution alors retour
            if ( true === this.stop ) {
                return;
            } 

            this.stop = true;

            // Initialisation des variables
            var $modeExe  = null;
            var $url      = null;
            var $key      = null;
            var $content  = null;
            var $postId   = null;
            var $pictures = [];

            // Routage spécifique si il y a un commentaire alors édition sinon création
            if ( null !== $post ) {

                $modeExe = "current";
                $key = $('#'+$post).attr('name');
                $content   = this.editors[$key].root.innerHTML;
                $postId    = $post.split('_', 2)[1];

                $url = Routing.generate('post_edit', {
                    'sujet': this.sujet,
                    'post':  $postId
                });

                $pictures = $('#ulPictureEdit > li > input');
  
            } else {

                $modeExe = "last";
                $content = this.editor.root.innerHTML;

                $url = Routing.generate('post_new', {
                    'sujet': this.sujet,
                });

                $pictures = $('#ulPictureNew > li > input');

            }

            // Si aucunes données dans le commentaire alors retour, la regex est là pour supprimer les balises mises avec l'éditeur de texte. 
            if ( null !== $content || "" !== $content ) {

                var $string  = $content;
                var $reg     = /<\s*[!\/]?\s*[a-zA-Z0-9]*[\/]?\s*>/;

                while ( $reg.test($string) ) {

                    $string = $string.replace($reg, "");

                }

                if ( null === $string || "" === $string.trim() ) {

                    alert("Aucunes données à traiter!");
                    return;

                }

            }
            
            // Nécessaire d'instancier un objet FormData pour l'envoie en Post (envoie plus simple à traiter)
            var $data = new FormData();
            $data.append('content', $content);
            var $count = 0;

            if ( $pictures.length ) {

                $($pictures).each( function() {

                    if ( 0 !== this.files.length ) { 

                        if ( 2000000 < this.files[0].size ) {

                            alert("Le poids d'une image ne peut excéder 2 mo.");
                            return;

                        }

                        $data.append('files'+$count, this.files[0]);
                        $count++;

                    }

                })

            }

            // Curseur de chargement
            $("body").css('cursor', 'wait'); 
    
            axios ({
                        
                method: 'post',
                url: $url,
                data: $data,
                headers: {
                    'Content-Type': 'multipart/form-data'
                }

            })            
            .then( (response) => {

                // Actualisation des commentaires et updated si status HTTP OK ou travail sur serveur local
                if ( response.status === 200 || response.status === 0 ) {

                    this.refreshComment( $modeExe );

                } else if ( response.status === 206 ) {

                    alert("Une ou plusieurs image n'a pas été téléchargée!\nVeuillez vérifier que le poids de l'image n'excède pas 2mo.")

                } else {

                    alert("Une erreur est survenue ... ça peut arriver non ..?")

                }

                if ( null === $post ) {
                    this.editor.root.innerHTML = "";
                }

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })
                    
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

                    this.refreshComment('current');

                }

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })
                    

        },

        /*****************************************************
        *   Raffraichissement de tous les commentaires
        *****************************************************/
        refreshComment: function( $modeExe = null ) {

            // Suppression de tous les commentaires en édition avant le refresh
            this.deletePostShown();
            
            // Suppression des blocs de réclamation ouvert
            $('.reclamationShow').removeClass('reclamationShow');

            var $id = $("span[name='sujet']").attr("id");    
            var $url = Routing.generate('post_list', {
                'sujet': $id,
            });
                   
            axios ({

                method: "get",
                url:    $url,
                responseType: 'json'

            })            
            .then( (response) => {
                
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
                if ( $modeExe === "last" ) {
                    this.viewComment( this.nbPages );
                } else if( $modeExe === "current" ) {
                    this.viewComment(this.selector);
                } else {
                    this.viewComment();
                }

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })

        },

        /*******************************************************************************
         *   Affichage des commentaires relatifs à la page sélectionnée + pagination
         ******************************************************************************/
        viewComment: function ( $page = null ) {
            
            // Suppression des blocs de commentaires en édition
            this.deletePostShown()

            // Suppression des blocs de réclamation ouvert
            $('.reclamationShow').removeClass('reclamationShow');

            // Tableau pour sauvegarder les numéros de pagination
            $array     = [];
            $nbPages   = this.nbPages;
            this.pages = [];

            // Recherche de la plage de commentaires à voir dans data selon l'index (la page) en cours 
            if ( null === $page ) {

                $index = 0;

            } else {

                $index = $page-1;

            }
            
            console.log($index);
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

        /*******************************************************************
         *   Création des formulaires d'édition pour chaques commentaires
         ******************************************************************/
        createForm: function () {

            // Initialisation des variables locales et des attributs
            this.editor   = null;
            this.editors  = [];
            var $compteur = 0;

            $(".textEditor").each(function() {
                        
                // Suppression de l'éditeur de Quill
                $(this).removeClass('ql-container ql-snow');

                // Suppression des Toolbars
                $( this ).parent().children().each( function() {

                    if ( $(this).hasClass('ql-toolbar') ) {
                                 
                        $( this ).remove();

                    }

                });

                // Ajout de l'ID et du Nom au conteneur principal
                $(this).parent().attr('name', 'editor'+$compteur);
                $(this).attr('id', 'editor'+$compteur);
                $compteur++;

            }); 

            // Suppression du formulaire de création d'un commentaire
            $("#editor_new").parent().children().each( function() {
                            
                if ( $(this).hasClass('ql-toolbar') ) {
                                 
                    $( this ).remove();

                }

            });

            $("#editor_new").removeClass('ql-container ql-snow');

            // Création du formulaire de création d'un commentaire
            this.editor = new Quill("#editor_new", options);

            // Modification css des rich text editor
            this.editorCssRebuilt()

        },

        /*******************************************************************
         *       Design des blocs de commentaires (rich text editor)
         ******************************************************************/
        editorCssRebuilt: function () {

            // Changement de style pour les formulaires : Toolbar (bordures... style que je n'ai pas pu changer via CSS)
            $('.ql-toolbar').each( function() {
                $(this).css('border', '1px solid rgba(0, 47, 70, 1)');
            });

            $('.ql-stroke').css('stroke', 'rgba(0, 47, 70, 1)')   
            $('.ql-picker').css('color', 'rgba(0, 47, 70, 1)')
            $('.ql-fill').css('fill', 'rgba(0, 47, 70, 1)')

            // Changement de style pour les formulaires : Editeur (bordures... style que je n'ai pas pu changer via CSS)
            $('.ql-container').css('border', '1px solid rgba(0, 47, 70, 1)');

        },
        
        /*******************************************************************
         *          Suppression des commentaires en mode édition 
         ******************************************************************/
        deletePostShown: function() {

            // Suppression du ou des commentaire(s) en mode édition avec liste d'images
            if ( $('#ulPictureEdit').length ) {
                
                $('#ulPictureEdit').remove();

            }

            if ( $(".PostShown").length ) {

                $(".PostShown").css('display', 'none');
                $(".PostShown").removeClass('PostShown');

            }

            // Remise à l'état d'origine du block nouveau commentaire
            if ( $('#ulPictureNew').length ) {
                
                $('#ulPictureNew').remove();

            }

        },

        /*******************************************************************
         *               Affichage de la fenêtre de signalement
         ******************************************************************/
        printReclamation: function($id) {
           
            if ( this.userID === 0 ) {
                alert("Veuillez vous connecter pour effectuer cette action !");
                return;
            }

            $elem = $('#'+$id);
            $elems = $('.reclamationShow');

            // Suppression du block de signalement en édition des commentaires sauf celui cliqué
            if ( $elems.length ) {
                
                $elems.each( function() {

                    if ( $(this).attr('id') !== $elem.attr('id') ) {
                        
                        $(this).removeClass('reclamationShow');
                    
                    }

                });
                    
            }
            
            if( $elem.css('display') === 'block' ) {

                $('#'+$id).removeClass('reclamationShow');

            } else {

                $('#'+$id).addClass('reclamationShow');

            }

        },

        /*******************************************************************
         *                  Signalement du commentaire
         ******************************************************************/
        reclamation: function ($post) {

            $url = Routing.generate("post_user_reclamation", {
                'user' : this.userID,
                'post' : $post
            })

            axios({

                method: 'get',
                url: $url,
                responseType: 'json',

            }).then((response) => {

                if ( response.status === 200 || response.status === 0 ) {

                    this.refreshComment("current");

                } 

            }).catch( function(error) {

                console.log(error)

            })

        },

        /***********************************
         *   Ajout d'un sélecteur d'image
         **********************************/    
        addPicture: function (modeExe = "") {

            if ( "Edit" === modeExe.trim() ) {

                var elem    = $('div').find('.PostShown');
                var listeId = "ulPictureEdit";

            } else {

                var elem    = $('#newPost');
                var listeId = "ulPictureNew";

            }
        
            var exist    = false;
            var compteur = 0;
            var id       = "";

            // Si plus de 3 images alors retour
            if ( this.countElem(elem, modeExe) > 2 ) {
                return;
            }

            // Si la liste n'existe pas on l'a créé
            elem.children().each( function() {
                if ( this.nodeName === "UL" ) {
                    exist = true;
                }
            })

            // Si pas de UL alors ajout d'une UL
            if ( false === exist ) {
                elem.append('<ul id="'+listeId+'"></ul>');
            }
            
            // Suppression des i dans la balise
            $('#'+listeId+' i').remove();
            $('#'+listeId).append('<li class="col-12 textColor"><label class="col-2" for="picture">Fichier : </label><input class="col-8" name="picture" type="file" accept="image/png, image/jpeg"></li>');
            
            // Recalcul du nombre d'image
            this.countElem(elem, modeExe);
        
            // Pour toutes lignes : Ajout  d'un id, d'un logo  
            $('#'+listeId+' > li').each( function() {

                var item = $( this );

                if ( "Edit" === modeExe.trim() ) {

                    id = "PictureEdit"+"_"+compteur;
                    item.attr('id', id);
                    item.append('<i style="float: right; color: white;" class="fas fa-trash imgCursor"></i>');

                } else {

                    id = "PictureNew"+"_"+compteur;
                    item.attr('id', id);
                    item.append('<i style="float: right; color: white;" class="fas fa-trash imgCursor"></i>');

                }

                compteur++;

            }) 
            
            // Ajout fonction de visualisation de l'image sur les input
            $('#'+listeId+' > li > i').click( function()  {

                vuePost.removePictureSelector($(this).parent(), modeExe);
                
            })

            // Ajout fonction de visualisation de l'image sur les input
            $('#'+listeId+' > li > input').change( function() {

                vuePost.viewPicture(this);
                
            })

        },

        /*****************************************************
         *          Suppression d'une image spécifique
         *****************************************************/
        removePicture: function ($id) {
            
            // Si la fonction est déjà en exécution alors retour
            if ( true === this.stop ) {
                return;
            } 

            this.stop = true;

            // Curseur de chargement
            $("body").css('cursor', 'wait'); 

            $url = Routing.generate('picture_delete', {
                'id': $id
            });

            axios ({
                        
                method: 'get',
                url: $url,

            })            
            .then( (response) => {

                // Actualisation des commentaires et updated si status HTTP OK ou travail sur serveur local
                if ( response.status === 200 || response.status === 0 ) {

                    this.refreshComment('current');

                }

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })

        },

        /*****************************************
         *   Suppression d'un sélecteur d'image
         *****************************************/
        removePictureSelector: function (el, modeExe = "") {

            // Suppression du sélecteur d'image
            el.remove();
            var elem = $(".PostShown");

            // Calcul du nombre d'image
            this.countElem(elem, modeExe);

        },

        /************************************************
         *    Chargement de l'image en visualisation
         ***********************************************/
        viewPicture: function (el) {

            if (el.files && el.files[0]) {

                var reader   = new FileReader();
                var element  = $(el);
                var liParent = $(element.parent());
                var imgChild = $(liParent.children('img'));
            
                // Ajout de la balise image vide
                if ( !imgChild.length ) {

                    liParent.append('<img class="col-4" src="#" alt="your image" style="margin-top: 2%; margin-bottom: 2%;"/>')
                    var imgChild = $(liParent.children('img'));
                } 
        
                // chargement de l'image
                reader.onload = function(e) {
                    $(imgChild).attr('src', e.target.result);
                }
                
                reader.readAsDataURL(element[0].files[0]);
            
            }
            
        },

        /*************************************************
         *   Compte le nombre d'image et de sélecteur 
         *   d'image du commentaire
         ************************************************/
        countElem: function (elem, modeExe = "") {

            if ( "Edit" === modeExe.trim() ) {
                var nbPictures = $('#ulPictureEdit > li > input').length + elem.parent().children('.userPostPictures').find('img').length;
            } else {
                var nbPictures = $('#ulPictureNew > li > input').length;
            }

            // Si supérieur à 3 alors boutton bleu sinon bouton rouge
            switch ( modeExe.trim() ) 
            {

                case "Edit":

                    if ( 2 < nbPictures ) {

                        $('i[name="addPictureButtonEdit"]').css('color', 'red');

                    } else {

                        $('i[name="addPictureButtonEdit"]').css('color', 'blue');

                    }

                break;

                case "New":

                    if ( 2 < nbPictures ) {

                        $('i[name="addPictureButtonNew"]').css('color', 'red');

                    } else {

                        $('i[name="addPictureButtonNew"]').css('color', 'blue');

                    }

                break;

            }

            return nbPictures;

        },

        /*******************************************************************
         *        Acquisition des autorisations de l'utilisateur
         ******************************************************************/
        getAuthorised: function() {

            $url = Routing.generate('user_authorised');

            axios({

                method: 'get',
                url: $url,
                responseType: 'json',

            }).then((response) => {

                if ( response.status === 200 || response.status === 0 ) {

                    // Utilisateur a le droit de supprimer un sujet   
                    this.userAuthorised = response.data['authorised'];
                    this.userID         = response.data['userID'];

                    // Mise à jour de l'historique utilisateur pour stats
                    this.registerActivity();

                } 

            }).catch( function(error) {

                console.log(error)

            })

        },

        /*******************************************************************
         *          Mise à jour de l'historique utilisateur
         ******************************************************************/
        registerActivity: function () {
            
            if ( 0 !== this.userID ) {

                $url =  Routing.generate('register_activity', {
                    'user':  this.userID,
                    'sujet': this.sujet
                }); 

                axios ({

                    method: 'get',
                    url: $url,
                    responseType: 'json',
                    
                }) 
                .catch ( function (error) {
                
                    console.log(error);
                
                })

            }

        },

        /*******************************************************************
         *                    Like ou Dislike d'un Post
         ******************************************************************/
        like: function ($post, $param) {

            if ( this.userID === 0 ) {
                alert("Veuillez vous connecter pour effectuer cette action !");
                return;
            }

            $url = Routing.generate('post_like', {
                'user' : this.userID,
                'post' : $post,
                'param': $param
            });

            axios({

                method: 'get',
                url: $url,
                responseType: 'json',

            }).then((response) => {

                if ( response.status === 200 || response.status === 0 ) {

                    this.refreshComment('current');

                } 

            }).catch( function(error) {

                console.log(error)

            })
    
        },

        /*******************************************************************
         *                  Raffraichissement automatique
         ******************************************************************/
        refreshAuto: function () {

            setInterval( () => {   

                if ( $(".PostShown").length || $(".reclamationShow").length || this.stop === true ) {

                    return;
    
                }

                $url = Routing.generate("post_count", {
                    'sujet' : this.sujet,
                })

                axios({

                    method: 'get',
                    url: $url,
                    responseType: 'json',

                })
                .then((response) => {

                    if ( response.status === 200 || response.status === 0 ) {

                        if ( this.nbComment !== response.data[0][1] ) {

                            this.nbComment = response.data[0][1];
                            this.refreshComment("current");
                        
                        }
                        
                    } 

                })
                .catch( function(error) {

                    console.log(error)

                })
                
            }, 5000 );

        },
        
    }, 

    mounted() {
        
        // Set le sujet dès le onload
        this.sujet = $("span[name='sujet']").attr("id");
        
        // Acquisition des autorisations utilisateurs
        this.getAuthorised();
         
        // Affichage des commentaires au chargement de l'instance de vue et donc de la page
        this.refreshComment();

        // Actualisation des commentaires 
        this.refreshAuto();
         
    }, 

    updated() {

        // A chaque modification des données (commentaires) : Création des formulaires dédiés
        this.createForm();
        
        // Mise à blanc de la page en surbrillance et mise en surbrillance de la page sélectionnée 
        $("a").css('background-color', 'transparent');
        $("#Selector_"+this.selector).css('background-color', 'grey');
        
        // Curseur par défaut
        $("body").css('cursor', 'default');
        
        // Le programme peut reprendre
        this.stop = false;

    }

});

