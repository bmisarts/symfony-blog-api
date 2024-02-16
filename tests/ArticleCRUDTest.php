<?php

namespace App\tests;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleCRUDTest extends WebTestCase
{

    public function testList(): void
    {
        $client = static::createClient();

        // Envoi d'une requête GET pour récupérer la liste des articles
        $client->request('GET', '/article');
        // Doctrine_Core::loadData(sfConfig::get('sf_test_dir').'/fixtures');

        // Vérifie que la requête a réussi (code de statut HTTP 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Vérifie que la réponse contient des données JSON représentant la liste des articles
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        // Ici, vous pouvez ajouter des assertions supplémentaires sur la structure ou les données des articles retournées
    }
    public function testShow(): void
    {
        $client = static::createClient();

        // Suppose que vous avez un article existant avec l'ID 1
        $articleId = 2;

        // Envoi d'une requête GET pour afficher les détails de l'article
        $client->request('GET', '/article/' . $articleId);

        // Vérifie que la requête a réussi (code de statut HTTP 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Vérifie que la réponse contient des données JSON représentant les détails de l'article
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        // Ici, vous pouvez ajouter des assertions supplémentaires sur la structure ou les données de l'article retournées
    }
    public function testCreate(): void
    {
        $client = static::createClient();

        // Les données JSON représentant un nouvel article à créer
        $data = [
            'title' => 'Nouvel article',
            'description' => 'Description de l\'article',
        ];

        // Envoi d'une requête POST avec les données JSON
        $client->request('POST', '/article', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // Vérifie que la création a réussi (code de statut HTTP 201)
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // Vérifie que l'article a bien été créé dans la base de données
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $articleRepository = $entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBy(['title' => 'Nouvel article']);
        $this->assertNotNull($article, 'L\'article devrait avoir été créé dans la base de données');
    }
    
    public function testUpdate(): void
    {
        $client = static::createClient();

        // Suppose que vous avez un article existant avec l'ID 1
        $articleId = 3;

        // Les nouvelles données JSON pour la mise à jour de l'article
        $data = [
            'title' => 'Nouveau titre',
            'description' => 'Nouvelle description',
        ];

        // Envoi d'une requête POST avec les données JSON
        $client->request('POST', '/article/' . $articleId, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        // Vérifie que la mise à jour a réussi (code de statut HTTP 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Vérifie que les données de l'article ont été mises à jour dans la base de données
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $articleRepository = $entityManager->getRepository(Article::class);
        $article = $articleRepository->find($articleId);
        $this->assertEquals('Nouveau titre', $article->getTitle());
        $this->assertEquals('Nouvelle description', $article->getDescription());
    }
    public function testDelete(): void
    {
        $client = static::createClient();

        // Suppose que vous avez un article existant avec l'ID 1
        $articleId = 4;

        // Envoi d'une requête DELETE pour supprimer l'article
        $client->request('DELETE', '/article/' . $articleId);

        // Vérifie que la suppression a réussi (code de statut HTTP 204)
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // Vérifie que l'article a bien été supprimé de la base de données
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $article = $entityManager->getRepository(Article::class)->find($articleId);
        $this->assertNull($article, 'L\'article devrait avoir été supprimé de la base de données');
    }
}