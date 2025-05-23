<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class AlbumController extends AbstractController
{
    #[Route('albums/create', name: 'app_album_create')]
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

    #[Route('albums/delete/{id<\d+>}', name: 'app_album_delete')]
    public function delete($id, AlbumRepository $repository, EntityManagerInterface $em, ParameterBagInterface $parameterBag): Response
    {
        $album = $repository->find($id);
        if (!$album) {
            throw $this->createNotFoundException("The album with id $id does not exist");
        }

        foreach ($album->getImages() as $image) {
            $filePath = $parameterBag->get('kernel.project_dir') . '/public' . $image->getPath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $image->setAlbum(null);
        }

        $em->flush();
        $em->remove($album);
        $em->flush();

        return $this->redirectToRoute('app_album');
    }
}
