<?php

namespace App\Controller;

Use App\Form\ModeleType;
use App\Entity\Modeles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/modeles")]
class ModelesController extends AbstractController
{
    #[Route('/', name: 'app_modeles')]
    public function index( EntityManagerInterface $em , Request $request ): Response
    {
        $modeles = new Modeles();
        $form = $this->createForm( ModeleType::class, $modeles );

        $form->handleRequest($request);
        if( $form->isSubmitted() && $form->isValid() ) {

            $em->persist($modeles);
            $em->flush();

            $this->addFlash('success','Modéles Ajoutée');
        }
            $modeles = $em->getRepository(Modeles::class)->findAll();
        return $this->render('modeles/index.html.twig', [
            'modeles' => $modeles,
            'ajout' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name:'modeles')]
    public function modeles( EntityManagerInterface $em, Request $request ,Modeles $modeles): Response
    {

        if($modeles == null) {
            $this->addFlash('danger','Modeles introuvable');
            return $this->redirectToRoute('app_modeles');
        }
        $form = $this->createForm( ModeleType::class,  $modeles );
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em->persist($modeles);
            $em->flush();
            $this->addFlash('success','Modéles mise à jour');
        }

        return $this->render('modeles/show.html.twig', [
            'modeles'=> $modeles,
            'edit'=> $form->createView()
        ]);
    }
    #[Route('/delete/{id}', name:'delete_modeles')]
    public function editModel( EntityManagerInterface $em, Modeles $modeles){
        if($modeles == null) {
            $this->addFlash('danger','Modeles introuvables');
            return $this->redirectToRoute('app_modeles');
    }

    $em->remove($modeles);
    $em->flush();

    $this->addFlash('warning','Modéles supprimer');
    return $this->redirectToRoute('app_modeles');
}
}