<?php

// src/Service/ArticleService.php
namespace App\Service;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exception\Article\ExistingTitleException;

class ArticleService
{
    private $articleRepository;
    private $entityManager;

    public function __construct(ArticleRepository $articleRepository, EntityManagerInterface $entityManager)
    {
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
    }

    public function all(): array
    {
        $articles = $this->articleRepository->findAll();
        
        $articleData = [];
        foreach ($articles as $article) {
            $articleData[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle()
            ];
        }
        
        return $articleData;
    }

    public function create(Article $article): void
    {
        // Vérifiez si un article avec le même titre existe déjà
        $existingArticle = $this->articleRepository->findOneBy(['title' => $article->getTitle()]);
        if ($existingArticle) {
            // Si un article existe déjà avec le même titre, renvoyer une exception BadRequestHttpException avec un message personnalisé
            throw new ExistingTitleException();
        }
        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    public function show(int $id): ?Article
    {
        // Essayez de trouver l'article avec l'ID donné
        $article = $this->articleRepository->find($id);
        // Vérifiez si l'article n'est pas trouvé
        if (!$article) {
            // Si l'article n'est pas trouvé, renvoyer une exception NotFoundHttpException avec un message personnalisé
            throw new NotFoundHttpException('L\'article avec l\'ID ' . $id . ' n\'a pas été trouvé.');
        }
        return $article;
    }

    public function update(Article $article): void
    {
        $existingArticle = $this->articleRepository->findOneBy(['title' => $article->getTitle()]);
        if ($existingArticle && ((int) $existingArticle->getId() != (int) $article->getId())) {
            // Si un article existe déjà avec le même titre, renvoyer une exception BadRequestHttpException avec un message personnalisé
            throw new ExistingTitleException('Un article different porte déjà ce titre');
        }
        $this->entityManager->flush();
    }

    public function delete(Article $article): void
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }
}

