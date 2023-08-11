<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ProfileType;
use App\Form\AdminUserType;
use App\Form\ProfilePasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('profile/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/leProfile', name: 'app_leProfile', methods: ['GET'])]
    public function leProfile(UserRepository $userRepository): Response
    {
        $roleUser = false;
        if ($this->getUser()->getRoles() == ["ROLE_USER", "ROLE_ADMIN"]) {

            $roleUser = false;
        }
        if ($this->getUser()->getRoles() == ["ROLE_USER"]) {

            $roleUser = true;
        }
        if ($this->getUser()->getRoles() == ["ROLE_USER", "ROLE_ADMIN"]) {

            $roleAdmin = true;
        }
        if ($this->getUser()->getRoles() == ["ROLE_USER", "ROLE_ADMIN"] && $roleUser = false) {

            return $this->redirectToRoute('app_profile_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->getUser()->getRoles() == ["ROLE_USER"] && $roleUser = true) {

            return $this->redirectToRoute('app_profile_indexUSER', [], Response::HTTP_SEE_OTHER);
        }
        // dd($this->getUser()->getRoles());

        return $this->render('profile/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }



    #[Route('/indexUser', name: 'app_profile_indexUSER', methods: ['GET'])]
    public function indexUser(UserRepository $userRepository): Response
    {
        return $this->render('profile/indexUser.html.twig', [
            'user' => $this->getUser(),
        ]);
    }


    #[Route('/new', name: 'app_profile_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_profile_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {

        $utilisateur = false;
        // dd($this->createForm(ProfileType::class));
        if ($user == $this->getUser() && $this->getUser()->getRoles() == ["ROLE_USER", "ROLE_ADMIN"]) {
            $utilisateur = true;
            $form = $this->createForm(AdminUserType::class, $user);
            $form->handleRequest($request);
        }
        if ($user != $this->getUser() && $this->getUser()->getRoles() == ["ROLE_USER", "ROLE_ADMIN"]) {
            $utilisateur = false;
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
        }
        if ($user == $this->getUser() && $this->getUser()->getRoles() == ["ROLE_USER"]) {
            $utilisateur = true;
            $form = $this->createForm(ProfileType::class);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_leProfile', [], Response::HTTP_SEE_OTHER);
        }
        // dd($user);
        // dd($this->getUser()->getRoles());
        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'utilisateur' => $utilisateur
        ]);
    }


    #[Route('/profil/edit/password', name: 'app_profil_edit_password')]
    public function editPassword(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepository->find($this->getUser());
        $form = $this->createForm(ProfilePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHasher->isPasswordValid($user, $form->get('oldPassword')->getData())) {
                // version plus longue
                $newPassword = $passwordHasher->hashPassword($user, $form->get('newPassword')->getData());
                $user->setPassword($newPassword);
                // version plus courte
                // $user->setPassword(
                //     $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
                // );
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_leProfile');
            } else {
                $this->addFlash('error', 'Old Password is incorrect');
                return $this->redirectToRoute('app_profil_edit_password');
            }
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_leProfile');
        }
        return $this->render('profile/editPassword.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_profile_index', [], Response::HTTP_SEE_OTHER);
    }
}
