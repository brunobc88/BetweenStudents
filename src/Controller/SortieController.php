<?php

namespace App\Controller;

use App\Entity\CommentaireSortie;
use App\Entity\Sortie;
use App\Entity\SortieCommentaire;
use App\Entity\SortieImage;
use App\Form\SearchSortieFormType;
use App\Form\SortieDeleteFormType;
use App\Form\SortieFormType;
use App\Repository\EtatRepository;
use App\Repository\SortieCommentaireRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Services\SearchSortie;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {
        $searchSortie = new SearchSortie();
        $searchSortie->page = $request->get('page', 1);

        $searchSortieFormType = $this->createForm(SearchSortieFormType::class, $searchSortie);
        $searchSortieFormType->handleRequest($request);

        $tableauSorties = $sortieRepository->findSearchSortiePaginate($searchSortie);
        $nbreResultats = count($sortieRepository->findSearchSortie($searchSortie));

        return $this->render('sortie/index.html.twig', [
            'searchSortieFormType' => $searchSortieFormType->createView(),
            'tableauSorties' => $tableauSorties,
            'nbreResultats' => $nbreResultats,
        ]);
    }

    /**
     * @Route("/sortie/{id}/detail", name="app_sortie_detail")
     */
    public function detail(int $id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, SortieCommentaireRepository $sortieCommentaireRepository, UserRepository $userRepository): Response
    {
        $sortie = $sortieRepository->findSortie($id);
        $commentaires = $sortieCommentaireRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);

        if ($sortie->getEtat()->getId() === 1 && $sortie->getOrganisateur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas accéder à cette sortie');
            return $this->redirectToRoute('app_sortie');
        }
        if ($sortie->getEtat()->getId() >= 5) {
            $this->addFlash('error', 'Vous ne pouvez plus accéder à cette sortie');
            return $this->redirectToRoute('app_sortie');
        }

        if ($request->get('inputNewCommentaireTexte')) {
            $newCommentaires = new SortieCommentaire();
            $newCommentaires->setSortie($sortie);
            $newCommentaires->setDate(new DateTime());
            $newCommentaires->setAuteur($userRepository->find($this->getUser()));
            $newCommentaires->setTexte($request->get("inputNewCommentaireTexte"));
            $entityManager->persist($newCommentaires);
            $entityManager->flush();

            $commentaires = $sortieCommentaireRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_commentaires.html.twig', compact('commentaires'))
            ]);
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'commentaires' => $commentaires,
        ]);
    }

    /**
     * @Route("/sortie/create", name="app_sortie_create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, UserRepository $userRepository): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm = $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if (!$sortieForm->get('etat')->getData()) {
                $etat = $etatRepository->find(1); // état = créée
                $reponse = 'sauvegardée';
            } else {
                $etat = $etatRepository->find(2); // état = publiée
                $reponse = 'publiée';
            }

            if ($sortieForm->get('image')->getData()) {
                $image = $sortieForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortieImage = new SortieImage();
                $sortieImage->setUrlImage($urlImage);
                $sortie->addImage($sortieImage);
            }

            // TODO prévoir l'ajout de plusieurs images

            $sortie->setEtat($etat);
            $user = $userRepository->find($this->getUser());
            $sortie->setOrganisateur($user);
            $sortie->setCampus($user->getCampus());

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'La sortie a bien été '.$reponse);
            return $this->redirectToRoute('app_sortie');
        }
        // TODO gérer erreur losque dateDebut ou DateCloture est null ou DateCloture après dateDebut. Constraints ne marche pas

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }

    /**
     * @Route("/sortie/{id}/edit", name="app_sortie_edit", requirements={"id"="\d+"})
     */
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository, UserRepository $userRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if ($sortie->getOrganisateur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas l\'organisateur de cette sortie. Vous ne pouvez pas la modifier');
            return $this->redirectToRoute('app_sortie_detail', ["id" => $sortie->getId()]);
        }
        $sortieClone = $sortie;
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm = $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if (!$sortieForm->get('etat')->getData() && $sortieClone->getEtat()->getLibelle() === 1) {
                $etat = $etatRepository->find(1); // état = créée
                $reponse = 'sauvegardée';
            } else {
                $etat = $etatRepository->find(2); // état = publiée
                $reponse = 'publiée';
            }

            if ($sortieForm->get('image')->getData()) {
                $image = $sortieForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortieImage = new SortieImage();
                $sortieImage->setUrlImage($urlImage);
                $sortie->addImage($sortieImage);
            }

            // TODO prévoir l'ajout de plusieurs images

            $sortie->setEtat($etat);
            $user = $userRepository->find($this->getUser());
            $sortie->setOrganisateur($user);
            $sortie->setCampus($user->getCampus());

            $entityManager->flush();

            $this->addFlash('success', 'La sortie a bien été modifiée et '.$reponse);
            return $this->redirectToRoute('app_sortie_detail', ["id" => $sortie->getId()]);
        }
        // TODO gérer erreur losque dateDebut ou DateCloture est null ou DateCloture après dateDebut. Constraints ne marche pas

        return $this->render('sortie/edit.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/sortie/{id}/delete", name="app_sortie_delete", requirements={"id"="\d+"})
     */
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $sortieDeleteForm = $this->createForm(SortieDeleteFormType::class, $sortie);
        $sortieDeleteForm = $sortieDeleteForm->handleRequest($request);

        if ($sortie->getOrganisateur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas l\'organisateur de cette sortie. Vous ne pouvez pas la supprimer');
            return $this->redirectToRoute('app_sortie_detail', ["id" => $sortie->getId()]);
        }

        if ($sortieDeleteForm->isSubmitted() && $sortieDeleteForm->isValid()) {
            $etat = $etatRepository->find(6); // état = annulée
            $sortie->setEtat($etat);
            $sortie->setDateAnnulation(new DateTime());

            $entityManager->flush();

            // Envoi d'un email à chaque participant pour les avertir de l'annulation
            $participants = $sortie->getParticipants();
            foreach ($participants as $participant) {
                $this->emailVerifier->sendEmailAnnulationSortie($participant, $sortie);
                $sortie->removeParticipant($participant);
            }

            $this->addFlash('success', 'La sortie a bien été supprimée');
            return $this->redirectToRoute('app_sortie');
        }

        return $this->render('sortie/delete.html.twig', [
            'sortieDeleteForm' => $sortieDeleteForm->createView(),
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/sortie/{id}/subscribe", name="app_sortie_subscribe", requirements={"id"="\d+"})
     */
    public function subscribe(int $id, SortieRepository $sortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($this->getUser());
        $sortie = $sortieRepository->findSortie($id);
        if (count($sortie->getParticipants()) < $sortie->getNbreInscriptionMax()) {
            if ($sortie->getEtat()->getId() === 2) {
                $sortie->addParticipant($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre inscription a bien été enregistrée');
            }
            else {
                $this->addFlash('error', 'Les inscriptions ne sont pas accessible pour cette sortie. Vous ne pouvez pas vous inscrire');
            }
        }
        else {
            $this->addFlash('error', 'La sortie est complète. Vous ne pouvez pas vous inscrire');
        }

        return $this->redirectToRoute('app_sortie_detail', ['id' => $id]);
    }

    /**
     * @Route("/sortie/{id}/unsubscribe", name="app_sortie_unsubscribe", requirements={"id"="\d+"})
     */
    public function unsubscribe(int $id, SortieRepository $sortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($this->getUser());
        $sortie = $sortieRepository->findSortie($id);
        if ($sortie->getEtat()->getId() === 2 || $sortie->getEtat()->getId() === 3) {
            $sortie->removeParticipant($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre désinscription a bien été enregistré');
        }
        else {
            $this->addFlash('error', 'Les inscriptions ne sont pas accessible pour cette sortie. Vous ne pouvez pas vous désinscrire');
        }

        return $this->redirectToRoute('app_sortie_detail', ['id' => $id]);
    }
}
