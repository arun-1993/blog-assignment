<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, UserRepository $userRepository, ValidatorInterface $validatorInterface): Response
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'maxlength' => 180,
                ],
                'required' => true,
            ])
            ->add('username', TextType::class, [
                'label' => 'Username',
                'attr' => [
                    'maxlength' => 180,
                ],
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'maxlength' => 180,
                ],
                'required' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords do not match',
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password']
            ])
            ->add('register', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $input = $form->getData();
            
            if($userRepository->findOneBy(['username' => $input['username']]))
            {
                $form->addError(new FormError('User already exists'));
            }

            else
            {

                $user = new User();

                $user->setName($input['name']);
                $user->setUsername($input['username']);
                $user->setEmail($input['email']);
                $user->setPassword($userPasswordHasherInterface->hashPassword($user, $input['password']));
                $user->setRawPassword($input['password']);

                $errors = $validatorInterface->validate($user);
                
                if(count($errors) > 0)
                {
                    return $this->render('registration/index.html.twig', [
                        'registration' => $form->createView(),
                        'errors' => $errors,
                    ]);
                }

                else
                {
                    $userRepository->add($user);

                    $this->addFlash('success', 'Registration Successful');
                    return $this->redirectToRoute('app_login');
                }
            }
        }
            
        return $this->render('registration/index.html.twig', [
            'registration' => $form->createView(),
            'errors' => null,
        ]);
    }
}
