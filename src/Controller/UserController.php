<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\DishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/list", name="list")
     */
    public function list(DishRepository $dishRepository): Response
    {
        $dishes = $dishRepository->findBy(['author' => $this->getUser()->getId()]);

        return $this->render('user/index.html.twig', [
            'dishes' => $dishes
        ]);
    }

    /**
     * @Route("/comments", name="comments")
     */
    public function comments(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(['user' => $this->getUser()->getId()]);

        return $this->render('user/comment.html.twig', [
            'comments' => $comments
        ]);
    }
}
