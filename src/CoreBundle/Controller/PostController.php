<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\Post;
use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use CoreBundle\Entity\Picture;
use BackendBundle\Entity\Theme;
use CoreBundle\Entity\PostLike;
use BackendBundle\Entity\Univers;
use CoreBundle\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $data = array();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $result = $this->manager->getRepository(Post::class)->fetchAll([
            'sujet' => $sujet,
            'user'  => $user
        ]);
        
        foreach ( $result as $post ) {

            $pictures = $this->manager->getRepository(Picture::class)->fetchByPost($post['id']);
            
            $elem = [
                
                'comment'  =>  $post,
                'pictures' =>  $pictures,
                 
            ]; 
            
            array_push($data, $elem);

        }
     
        $commentaires = json_encode($data);

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
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        /**
         * Pause d'une seconde
         */
        sleep(1);

        if ( isset($_POST['content']) && !empty($_POST['content']) ) {

            $post    = new Post();
            $content = $_POST['content'];
            $user    = $this->get('security.token_storage')->getToken()->getUser();

            /**
             * Création du commentaire
             */
            $post->setCommentaire($content);
            $post->setUser($user);
            $post->setSujet($sujet);
            $post->setDate(new \DateTime);

            $this->manager->persist($post);
            $this->manager->flush();
            $this->manager->refresh($post);

            for ( $i = 0; $i < count($_FILES); $i++ ) {

                if (  2000000 < $_FILES['files'.$i]['size'] ) {

                    $response = new Response(
                        Response::HTTP_PARTIAL_CONTENT,
                    );

                    return $response;

                }

                $picture = new Picture($_FILES['files'.$i], $post);
                $this->manager->persist($picture);
                $this->manager->flush();

            }

            $response = new Response(
                Response::HTTP_OK,
            );
        
        } else {

            $response = new Response(
                Response::HTTP_NOT_FOUND,
            );

        }

        return $response;

    }

    /**
     * @Route("/edit/commentaire/{sujet}/{post}", name="post_edit", options = {"expose" = true}, requirements={"sujet"="\d+", "post"="\d+"})
     * @Method({"POST"})
     */
    public function edit(Request $request, Sujet $sujet, Post $post = null)
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        /**
         * Pause d'une seconde
         */
        sleep(1);
        
        if ( isset($_POST['content']) && !empty($_POST['content']) ) {

            $content  = $_POST['content'];
            $nbFile   = 0;

            /**
             * Modification du commentaire
             */
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $post->setCommentaire($content);
            $this->manager->persist($post);
            $this->manager->flush();
            
            for ( $i = 0; $i < count($_FILES); $i++ ) {

                if (  2000000 < $_FILES['files'.$i]['size'] ) {

                    $response = new Response(
                        Response::HTTP_PARTIAL_CONTENT,
                    );

                    return $response;

                }

                $picture = new Picture($_FILES['files'.$i], $post);
                $this->manager->persist($picture);
                $this->manager->flush();

            }
            
            $response = new Response(
                Response::HTTP_OK,
            );
        
        } else {

            $response = new Response(
                Response::HTTP_NOT_FOUND,
            );
        }

        return $response;

    }

    /**
     * @Route("/delete/commentaire", name="post_delete", options={"expose"=true}, requirements={"post"="\d+"})
     * @Method({"POST"})
     */
    public function delete(Request $request)
    {

        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        /**
         * Pause d'une seconde
         */
        sleep(1);
        
        if ( isset($_POST['postID']) && !empty($_POST['postID']) ) {

            $post = $this->manager->getRepository(Post::class)->find($_POST['postID']);

            if ( null !== $post) {

                $this->manager->remove($post);
                $this->manager->flush();

                $response = new Response(
                    Response::HTTP_OK,
                );

            } else {

                $response = new Response(
                    Response::HTTP_NOT_FOUND,
                );

            }

        } else {

            $response = new Response(
                Response::HTTP_NOT_FOUND,
            );
        }

        return $response;

    }

    /**
     * @Route("delete/picture/{id}", options={"expose"=true}, name="picture_delete", methods={"GET"})
     */
    public function deletePicture(Picture $picture)
    {

        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Veuillez vous connecter !');

        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->manager->remove($picture);
        $this->manager->flush();

        $response = new Response(
            Response::HTTP_OK,
        );

        return $response;

    }
     
    /**
     * @Route("/like/{user}/{post}/{param}", name="post_like", options = {"expose" = true}, methods={"GET"})
     */
    public function like(User $user, Post $post, $param)
    {
        
        $exist = true;
        $postLike = $this->manager->getRepository(PostLike::class)->findOneBy([

            'userWhoLiked'  => $user,
            'post'          => $post,

        ]);   

        if ( !$postLike ) {
                    
            $postLike = new PostLike();
            $exist    = false;

        } 

        switch ($param) {

            case 1:
                
                if ( $exist && true === $postLike->getLike() ) {
 
                    $this->manager->remove($postLike);

                } else {

                    $postLike->setLike(true);
                    $this->manager->persist($postLike);

                }

            break;

            case 2:
                
                if ( $exist && false === $postLike->getLike() ) {

                    $this->manager->remove($postLike);
                         
                } else {

                    $postLike->setLike(false);
                    $this->manager->persist($postLike);
                    
                }

            break;

            default:
                
                if ( $exist ) {

                    $this->manager->remove($postLike);

                }

            break;
        }

        if ( !$exist ) {

            $postLike->setPost($post);
            $postLike->setUserWhoLiked($user);
            $postLike->setUserConcerned($post->getUser());
            $this->manager->persist($postLike);

        }
        
        $this->manager->flush();

        $response = new Response(
            Response::HTTP_OK,
        );

        return $response;

    }

    /**
     * @Route("/count/{sujet}", name="post_count", options = {"expose" = true}, methods={"GET", "POST"})
     */
    public function countPost(Sujet $sujet)
    {
        $result = $this->manager->getRepository(Sujet::class)->countPostQuery3($sujet);

        $response = new Response(
            json_encode($result)
        );

        return $response;
    }

}
