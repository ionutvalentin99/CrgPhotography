<?php

namespace App\Form;

use App\Entity\Album;
use DateTime;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumType extends AbstractType
{
    /**
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $date = new DateTime('now');
        $builder
            ->add('title', TextType::class, [
                'label' => 'Album Title',
                'attr' => [
                    'class' => 'w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => 'Enter album title',
                ],
            ])
            ->add('shootDate', DateType::class, [
                'mapped' => true,
                'label' => 'Shoot Date: ',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y')),
                'attr' => [
                    'class' => 'w-full p-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:border-blue-500 focus:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:bg-gray-800 dark:focus:border-blue-500 transition-all duration-300',
                    'name' => 'shoot_date'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create Album',
                'attr' => [
                    'class' => 'w-full px-4 py-2 bg-blue-600 text-white font-medium text-lg rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
        ]);
    }
}
