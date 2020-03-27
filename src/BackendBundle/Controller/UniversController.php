<?php

/**
 * author: CHU VAN Jimmy
 */

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
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
     * 
     * @var ObjectManager
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
     * Création d'un nouvel univers
     * 
     * @param Request
     * @Route("/new", name="univers_new")
     * @Method({"GET", "POST"})
     */
    public function newUnivers(Request $request)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors  = null;
        $univers = new Univers();
        $universList = $this->manager->getRepository(Univers::class)->findAll();
        $form = $this->createForm('BackendBundle\Form\UniversType', $univers);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $this->addFlash('success', 'Un nouvel univers a été créé !');
                $this->manager->persist($univers);
                $this->manager->flush();
                return $this->redirectToRoute('univers_edit', ['id' => $univers->getId()]);

            } 

            $errors = $this->get('validator')->validate($univers);

        }

        return $this->render('Admin/Univers/edit.html.twig', array(

            'universList'   => $universList,
            'univers'       => $univers,
            'form'          => $form->createView(),
            'errors'        => $errors

        ));

    }   

    /**
     * Modification d'un univers existant
     * 
     * @param Request
     * @param Univers
     * @Route("/edit/{id}", name="univers_edit")
     * @Method({"GET", "POST"})
     */
    public function editUnivers(Request $request, Univers $univers = null)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $errors      = null;
        $universList = $this->manager->getRepository(Univers::class)->findAll();
        $form        = $this->createForm('BackendBundle\Form\UniversType', $univers);
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                $this->addFlash('success', 'L\'univers a été modifié !');
                $this->manager->persist($univers);
                $this->manager->flush();

            } 

            $errors = $this->get('validator')->validate($univers);

        }

        return $this->render('Admin/Univers/edit.html.twig', array(

            'universList'   => $universList,
            'univers'       => $univers,
            'form'          => $form->createView(),
            'errors'        => $errors

        ));

    }   

    /**
     * Suppression d'un univers
     *
     * @param Request
     * @param Univers
     * @Route("/{id}", name="univers_delete")
     * @Method("POST")
     */
    public function deleteUnivers(Request $request, Univers $univers)
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');
        }
        
        $universList = $this->manager->getRepository(Univers::class)->findAll();

        if ( "POST" === $request->getMethod() ) {

            $this->manager->remove($univers);
            $this->manager->flush();

        } 

        return $this->redirectToRoute('admin_control_forum');

    }

}
