<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Repository\ChambreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, ChambreRepository $chambreRepository): Response
    {
        $cart = $session->get('cart');
        $total = 0;
        $nbrChambre = 0;
        foreach ($cart as $id => $chambreCart) {

            $nbrChambre = $nbrChambre + 1;
            $chambre = $chambreRepository->find($id);
            $total += $chambre->getPrixJournalier() * $nbrChambre;
        }
        dd($chambre->getId());
        // $cart2 = $cart;
        //dd($cart2);
        // $total = 0;
        // $dataCart = [];
        // foreach ($cart as $id => $quantity) {
        //     $chambre = $chambreRepository->find($id);
        //     $cart2 = $id;
        //     $dataCart[] = [
        // "chambre" => $chambre
        // 'quantity' => $quantity
        // ];
        // $total += $chambre->getPrixJournalier() * $quantity;
        // }
        // dd($cart2);

        return $this->render('panier/index.html.twig', [
            // 'dataCart' => $dataCart,
            // 'total' => $total
        ]);
    }

    #[Route('/add/{id}', name: 'app_add_panier')]
    public function ADD(SessionInterface $session, $id, Chambre $chambre): Response
    {
        // $cart = $session->get('cart');
        // $cart[$id] = [
        //     "titre" => $chambre->getTitre(),
        //     "descriptionCourte" => $chambre->getDescriptionCourte(),
        //     "descriptionLongue" => $chambre->getDescriptionLongue(),
        //     "prixJournalier" => $chambre->getPrixJournalier(),
        // ];
        // dd($cart);

        $cart = $session->set('cart', []);
        $session->set('cart', $cart);


        $cart = $session->get('cart');
        $cart[$id] = [
            "titre" => $chambre->getTitre(),
            "descriptionCourte" => $chambre->getDescriptionCourte(),
            "descriptionLongue" => $chambre->getDescriptionLongue(),
            "prixJournalier" => $chambre->getPrixJournalier(),
        ];
        // dd($cart);



        // dd($chambre->getTitre());

        // if (!empty($cart[$id])) {
        //     $cart[$id]++;
        // } else {
        //     $cart[$id] = 1;
        // }
        // dd($cart);
        $session->set('cart', $cart);
        return $this->redirectToRoute("app_panier");
    }

    #[Route('/remove/{id}', name: 'app_remove_panier')]
    public function remove(SessionInterface $session, $id): Response
    {
        $cart = $session->get('cart');
        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute("app_panier");
    }

    #[Route('/delete/{id}', name: 'app_delete_panier')]
    public function delete(SessionInterface $session, $id): Response
    {
        $cart = $session->get('cart');
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute("app_panier");
    }

    #[Route('/flush', name: 'app_flush_panier')]
    public function flush(SessionInterface $session): Response
    {
        $cart = $session->set('cart', []);
        $session->set('cart', $cart);
        return $this->redirectToRoute("app_home");
    }
}
