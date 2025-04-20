<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Service\ContactService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class ContactController extends AbstractController
{
    #[Route('messages/unread', name: 'app_admin_messages_unread')]
    public function unreadMessages(EntityManagerInterface $entityManager): Response
    {
        $messages = $entityManager->getRepository(Contact::class)->getUnreadDesc();

        return $this->render('admin/messages/unread.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('messages/read', name: 'app_admin_messages_read')]
    public function readMessages(EntityManagerInterface $entityManager): Response
    {
        $messages = $entityManager->getRepository(Contact::class)->getReadDesc();

        return $this->render('admin/messages/read.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('messages/modify/{id}', name: 'app_admin_messages_modify')]
    public function modify(int $id, EntityManagerInterface $entityManager, Request $request, ContactService $contactService): Response
    {
        $message = $entityManager->getRepository(Contact::class)->find($id);
        if (!$message) {
            throw $this->createNotFoundException('Message with id ' . $id . ' cannot be found');
        }
        $contactService->modify($message, $request);

        $previousRoute = $request->headers->get('referer');
        if ($previousRoute) {
            return $this->redirect($previousRoute);
        }

        return $this->redirectToRoute('app_home');
    }
}
