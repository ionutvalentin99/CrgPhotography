<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Image;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filename', TextType::class, [
                'label' => 'Filename',
                'attr' => [
                    'class' => 'block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500', // Tailwind classes for styling
                    'placeholder' => 'Enter the filename',
                ],
            ])
            ->add('album', EntityType::class, [
                'class' => Album::class,
                'choice_label' => 'title',
                'label' => 'Select Album',
                'attr' => [
                    'class' => 'block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500', // Dropdown styling
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'w-full px-4 py-2 bg-blue-600 text-white font-medium text-lg rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
