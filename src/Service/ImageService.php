<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    )
    {
    }

    public function upload($uploadedFile, Image $image): void
    {
        if ($uploadedFile) {
            $uploadsDirectory = $this->parameterBag->get('kernel.project_dir') . '/public/assets/images';
            $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move($uploadsDirectory, $newFilename);

            $image->setFilename($uploadedFile->getClientOriginalName());
            $image->setPath('/assets/images/' . $newFilename);
            $image->setUploadedAt(new DateTime('now'));
        }
    }
}