<?php

namespace App\Controller;

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
}
