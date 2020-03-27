Vue.filter('dateFR', function (value) {
                    
    if ( !value ) {

        return ''
        
    }

    var date = value.date.split(" ", 1).join();

    return date.split("-").reverse().join('/');
 
})

new Vue({
    
    el: '#app',
    delimiters: ['[[' , ']]'],
    data: {

        userAuthorised: false,
        userID:             0,
        input:           null,
        sujets:            [],

    } ,
    methods: {

        handleSearch: function (theme, section = null)  {

            /**
             * Sélection du filtre
             */ 
            var $select = $('select[name="select"]').prop('selectedIndex');

            /**
             * Curseur de chargement
             */
            $("body").css('cursor', 'wait');

            if ( null !== section ) {
                if ( null === this.input || "" === this.input.trim() ) {
                    var $url = Routing.generate('sujet_list_search_section', {
                        'theme': theme,
                        'section': section,
                        'option': $select
                    });
                } else  {
                    var $url = Routing.generate('sujet_list_search_section', {
                        'theme': theme,
                        'section': section, 
                        'libelle': this.input.trim(),
                        'option': $select
                    });
                }
            } else {
                if ( null === this.input || "" === this.input.trim() ) {
                    var $url = Routing.generate('sujet_list_search_theme', {
                        'theme': theme,
                        'option': $select
                    });
                } else  {
                    var $url = Routing.generate('sujet_list_search_theme', {
                        'theme': theme, 
                        'libelle': this.input.trim(),
                        'option': $select
                    });
                }
            }

            axios ({

                method: 'get',
                url: $url,
                responseType: 'json',
                
            }) 
            .then((response)  => { 

                if ( response.status === 200 || response.status === 0 ) {
                    /**
                     * Destruction de la table
                     */
                    $myTable = $('#myDataTable').DataTable();
                    $myTable.destroy();

                    /**
                     * Création de la table
                     */
                    this.sujets = response.data; 
                } 
               
            })
            .catch ( function (error) {
            
                console.log(error);
            
            })

        },

        redirectSubject: function ($id) {
              
            $url =  Routing.generate('post_view', {
                'sujet': $id,
            });

            window.location.href = $url;

        },

        editSubject: function($id) {

            $url =  Routing.generate('sujet_edit', {
                'sujet': $id,
            });

            window.location.href = $url;

        },

        deleteSubject: function ($id) {

            if ( !confirm("Voulez-vous supprimer rééllement supprimer ce sujet ?") ) {
                return;
            }

            /**
             * Curseur de chargement
             */
            $("body").css('cursor', 'wait');
         
            $url =  Routing.generate('sujet_delete', {
                'sujet': $id,
            }); 

            axios ({

                method: 'get',
                url: $url,
                responseType: 'json',
                
            }) 
            .then((response)  => { 

                if ( response.status === 200 || response.status === 0 ) {
                    /**
                     * Destruction de la table
                     */
                    $myTable = $('#myDataTable').DataTable();
                    $myTable.destroy();

                    /**
                     * Création de la table
                     */
                    this.sujets = response.data; 
                    alert("Sujet supprimé !");
                    this.refreshSubjectHandleInput();
                } 
               
            })
            .catch ( function (error) {
            
                console.log(error);
            
            })

        },

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

        refreshSubjectHandleInput: function() {

            var params = $("input").attr("id").split(",");
        
            if ( params.length > 1 ) {
                this.handleSearch(params[0], params[1]);
            } else {
                this.handleSearch(params[0]);
            }

        }

    }, 
    mounted() {

        // Acquisition des autorisations utilisateurs
        this.getAuthorised();

        // Recherche des sujets
        this.refreshSubjectHandleInput();

    },

    updated() {

        // Création de la table
        $myTable = $('#myDataTable').DataTable({
            "dom": '<"top"<"clear">>rt<"bottom"p<"clear">>',
            "language": {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
            },
            "retrieve": true
        });

        // Curseur par défaut
        $("body").css('cursor', 'default');
         
    }

});
