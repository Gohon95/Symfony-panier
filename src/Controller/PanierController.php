<?php

namespace App\Controller;

use App\Entity\Panier;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 *  @Route("/panier")
 */
class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $panier = $em->getRepository(Panier::class)->findAll();
        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'controller_name' => 'PanierController',
        ]);
    }

    /**
     * @Route("/panier/delete/{id}", name="delete_produit_from_panier")
     */
    public function delete(Panier $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();
            $this->addFlash("success", $translator->trans('flash.panier.supprimer'));
        } else {
            $this->addFlash("danger", $translator->trans('flash.panier.passupprimer'));
        }
        return $this->redirectToRoute('panier');
    }

    /**
     * @Route("/panier/acheter", name="panier_acheter")
     */
    public function panierAcheter(TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $paniers = $em->getRepository(Panier::class)->findAll();
        if ($paniers != null) {
            foreach ($paniers as $panier) {
                $panier->setEtat(true);
            }
            $em->persist($panier);
            $em->flush();
            $this->addFlash("success", $translator->trans('flash.panier.acheter'));
        } else {
            $this->addFlash("danger", $translator->trans('flash.panier.pasacheter'));
        }
        return $this->redirectToRoute('panier');
    }
}
