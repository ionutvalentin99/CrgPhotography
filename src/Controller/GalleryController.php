<?php

namespace App\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GalleryController extends AbstractController
{
    #[Route('/gallery', name: 'app_gallery')]
    public function index(AlbumRepository $albumRepository): Response
    {
        return $this->render('gallery/index.html.twig', [
            'albums' => $albumRepository->findVisibleSummaries(),
        ]);
    }

    #[Route('/gallery/{id}', name: 'app_gallery_show', requirements: ['id' => '\d+'])]
    public function show(Album $album): Response
    {
        if (!$album->isVisible()) {
            throw $this->createNotFoundException();
        }

        $photos = $album->getPhotos()->filter(fn ($photo) => $photo->isVisible());

        return $this->render('gallery/show.html.twig', [
            'album' => $album,
            'photos' => $photos,
        ]);
    }
}
