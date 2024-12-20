<?php

namespace App\Controller;

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

        $path = $this->getParameter('kernel.project_dir') . '/public' . $image->getPath();
        if (file_exists($path)) {
            unlink($path);
        }

        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
    }
}