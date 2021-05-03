<?php

namespace App\Controller;

use App\Form\UserEditFormType;
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

        $userForm = $this->createForm(UserEditFormType::class, $user);
        $userForm = $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if ($userForm->get('image')->getData()) {
                if ($user->getUrlImage() && !preg_match('#'.'http'.'#', $user->getUrlImage())) {
                    unlink($this->getParameter('image_user_directory') . '/' . $user->getUrlImage());
                }

                $image = $userForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_user_directory'), $urlImage);
                $user->setUrlImage($urlImage);
            }
            // TODO gérer erreur losque pseudo ou email est null. Constraints NotNull ne marche pas

            $entityManager->flush();
            $this->addFlash('success', 'Le profil a été modifié');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/edit.html.twig', [
            'userForm' => $userForm->createView(),
        ]);
    }
}
