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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ReservationController extends AbstractController
{
    public function __construct(protected ParameterBagInterface $parameterBag)
    {
    }

    #[Route('/reservation/dateChoice/{id}', name: 'app_dateChoice')]
    public function dateChoice(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, ChambreRepository $chambreRepository, $id)
    {
        $form = $this->createForm(ReservationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cart = $session->get('cart');
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $interval = date_diff($startDate, $endDate);
            $days = $interval->days + 1;
            $total = $chambreRepository->find($id)->getPrixJournalier() * $days;
            $cart[$id] =
                [
                    'startDate' => $form->get('startDate')->getData(),
                    'endDate' => $form->get('endDate')->getData(),
                    'days' => $days,
                    'total' => $total,
                    'chambre' => $chambreRepository->find($id)
                ];
            $session->set('cart', $cart);



            // $reservation = [
            //     'endDate' => $endDate,
            //     'startDate' => $startDate,
            //     'total' => $total,
            //     'chambre' => $chambreRepository->find($id)
            // ];

            // $session->set('reservation', $reservation);
            return $this->redirectToRoute('app_reservation');
        }

        return $this->render('reservation/dateChoice.html.twig', [
            'form' => $form,
            'chambre' => $chambreRepository->find($id)
        ]);
    }




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


        return $this->render('reservation/index.html.twig', [
            'cart' => $session->get('cart')
        ]);
    }




    #[Route('/payment', name: 'app_payment')]
    public function payment(SessionInterface $session)
    {
        if ($session->get('cart') == null) {
            return $this->redirectToRoute('app_chambre_index');
        }
        $reservation = $session->get('reservation');
        foreach ($session->get('cart') as $item) {


            $tableau[] = [


                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['chambre']->getTitre() . '-' . $item['chambre']->getDescriptionCourte() . '-' . $item['chambre']->getDescriptionLongue(),

                    ],

                    'unit_amount' => $item['total'] * 100,
                ],
                'quantity' => 1,



            ];
        }


        $stripeKey = $this->parameterBag->get('stripeSecret');

        $stripe = new StripeClient($stripeKey);

        $checkout_session = $stripe->checkout->sessions->create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => $tableau,
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
        $cart = $session->get('cart');

        // foreach ($cart as $id) {
        //     dd($id);
        // }
        // dd($cart[3]);
        $uniqId = uniqid();
        foreach ($cart as $id => $value) {
            $commande->setUser($this->getUser())
                ->setChambre($chambreRepository->find($id))
                ->setCreateAt(new DateTimeImmutable())
                ->setTotal($value['total'])
                ->setStartDate(date_format($value['startDate'], 'Y-m-d'))
                ->setEndDate(date_format($value['endDate'], 'Y-m-d'))
                ->setCommandeId($uniqId);
        }
        $entityManager->persist($commande);
        $entityManager->flush();
        $session->set('cart', null);

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
