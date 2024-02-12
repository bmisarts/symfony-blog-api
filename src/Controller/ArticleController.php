<?php

namespace App\Controller;

use App\Entity\Article;
use App\Service\ArticleService;
use App\Service\RequestValidationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exception\Article\ExistingTitleException;

class ArticleController extends AbstractController
{
    private $articleService;
    private $serializer;
    private $validator;

    public function __construct(ArticleService $articleService, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->articleService = $articleService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/article', name: 'article_list', methods:['get'] )]
    public function index(): JsonResponse
    {
        $data = $this->articleService->all();
        
        //Retourner la liste des articles trouvés
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/article/{id}', name: 'article_show', methods:['get'] )]
    public function show(int $id): Response
    {
        try{
            $article = $this->articleService->show($id);
            $articleData = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
                'created_date' => $article->getCreatedDate(),
                'updated_date' => $article->getUpdatedDate(),
            ];
        
            // Retourner l'article trouvé
            return new JsonResponse($articleData, Response::HTTP_OK);
        } catch (\Exception $e) {
            //Lever l'exeption si l'article recherché n'apas ete retrouvé
            if($e instanceof NotFoundHttpException){
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
            //Lever une exception quelconque si jamais sur la capturee quelque part (Exemple : erreur SQL)
            else{
                return new JsonResponse(['error' => $e->getMessage(), get_class($e)], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
    
    #[Route('/article', name: 'article_create', methods:['post'] )]
    public function create(Request $request, RequestValidationService $validationService): Response
    {
        $rules = [
            'title' => [
                new NotBlank(null, 'Le titre est requis'),
                new Type(['type' => 'string']),
                new Length(
                    null, 5, 50, null, null, null, null,
                    'La chaine est tres courte, veillez ajouter le nombre de caracteres',
                    'La chaine est tres longue, veillez reduire le nombre de caracteres'
                ),
            ],
        ];
        $errors = $validationService->validateRequest($request, $rules);
    
        if (!empty($errors)) {
            // Traiter les erreurs de validation
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Créer l'article
        $article = new Article();
        $article->setTitle($request->request->get('title'));
        $article->setDescription($request->request->get('description'));
        
        try{
            $this->articleService->create($article);
        } catch (\Exception $e) {
            //Lever l'exeption si un article à été retrouvé avec le même titre
            if($e instanceof ExistingTitleException){
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
            }
            //Lever une exception quelconque si jamais sur la capturee quelque part (Exemple : erreur SQL)
            else{
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->json($article, Response::HTTP_CREATED);
    }
    
    #[Route('/article/{id}', name: 'article_update', methods:['post'] )]
    public function update(Request $request, int $id, RequestValidationService $validationService): Response
    {
        $rules = [
            'title' => [
                new NotBlank(null, 'Le titre est requis'),
                new Type(['type' => 'string']),
                new Length(
                    null, 5, 50, null, null, null, null,
                    'La chaine est tres courte, veillez ajouter le nombre de caracteres',
                    'La chaine est tres longue, veillez reduire le nombre de caracteres'
                ),
            ],
        ];
        $errors = $validationService->validateRequest($request, $rules);
    
        if (!empty($errors)) {
            // Traiter les erreurs de validation
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try{
            // rechercher l'article
            $article = $this->articleService->show($id);
            
            $article->setTitle($request->request->get('title'));
            $article->setDescription($request->request->get('description'));
            
            $this->articleService->update($article);
        } catch (\Exception $e) {
            //Lever l'exeption si l'article recherché n'apas ete retrouvé
            if($e instanceof NotFoundHttpException){
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
            //Lever l'exeption si un article à été retrouvé avec le même titre
            else if($e instanceof ExistingTitleException){
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
            }
            //Lever une exception quelconque si jamais sur la capturee quelque part (Exemple : erreur SQL)
            else{
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Retourner le message de succès
        return $this->json($article, Response::HTTP_OK);
    }

    #[Route('/article/{id}', name: 'article_delete', methods:['delete'] )]
    public function destroy(Request $request, int $id, RequestValidationService $validationService): Response
    {
        try{
            $article = $this->articleService->show($id);
            $this->articleService->delete($article);
        } catch (\Exception $e) {
            //Lever l'exeption si l'article recherché n'apas ete retrouvé
            if($e instanceof NotFoundHttpException){
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
            //Lever une exception quelconque si jamais sur la capturee quelque part (Exemple : erreur SQL)
            else{
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Retourner le message de succès
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}
