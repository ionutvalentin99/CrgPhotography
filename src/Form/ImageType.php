<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Image;
use App\Repository\AlbumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '15M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG).',
                    ])
                ]
            ])
            ->add('album', EntityType::class, [
                'class' => Album::class,
                'choice_label' => function ($album) {
                    return sprintf('%s - %s', $album->getTitle(), $album->getShootDate()->format('d.m.Y'));
                },
                'label' => 'Select Album',
                'query_builder' => function (AlbumRepository $repository) {
                    return $repository->createQueryBuilder('a')
                        ->orderBy('a.shoot_date', 'DESC'); // Adjust 'title' and 'ASC' as needed
                },
                'attr' => [
                    'class' => 'block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500', // Dropdown styling
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Upload',
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
