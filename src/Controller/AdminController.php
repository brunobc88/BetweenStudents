<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusFormType;
use App\Form\SearchCampusFormType;
use App\Form\SearchSortieFormType;
use App\Form\SearchUserFormType;
use App\Form\SearchVilleFormType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use App\Security\EmailVerifier;
use App\Services\SearchCampus;
use App\Services\SearchSortie;
use App\Services\SearchUser;
use App\Services\SearchVille;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(SortieRepository $sortieRepository, UserRepository $userRepository, VilleRepository $villeRepository, CampusRepository $campusRepository): Response
    {
        $nbreResultatsSortie = $sortieRepository->countResultSearchSortie(new SearchSortie(), true);
        $nbreResultatsUser = $userRepository->countResultSearchUser(new SearchUser());
        $nbreResultatsVille = $villeRepository->countResultSearchVille(new SearchVille());
        $nbreResultatsCampus = $campusRepository->countResultSearchCampus(new SearchCampus());

        return $this->render('admin/index.html.twig', [
            'nbreResultatsSortie' => $nbreResultatsSortie,
            'nbreResultatsUser' => $nbreResultatsUser,
            'nbreResultatsVille' => $nbreResultatsVille,
            'nbreResultatsCampus' => $nbreResultatsCampus,
        ]);
    }

    /**
     * @Route("/admin/sortie", name="app_admin_sortie")
     */
    public function sortie(Request $request, SortieRepository $sortieRepository): Response
    {
        $searchSortie = new SearchSortie();
        $searchSortie->page = $request->get('page', 1);

        $searchSortieFormType = $this->createForm(SearchSortieFormType::class, $searchSortie);
        $searchSortieFormType->handleRequest($request);

        $tableauSorties = $sortieRepository->findSearchSortiePaginate($searchSortie, 24, true);
        $nbreResultats = $sortieRepository->countResultSearchSortie($searchSortie, true);

        return $this->render('admin/sortie.html.twig', [
            'searchSortieFormType' => $searchSortieFormType->createView(),
            'tableauSorties' => $tableauSorties,
            'nbreResultats' => $nbreResultats,
        ]);
    }

    /**
     * @Route("/admin/sortie/{id}/delete", name="app_admin_sortie_delete")
     */
    public function sortieDelete(int $id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $etat = $etatRepository->find(6); // état = annulée
        $sortie->setEtat($etat);
        $sortie->setDateAnnulation(new DateTime());
        $sortie->setRaisonAnnulation('La sortie ne respecte pas les conditions générales');

        $entityManager->flush();

        // Envoi d'un email à chaque participant pour les avertir de l'annulation
        $participants = $sortie->getParticipants();
        foreach ($participants as $participant) {
            $this->emailVerifier->sendEmailAnnulationSortie($participant, $sortie);
            $sortie->removeParticipant($participant);
        }

        $this->addFlash('success', 'La sortie a bien été supprimée');
        return $this->redirectToRoute('app_admin_sortie');
    }

    /**
     * @Route("/admin/user", name="app_admin_user")
     */
    public function user(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $searchUser = new SearchUser();
        $searchUser->page = $request->get('page', 1);

        $searchUserFormType = $this->createForm(SearchUserFormType::class, $searchUser);
        $searchUserFormType->handleRequest($request);

        $tableauUsers = $userRepository->findSearchUserPaginate($searchUser, 24);
        $nbreResultats = $userRepository->countResultSearchUser($searchUser);

        if ($request->get('ajax') && ($request->get('checkboxAdmin') || $request->get('checkboxActif'))) {
            $user = $userRepository->find($request->get('id'));

            if ($request->get('checkboxAdmin')) {
                if ($request->get('checkboxAdmin') === 'true') {
                    $user->setRoles(['ROLE_ADMIN']);
                    $user->setAdministrateur(true);
                }
                else {
                    $user->setRoles(['ROLE_USER']);
                    $user->setAdministrateur(false);
                }
            }
            if ($request->get('checkboxActif')) {
                if ($request->get('checkboxActif') === 'true') {
                    $user->setActif(true);
                    $this->emailVerifier->sendEmailUserEtatCompte($user);
                }
                else {
                    $user->setActif(false);
                    $this->emailVerifier->sendEmailUserEtatCompte($user);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Modification enregistrée');

            return new JsonResponse([
                'content' => $this->renderView('inc/message-flash.html.twig')
            ]);
        }

        return $this->render('admin/user.html.twig', [
            'searchUserFormType' => $searchUserFormType->createView(),
            'tableauUsers' => $tableauUsers,
            'nbreResultats' => $nbreResultats,
        ]);
    }

    /**
     * @Route("/admin/ville", name="app_admin_ville")
     */
    public function ville(Request $request, VilleRepository $villeRepository): Response
    {
        $searchVille = new SearchVille();
        $searchVille->page = $request->get('page', 1);

        $searchVilleFormType = $this->createForm(SearchVilleFormType::class, $searchVille);
        $searchVilleFormType->handleRequest($request);

        $tableauVilles = $villeRepository->findSearchVillePaginate($searchVille, 24);
        $nbreResultats = $villeRepository->countResultSearchVille($searchVille);

        return $this->render('admin/ville.html.twig', [
            'searchVilleFormType' => $searchVilleFormType->createView(),
            'tableauVilles' => $tableauVilles,
            'nbreResultats' => $nbreResultats,
        ]);
    }

    /**
     * @Route("/admin/campus", name="app_admin_campus")
     */
    public function campus(Request $request, EntityManagerInterface $entityManager, CampusRepository $campusRepository, VilleRepository $villeRepository): Response
    {
        $searchCampus = new SearchCampus();
        $searchCampus->page = $request->get('page', 1);

        $searchCampusFormType = $this->createForm(SearchCampusFormType::class, $searchCampus);
        $searchCampusFormType->handleRequest($request);

        $tableauCampus = $campusRepository->findSearchCampusPaginate($searchCampus, 24);
        $nbreResultats = $campusRepository->countResultSearchCampus($searchCampus);

        $campus = new Campus();

        $campusFormType = $this->createForm(CampusFormType::class, $campus);
        $campusFormType->handleRequest($request);

        if ($campusFormType->isSubmitted() && $campusFormType->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a bien été enregistré');
            return $this->redirectToRoute('app_admin_campus');
        }

        if ($request->get('ajax') && $request->get('campus_form')['codePostal']) {
            $codePostal = $request->get('campus_form')['codePostal'];

            $villes = $villeRepository->findBy(array('codePostal' => $codePostal), array('nom' => 'ASC'), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_selectVille.html.twig', compact('villes'))
            ]);
        }

        return $this->render('admin/campus.html.twig', [
            'searchCampusFormType' => $searchCampusFormType->createView(),
            'campusFormType' => $campusFormType->createView(),
            'tableauCampus' => $tableauCampus,
            'nbreResultats' => $nbreResultats,
        ]);
    }

    /**
     * @Route("/admin/statistique", name="app_admin_stat")
     */
    public function stat(SortieRepository $sortieRepository, UserRepository $userRepository, CampusRepository $campusRepository): Response
    {
        $tableauDerniersMois = [];
        $date = new DateTime();
        $date->modify('-6 month');
        for ($i = 0; $i < 6; $i++) {
            $tableauDerniersMois[] = $date->modify('+1 month')->format('M');
        }

        $statsSortie = $sortieRepository->statsSortie();

        $statsUsers = [];
        $searchUser = new SearchUser();
        $tableauCampus = $campusRepository->findAll();
        for ($i = 0; $i < count($tableauCampus); $i++) {
            $searchUser->campus = $tableauCampus[$i];
            $statsUsers[] = $userRepository->countResultSearchUser($searchUser);
        }

        return $this->render('admin/stat.html.twig', [
            'statsSortie' => $statsSortie,
            'statsUsers' => $statsUsers,
            'tableauCampus' => $tableauCampus,
            'tableauDerniersMois' => $tableauDerniersMois,
        ]);
    }
}
