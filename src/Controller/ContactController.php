<?php
/**
 * Created by PhpStorm.
 * User: wilder21
 * Date: 21/01/19
 * Time: 11:09
 */

namespace App\Controller;

use App\Entity\Mail;
use App\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    const MAIL="wild.movies3@gmail.com";

    /**
     * @Route("/contact", name="contact", methods={"GET","POST"})
     */
    public function index(Request $request, \Swift_Mailer $mailer): Response
    {
        $mail=new Mail();
        $form = $this->createForm(MailType::class, $mail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mail);
            $entityManager->flush();

            $email=$mail->getEmail();
            $object=$mail->getObject();
            $emailText=$mail->getEmailText();
            $message = (new \Swift_Message($object))
                ->setFrom([$email => 'sender name'])
                ->setTo([self::MAIL])
                ->setBody(
                    $this->renderView(
                        'contact/message.html.twig', [
                            'emailText' => $emailText,
                            'object' => $object,
                            'email' => $email
                        ]
                    ),
                    'text/html'
                );

            if ($mailer->send($message)) {
                $this->addFlash(
                    'success',
                    "Email envoyé avec succès !"
                );
            } else {
                $this->addFlash(
                    'danger',
                    "L'email n'a pas pu être envoyé !"
                );
            }

            return $this->redirectToRoute('contact');
        }
        return $this->render('contact/contact.html.twig', [
            'mail' => $mail,
            'form' => $form->createView(),
        ]);
    }
}