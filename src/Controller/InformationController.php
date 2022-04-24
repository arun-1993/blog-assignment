<?php

namespace App\Controller;

use App\Service\Information;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class InformationController extends AbstractController
{
    /**
     * @Route("/info", name="information")
     */
    public function info(Information $information)
    {
        $overview = $information->overview();
        dump($overview);
        return new Response("Test");
    }
}