<?php

namespace BackendBundle\Controller;

use CoreBundle\Entity\User;
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
     * Creates a new theme entity.
     *
     * @Route("/new/{theme}", name="section_new", requirements={"theme"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function new(Request $request, Theme $theme)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $section = new Section();
        $form  = $this->createForm('BackendBundle\Form\SectionType', $section);
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

            return $this->render('Admin/Section/edit.html.twig', array(

                'form' => $form->createView(),
                'errors' => $errors

            ));

        }

        return $this->render('Admin/Section/edit.html.twig', array(

            'form' => $form->createView(),

        ));

    }   

    /**
     * Creates a new univer entity.
     *
     * @Route("/edit/{section}", name="section_edit", requirements={"section"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, Section $section)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $form = $this->createForm('BackendBundle\Form\SectionType', $section);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $this->manager->persist($section);
                $this->manager->flush();
                $this->addFlash('success', 'La section a été modifiée !');

                return $this->render('Admin/Section/edit.html.twig', array(

                    'form'   => $form->createView(),
        
                ));

            }

            $errors = $this->get('validator')->validate($section);

            return $this->render('Admin/Section/edit.html.twig', array(

                'form'      => $form->createView(),
                'errors'    => $errors

            ));
        
        }

        return $this->render('Admin/Section/edit.html.twig', array(

            'form'   => $form->createView(),

        ));

    }   

    /**
     * Deletes a univer entity.
     *
     * @Route("/delete/{section}", name="section_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, Section $section)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }
       
        if ( "GET" === $request->getMethod() ) {

            $this->manager->remove($section);
            $this->manager->flush();

        } 

        return $this->redirectToRoute('admin_show_entity');
    }

    /**
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
