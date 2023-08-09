<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Commande;
use Stripe\StripeClient;
use App\Form\ReservationType;
use App\Repository\UserRepository;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, SessionInterface $session, ChambreRepository $chambreRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($session->get('cart') == null) {
            return $this->redirectToRoute('app_chambre_index');
        }
        $user = $userRepository->find($this->getUser());

        $form = $this->createForm(ReservationType::class);
        $form->handleRequest($request);
        $cart3 = 0;
        if ($form->isSubmitted() && $form->isValid()) {

            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $interval = date_diff($startDate, $endDate);
            $days = $interval->days + 1;
            $cart = $session->get('cart');

            foreach ($cart as $id) {
                $cart3 = $id;
                // $total += $chambre->getPrixJournalier() * $quantity;
            }
            $chambre = $chambreRepository->find($cart3);
            $total = $chambre->getPrixJournalier() * $days;
            // foreach ($cart as $id => $quantity) {
            //     $chambre = $chambreRepository->find($id);
            //     $dataCart[] = [
            //         "chambre" => $chambre,
            //         'quantity' => $quantity
            //     ];
            //     $total += $chambre->getPrixJournalier() * $quantity;
            // }

            $reservation = [
                'endDate' => $endDate,
                'startDate' => $startDate,
                'total' => $total,
                'chambre' => $session->get('cart')
            ];

            $session->set('reservation', $reservation);
            return $this->redirectToRoute('app_payment');
        }
        dd($cart3);
        return $this->render('reservation/index.html.twig', [
            'form' => $form,
            'chambres' => $chambreRepository->find($cart3)
        ]);
    }
    #[Route('/payment', name: 'app_payment')]
    public function payment(SessionInterface $session)
    {
        $stripeKey = $this->parameterBag->get('stripeSecret');
        $reservation = $session->get('reservation');

        $stripe = new StripeClient($stripeKey);

        $checkout_session = $stripe->checkout->sessions->create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $reservation['chambre']->getTitre() . '-' . $reservation['chambre']->getDescriptionCourte() . '-' . $reservation['chambre']->getDescriptionLongue(),
                    ],

                    'unit_amount' => $reservation['total'] * 10,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        return $this->redirect($checkout_session->url, 303);
    }


    #[Route('/success', name: 'app_success')]
    public function appSuccess(ChambreRepository $chambreRepository, SessionInterface $session, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $commande = new Commande();
        $reservation = $session->get('reservation');
        $uniqId = uniqid();
        $commande->setUser($this->getUser())
            ->setChambre($chambreRepository->find($session->get('cart')))
            ->setCreateAt(new DateTimeImmutable())
            ->setTotal($session->get('reservation')['total'])
            ->setStartDate(date_format($reservation['startDate'], 'Y-m-d'))
            ->setEndDate(date_format($reservation['endDate'], 'Y-m-d'))
            ->setCommandeId($uniqId);

        $entityManager->persist($commande);
        $entityManager->flush();
        $session->set('cart', null);
        $session->set('reservation', null);

        // envoie d'email
        $email = (new TemplatedEmail())
            ->from('info@hotel-house.com')
            ->to($this->getUser()->getEmail())
            ->subject('commande n. ' . $uniqId)
            ->text('Thanks for your order');

        $mailer->send($email);

        return $this->render('reservation/success.html.twig', [
            'uniqId' => $uniqId
        ]);
    }

    #[Route('/cancel', name: 'app_cancel')]
    public function appCancel()
    {
        return $this->render('reservation/cancel.html.twig');
    }
}
