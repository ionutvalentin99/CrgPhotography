<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/albums', name: 'app_album')]
    public function index(AlbumRepository $repository): Response
    {
        return $this->render('album/index.html.twig', [
            'albums' => $repository->getAlbumsDesc(),
        ]);
    }

    #[Route('/album/{id}', name: 'app_album_show')]
    public function show(int $id, AlbumRepository $repository): Response
    {
        $album = $repository->find($id);
        if (!$album) {
            throw $this->createNotFoundException('Album with id ' . $id . ' cannot be found');
        }
        $images = $album->getImages()->toArray();

        return $this->render('album/show.html.twig', [
            'album' => $album,
            'images' => $images,
        ]);
    }
}
