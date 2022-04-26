<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Dish;
use App\Entity\User;
use App\Form\DishType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\DishRepository;
use Doctrine\Persistence\ManagerRegistry;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
    public function show(Dish $dish, CommentRepository $commentRepository, Request $request)
    {
        $comment_form = $this->createFormBuilder()
            ->add('comment', TextareaType::class, [
                'label' => 'Enter Your Comment',
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'maxlength' => 65535,
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Post Comment'])
            ->getForm()
        ;

        $comment_form->handleRequest($request);

        if($comment_form->isSubmitted() && $comment_form->isValid())
        {
            $comment = new Comment();
            
            $input['comment'] = $comment_form->get('comment')->getData();;
            $input['dish'] = $dish;
            $input['user'] = $this->getUser();
            
            $commentRepository->add($comment, $input);

            return $this->redirectToRoute('dish_single', ['id' => $dish->getId()]);
        }

        $comments = $commentRepository->findBy(['dish' => $dish->getId()], ['createdOn' => 'DESC']);

        return $this->render('dish/show.html.twig', [
            'dish' => $dish,
            'comments' => $comments,
            'comment_form' => $comment_form->createView(),
        ]);
    }

    /**
     * @Route("/view/{category}", name="list")
     */
    public function list(DishRepository $dishRepository, CategoryRepository $categoryRepository, Request $request, string $category = 'all'): Response
    {
        $dishes = $dishRepository->findBy([], ['name' => 'ASC']);
        $categories = $categoryRepository->findBy([], ['id' => 'ASC']);

        $filter = $this->createFormBuilder()
            ->add('date_filter', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date',
            ])
            ->add('filter_submit', SubmitType::class, ['label' => 'Filter'])
            ->getForm()
        ;

        $filter->handleRequest($request);

        if($filter->isSubmitted() && $filter->isValid())
        {
            $search = $filter->get('date_filter')->getData();
            $dishes = $dishRepository->getByDate($search);
        }

        return $this->render('dish/index.html.twig', [
            'dishes' => $dishes,
            'category' => $category,
            'cat_list' => $categories,
            'filter' => $filter->createView(),
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, ManagerRegistry $doctrine, DishRepository $dishRepository): Response
    {
        $dish = new Dish();

        $form = $this->createForm(DishType::class, $dish);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('image')->getData();
            $allowed_types = array('image/gif', 'image/jpeg', 'image/png');

            if($image && in_array($image->getMimeType(), $allowed_types))
            {
                $file_name = md5(uniqid()). '.'. $image->guessExtension();
                
                $image->move(
                    $this->getParameter('images_folder'),
                    $file_name
                );
            }

            else
            {
                $file_name = 'default.png';
                $this->addFlash('error', 'There was no image or image type was not supported. So the default image was chosen.');
            }

            $author = $doctrine->getRepository(User::class)->find($this->getUser()->getId());

            $dishRepository->add($dish, $author, $file_name);
            
            $this->addFlash('success', 'Dish created successfully!');
            return $this->redirectToRoute('user_list');
        }

        return $this->render('dish/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Dish $dish, Request $request, DishRepository $dishRepository): Response
    {
        if($dish->getAuthor() != $this->getUser())
        {
            $this->addFlash('error', 'You do not have permission to edit this dish!');
            return $this->redirectToRoute('user_list');
        }

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Title',
                'attr' => ['maxlength' => 255],
                'data' => $dish->getName()
            ])
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'data' => $dish->getCategory()
            ])
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Excerpt (A short description)',
                'attr' => [
                    'rows' => 5,
                    'maxlength' => 2000,
                ],
                'data' => $dish->getDescription()
            ])
            ->add('content', CKEditorType::class, [
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 4294967295,
                ],
                'data' => $dish->getContent(),
            ])
            ->add('update', SubmitType::class, ['label' => 'Update Dish'])
            ->getForm()
        ;

        // $form = $this->createForm(DishType::class, $dish);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('image')->getData();
            $allowed_types = array('image/gif', 'image/jpeg', 'image/png');

            if($image && in_array($image->getMimeType(), $allowed_types))
            {
                $file_name = md5(uniqid()). '.'. $image->guessClientExtension();
                
                $image->move(
                    $this->getParameter('images_folder'),
                    $file_name
                );

                $input['image'] = $file_name;
            }

            $input = $form->getData();
            $input['author'] = $this->getUser();

            $dishRepository->edit($dish, $input);

            $this->addFlash('success', 'Dish edited successfully!');
            return $this->redirectToRoute('user_list');
        }

        return $this->render('dish/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Dish $dish, DishRepository $dishRepository, ManagerRegistry $doctrine): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_USER');

        if($dish->getAuthor() != $this->getUser())
        {
            $this->addFlash('error', 'You do not have permission to delete this dish!');
        }
        
        else
        {
            $dishRepository->remove($dish);

            $this->addFlash('success', 'Dish removed successfully!');
        }
        
        return $this->redirectToRoute('user_list');
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
    public function catList(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy([], ['id' => 'ASC']);

        return $this->render('_categories_list.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/search_bar", name="search_bar")
     */
    public function search_bar(Request $request): Response
    {
        $search = $this->createFormBuilder()
            ->add('general_search', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Search',
                    'style' => 'width: 200px'
                ],
            ])
            ->add('search_submit', SubmitType::class, [
                'label' => 'Search',
            ])
            ->getForm()
        ;

        $search->handleRequest($request);

        if($search->isSubmitted() && $search->isValid())
        {
            dump($search);
            return $this->redirectToRoute('dish_search');
        }

        return $this->render('_search_dishes.html.twig', [
            'search' => $search->createView(),
        ]);
    }

    /**
     * @Route("/search/{search}", name="search")
     */
    public function search(DishRepository $dishRepository, CategoryRepository $categoryRepository, string $search = ''): Response
    {
        return new Response($search);
    }
}
