<?php

namespace App\Form;

use App\Entity\ContactRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nume',
                'attr' => ['placeholder' => 'Numele tău', 'class' => 'form-control'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'adresa@exemplu.ro', 'class' => 'form-control'],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Telefon (opțional)',
                'required' => false,
                'attr' => ['placeholder' => '07xx xxx xxx', 'class' => 'form-control'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Mesaj',
                'attr' => [
                    'placeholder' => 'Spune-mi câteva cuvinte despre ședința pe care o ai în minte...',
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
            // Honeypot: hidden from people, filled in by most spam bots.
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Website',
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactRequest::class,
        ]);
    }
}
