<?php
/**
 * Created by PhpStorm.
 * User: wilder21
 * Date: 21/01/19
 * Time: 11:09
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('contact/contact.html.twig');
    }
}