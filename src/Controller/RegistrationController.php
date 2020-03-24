<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /*
     *  php bin/console doctrine:mapping:import "App\Entity" annotation --path=src/Entity   MAPPING DE LA BD(reverse engineering)
     *  php bin/console make:entity --regenerate App  GENERATION OF GETTERS AND SETTERS
     *
     *
     * "php bin/console make:user" pour créer une classe user de base
     * "php bin/console make:auth" pour créer la méthode d'authentification de base
     * "php bin/console doctrine:migrations:diff" pour génerer les migrations.Cette commande crée également une table 'migration_versions' dans la BD
     * "php bin/console doctrine:migrations:migrate" pour appliquer les migrations en créant les nouvelles tables
     * "php bin/console make:registration-form"  pour créer le formulaire d'inscription
     */

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        #How to authenticate with XMLHttpRequest
        #Create a news applications

        if ($form->isSubmitted() && $form->isValid()) {
            /*if(!){
                #var_dump($form['email']->getErrors()[0]['messageTemplate']);
                return new JsonResponse(['error'=>$form->getErrors()[0]->getMessage()]);
            }*/
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            # $guardHandler->
            // do anything else you need here, like send an email
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
