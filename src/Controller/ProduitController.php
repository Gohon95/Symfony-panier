<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Form\ProduitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        // Récupère Doctrine (service de gestion de BDD)
        $pdo = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        // Analyse la requête HTTP
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // Le formulaire a été envoyé, on le sauvegarde
            // On récupère le fichier du formulaire
            $fichier = $form->get('ImageUpload')->getData();
            // Si un fichier a été uploadé
            if($fichier){
                $nomFichier = uniqid() .'.'. $fichier->guessExtension();

                try{
                    $fichier->move(
                        $this->getParameter('dossier'),
                        $nomFichier
                    );
                }
                catch(FileException $e){
                    $this->addFlash(
                        "danger", 
                        $translator->trans('file.error')
                    );
                    return $this->redirectToRoute('home');
                }

                $produit->setImage($nomFichier);
            }

            $pdo->persist($produit); // prepare
            $pdo->flush();           // execute

            $this->addFlash("success", "Produit ajouté");
        }

        // Récupère tous les produits
        $produits = $pdo->getRepository(Produit::class)->findAll();
        /*
            ->findOneBy(['id' => 2])
            ->findBy(['nom' => 'Nom du produit'])
        */

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'form_produit_new' => $form->createView()
        ]);
    }

    /**
     * @Route("/produit/{id}", name="un_produit")
     */
    public function produit(Request $request, Produit $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $panier = new Panier($produit);
            $form = $this->createForm(PanierType::class, $panier);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($panier->getQuantite() <= $produit->getQuantite()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($panier);
                    $em->flush();
                    $this->addFlash('success', $translator->trans('flash.produit.ajoutpanier'));
                } else {
                    $this->addFlash('danger', $translator->trans('flash.produit.stock'));
                }
            }

            return $this->render('produit/produit.html.twig', [
                'produit' => $produit,
                'form_ajout_panier' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('produit');
        }
    }

    /**
     * @Route("/produit/delete/{id}", name="produit_delete")
     */
    public function delete(Produit $produit=null){
        if($produit != null){
            $pdo = $this->getDoctrine()->getManager();
            $pdo->remove($produit);
            $pdo->flush();

            $this->addFlash("success", "Produit supprimé");
        }
        else{
            $this->addFlash("danger", "Produit introuvable");
        }
        return $this->redirectToRoute('home');
    }
}
