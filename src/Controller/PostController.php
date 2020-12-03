<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Services\FileUploader;
use App\Services\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{

    /**
     * @Route("/", name="index")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {

//        $session = new Session();
//        $session->start();

        $posts = $postRepository->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param Notification $notification
     * @return Response
     */
    public function create(Request $request, FileUploader $fileUploader, Notification $notification)
    {
        // create a new post with a title
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        $form->getErrors();

        if ($form->isSubmitted()) {
            //entity manager

            $em = $this->getDoctrine()->getManager();

            /** @var UploadedFile $file */
            $file = $request->files->get('post')['attachment'];
            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $post->setImage($filename);
                $em->persist($post);
                $em->flush();
            }


            return $this->redirect($this->generateUrl('post.index'));
        }


        //return a response
        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/show/{id}", name="show")
     * @param Post $post
     * @return Response
     */
    public function show(Post $post)
    {
        //^Can be done also like: $post = $postRepository->find($id), with arguments ($id, PostRepository $postRepository)

        //create show view
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Post $post
     * @return RedirectResponse
     */
    public function remove(Post $post)
    {


        $em = $this->getDoctrine()->getManager();

        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post was removed');

        return $this->redirect($this->generateUrl('post.index'));
    }
}
