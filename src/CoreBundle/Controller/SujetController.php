<?php

/**
 * author: CHU VAN Jimmy 
 */

namespace CoreBundle\Controller;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use BackendBundle\Entity\Theme;
use BackendBundle\Entity\Section;
use BackendBundle\Entity\Univers;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * 
 * Theme controller.
 *
 * @Route("sujet")
 */
class SujetController extends Controller
{

    /**
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
     */
    private $manager;

    /**
     * Constructeur de la classe.
     * 
     * @param ObjectManager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Retourne la liste des sujets pour un thème ou une section selectionnée.
     * 
     * @param Theme
     * @param Section
     * @Route("/liste/{theme}", name="sujet_list_theme", requirements={"theme"="\d+"})
     * @Route("/liste/{theme}/{section}", name="sujet_list_section", requirements={"theme"="\d+", "section"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function listSujet(Theme $theme, Section $section = null)
    {
    
        $univerList = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Sujet/list.html.twig', [

            'universList'   =>  $univerList,
            'theme'         =>  $theme,
            'section'       =>  $section
        
        ]);

    }

    /**
     * Fonction utlisée pour filtrer les sujets.
     * 
     * @param Theme
     * @param Section
     * @param string
     * @param int
     * @Route("/liste/filtre/theme/{theme}/{option}/{libelle}", name="sujet_list_search_theme", options = {"expose" = true}, requirements={"theme"="\d+"})
     * @Route("/liste/filtre/theme/{theme}/section/{section}/{option}/{libelle}", name="sujet_list_search_section", options = {"expose" = true}, requirements={"theme"="\d+","section"="\d+"})
     * @Method({"GET"})
     */
    public function searchSujet(Theme $theme, Section $section = null, $libelle = null, $option)
    {
        
        /**
         * Mise à l'arrêt du programme 
         */
        sleep(1);

        if ( null !== $libelle && "" !== $libelle ) {

            if ( null !== $section ) {

                switch ( $option )
                {

                    case 0:

                        $result = $this->manager->getRepository(Sujet::class)->fetchSubjectByLibelleBegin([
                            'section'     => $section,
                            'libelle'     => $libelle,
                        ]);

                    break;

                    case 1:

                        $result = $this->manager->getRepository(Sujet::class)->fetchSubjectByLibelleContains([
                            'section'     => $section,
                            'libelle'     => $libelle,
                        ]);

                    break;
                }

            } else {

                switch ( $option )
                {

                    case 0:

                        $result = $this->manager->getRepository(Sujet::class)->fetchSubjectByLibelleBegin([
                            'theme'     => $theme,
                            'libelle'   => $libelle,
                        ]);

                    break;

                    case 1:

                        $result = $this->manager->getRepository(Sujet::class)->fetchSubjectByLibelleContains([
                            'theme'     => $theme,
                            'libelle'   => $libelle,
                        ]);

                    break;

                }

            }
     
        } else {

            if ( null !== $section ) {
                
                $result = $this->manager->getRepository(Sujet::class)->fetchAllSubject([
                    'section' => $section,
                ]);

            } else {

                $result = $this->manager->getRepository(Sujet::class)->fetchAllSubject([
                    'theme' => $theme,
                ]);

            }

        }

        $sujets = json_encode($result);

        /**
         * Envoie de la réponse en format JSON
         */
        $response = new Response($sujets);

        return $response;

    }

    /**
     * Création d'un sujet
     * 
     * @param Request
     * @param Theme
     * @param Section
     * @Route("/new/{theme}", name="sujet_new_theme", options = {"expose" = true}, requirements={"theme"="\d+"})
     * @Route("/new/{theme}/{section}", name="sujet_new_section", options = {"expose" = true}, requirements={"theme"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function newSujet(Request $request, Theme $theme, Section $section = null)
    {
         
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        $user    = $this->get('security.token_storage')->getToken()->getUser();
        $univers = $this->manager->getRepository(Univers::class)->findAll(); 
        $errors  = null;
        $sujet   = new Sujet();  

        if ( "POST" === $request->getMethod() ) {
             
            if ( null !== $section ) {
             
                $sujet->setSection($section);
    
            } 

            $sujet->setLibelle($request->request->get('content'));
            $sujet->setTheme($theme);
            $sujet->setDate(new \DateTime);
            $sujet->setUser($user);
            $this->manager->persist($sujet);
            $this->manager->flush();
            
            $response = new Response(json_encode($sujet->getId()));

            return $response;

        }

        return $this->render('Sujet/edit.html.twig', array(

            'modeExe'       => 'Création',
            'theme'         =>  $theme,
            'section'       =>  $section,
            'universList'   =>  $univers,

        ));

    }   

    /**
     * Modification d'un sujet existant
     * 
     * @param Request
     * @param Sujet
     * @Route("/edit/{sujet}", name="sujet_edit", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function editSujet(Request $request, Sujet $sujet)
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        $user    = $this->get('security.token_storage')->getToken()->getUser();
        $univers = $this->manager->getRepository(Univers::class)->findAll(); 

        if ( "POST" === $request->getMethod() ) {

            $sujet->setLibelle($request->request->get('content'));
            $this->manager->persist($sujet);
            $this->manager->flush();
            
            $response = new Response(json_encode($sujet->getId()));

            return $response;

        }

        return $this->render('Sujet/edit.html.twig', array(

            'modeExe'       => 'Modification',
            'sujet'         =>  $sujet,
            'theme'         =>  $sujet->getTheme(),
            'universList'   =>  $univers,

        ));

    }   

    /**
     * Suppression d'un sujet
     * 
     * @param Request
     * @param Sujet
     * @Route("/{sujet}", name="sujet_delete", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method("GET")
     */
    public function deleteSujet(Request $request, Sujet $sujet)
    {

        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }
       
        $theme   = $sujet->getTheme();
        $section = $sujet->getSection();

        if ( "GET" === $request->getMethod() ) {

            $this->manager->remove($sujet);
            $this->manager->flush();

        } 

        if ( null !== $section ) {

            return $this->redirectToRoute('sujet_list_section', [

                'theme'=> $theme->getId(), 
                'section' => $section->getId()

            ]);

        }

        return $this->redirectToRoute('sujet_list_theme', ['theme'=> $theme->getId()]);

    }

    /**
     * Retourne le titre d'un sujet
     * 
     * @param Request
     * @param Sujet
     * @Route("/fetch/{sujet}", name="sujet_fetch", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function fetchSujet(Request $request, Sujet $sujet)
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }
            
        $response = new Response(json_encode($sujet->getLibelle()));

        return $response;

    }   

}
