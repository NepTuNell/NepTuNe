<?php

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
     * @var User
     */
    private $user;

    /**
     * @var ObjectManager
     */
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     *
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
     * @Route("/liste/filtre/theme/{theme}/{libelle}", name="sujet_list_search_theme", options = {"expose" = true}, requirements={"theme"="\d+"})
     * @Route("/liste/filtre/theme/{theme}/section/{section}/{libelle}", name="sujet_list_search_section", options = {"expose" = true}, requirements={"theme"="\d+","section"="\d+"})
     * @Method({"GET"})
     */
    public function searchSujet(Theme $theme, Section $section = null, $libelle = null)
    {
        
        if ( null !== $libelle && "" !== $libelle ) {

            if ( null !== $section ) {

                $result=$this->manager->getRepository(Sujet::class)->fetchSubjectByLibelle([
                    'section'     => $section,
                    'libelle'     => $libelle,
                ]);

            } else {

                $result=$this->manager->getRepository(Sujet::class)->fetchSubjectByLibelle([
                    'theme'     => $theme,
                    'libelle'   => $libelle,
                ]);

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

        $response = new Response(
            $sujets,
        );

        return $response;

    }

    /**
     * Creates a new theme entity.
     *
     * @Route("/new/{theme}", name="sujet_new_theme", requirements={"theme"="\d+"})
     * @Route("/new/{theme}/{section}", name="sujet_new_section", requirements={"theme"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function new(Request $request, Theme $theme, Section $section = null)
    {
         
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        $errors = null;
        $sujet  = new Sujet();
        $form   = $this->createForm('CoreBundle\Form\SujetType', $sujet);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                if ( null !== $section ) {

                    $sujet->setSection($section);
    
                } 

                $sujet->setTheme($theme);
                $sujet->setDate(new \DateTime);
                $this->manager->persist($sujet);
                $this->manager->flush();
                $this->addFlash('success', 'Une nouveau sujet a été créé !');
                
                return $this->redirectToRoute('sujet_edit', ['sujet' => $sujet->getId()]);

            }

            $errors = $this->get('validator')->validate($sujet);

        }

        return $this->render('Sujet/edit.html.twig', array(

            'form'      =>  $form->createView(),
            'theme'     =>  $theme,
            'section'   =>  $section,
            'errors'    =>  $errors

        ));

    }   

    /**
     * Creates a new univer entity.
     *
     * @Route("/edit/{sujet}", name="sujet_edit", requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, Sujet $sujet)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors   = null;
        $section  = $sujet->getSection();
        $theme    = $sujet->getTheme();
        $form     = $this->createForm('CoreBundle\Form\SujetType', $sujet);

        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $this->manager->persist($sujet);
                $this->manager->flush();
                $this->addFlash('success', 'Le sujet a été modifié !');

                return $this->render('Sujet/edit.html.twig', array(

                    'form'   => $form->createView(),
        
                ));

            }

            $errors = $this->get('validator')->validate($sujet);

        }

        return $this->render('Sujet/edit.html.twig', array(

            'form'      =>  $form->createView(),
            'theme'     =>  $theme,
            'section'   =>  $section,
            'errors'    =>  $errors

        ));

    }   

    /**
     * Deletes a univer entity.
     *
     * @Route("/{sujet}", name="sujet_delete", requirements={"sujet"="\d+"})
     * @Method("GET")
     */
    public function deleteAction(Request $request, Sujet $sujet)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
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

}
