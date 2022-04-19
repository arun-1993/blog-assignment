<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Form\DishType;
use App\Repository\DishRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dish", name="dish_")
 */
class DishController extends AbstractController
{
    /**
     * @Route("/show/{id}", name="show")
     */
    public function show(Dish $dish)
    {
        return $this->render('dish/show.html.twig', [
            'dish' => $dish
        ]);
    }

    /**
     * @Route("/", name="list")
     */
    public function index(DishRepository $dishRepository): Response
    {
        $dishes = $dishRepository->findAll();

        return $this->render('dish/index.html.twig', [
            'dishes' => $dishes,
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, ManagerRegistry $doctrine)
    {
        $dish = new Dish();

        $form = $this->createForm(DishType::class, $dish);

        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $image = $form->get('image')->getData();

            if($image)
            {
                $file_name = md5(uniqid()). '.'. $image->guessClientExtension();
            }

            $image->move(
                $this->getParameter('images_folder'),
                $file_name
            );

            $dish->setImage($file_name);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($dish);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('dish_view'));
        }

        $this->addFlash('success', 'Dish added successfully');

        return $this->render('dish/create.html.twig', [
            'createForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(int $id, DishRepository $dishRepository, ManagerRegistry $doctrine)
    {
        $dish = $dishRepository->find($id);
        
        $entityManager = $doctrine->getManager();
        $entityManager->remove($dish);
        $entityManager->flush();

        $this->addFlash('success', 'Dish removed successfully');
        
        return $this->redirect($this->generateUrl('dish_view'));
    }
}
