<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Image;
use App\Form\AlbumType;
use App\Form\ImageType;
use App\Service\ImageService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
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

    #[Route('/image/upload', name: 'app_image_upload')]
    public function upload(Request $request, EntityManagerInterface $em, ImageService $uploadService): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['image']->getData();
            $uploadService->upload($uploadedFile, $image);

            $em->persist($image);
            $em->flush();

            $this->addFlash('success', 'Image uploaded successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
        }

        return $this->render('image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/image/{id}/delete', name: 'app_image_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $image = $em->getRepository(Image::class)->find($id);
        if (!$image) {
            throw $this->createNotFoundException('Image with id ' . $id . ' cannot be found');
        }

        $album = $image->getAlbum();
        if ($image === $album->getThumbnail()) {
            // Get all images of the album except the one being deleted
            $images = $album->getImages()->toArray();
            $images = array_filter($images, fn($img) => $img !== $image);

            // Set a random image as the new thumbnail, or null if no other images exist
            $newThumbnail = count($images) > 0 ? $images[array_rand($images)] : null;
            $album->setThumbnail($newThumbnail);
        }

        $path = $this->getParameter('kernel.project_dir') . '/public' . $image->getPath();
        if (file_exists($path)) {
            unlink($path);
        }

        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
    }

    #[Route('/image/{id}/changeThumbnail', name: 'app_image_thumbnail')]
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
