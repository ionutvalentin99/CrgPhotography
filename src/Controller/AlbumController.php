<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/albums', name: 'app_album')]
    public function index(AlbumRepository $repository): Response
    {
        $albums = $repository->findAll();

        return $this->render('album/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/album/{id}', name: 'app_album_show')]
    public function show($id, AlbumRepository $repository): Response
    {
        $album = $repository->find($id);
        $images = $album->getImages();

        return $this->render('album/show.html.twig', [
            'album' => $album,
            'images' => $images,
        ]);
    }

    #[Route('/albums/create', name: 'app_album_create')]
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

            return $this->render('album/create.html.twig', [
                'form' => $form->createView(),
            ]);
    }
}
