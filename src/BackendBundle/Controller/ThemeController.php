<?php

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
     *
     * @Route("/new/{univers}", name="theme_new", requirements={"univers"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function new(Request $request, Univers $univers)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors         = null;
        $theme          = new Theme();
        $universList    = $this->manager->getRepository(Univers::class)->findAll();
        $form           = $this->createForm('BackendBundle\Form\ThemeType', $theme);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                
                $theme->setUnivers($univers);
                $this->manager->persist($theme);
                $this->manager->flush();
                $this->addFlash('success', 'Un nouveau thème a été créé !');
                
                return $this->redirectToRoute('theme_edit', ['theme' => $theme->getId()]);

            }

            $errors = $this->get('validator')->validate($theme);

        }

        return $this->render('Admin/Theme/edit.html.twig', array(

            'theme'         => $theme,
            'form'          => $form->createView(),
            'errors'        => $errors,
            'universList'   =>  $universList

        ));

    }   

    /**
     * 
     * @Route("/edit/{theme}", name="theme_edit", requirements={"theme"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, Theme $theme = null)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors = null;
        $univers = $this->manager->getRepository(Univers::class)->findAll();
        $form = $this->createForm('BackendBundle\Form\ThemeType', $theme);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $this->manager->persist($theme);
                $this->manager->flush();
                $this->addFlash('success', 'Le thème a été modifié !');

            }

            $errors = $this->get('validator')->validate($theme);

        }

        return $this->render('Admin/Theme/edit.html.twig', array(

            'theme'  => $theme,
            'form'   => $form->createView(),
            'errors' => $errors,
            'universList'   =>  $univers

        ));

    }   

    /**
     * Deletes a univer entity.
     *
     * @Route("/{theme}", name="theme_delete")
     * @Method("POST")
     */
    public function deleteTheme(Request $request, Theme $theme)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }
        
        $message     = array();
        $universList = $this->manager->getRepository(Univers::class)->findAll();

        if ( "GET" === $request->getMethod() ) {

            $sections = $this->manager->getRepository(Section::class)->findBy([
                'theme' => $theme,
            ]);

            $sujets   = $this->manager->getRepository(Sujet::class)->findBy([
                'theme' => $theme,
            ]);

            if ( count($sections) > 0 || count($sujets) > 0 ) {
                $message[] = "Le thème est utilisé !";
            } else {
                $this->manager->remove($theme);
                $this->manager->flush();
            }

        } 

        return $this->render('Admin/controlForum.html.twig', [
            "message"   =>   $message,
            "universList" => $universList,
        ]);
    }

}
