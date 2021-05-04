<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SearchSortieFormType;
use App\Form\SortieFormType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Services\SearchSortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
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
    public function detail(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->findSortie($id);

        if ($sortie->getEtat()->getId() >= 5) {
            $this->addFlash('error', 'Cette sortie n\'est plus disponible. Vous ne pouvez plus y accéder');
            return $this->redirectToRoute('app_sortie');
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/sortie/create", name="app_sortie_create")
     */
    public function create(Request $request): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm = $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            return $this->redirectToRoute('app_sortie');
        }
        // TODO gérer erreur losque dateDebut ou DateCloture est null. Constraints NotNull ne marche pas

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }

    /**
     * @Route("/sortie/{id}/edit", name="app_sortie_edit", requirements={"id"="\d+"})
     */
    public function edit(int $id): Response
    {
        return $this->render('sortie/edit.html.twig');
    }

    /**
     * @Route("/sortie/{id}/delete", name="app_sortie_delete", requirements={"id"="\d+"})
     */
    public function delete(int $id): Response
    {
        return $this->redirectToRoute('app_sortie');
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
