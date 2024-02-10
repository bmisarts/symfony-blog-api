<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Article;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
 
$entityManager;
$articleRepository;
class ArticleRepository extends ServiceEntityRepository
{ 
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
        $this->entityManager = $registry->getManager();
        $this->articleRepository = $this->entityManager->getRepository(Article::class);
    }

   /**
    * @return Article[] Returns an array of Article objects
    */
    public function getArticles()
    {
        // Récupérer tous les articles à partir du repository Article
        return $this->articleRepository->findAll();
    }

   
    public function getArticleById($id)
    {
        // Récupérer l'article par son id à partir du repository Article
        return $this->articleRepository->find($id);
    }
   
    public function createArticle(Article $article)
    {
        // Créer un article
        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }
    
    public function updateArticle(int $id, Article $article)
    {
        // flush() pour enregistrer les modifications dans la base de données.
        $this->entityManager->flush();
    }
    
    public function deleteArticle(Article $article)
    {
        // Supprimer l'entité de la base de données
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }

}
