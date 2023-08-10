<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccesController extends AbstractController
{
    #[Route('/acces', name: 'app_acces')]
    public function index(): Response
    {
        return $this->render('acces/index.html.twig', [
            'controller_name' => 'AccesController',
        ]);
    }
}
