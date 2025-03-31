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

class ImageController extends AbstractController
{
    #[Route('admin/image/upload', name: 'app_image_upload')]
    public function upload(Request $request, ImageService $imageService): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['image']->getData();
            $imageService->upload($uploadedFile, $image);

            $this->addFlash('success', 'Image uploaded successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
        }

        return $this->render('admin/image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('admin/image/delete/{id}', name: 'app_image_delete')]
    public function delete(int $id, EntityManagerInterface $em, ImageService $imageService): Response
    {
        $image = $em->getRepository(Image::class)->find($id);

        if (!$image) {
            throw $this->createNotFoundException('Image with id ' . $id . ' cannot be found');
        }
        $album = $image->getAlbum();
        $imageService->delete($image);

        if ($album->getImages()->isEmpty()) {
            $em->remove($album);
            $em->flush();

            return $this->redirectToRoute('app_album');
        }

        return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
    }

    #[Route('admin/image/changeThumbnail/{id}', name: 'app_image_thumbnail')]
    public function changeThumbnail(int $id, EntityManagerInterface $em): Response
    {
        $image = $em->getRepository(Image::class)->find($id);
        if (!$image) {
            throw $this->createNotFoundException('Image with id ' . $id . ' cannot be found');
        }

        $album = $image->getAlbum();
        $album->setThumbnail($image);

        $em->flush();

        return $this->redirectToRoute('app_album');
    }
}
