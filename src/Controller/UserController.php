<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="app_user")
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->find($this->getUser());

        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/edit", name="app_user_edit")
     */
    public function edit(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($this->getUser());

        $userForm = $this->createForm(UserFormType::class, $user);
        $userForm = $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if ($userForm->get('image')->getData()) {
                if ($user->getUrlImage()) {
                    unlink($this->getParameter('image_user_directory') . '/' . $user->getUrlImage());
                }

                $image = $userForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_user_directory'), $urlImage);
                $user->setUrlImage($urlImage);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le profil a été modifié');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/edit.html.twig', [
            'userForm' => $userForm->createView(),
        ]);
    }
}
