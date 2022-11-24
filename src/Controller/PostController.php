<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        $posts = $this->em->getRepository(Post::class)->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(Post $id): Response
    {
        $post = $this->em->getRepository(Post::class)->find($id);
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
