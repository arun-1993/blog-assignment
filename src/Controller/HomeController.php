<?php

namespace App\Controller;

use App\Repository\DishRepository;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(DishRepository $dishRepository): Response
    {
        $dishes = $dishRepository->findAll();
        $random = array_rand($dishes);

        return $this->render('home/index.html.twig', [
            'dish' => $dishes[$random],
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(DishRepository $dishRepository): Response
    {
        $dishes = $dishRepository->findAll();
        $random = array_rand($dishes);

        return $this->render('home/index.html.twig', [
            'dish' => $dishes[$random],
        ]);
    }
}
