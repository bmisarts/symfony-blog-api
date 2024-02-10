<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Services\RequestValidationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\ArticleService;
use App\Entity\Article;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'article_list', methods:['get'])]
    public function index(ArticleService $articleService): JsonResponse
    {
        $data = $articleService->getAll();
        return $this->json($data);
    }
    
    
    #[Route('/article/{id}', name: 'article_show', methods:['get'])]
    public function show(int $id, ArticleService $articleService): JsonResponse
    {
        $article = $articleService->getArticleById($id);
        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($article);
    }
    
    #[Route('/article', name: 'article_create', methods:['post'] )]
    public function create(Request $request, RequestValidationService $validationService, ArticleService $articleService): JsonResponse
    {
        // Récupérer toutes les données de la requête
        $data = $request->request->all();
        $rules = [
            'title' => [
                new NotBlank(null, 'Le libellé est requis'),
                new Type(['type' => 'string']),
                new Length(
                    null, 5, 50, null, null, null, null,
                    'La chaine est tres courte, veillez ajouter le nombre de caracteres',
                    'La chaine est tres longue, veillez reduire le nombre de caracteres'
                ),
            ],
            // Ajoutez d'autres champs et contraintes de validation selon vos besoins
        ];
        // return new JsonResponse(['rules' => $rules], Response::HTTP_BAD_REQUEST);
        $errors = $validationService->validateRequest($request, $rules);
    
        if (!empty($errors)) {
            // Traiter les erreurs de validation
            // Par exemple, les renvoyer au client ou les logguer
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
           
        $article = new Article();
        $article->setTitle($request->request->get('title'));
        $article->setDescription($request->request->get('description'));
        
        try{
            $articleService->save($article);
        }catch(\Exception $e){
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
           
        return new JsonResponse(['message' => 'Article enregistré avec succès'], 200);
    }
    
    #[Route('/article/edit/{id}', name: 'article_edit', methods:['post'] )]
    public function update(Request $request, int $id, RequestValidationService $validationService, ArticleService $articleService): JsonResponse
    {
        // Récupérer toutes les données de la requête
        $data = $request->request->all();
        $rules = [
            'title' => [
                new NotBlank(null, 'Le libellé est requis'),
                new Type(['type' => 'string']),
                new Length(
                    null, 5, 50, null, null, null, null,
                    'La chaine est tres courte, veillez ajouter le nombre de caracteres',
                    'La chaine est tres longue, veillez reduire le nombre de caracteres'
                ),
            ],
            // Ajoutez d'autres champs et contraintes de validation selon vos besoins
        ];
        // return new JsonResponse(['rules' => $rules], Response::HTTP_BAD_REQUEST);
        $errors = $validationService->validateRequest($request, $rules);
    
        if (!empty($errors)) {
            // Traiter les erreurs de validation
            // Par exemple, les renvoyer au client ou les logguer
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
           
        $article = new Article();
        $article->setTitle($request->request->get('title'));
        $article->setDescription($request->request->get('description'));
        
        try{
            $articleService->update($id, $article);
        }catch(\Exception $e){
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
           
        return new JsonResponse(['message' => 'Article enregistré avec succès'], 200);
    }
    
    #[Route('/article/delete/{id}', name: 'article_delete', methods:['delete'])]
    public function delete(int $id, ArticleService $articleService): JsonResponse
    {
        return $articleService->delete($id);
    }
}
