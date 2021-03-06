<?php

/**
 * author: Jimmy
 */

namespace BackendBundle\Controller;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use BackendBundle\Entity\Theme;
use BackendBundle\Entity\Section;
use BackendBundle\Entity\Univers;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * 
 * Theme controller.
 *
 * @Route("section")
 */
class SectionController extends Controller
{

    /**
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
     * 
     * @var ObjectManager
     */
    private $manager;

    /**
     * Constructeur de la classe
     * 
     * @param ObjectManager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Création d'une nouvelle section
     * 
     * @param Request
     * @param Theme
     * @Route("/new/{theme}", name="section_new", requirements={"theme"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function newSection(Request $request, Theme $theme)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors         = null;
        $universList    = $this->manager->getRepository(Univers::class)->findAll();
        $section        = new Section();
        $form           = $this->createForm('BackendBundle\Form\SectionType', $section);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                
                $section->setTheme($theme);
                $this->manager->persist($section);
                $this->manager->flush();
                $this->addFlash('success', 'Une nouvelle section a été créée !');
                
                return $this->redirectToRoute('section_edit', ['section' => $section->getId()]);

            }

            $errors = $this->get('validator')->validate($theme);

        }

        return $this->render('Admin/Section/edit.html.twig', array(

            'form' => $form->createView(),
            'universList' => $universList,
            'errors' => $errors

        ));

    }   

    /**
     * Modification d'une section existante
     *
     * @param Request
     * @param Section
     * @Route("/edit/{section}", name="section_edit", requirements={"section"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function editSection(Request $request, Section $section)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors = null;
        $universList = $this->manager->getRepository(Univers::class)->findAll();
        $form = $this->createForm('BackendBundle\Form\SectionType', $section);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $this->manager->persist($section);
                $this->manager->flush();
                $this->addFlash('success', 'La section a été modifiée !');

            }

            $errors = $this->get('validator')->validate($section);

        }

        return $this->render('Admin/Section/edit.html.twig', array(

            'form'          => $form->createView(),
            'errors'        => $errors,
            'universList'   => $universList,

        ));

    }   

    /**
     * Suppression d'une section
     * 
     * @param Request
     * @param Section
     * @Route("/delete/{section}", name="section_delete")
     * @Method("POST")
     */
    public function deleteSection(Request $request, Section $section)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }
        
        $message     = array();
        $universList = $this->manager->getRepository(Univers::class)->findAll();
        
        if ( "GET" === $request->getMethod() ) {

            $sujets   = $this->manager->getRepository(Sujet::class)->findBy([
                'section' => $section,
            ]);

            if ( count($sujets) > 0 ) {
                $message[] = "La section est utilisée !";
            } else {
                $this->manager->remove($section);
                $this->manager->flush();
            }

        } 

        return $this->render('Admin/controlForum.html.twig', [
            "message"   =>   $message,
            "universList" => $universList,
        ]);

    }

    /**
     * Cette fonction retourne la liste des sections
     * 
     * @param Theme
     * @Route("/liste/{theme}", name="section_list")
     * @Method("GET")
     */
    public function listSection(Theme $theme)
    {
        $universList  = $this->manager->getRepository(Univers::class)->findAll();
        $sectionsList = $this->manager->getRepository(Section::class)->findBy([
            'theme' => $theme,
        ]);

        return $this->render('Section/list.html.twig', [
            'sectionList' => $sectionsList,
            'universList' => $universList,
        ]);

    }

}
