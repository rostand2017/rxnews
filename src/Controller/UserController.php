<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\News;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/index.html.twig', compact("users", "categories", "categoriesWithNews", "popularsNews"));
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($user->getPlainPassword()){
                $entityManager = $this->getDoctrine()->getManager();
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $user->setRoles("ROLE_ADMIN");
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash("custom_success", "Benutzer erfolgreich hinzugefügt");
                return $this->redirectToRoute('user_index');
            }
            $this->addFlash("custom_error", "Sie müssen ein Passwort eingeben");
        }

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        $form = $form->createView();
        return $this->render('user/new.html.twig', compact("user", "form", "categories", "categoriesWithNews", "popularsNews"));
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        return $this->render('user/show.html.twig', compact("user", "categoriesWithNews", "popularsNews", "categories"));
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($user->getPlainPassword()){
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("custom_success", "Benutzer erfolgreich bearbeitet");
            return $this->redirectToRoute('user_index');
        }

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        $form = $form->createView();
        return $this->render('user/new.html.twig', compact("user", "form", "categories", "categoriesWithNews", "popularsNews"));
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
