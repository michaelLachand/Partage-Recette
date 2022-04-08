<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ingredient')]
class IngredientController extends AbstractController
{
    #[Route('/', name: 'ingredient_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $ingredientRepo,
                          PaginatorInterface $paginator,
                          Request $request
    ): Response
    {
        $ingredients = $paginator->paginate(
            $ingredientRepo->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    #[Route('/nouveau', name: 'ingredient_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request,
                        EntityManagerInterface $em
    ): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash(
                'success','Votre ingrédient a été crée avec succès !!'
            );

            return $this->redirectToRoute('ingredient_index');
        }

        return $this->render('pages/ingredient/new.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'ingredient_edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    public function edit(Ingredient $ingredient,
                         Request $request,
                         EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash(
                'success','Votre ingrédient a été modifie avec succès !!'
            );

            return $this->redirectToRoute('ingredient_index');
        }

        return $this->render('pages/ingredient/edit.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/suppression/{id}', name: 'ingredient_delete', methods: ['POST','GET'])]
    public function delete(EntityManagerInterface $em,
                           Ingredient $ingredient
    ): Response
    {
        $em->remove($ingredient);
        $em->flush();

        $this->addFlash(
            'success','Votre ingrédient a été supprimé avec succès !!'
        );

        return $this->redirectToRoute('ingredient_index');
    }
}
