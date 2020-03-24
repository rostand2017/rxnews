<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\News;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/change_password", name="change_password")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser()->getId());
        if($request->isMethod("POST")){
            $password = $request->request->get("password");
            $oldPassword = $request->request->get("old_password");
            if($passwordEncoder->isPasswordValid($user, $oldPassword)){
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $password)
                );
                $em->persist($user);
                $em->flush();
                $this->addFlash("message", "Passwort erfolgreich bearbeitet");
            }else
                $this->addFlash("message", "falsches Passwort");
        }
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        return $this->render('security/change_password.html.twig', compact("categories", "popularsNews", "categoriesWithNews"));
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
