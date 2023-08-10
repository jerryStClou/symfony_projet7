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
