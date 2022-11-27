<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/register', name: 'user_register')]
    public function user_register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User;

        $formRegister = $this->createForm(UserType::class, $user);
        $formRegister->handleRequest($request);

        if ($formRegister->isSubmitted() && $formRegister->isValid()) {

            $plainTextPassword = $formRegister->get('password')->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainTextPassword
            );

            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('user_register');
        }

        return $this->render('user/register.html.twig', [
            'formRegister' => $formRegister->createView()
        ]);
    }
}
