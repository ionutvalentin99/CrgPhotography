<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    public function __construct(
        private readonly ParameterBagInterface  $parameterBag,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function upload($uploadedFile, Image $image): void
    {
        if ($uploadedFile) {
            $uploadsDirectory = $this->parameterBag->get('kernel.project_dir') . '/public/assets/images';
            $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move($uploadsDirectory, $newFilename);

            if ($image->getAlbum()->getThumbnail() === null) {
                $image->getAlbum()->setThumbnail($image);
            }

            $image->setFilename($uploadedFile->getClientOriginalName());
            $image->setPath('/assets/images/' . $newFilename);
            $image->setUploadedAt(new DateTime('now'));

            $this->entityManager->persist($image);
            $this->entityManager->flush();
        }
    }

    public function delete($image): void
    {
        $album = $image->getAlbum();
        if ($image === $album->getThumbnail()) {
            // Get all images of the album except the one being deleted
            $images = $album->getImages()->toArray();
            $images = array_filter($images, fn($img) => $img !== $image);

            // Set a random image as the new thumbnail, or null if no other images exist
            $newThumbnail = count($images) > 0 ? $images[array_rand($images)] : null;
            $album->setThumbnail($newThumbnail);
        }

        $path = $this->parameterBag->get('kernel.project_dir') . '/public' . $image->getPath();
        if (file_exists($path)) {
            unlink($path);
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }
}