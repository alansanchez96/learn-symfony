<?php

namespace App\Controller;

use Exception;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_post_index')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post;
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        $allPosts = $this->em->getRepository(Post::class)->findAllPosts();

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $slug = str_replace(' ', '-', $form->get('title')->getData());
            $user = $this->em->getRepository(User::class)->find(1);

            if ($file) {
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);
                $newFilename = $safeFileName.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (\Throwable $th) {
                    throw new Exception("Error Processing Request", 1);
                }

                $post->setFile($newFilename);
            }

            $post->setSlug($slug);
            $post->setUser($user);

            $this->em->persist($post);
            $this->em->flush();

            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $allPosts
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(Post $id): Response
    {
        $post = $this->em->getRepository(Post::class)->find($id);           // Tambien existe findBy, findAll y findOneBy
        $post2 = $this->em->getRepository(Post::class)->findPost($id);      // Metodo creado desde el repositorio

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'post2' => $post2,
        ]);
    }

    #[Route('/crear-post', name: 'app_post_create')]
    public function create()
    {
        $post = new Post(
            'Nuevo titulo',
            'Esta es una descripcion',
            'opinion',
            'nuevo-titulo',
            'url.com'
        );
        $user = $this->em->getRepository(User::class)->find(1);
        $post->setUser($user);

        $this->em->persist($post);
        $this->em->flush();

        return new JsonResponse(['success' => 'El post fue creado correctamente'], 200);
    }

    #[Route('/actualizar-post', name: 'app_post_update')]
    public function update()
    {
        $post = $this->em->getRepository(Post::class)->find(3);
        $post->setTitle('Titulo actualizado');

        $this->em->flush();

        return new JsonResponse(['success' => 'El post fue actualizado correctamente'], 200);
    }

    #[Route('/eliminar-post', name: 'app_post_update')]
    public function destroy()
    {
        $post = $this->em->getRepository(Post::class)->find(3);
        $this->em->remove($post);

        $this->em->flush();

        return new JsonResponse(['success' => 'El post fue eliminado correctamente'], 200);
    }
}
