<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UpdatePasswordUserFormType;
use App\Form\UpdateUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile/edit', name: 'profile_edit')]
    #[IsGranted('ROLE_USER', message: 'vous devez ètre connecté pour acceder à cette page')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $firstname = ucfirst($form->get('firstname')->getData());
            $lastname = mb_strtoupper($form->get('lastname')->getData());
            $user->setFirstname($firstname)
            ->setLastname($lastname);
            $em->flush();
            $this->addFlash('success', 'vos modification ont bien été pris en compte');
            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('profile/edit/editPassword', name: 'profile_editPassword')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function editPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UpdatePasswordUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $password = $userPasswordHasher->hashPassword($user, $newPassword);

            $user->setPassword($password);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour');
            return $this->redirectToRoute('profile_edit');
        }
        return $this->render('profile/password.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
}
