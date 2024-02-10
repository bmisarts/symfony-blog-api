<?php

namespace App\Services;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArticleService
{
    private $entityManager;
    private $articleRepository;

    public function __construct(EntityManagerInterface $entityManager, ArticleRepository $articleRepository)
    {
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
    }

    public function getAll(): array
    {
        return $this->articleRepository->getArticles();
    }
    public function findOne($id): Article
    {
        return $this->articleRepository->getArticleById($id);
    }
    
    public function save(Article $article)
    {
        try {
            // Vérifier si un article avec le même titre existe déjà
            $existingArticle = $this->entityManager->getRepository(Article::class)
                ->findOneBy(['title' => $article->getTitle()]);

            if ($existingArticle !== null) {
                throw new \Exception('Un article avec le même titre existe déjà.');
            }
            // Persister l'article en base de données
            $this->articleRepository->createArticle($article);
        } catch (\Exception $e) {
            // Gérer l'exception pour les contraintes d'unicité (doublons)
            throw new \Exception($e->getMessage());
        }
    }
    
    public function update(int $id, Article $article): JsonResponse
    { 
        $existingArticle = $this->findOne($id);
        
        if ($existingArticle !== null) {
            return new JsonResponse(['message' => 'Article inexistant'], Response::HTTP_NOT_FOUND);
        }        
        // Save the updated article
        $this->articleRepository->UpdateArticle($id, $article);
        // Gérer l'exception pour les contraintes d'unicité (doublons)
        
        return new JsonResponse(['message' => 'Article Modified avec succes'], 200);
    }
    
    
    public function delete($id): JsonResponse
    {
        $article = $this->articleRepository->getArticleById($id);
        if (!$article) {
            return  new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        $this->articleRepository->DeleteArticle($article);
        return new JsonResponse(['message' => 'Article deleted'], 200);
    }
}
