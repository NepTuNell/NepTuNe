 
$('document').ready( function ( ) {

    var container = document.getElementById('NepTuNe');

    if ( !container ) {
        return;
    }

    //document.addEventListener('mousemove', followCamera, false);
    init();
    animate();

    function init() {

        //////////////////////////////////////////////////////
        //           CODE D'INITIALISATION DE BASE
        //////////////////////////////////////////////////////

        // on initialise le moteur de rendu
        renderer = new THREE.WebGLRenderer( { alpha: true, antialias: true} );
        renderer.setSize( window.innerWidth,  window.innerHeight, false);
        renderer.setClearColor( 0xffffff, 0 );
        container.appendChild(renderer.domElement);

        // Ombrage
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        // on initialise la scène
        scene = new THREE.Scene();
        scene.add( new THREE.AmbientLight( 0xffffff ) );

        // on initialise la camera que l’on place ensuite sur la scène
        camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 1, 1000);
        scene.add(camera);

        //////////////////////////////////////////////////////
        //              CREATION LUMIERE
        //////////////////////////////////////////////////////

        var light = new THREE.SpotLight(  0xffffff );
        light.position.x = 0 - ((window.innerWidth*8) / 100) - 64;
        light.position.y = 0 + ((window.innerHeight*4) / 100);
        light.position.z = 32;
        light.castShadow = true;
        light.shadow.mapSize.width = 256;
        light.shadow.mapSize.height = 100;
        scene.add(light);

        //////////////////////////////////////////////////////
        //              CREATION PLANETE
        //////////////////////////////////////////////////////

        // Chargement des textures / Positionnement
	    var textureNept = new THREE.TextureLoader().load( "../../js/textures/2k_neptune.jpg" );
        textureNept.wrapS = THREE.RepeatWrapping;
        textureNept.wrapT = THREE.RepeatWrapping;
        textureNept.repeat.set( 1, 1 );

        // Texture NepTuNe
        var textureAst = new THREE.TextureLoader().load( "../../js/textures/2k_mercury.jpg" ); // Chargement des textures
        textureAst.wrapS = THREE.RepeatWrapping;
        textureAst.wrapT = THREE.RepeatWrapping;
        textureAst.repeat.set( 1, 1 );

        // Création de neptune
        var geometryNeptune = new THREE.SphereGeometry(  16, 256, 256  );
        var materialNeptune = new THREE.MeshLambertMaterial( { map: textureNept } );
        meshNeptune = new THREE.Mesh( geometryNeptune, materialNeptune );
        meshNeptune.receiveShadow = true;

        // Positionnement de NepTuNe
        meshNeptune.position.x = 0 - ((window.innerWidth*8) / 100);
        meshNeptune.position.y = 0 + ((window.innerHeight*4) / 100);
        meshNeptune.position.z = -50;

        // Bounding box pour collision
        var neptBox = new THREE.Box3().setFromObject(meshNeptune);

        // Ajout à la scene
        scene.add( meshNeptune );

        //////////////////////////////////////////////////////
        //         CREATION POINT DE PIVOT POUR LUNE
        //////////////////////////////////////////////////////
        
        pivotPoint = new THREE.Object3D();
        pivotPoint.position.x = meshNeptune.position.x;  
        pivotPoint.position.y = meshNeptune.position.y;
        pivotPoint.position.z = meshNeptune.position.z;
        meshNeptune.add(pivotPoint); // Ajout du pivot au centre de NepTuNe
        
        //////////////////////////////////////////////////////
        //                  CREATION LUNE
        //////////////////////////////////////////////////////

        asteroidArray = [];

        for ( i = 1; i < 15; i++) {

            collision = true; 

            // création de l'astéroide
            var geometryAsteroid = new THREE.SphereGeometry( (Math.random()+0.1), 8, 8 );
            var materialAsteroid = new THREE.MeshLambertMaterial( { map: textureAst } );
            var meshAsteroid     = new THREE.Mesh( geometryAsteroid, materialAsteroid );
                
            while ( collision === true ) {

                // Positionnement
                meshAsteroid.position.y = meshNeptune.position.y + (Math.random() * 32) * 2 - 32;
                meshAsteroid.position.x = meshNeptune.position.x + (Math.random() * 32) * 2 - 32;
                meshAsteroid.position.z = meshNeptune.position.z + (Math.random() * 32) * 2 - 32;
               
                // Bounding box pour collision
                var astBox = new THREE.Box3().setFromObject(meshAsteroid);

                if ( intersect(astBox, neptBox) ) {

                    continue;

                }
                
                // test collision
                collision = false;
 
            }
                   
            
            // Sauvegarde dans array de l'objet
            asteroidArray[i] = meshAsteroid;

            // Ajout à la scène
            scene.add(asteroidArray[i])
            
            // Ajout de la lune sur le point de pivot de NepTuNe
            pivotPoint.attach(asteroidArray[i]);
        
        }
         
        //////////////////////////////////////////////////////
        //                CREATION ETOILES
        //////////////////////////////////////////////////////
        starsArray = [];

        for ( i = 1; i < 100; i++) {

            // création de l'étoile
            var geometryStars = new THREE.SphereGeometry( Math.random()+0.01, Math.random()+0.01);
            var materialStars = new THREE.MeshLambertMaterial( { color: 0xffffff, wireframe: false, transparent: false, opacity: 1 } ); 
            meshStars = new THREE.Mesh( geometryStars, materialStars );

            // Positionnement
            var yAxis = (Math.random() * 400) * 2 - 400;
            var xAxis = (Math.random() * 400) * 2 - 400;
            var zAxis = -150;

            meshStars.position.y  = yAxis;
            meshStars.position.x  = xAxis;
            meshStars.position.z  = zAxis;
                   
            // Ajout à la scène
            scene.add(meshStars);

            // Sauvegarde dans array de l'objet
            starsArray[i] = meshStars;
        
        }

        // Positionnement caméra
        camera.position.z = 150;
        camera.lookAt( scene.position );

    }

    function intersect(a, b) {
        
        return (a.min.x <= b.max.x && a.max.x >= b.min.x) &&
               (a.min.y <= b.max.y && a.max.y >= b.min.y) &&
               (a.min.z <= b.max.z && a.max.z >= b.min.z);

    }

    function animate() {

        requestAnimationFrame( animate );
        meshNeptune.rotation.y += 0.001;

        for ( i = 1; i < 15; i++ ) {

            asteroidArray[i].rotation.y += 0.01;    
            
        }

        renderer.render( scene, camera );

    }

    function followCamera(event) {

        var mouseX = event.clientX - window.innerWidth/2;
        var mouseY = event.clientY - window.innerHeight/2;

        camera.position.x += (mouseX - camera.position.x)*0.0005;
        camera.position.y += (mouseY - camera.position.y)*0.0005;
        camera.position.z = 250;
        camera.lookAt( scene.position )
        renderer.render( scene, camera );

    }

    window.addEventListener('resize', () => {
        let width = window.innerWidth
        let height = window.innerHeight
        renderer.setSize(width, height)
        camera.aspect = width / height
        camera.updateProjectionMatrix()
    })

});
    
 