<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImageController extends AbstractController
{
    #[Route('/image/upload', name: 'app_image_upload')]
    public function uploadImage(Request $request, EntityManagerInterface $em): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['image']->getData();

            if ($uploadedFile) {
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/assets/images';
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move($uploadsDirectory, $newFilename);

                $image->setFilename($uploadedFile->getClientOriginalName());
                $image->setPath('/assets/images/' . $newFilename);
                $image->setUploadedAt(new DateTime('now'));
            }

            $em->persist($image);
            $em->flush();

            $this->addFlash('success', 'Image uploaded successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $image->getAlbum()->getId()]);
        }

        return $this->render('image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/image/{id}', name: 'app_image_show', methods: ['GET'])]
    public function show($id, ImageRepository $repository): Response
    {
        $image = $repository->find($id);
        $imagePath = $image->getPath();

        return $this->render('image/index.html.twig', [
            'imagePath' => $imagePath,
        ]);
    }
}