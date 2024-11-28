<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageController extends AbstractController
{
    #[Route('/image/upload', name: 'app_image_upload')]
    public function uploadImage(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['uploadedFile']->getData();

            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                // Move the file to the directory where images are stored
                $uploadedFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                // Update the entity
                $image->setFilename($newFilename);
                $image->setPath($this->getParameter('images_directory'));
                $image->setUploadedAt(new DateTime());

                // Persist to the database
                $em->persist($image);
                $em->flush();

                $this->addFlash('success', 'Image uploaded successfully!');

                return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
            }
        }

        return $this->render('image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}