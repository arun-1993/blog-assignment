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

class DishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('category', EntityType::class, ['class' => Category::class])
            ->add('image', FileType::class, ['required' => false])
            ->add('description', TextareaType::class)
            ->add('content')
            ->add('Save', SubmitType::class)
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
