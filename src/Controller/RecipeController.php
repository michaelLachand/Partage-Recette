<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recette')]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'recipe_index', methods: ['GET'])]
    public function index(PaginatorInterface $paginator,
                          Request $request,
                          RecipeRepository $recipeRepo
    ): Response
    {

        $recipes = $paginator->paginate(
            $recipeRepo->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/nouveau', name: 'recipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        EntityManagerInterface $em
    ): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $em->persist($recipe);
            $em->flush();

            $this->addFlash(
                'success','Votre recette a été crée avec succès !!'
            );

            return $this->redirectToRoute('recipe_index');
        }


        return $this->renderForm('pages/recipe/new.html.twig',[
            'form' => $form
        ]);
    }

    #[Route('/edition/{id}', name: 'recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe,
                         Request $request,
                         EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $em->persist($recipe);
            $em->flush();

            $this->addFlash(
                'success','Votre recette a été modifié avec succès !!'
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('pages/recipe/edit.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/suppression/{id}', name: 'recipe_delete', methods: ['POST','GET'])]
    public function delete(EntityManagerInterface $em,
                           Recipe $recipe
    ): Response
    {
        $em->remove($recipe);
        $em->flush();

        $this->addFlash(
            'success','Votre recette a été supprimé avec succès !!'
        );

        return $this->redirectToRoute('recipe_index');
    }
}
