<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Dish;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;

class DishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Title',
                'attr' => ['maxlength' => 255],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
            ])
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Excerpt (A short introduction or description of the dish)',
                'attr' => [
                    'rows' => 5,
                    'maxlength' => 2000,
                ],
            ])
            ->add('content', CKEditorType::class, [
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 4294967295,
                ],
            ])
            ->add('Save', SubmitType::class, ['label' => 'Create Dish'])
        ;

        // $builder
        //     ->get('image')->addModelTransformer(new CallbackTransformer(
        //         function($image) {
        //             return null;
        //         },
        //         function($image) {
        //             return $this->container->getParameter('images_folder').'/'.$image;
        //         }
        //     ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dish::class,
        ]);
    }
}
