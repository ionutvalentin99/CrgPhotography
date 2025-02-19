<?php

namespace App\Controller\Client;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\ContactType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $message = new Contact();
        $form = $this->createForm(ContactType::class, null, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $message->setName($data->getName());
            $message->setEmail($data->getEmail());
            $message->setSubject($data->getSubject());
            $message->setMessage($data->getMessage());
            $message->setStatus('pending');
            $message->setCreatedAt(new DateTime('now'));
            $message->setUpdatedAt(new DateTime('now'));

            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message sent successfully!');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
