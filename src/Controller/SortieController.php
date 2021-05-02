<?php

namespace App\Controller;

use App\Form\SearchSortieFormType;
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

        $tableauSorties = $sortieRepository->findSearchSortie($searchSortie);
        $nbreResultats = count($sortieRepository->countSearchSortie($searchSortie));

        return $this->render('sortie/index.html.twig', [
            'searchSortieFormType' => $searchSortieFormType->createView(),
            'tableauSorties' => $tableauSorties,
            'nbreResultats' => $nbreResultats,
        ]);
    }

    /**
     * @Route("/sortie/{id}/detail", name="app_sortie_detail")
     */
    public function detail(int $id, Request $request, SortieRepository $sortieRepository, UserRepository $userRepository): Response
    {
        $sortie = $sortieRepository->findSortie($id);

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/sortie/create", name="app_sortie_create")
     */
    public function create(): Response
    {
        return $this->render('sortie/create.html.twig');
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
    public function subscribe(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->findSortie($id);
        if (count($sortie->getParticipants()) < $sortie->getNbreInscriptionMax()) {
            if ($sortie->getDateClotureInscription() > new \DateTime()) {
                $sortie->addParticipant($this->getUser());
                $entityManager->flush();
                $this->addFlash('success', 'Votre inscription a bien été enregistré !');
            }
            else {
                $this->addFlash('error', 'La date limite pour s\'inscrire est dépassée. Vous ne pouvez pas vous y inscrire !');
            }
        }
        else {
            $this->addFlash('error', 'La sortie est complète. Vous ne pouvez pas vous y inscrire !');
        }

        return $this->redirectToRoute('app_sortie_detail', ['id' => $id]);
    }

    /**
     * @Route("/sortie/{id}/unsubscribe", name="app_sortie_unsubscribe", requirements={"id"="\d+"})
     */
    public function unsubscribe(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->findSortie($id);
        if ($sortie->getDateDebut() > new \DateTime()) {
            $sortie->removeParticipant($this->getUser());
            $entityManager->flush();
            $this->addFlash('success', 'Votre désinscription a bien été enregistré !');
        }
        else {
            $this->addFlash('error', 'La date limite pour vous désinscrire est dépassée. Vous ne pouvez pas vous y désinscrire !');
        }

        return $this->redirectToRoute('app_sortie_detail', ['id' => $id]);
    }

}
