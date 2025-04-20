<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class ImageService
{
    public function __construct(
        private ParameterBagInterface  $parameterBag,
        private EntityManagerInterface $entityManager,
        private SluggerInterface       $slugger,
    )
    {
    }

    public function upload($uploadedFile, Image $image): void
    {
        if ($uploadedFile) {
            $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $this->slugger->slug($originalFileName);
            $newFileName = $safeFileName . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadDirectory = $this->parameterBag->get('uploads_directory');

            try {
                $uploadedFile->move($uploadDirectory, $newFileName);
            } catch (FileException $e) {
                echo $e->getMessage();
            }

            if ($image->getAlbum()->getThumbnail() === null) {
                $image->getAlbum()->setThumbnail($image);
            }

            $image->setFilename($newFileName);
            $image->setPath('/assets/images/' . $newFileName);
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

    public function findImageOr404(int $id): Image
    {
        $image = $this->entityManager->getRepository(Image::class)->find($id);
        if (!$image) {
            throw new NotFoundHttpException('Image with id ' . $id . ' cannot be found');
        }
        return $image;
    }
}