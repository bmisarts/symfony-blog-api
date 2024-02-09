<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'article_list', methods:['get'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $articles = $doctrine
            ->getRepository(Article::class)
            ->findAll();
   
        $data = [];
   
        foreach ($articles as $article) {
           $data[] = [
               'id' => $article->getId(),
               'title' => $article->getTitle(),
               'description' => $article->getDescription(),
           ];
        }
   
        return $this->json($data);
    }
    
    
    #[Route('/article', name: 'article_create', methods:['post'] )]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        //validateur de données
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(ArticleType::class);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 400);
        }
        
        $entityManager = $doctrine->getManager();
   
        $article = new Article();
        $article->setTitle($request->request->get('title'));
        $article->setDescription($request->request->get('description'));
        
        
        $entityManager->persist($article);
        $entityManager->flush();
           
        return new JsonResponse(['message' => 'Article enregistré avec succès'], 200);
    }
}
