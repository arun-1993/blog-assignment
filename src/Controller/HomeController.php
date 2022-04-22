<?php

namespace App\Controller;

use App\Repository\DishRepository;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
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
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(MailerInterface $mailerInterface, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('message', TextareaType::class, [
                'attr' => ['rows' => '5']
            ])
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $input = $form->getData();

            $name = $input['name'];
            $user_email = $input['email'];
            $message = $input['message'];

            $email = (new TemplatedEmail())
                ->from($user_email)
                ->to('arun0306.r@gmail.com')
                ->cc('support@cuisineculture.com')
                ->subject('Customer Submission')
                ->htmlTemplate('home/mail.html.twig')
                ->context([
                    'name' => $name,
                    'message' => $message,
                    'user_email' => $user_email,
                ])
            ;

            $mailerInterface->send($email);
            $this->addFlash('success', 'Your message was sent. Thank you for contacting us!');
            return $this->redirect($this->generateUrl('contact'));
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
