<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('admin/albums/create', name: 'app_album_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->setCreatedAt(new DateTime('now'));
            $em->persist($album);
            $em->flush();

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('admin/album/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
