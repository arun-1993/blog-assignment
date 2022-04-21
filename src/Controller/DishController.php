<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Dish;
use App\Entity\User;
use App\Form\DishType;
use App\Repository\CategoryRepository;
use App\Repository\DishRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dish", name="dish_")
 */
class DishController extends AbstractController
{
    /**
     * @Route("/view/{id}", name="single", requirements={"id"="\d+"})
     */
    public function show(Dish $dish)
    {
        return $this->render('dish/show.html.twig', [
            'dish' => $dish
        ]);
    }

    /**
     * @Route("/view/{category}", name="list")
     */
    public function list(DishRepository $dishRepository, string $category = 'all'): Response
    {
        $dishes = $dishRepository->findAll();

        return $this->render('dish/index.html.twig', [
            'dishes' => $dishes,
            'category' => $category
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $dish = new Dish();

        $form = $this->createForm(DishType::class, $dish);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('image')->getData();

            if($image)
            {
                $file_name = md5(uniqid()). '.'. $image->guessClientExtension();
                
                $image->move(
                    $this->getParameter('images_folder'),
                    $file_name
                );
            }

            else
            {
                $file_name = 'default.png';
            }

            $author = $doctrine->getRepository(User::class)->find($this->getUser()->getId());
            $dish->setAuthor($author);
            $dish->setImage($file_name);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($dish);
            $entityManager->flush();

            $this->addFlash('success', 'Dish created successfully!');
            return $this->redirect($this->generateUrl('user_list'));
        }

        return $this->render('dish/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Dish $dish, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if($dish->getAuthor()->getId() != $this->getUser()->getId())
        {
            $this->addFlash('error', 'You do not have permission to edit this dish!');
            return $this->redirect($this->generateUrl('user_list'));
        }

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'empty_data' => $dish->getName(),
                'data' => $dish->getName()
            ])
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'empty_data' => $dish->getCategory(),
                'data' => $dish->getCategory()
            ])
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'empty_data' => $dish->getDescription(),
                'data' => $dish->getDescription()
            ])
            ->add('content', TextareaType::class, [
                'empty_data' => $dish->getContent(),
                'data' => $dish->getContent()
            ])
            ->add('update', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('image')->getData();

            if($image)
            {
                $file_name = md5(uniqid()). '.'. $image->guessClientExtension();
                
                $image->move(
                    $this->getParameter('images_folder'),
                    $file_name
                );

                $dish->setImage($file_name);
            }

            $input = $form->getData();
            $author = $doctrine->getRepository(User::class)->find($this->getUser()->getId());
            
            $dish->setName($input['name']);
            $dish->setCategory($input['category']);
            $dish->setDescription($input['description']);
            $dish->setContent($input['content']);
            $dish->setAuthor($author);

            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Dish edited successfully!');
            return $this->redirect($this->generateUrl('user_list'));
        }

        return $this->render('dish/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Dish $dish, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if($dish->getAuthor()->getId() != $this->getUser()->getId())
        {
            $this->addFlash('error', 'You do not have permission to delete this dish!');
        }
        
        else
        {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($dish);
            $entityManager->flush();
            $this->addFlash('success', 'Dish removed successfully!');
        }
        
        return $this->redirect($this->generateUrl('user_list'));
    }

    /**
     * @Route("/recent", name="recent")
     */
    public function recent(ManagerRegistry $doctrine): Response
    {
        $dishes = $doctrine->getRepository(Dish::class)->getRecentDishes();

        return $this->render('_recent_dishes.html.twig', [
            'dishes' => $dishes
        ]);
    }

    /**
     * @Route("/cat_list", name="cat_list")
     */
    public function catList(CategoryRepository $categoryRepository, ManagerRegistry $doctrine): Response
    {
        $categories = $categoryRepository->findBy([], ['id' => 'ASC']);

        return $this->render('_categories_list.html.twig', [
            'categories' => $categories
        ]);
    }
}
