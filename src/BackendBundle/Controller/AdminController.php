<?php

namespace BackendBundle\Controller;

use CoreBundle\Entity\User;
use BackendBundle\Entity\Theme;
use BackendBundle\Entity\Univers;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * 
 * Theme controller.
 *
 * @Route("admin")
 */
class AdminController extends Controller
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
     * Lists all entities of BackendBundle.
     *
     * @Route("/show", name="admin_show_entity")
     * @Method("GET")
     */
    public function indexAction()
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $data = new ArrayCollection();
        $universList = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Admin/show.html.twig', array(

            'universList' => $universList, 
            
        ));

    }

}
