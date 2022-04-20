<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $contact = new Contact();

        if($this->getUser()){
            $contact->setFullname($this->getUser()->getFullName())
                ->setEmail($this->getUser()->getEmail());
        }
        $form  = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           $contact = $form->getData();

           $em->persist($contact);
           $em->flush();

            $this->addFlash(
                'success','Votre message a été envoyé avec succès !!'
            );

           return $this->redirectToRoute('contact_index');

        }
        return $this->renderForm('pages/contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
