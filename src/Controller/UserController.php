<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/utilisateur')]
class UserController extends AbstractController
{
    #[Route('/edition/{id}', name: 'user_edit')]
    #[Security("is_granted('ROLE_USER') and user ===")]
    public function edit(User $choosenUser,
                         EntityManagerInterface $em,
                         Request $request,
                         UserPasswordHasherInterface $hasher
    ): Response
    {

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($choosenUser, $form->getData()->getPlainPassword())){
                $user = $form->getData();

                $em->persist($user);
                $em->flush();

                $this->addFlash(
                    'success','Votre profil a été modifié avec succès !!'
                );

                return $this->redirectToRoute('recipe_index');

            } else {
                $this->addFlash(
                    'warning','Le mot de passe renseigné est incorrect.');
            }
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/edition-mot-de-passe/{id}', name: 'user_editPassword', methods: ['GET', 'POST'])]
    public function editPassword(User $choosenUser,
                                 Request $request,
                                 UserPasswordHasherInterface $hasher,
                                 EntityManagerInterface $em,
    ): Response
    {

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($choosenUser, $form->getData()['plainPassword'])){
                $choosenUser->setUpdatedAt(new \DateTimeImmutable());
                $choosenUser->setPlainPassword(
                        $form->getData()['newPassword']
                );

                $em->persist($choosenUser);
                $em->flush();

                $this->addFlash(
                    'success','Le mot de passe a été modifié avec succès !!'
                );

                return $this->redirectToRoute('recipe_index');

            } else {
                $this->addFlash(
                    'warning','Le mot de passe renseigné est incorrect.');
            }
        }

        return $this->renderForm('pages/user/edit_password.html.twig',[
            'form' => $form,
        ]);
    }
}
