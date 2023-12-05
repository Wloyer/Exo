<?php

namespace App\Controller;
use App\Entity\Marques;
use App\Form\MarquesType;
Use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/marques')]
class MarquesController extends AbstractController
{
    #[Route('/', name: 'app_marques')]
    public function index(EntityManagerInterface $em , Request $request ): Response
    {
        $marques = new Marques();
        $form = $this->createForm(MarquesType::class, $marques);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $logoFile = $form->get('logo') ->getData();

            if($logoFile) {
                $newFileName = uniqid().'.'.$logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('upload_directory'),
                        $newFileName
                    );
                } catch(FileException $e) {
                    $this->addFlash('error', 'Impossible d\ajouter le logo');
                }
            
            $marques->setLogo($newFileName);
        }
            $em->persist($marques);
            $em->flush();
            $this->addFlash('success','Marques ajoutée');
        }

        $marques = $em->getRepository(Marques::class)->findAll();
        return $this->render('marques/index.html.twig', [
            'marques' => $marques,
            'ajout' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name:'marques')]
public function marques(Marques $marques, Request $request, EntityManagerInterface $em): Response
{
    if ($marques == null) {
        $this->addFlash('danger', 'Marques introuvables');
        return $this->redirectToRoute('app_marques');
    }

    $oldLogo = $marques->getLogo(); // Stocke l'ancien nom de fichier du logo
    $form = $this->createForm(MarquesType::class, $marques);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $logoFile = $form->get('logo')->getData();
        if ($logoFile) {
            $newFileName = uniqid().'.'.$logoFile->guessExtension();
            try {
                $logoFile->move(
                    $this->getParameter('upload_directory'),
                    $newFileName
                );
                $marques->setLogo($newFileName);

                if ($oldLogo) {
                    $oldFilePath = $this->getParameter('upload_directory').'/'.$oldLogo;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
            } catch (FileException $e) {
                $this->addFlash('error', 'Impossible d\'ajouter le logo');
            }
        } else {
            $marques->setLogo($oldLogo);
        }

        $em->persist($marques);
        $em->flush();
        $this->addFlash('success', 'Marques mise à jour');
    }

    return $this->render('marques/show.html.twig', [
        'marques' => $marques,
        'edit' => $form->createView()
    ]);
}
    #[Route('/delete/{id}', name:'delete_marques')]
    public function delete ( Marques $marques, EntityManagerInterface $em ){
        if($marques == null) {
            $this->addFlash('danger','Marques introuvable');
            return $this->redirectToRoute('app_marques');
    }

    $em->remove($marques);
    $em->flush();

    $this->addFlash('warning','Marques supprimer');
    return $this->redirectToRoute('app_marques');
    }
}
