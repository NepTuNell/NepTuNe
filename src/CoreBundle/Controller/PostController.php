<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\Post;
use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use BackendBundle\Entity\Theme;
use BackendBundle\Entity\Univers;
use CoreBundle\Repository\PostRepository;
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
 * @Route("post")
 */
class PostController extends Controller
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
     * @Route("/view/{sujet}", name="post_view", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function view(Request $request, Sujet $sujet)
    {

        $universList = $this->manager->getRepository(Univers::class)->findAll();

        $post   = new Post();
        $form   = $this->createForm('CoreBundle\Form\PostType', $post);

        return $this->render('Post/list.html.twig', [
            'universList'   => $universList,
            'sujet'         => $sujet,
            'form'          => $form->createView()
        ]);

    }

    /**
     * @Route("/list/{sujet}", name="post_list", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function list(Request $request, Sujet $sujet)
    {

        $result = $this->manager->getRepository(Post::class)->fetchAll([
            'sujet' => $sujet,
        ]);

        $commentaires = json_encode($result);

        $response = new Response(
            $commentaires,
        );

        return $response;

    }

    /**
     * @Route("/new/commentaire/{sujet}", name="post_new", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"POST"})
     */
    public function new(Request $request, Sujet $sujet)
    {
        
        if ( isset($_POST['content']) && !empty($_POST['content']) ) {

            $post = new Post();
            $content = $_POST['content'];
            $user    = $this->get('security.token_storage')->getToken()->getUser();

            $post->setCommentaire($content);
            $post->setUser($user);
            $post->setSujet($sujet);
            $post->setDate(new \DateTime);

            $this->manager->persist($post);
            $this->manager->flush();
        
        }

        return $this->redirectToRoute('post_list', [
            'sujet' => $sujet->getId()
        ]);

    }

    /**
     * @Route("/new/commentaire/{sujet}/{post}", name="post_edit", options = {"expose" = true}, requirements={"sujet"="\d+", "post"="\d+"})
     * @Method({"POST"})
     */
    public function edit(Request $request, Sujet $sujet, Post $post = null)
    {
        
        if ( isset($_POST['content']) && !empty($_POST['content']) ) {

            $content = $_POST['content'];
            $user    = $this->get('security.token_storage')->getToken()->getUser();

            $post->setCommentaire($content);

            $this->manager->persist($post);
            $this->manager->flush();
        
        }

        return $this->redirectToRoute('post_list', [
            'sujet' => $sujet->getId()
        ]);

    }
     
}
