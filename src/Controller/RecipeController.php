<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recette')]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'recipe_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(PaginatorInterface $paginator,
                          Request $request,
                          RecipeRepository $recipeRepo
    ): Response
    {

        $recipes = $paginator->paginate(
            $recipeRepo->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/communaute', name: 'recipe_community', methods: ['GET'])]
    public function indexPublic(PaginatorInterface $paginator,
                                RecipeRepository $recipeRepo,
                                Request $request): Response
    {
        $recipes = $paginator->paginate(
            $recipeRepo->findPulbicRecipe(5),
            $request->query->getInt('page', 1),
            10

        );
        return $this->render('pages/recipe/community.html.twig',[
            'recipes' => $recipes
        ]);
    }

    #[Route('/nouveau', name: 'recipe_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request,
                        EntityManagerInterface $em
    ): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

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

    #[Security("is_granted('ROLE_USER') and (recipe.getIsPublic() === true || user === recipe.getUser())")]
    #[Route('/{id}', name: 'recipe_show', methods: ['GET', 'POST'])]
    public function show(Recipe $recipe,
                         Request $request,
                         MarkRepository $markRepo,
                         EntityManagerInterface $em
    ): Response
    {
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepo->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if(!$existingMark) {
                $em->persist($mark);
            } else {
                $existingMark->setMark(
                     $form->getData()->getMark()
                );
            }

            $em->flush();

            $this->addFlash(
                'success','Votre note a été crée avec succès prise en compte');

            return $this->redirectToRoute('recipe_show', ['id' => $recipe->getId()]);
        }

        return $this->render('pages/recipe/show.html.twig',[
            'recipe' => $recipe,
            'form' => $form->createView(),
        ]);
    }



    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
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
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
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
