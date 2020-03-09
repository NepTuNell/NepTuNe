
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

        sujetID:               0,
        sujetLibelle:         "",
        user:               null,
        userID:                0,
        data:                 [],
        editor:             null,
        interval:              0,
        stop:              false,
        sectionID:             0,
        themeID:               0,
        modeExe:              ""

    },
    methods: {

        /*******************************************
         *  Création du sujet
         *******************************************/
        sujetCreate: function() {

            // Si l'utilisateur n'est pas connecté alors retour
            if ( this.userID === 0 ) {
                alert("Veuillez vous connecter !");
                return;
            }
         
            // Si la fonction est déjà en exécution alors retour
            if ( true === this.stop ) {
                return;
            } 

            // Initialisation des variables
            var $url = null;

            // Test si sujet renseigné
            if ( this.sujetLibelle.trim() === "" ) {

                alert("Veuillez saisir le titre du sujet !")
                return;

            }

            if ( this.modeExe.trim() === "Création" ) {

                $content = this.editor.root.innerHTML;

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

                // Blocage du programme
                this.stop = true;

                // Vérfication des images à uploader
                $pictures = $('#ulPictureNew > li > input');

                if ( $pictures.length ) {

                    $($pictures).each( function() {

                        if ( 0 !== this.files.length ) { 

                            if ( 2000000 < this.files[0].size ) {

                                alert("Le poids d'une image ne peut excéder 2 mo.");
                                return;

                            }

                            if ( this.files[0].type !== "image/jpeg" && this.files[0].type !== "image/jpg" && this.files[0].type !== "image/png" ) {

                                alert("Le format de l'image n'est pas valide. Format accepté png et jpeg.");
                                return;

                            }

                        }

                    })

                }
            
            }

            // Routage création du sujet
            this.sectionID = $("span[name='section']").attr("id");
            this.themeID = $("span[name='theme']").attr("id");

            if ( this.modeExe.trim() === "Création" ) {

                if ( this.sectionID !== 0) {

                    $url = Routing.generate('sujet_new_section', {
                        'theme': this.themeID,
                        'section': this.sectionID
                    });

                } else {

                    $url = Routing.generate('sujet_new_theme', {
                        'theme': this.themeID
                    });

                }
            
            } else {

                $url = Routing.generate('sujet_edit', {
                    'sujet': this.sujetID
                });

            }

            $data = new FormData();
            $data.append('content', this.sujetLibelle);

            axios({

                method: 'post',
                url: $url,
                data: $data
                
            })
            .then( (response) => {

                if ( response.status === 200 || response.status === 0 ) {

                    // Récupération de l'ID du sujet créé
                    this.sujetID = response.data;
                    
                    // Création du commentaire
                    if ( this.sujetID !== 0 && this.modeExe.trim() === "Création" ) {
                        this.postComment();
                    } else {
                        alert("Modification du sujet réussi !");
                    }

                }

            })
            .catch( function (error) {
                                
                console.log(error);
                                
            })

        },

        /*******************************************
         *  Création du premier commentaire
         *******************************************/
        postComment: function() { 

            // Initialisation des variables
            var $url      = null;
            var $content  = null;
            var $pictures = [];

            // Routage spécifique si il y a un commentaire alors édition sinon création
            $content = this.editor.root.innerHTML;

            $url = Routing.generate('post_new', {
                'sujet': this.sujetID,
            });

            $pictures = $('#ulPictureNew > li > input');
            
            // Nécessaire d'instancier un objet FormData pour l'envoie en Post (envoie plus simple à traiter)
            var $data = new FormData();
            $data.append('content', $content);
            var $count = 0;

            if ( $pictures.length ) {

                $($pictures).each( function() {

                    if ( 0 !== this.files.length ) { 

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

                    // Redirection sur le sujet créé
                    $url = Routing.generate('post_view', {
                        'sujet': this.sujetID
                    });

                    window.location.href=''+$url+'';

                } else if ( response.status === 206 ) {

                    alert("Une ou plusieurs image n'a pas été téléchargée!\nVeuillez vérifier que le poids de l'image n'excède pas 2mo.")

                } else {

                    alert("Une erreur est survenue ... ça peut arriver non ..?")

                }

            })
            .catch ( function (error) {
                                
                console.log(error);
                                
            })
                    
        },

        /*******************************************************************
         *   Création des formulaires d'édition pour chaques commentaires
         ******************************************************************/
        createForm: function () {
            
            // Création du formulaire de création d'un commentaire
            this.editor = new Quill("#editor_new", options);

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

        /***********************************
         *   Ajout d'un sélecteur d'image
         **********************************/    
        addPicture: function (modeExe = "") {

            var elem     = $('#newPost');
            var listeId  = "ulPictureNew";
            var exist    = false;
            var compteur = 0;
            var id       = "";

            // Si plus de 3 images alors retour
            if ( this.countElem(elem) > 2 ) {
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
            this.countElem(elem);
        
            // Pour toutes lignes : Ajout  d'un id, d'un logo  
            $('#'+listeId+' > li').each( function() {

                var item = $( this );
                id = "PictureNew"+"_"+compteur;
                item.attr('id', id);
                item.append('<i style="float: right; color: white;" class="fas fa-trash imgCursor"></i>');
                compteur++;

            }) 
            
            // Ajout fonction de visualisation de l'image sur les input
            $('#'+listeId+' > li > i').click( function()  {

                vuePost.removePictureSelector($(this).parent());
                
            })

            // Ajout fonction de visualisation de l'image sur les input
            $('#'+listeId+' > li > input').change( function() {

                vuePost.viewPicture(this);
                
            })

        },

        /*****************************************
         *   Suppression d'un sélecteur d'image
         *****************************************/
        removePictureSelector: function (el) {

            // Suppression du sélecteur d'image
            el.remove();
            var elem = $(".PostShown");

            // Calcul du nombre d'image
            this.countElem(elem);

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
        countElem: function (elem) {

            var nbPictures = $('#ulPictureNew > li > input').length;

            // Si supérieur à 3 alors boutton bleu sinon bouton rouge
            if ( 2 < nbPictures ) {

                $('i[name="addPictureButtonNew"]').css('color', 'red');

            } else {

                $('i[name="addPictureButtonNew"]').css('color', 'blue');

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

                } 

            }).catch( function(error) {

                console.log(error)

            })

        },

        /*******************************************************************
         *                 Acquisition du libelle du sujet
         ******************************************************************/
        fetchSujet: function () {

            $url = Routing.generate('sujet_fetch', {
                'sujet': this.sujetID
            });

            axios({

                method: 'get',
                url: $url,
                responseType: 'json',

            }).then((response) => {

                if ( response.status === 200 || response.status === 0 ) {

                    this.sujetLibelle = response.data;

                } 

            }).catch( function(error) {

                console.log(error)

            })

        }

    },

    mounted() {

        // Blocage en attendant le chargement complet
        this.stop = true;

        // Set le mode d'exécution dès le onload
        this.modeExe = $("div[name='mode']").attr('data-value');
        
        // Acquisition des autorisations utilisateurs
        this.getAuthorised();

        // Création du formulaire de création d'un commentaire, nexttick utilisé car chargement asynchrone
        this.$nextTick( () => {

            if ( this.modeExe.trim() === "Création" ) {
                this.createForm();
            } else {
                // Set le sujet dès le onload pour la modification
                this.sujetID = $("span[name='sujet']").attr("id");
                this.fetchSujet();
            }

            this.stop = false;

        });
        
    }, 

    updated() {
        
        // Curseur par défaut
        $("body").css('cursor', 'default');
        
        // Le programme peut reprendre
        this.stop = false;

    }

});

