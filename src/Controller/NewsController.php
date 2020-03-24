<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/news")
 */
class NewsController extends AbstractController
{
    /**
     * @Route("/", name="news_index", methods={"GET"})
     */
    public function index(Request $request, NewsRepository $newsRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$news]);
        }
        $search = $request->query->get('q');
        $position = $request->query->get('p');
        $position = $position ? $position : 0;
        $search = $search ? $search : "";
        $news = $em->getRepository(News::class)->getNewsByTitleContent2($search, $position);
        return $this->render('news/index.html.twig', compact("news", "popularsNews", "categories", "categoriesWithNews"));
    }

    /**
     * @Route("/new", name="news_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = $fileUploader->upload( FileUploader::NEWS_TYPE, $file);
                $news->setImage($fileName);
                $entityManager->persist($news);
                $entityManager->flush();
                $this->addFlash("custom_success", "Nachricht erfolgreich hinzugefügt");
                return $this->redirectToRoute('news_index');
            }else{
                $this->addFlash("custom_error", "Das bild wird nicht gültig hochladen");
            }
        }

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $_news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$_news]);
        }
        $form = $form->createView();
        return $this->render('news/new.html.twig', compact("news", "form", "popularsNews", "categories", "categoriesWithNews"));
    }

    /**
     * @Route("/{id}", name="news_show", methods={"GET"})
     */
    public function show(News $news): Response
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $_news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$_news]);
        }
        return $this->render('news/show.html.twig', compact("news", "popularsNews", "categories", "categoriesWithNews"));
    }

    /**
     * @Route("/{id}/edit", name="news_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, News $news, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();
            $fileToDelete = null;
            if ($file) {
                $fileName = $fileUploader->upload( FileUploader::NEWS_TYPE, $file);
                $fileToDelete = $news->getImage();
                $news->setImage($fileName);
            }
            $news->setUpdatedat(new \DateTime());
            $em->persist($news);
            $em->flush();
            if($fileToDelete)
                $fileUploader->deleteFile( FileUploader::NEWS_TYPE, $fileToDelete);
            $this->addFlash("custom_success", "Nachricht erfolgreich bearbeitet");
            return $this->redirectToRoute('news_index');
        }

        $categories = $em->getRepository(Category::class)->findAll();
        $popularsNews = $em->getRepository(News::class)->getPopularsNews(5);
        $categoriesWithNews = [];
        foreach ($categories as $category){
            $_news = $em->getRepository(News::class)->findBy(['category'=>$category->getId()], ['createdat'=>'desc'], 4, 0);
            array_push($categoriesWithNews, ['category'=>$category, 'news'=>$_news]);
        }
        $form = $form->createView();
        return $this->render('news/edit.html.twig', compact("news", "form", "popularsNews", "categories", "categoriesWithNews"));
    }

    /**
     * @Route("/{id}", name="news_delete", methods={"DELETE"})
     */
    public function delete(Request $request, News $news): Response
    {
        if ($this->isCsrfTokenValid('delete'.$news->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($news);
            $entityManager->flush();
        }

        return $this->redirectToRoute('news_index');
    }
}
