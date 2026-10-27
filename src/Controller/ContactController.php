<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Form\ContactRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    private const SUCCESS_MESSAGE = 'Mulțumesc! Mesajul tău a fost trimis, te voi contacta cât mai curând.';

    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        RateLimiterFactoryInterface $contactFormLimiter,
    ): Response {
        $contactRequest = new ContactRequest();
        $form = $this->createForm(ContactRequestType::class, $contactRequest);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->render('contact/index.html.twig', ['form' => $form]);
        }

        if (!$contactFormLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            $this->addFlash('error', 'Ai trimis prea multe mesaje într-un timp scurt. Te rog încearcă din nou mai târziu sau scrie-mi direct pe email.');

            return $this->render('contact/index.html.twig', ['form' => $form], new Response(status: Response::HTTP_TOO_MANY_REQUESTS));
        }

        // Bots get the same response as people, so they can't tell the honeypot caught them.
        if ('' !== (string) $form->get('website')->getData()) {
            $this->addFlash('success', self::SUCCESS_MESSAGE);

            return $this->redirectToRoute('app_contact');
        }

        if ($form->isValid()) {
            $entityManager->persist($contactRequest);
            $entityManager->flush();

            $this->addFlash('success', self::SUCCESS_MESSAGE);

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', ['form' => $form]);
    }
}
