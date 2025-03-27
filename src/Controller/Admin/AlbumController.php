<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/admin/albums/create', name: 'app_album_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($album);
            $em->flush();

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('admin/album/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/albums/{id}/delete', name: 'app_album_delete')]
    public function delete($id, AlbumRepository $repository, EntityManagerInterface $em): Response
    {
        $album = $repository->find($id);
        if (!$album) {
            throw $this->createNotFoundException("The album with id $id does not exist");
        }

        $album->setThumbnail(null);
        $em->persist($album);
        $em->flush();
        $em->remove($album);
        $em->flush();

        return $this->redirectToRoute('app_album');
    }
}
