<?php

namespace App\Controller\Admin;

use App\Repository\ContactRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/mesaje')]
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
