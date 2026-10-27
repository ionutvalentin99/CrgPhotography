<?php

namespace App\Tests\Support;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

trait EntityFactoryTrait
{
    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createUser(array $roles = ['ROLE_ADMIN'], string $email = 'admin@example.com'): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setRoles($roles)
            ->setPassword('not-used-in-tests');

        $this->entityManager()->persist($user);
        $this->entityManager()->flush();

        return $user;
    }

    private function createAlbum(
        User $author,
        string $title,
        bool $visible = true,
        int $visiblePhotos = 0,
        int $hiddenPhotos = 0,
        ?\DateTimeImmutable $createdAt = null,
    ): Album {
        $entityManager = $this->entityManager();

        $album = (new Album())
            ->setTitle($title)
            ->setVisible($visible)
            ->setCreatedBy($author);

        if (null !== $createdAt) {
            $album->setCreatedAt($createdAt);
        }

        $entityManager->persist($album);

        $photos = [];
        for ($i = 0; $i < $visiblePhotos + $hiddenPhotos; ++$i) {
            $photo = (new Photo())
                ->setAlbum($album)
                ->setPath(sprintf('uploads/%s-%d.jpg', md5($title), $i))
                ->setSortOrder($i)
                ->setVisible($i < $visiblePhotos)
                ->setCreatedBy($author);

            $entityManager->persist($photo);
            $photos[] = $photo;
        }

        $entityManager->flush();

        if ([] !== $photos) {
            $album->setCover($photos[0]);
            $entityManager->flush();
        }

        return $album;
    }
}
