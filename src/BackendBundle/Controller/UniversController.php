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
 * Univer controller.
 *
 * @Route("univers")
 */
class UniversController extends Controller
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
     * Creates a new univer entity.
     * 
     * @Route("/new", name="univers_new")
     * @Route("/edit/{id}", name="univers_edit")
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, Univers $univers = null, Theme $theme = null)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        if ( !$univers ) {

            $univers = new Univers();

        }

        $form = $this->createForm('BackendBundle\Form\UniversType', $univers);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                if ( null === $univers->getId() ) {

                    $this->addFlash('success', 'Un nouvel univers a été créé !');
                
                } else {

                    $this->addFlash('success', 'L\'univers a été modifié !');

                }

                $this->manager->persist($univers);
                $this->manager->flush();

                return $this->redirectToRoute('admin_show_entity');

            } 

            $errors = $this->get('validator')->validate($univers);

            return $this->render('Admin/Univers/edit.html.twig', array(

                'univers' => $univers,
                'form' => $form->createView(),
                'errors' => $errors

            ));

        }

        return $this->render('Admin/Univers/edit.html.twig', array(

            'univers' => $univers,
            'form' => $form->createView(),

        ));

    }   

    /**
     * Deletes a univer entity.
     *
     * @Route("/{id}", name="univers_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, Univers $univers)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }

        if ( "POST" === $request->getMethod() ) {

            $this->manager->remove($univers);
            $this->manager->flush();

        } 

        return $this->redirectToRoute('admin_show_entity');
    }

}
