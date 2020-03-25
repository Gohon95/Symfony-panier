
<?php

namespace App\Controller;

use App\Entity\Panier;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *  @Route("/panier")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return new Response('It works!');
    }
}
