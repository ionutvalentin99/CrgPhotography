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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email address'
                    ])
                ],
                'data' => $user ? $user->getEmail() : '',
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'attr' => [
                    'class' => "w-full rounded-md py-2.5 px-4 border text-sm outline-[#007bff]",
                    'placeholder' => 'Subject',
                    'required' => true
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your subject should be at least {{ limit }} characters.',
                    ])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'class' => "w-full rounded-md py-2.5 px-4 border text-sm outline-[#007bff]",
                    'placeholder' => 'Your message...',
                    'required' => true,
                    'rows' => 5,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 5,
                        'max' => 1000,
                        'minMessage' => 'Your message is less than {{ limit }} characters',
                        'maxMessage' => 'Your message is bigger than {{ limit }} characters',
                    ])
                ]
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Send',
                'attr' => [
                    'class' => "text-white bg-[#007bff] hover:bg-blue-600 font-semibold rounded-md text-sm px-4 py-2.5 w-full"
                ]
            ]);
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
