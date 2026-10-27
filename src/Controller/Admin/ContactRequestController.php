<?php

namespace App\Controller\Admin;

use App\Repository\ContactRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/mesaje')]
#[IsGranted('ROLE_ADMIN')]
final class ContactRequestController extends AbstractController
{
    #[Route('', name: 'admin_contact_requests')]
    public function index(ContactRequestRepository $contactRequestRepository): Response
    {
        return $this->render('admin/contact_request/index.html.twig', [
            'contactRequests' => $contactRequestRepository->findAllNewestFirst(),
        ]);
    }
}
