<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class ImageController extends AbstractController
{
    public function __construct(
        private readonly ImageService           $imageService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('image/upload', name: 'app_image_upload')]
    public function upload(Request $request): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['image']->getData();
            $this->imageService->upload($uploadedFile, $image);

            $this->addFlash('success', 'Image uploaded successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
        }

        return $this->render('admin/image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('image/delete/{id}', name: 'app_image_delete')]
    public function delete(int $id): Response
    {
        $image = $this->imageService->findImageOr404($id);
        $album = $image->getAlbum();
        $this->imageService->delete($image);

        if ($album->getImages()->isEmpty()) {
            $this->entityManager->remove($album);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_album');
        }

        return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
    }

    #[Route('image/changeThumbnail/{id}', name: 'app_image_thumbnail')]
    public function changeThumbnail(int $id): Response
    {
        $image = $this->imageService->findImageOr404($id);
        $album = $image->getAlbum();
        $album->setThumbnail($image);

        $this->entityManager->flush();

        return $this->redirectToRoute('app_album');
    }
}