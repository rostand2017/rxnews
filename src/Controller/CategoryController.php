<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\News;
use App\Form\CategoryType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="category_index", methods={"GET"})
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

        return $this->render('category/index.html.twig', compact("categories", "popularsNews", "categoriesWithNews"));
    }

    /**
     * @Route("/new", name="category_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = $fileUploader->upload( FileUploader::CATEGORY_TYPE, $file);
                $category->setImage($fileName);
                $entityManager->persist($category);
                $entityManager->flush();
                $this->addFlash("custom_success", "Kategorie erfolgreich hinzugefügt");
                return $this->redirectToRoute('category_index');
            }else{
                $this->addFlash("custom_error", "Das bild wird nicht gültig hochladen");
            }
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
        return $this->render('category/new.html.twig', compact('category', 'form', 'popularsNews', 'categories', 'categoriesWithNews'));
    }

    /**
     * @Route("/{id}", name="category_show", methods={"GET"})
     */
    public function show(Category $category): Response
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }

        return $this->render('category/show.html.twig', compact("categories", "popularsNews", "categoriesWithNews", "category"));
    }

    /**
     * @Route("/{id}/edit", name="category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Category $category, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = $fileUploader->upload( FileUploader::CATEGORY_TYPE, $file);
                $fileToDelete = $category->getImage();
                $category->setImage($fileName);
            }
            $em->persist($category);
            $em->flush();
            $fileUploader->deleteFile( FileUploader::CATEGORY_TYPE, $fileToDelete);
            $this->addFlash("custom_success", "Kategorie '".$category->getTitle()."' ist erfolgreich bearbeitet");
            return $this->redirectToRoute('category_index');
        }
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            "categories" => $categories,
            "categoriesWithNews" => $categoriesWithNews,
            "popularsNews" => $popularsNews,
        ]);
    }

    /**
     * @Route("/{id}", name="category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('category_index');
    }
}
