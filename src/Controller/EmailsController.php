<?php

namespace App\Controller;

use App\Entity\Emails;
use App\Form\EmailsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/emails")
 */
class EmailsController extends AbstractController
{
    /**
     * @Route("/", name="emails_index", methods={"GET"})
     */
    public function index(): Response
    {
        $emails = $this->getDoctrine()
            ->getRepository(Emails::class)
            ->findAll();

        return $this->render('emails/index.html.twig', [
            'emails' => $emails,
        ]);
    }

    /**
     * @Route("/new", name="emails_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $email = new Emails();
        $form = $this->createForm(EmailsType::class, $email);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($email);
            $entityManager->flush();

            return $this->redirectToRoute('emails_index');
        }

        return $this->render('emails/new.html.twig', [
            'email' => $email,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="emails_show", methods={"GET"})
     */
    public function show(Emails $email): Response
    {
        return $this->render('emails/show.html.twig', [
            'email' => $email,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="emails_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Emails $email): Response
    {
        $form = $this->createForm(EmailsType::class, $email);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('emails_index');
        }

        return $this->render('emails/edit.html.twig', [
            'email' => $email,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="emails_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Emails $email): Response
    {
        if ($this->isCsrfTokenValid('delete'.$email->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($email);
            $entityManager->flush();
        }

        return $this->redirectToRoute('emails_index');
    }
}
