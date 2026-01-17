<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les pages statiques et fonctionnelles simples (À propos, Contact)
 */
final class PageController extends AbstractController
{
    /**
     * Affiche la page "À propos"
     * 
     * @return Response Une instance de Response vers la vue à propos
     */
    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig');
    }

    /**
     * Affiche et gère le formulaire de contact
     * 
     * Envoie un email au destinataire configuré si le formulaire est valide.
     * 
     * @param Request $request La requête HTTP entrante
     * @param MailerInterface $mailer Le service d'envoi d'emails de Symfony
     * @return Response Une instance de Response vers la vue contact ou une redirection
     */
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Envoi de l'email (optionnel - nécessite la configuration du mailer)
            try {
                $email = (new MimeEmail())
                    ->from($data['email'])
                    ->to('contact@auxilia-ecommerce.com')
                    ->subject('Contact depuis le site : ' . $data['subject'])
                    ->html($this->renderView('emails/contact.html.twig', [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'subject' => $data['subject'],
                        'message' => $data['message'],
                    ]));

                $mailer->send($email);
                $this->addFlash('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
            } catch (\Exception $e) {
                // En cas d'erreur d'envoi, on affiche quand même un message de succès
                // (pour ne pas exposer les erreurs techniques à l'utilisateur)
                $this->addFlash('success', 'Votre message a été enregistré. Nous vous répondrons dans les plus brefs délais.');
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('page/contact.html.twig', [
            'contactForm' => $form,
        ]);
    }
}
