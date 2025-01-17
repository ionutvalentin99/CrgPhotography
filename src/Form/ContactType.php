<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'class' => "w-full rounded-md py-2.5 px-4 border text-sm outline-[#007bff]",
                    'placeholder' => "Name",
                    'required' => true
                ],
                'data' => $user ? $user->getFirstName() . ' ' . $user->getLastName() : ''
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => "w-full rounded-md py-2.5 px-4 border text-sm outline-[#007bff]",
                    'placeholder' => 'example@example.com',
                    'required' => true
                ],
                'data' => $user ? $user->getEmail() : '',
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'attr' => [
                    'class' => "w-full rounded-md py-2.5 px-4 border text-sm outline-[#007bff]",
                    'placeholder' => 'Subject',
                    'required' => true
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'class' => "w-full rounded-md py-2.5 px-4 border text-sm outline-[#007bff]",
                    'placeholder' => 'Your message...',
                    'required' => true,
                    'rows' => 5,
                ]
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Send',
                'attr' => [
                    'class' => "text-white bg-[#007bff] hover:bg-blue-600 font-semibold rounded-md text-sm px-4 py-2.5 w-full"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'user' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
