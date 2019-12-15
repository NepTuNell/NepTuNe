<?php

namespace BackendBundle\Controller;

use CoreBundle\Entity\User;
use BackendBundle\Entity\Theme;
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
 * @Route("theme")
 */
class ThemeController extends Controller
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
     * @Route("/new/{univers}", name="theme_new", requirements={"univers"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function new(Request $request, Univers $univers)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $theme = new Theme();
        $form  = $this->createForm('BackendBundle\Form\ThemeType', $theme);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                
                $theme->setUnivers($univers);
                $this->manager->persist($theme);
                $this->manager->flush();
                $this->addFlash('success', 'Un nouveau a été créé !');
                
                return $this->redirectToRoute('theme_edit', ['theme' => $theme->getId()]);

            }

            $errors = $this->get('validator')->validate($theme);

            return $this->render('Admin/Theme/edit.html.twig', array(

                'theme' => $theme,
                'form' => $form->createView(),
                'errors' => $errors

            ));

        }

        return $this->render('Admin/Theme/edit.html.twig', array(

            'theme' => $theme,
            'form' => $form->createView(),

        ));

    }   

    /**
     * Creates a new univer entity.
     *
     * @Route("/edit/{theme}", name="theme_edit", requirements={"theme"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, Theme $theme = null)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $form = $this->createForm('BackendBundle\Form\ThemeType', $theme);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $this->manager->persist($theme);
                $this->manager->flush();
                $this->addFlash('success', 'Le thème a été modifié !');

                return $this->render('Admin/Theme/edit.html.twig', array(

                    'theme'  => $theme,
                    'form'   => $form->createView(),
        
                ));

            }

            $errors = $this->get('validator')->validate($theme);

            return $this->render('Admin/Theme/edit.html.twig', array(

                'theme'  => $theme,
                'form'   => $form->createView(),
                'errors' => $errors

            ));
        
        }

        return $this->render('Admin/Theme/edit.html.twig', array(

            'theme'  => $theme,
            'form'   => $form->createView(),

        ));

    }   

    /**
     * Deletes a univer entity.
     *
     * @Route("/{theme}", name="theme_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, Theme $theme)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }
       
        if ( "GET" === $request->getMethod() ) {

            $this->manager->remove($theme);
            $this->manager->flush();

        } 

        return $this->redirectToRoute('admin_show_entity');
    }

}
