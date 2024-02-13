<?php

namespace App\Controller;

use App\Entity\Article;
use App\Service\ArticleService;
use App\Repository\ArticleRepository;
use App\Service\RequestValidationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\Article\ExistingTitleException;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Length;

//Importation des packages nelmio pour la documentation
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenApi\Annotations as OA;

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

    /**
     * Récupéreration d'un articles.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des articles",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="Aucun article dans la base de données ne porte l'ID fourni en paramètre",
     *     @OA\JsonContent(
     *        type="string",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="article_list")
     */
    #[Route('/article', name: 'article_list', methods:['get'] )]
    public function index(ArticleRepository $articleRepository, Request $request): JsonResponse
    {
        try{
            $data = $this->articleService->all();
            //Retourner la liste des articles trouvés
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), get_class($e)], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Affichage d'un article dont l'ID est pris en paramètre.
     *
     * Cette route affiche la liste des articles recuperée en base de données.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne l'article trouvé",
     *     @OA\JsonContent(
     *        type="Object",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="Aucun article dans la base de données ne porte l'ID fourni en paramètre",
     *     @OA\JsonContent(
     *        type="string",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="article_show")
     */
    #[Route('/article/{id}', name: 'article_show', methods:['get'])]
    public function show(int $id, ArticleRepository $articleRepository, Request $request): Response
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
    
    /**
     * Création d'un nouvel article.
     *
     * @OA\RequestBody(
     *     description: "Récupération des paramètres de la requête."
     *     @OA\JsonContent(
     *        properties (
     *              @OA\Property(property="title", required=true, description="Le titre de l'article.", type="string"),
     *              @A\Property(property="description", required=false, description="La description ou encore le message de l'article.", type="string"),
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Retourne l'article nouvellement créé.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Response(
     *     response=409,
     *     description="Conflit de titre avec un autre article existant.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="article_create")
     */
    #[Route('/article', name: 'article_create', methods:['post'] )]
    public function create(RequestValidationService $validationService, Request $request): Response
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
    
    /**
     * Modification d'un article.
     *
     * @OA\RequestBody(
     *     description: "Récupération des paramètres de la requête."
     *     @OA\JsonContent(
     *        properties (
     *              @OA\Property(property="title", required=true, description="Le titre de l'article.", type="string"),
     *              @A\Property(property="description", required=false, description="La description ou encore le message de l'article.", type="string"),
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Retourne l'article nouvellement créé.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Response(
     *     response=409,
     *     description="Conflit de titre avec un autre article existant.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="article_update")
     */
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

    /**
     * Suppression d'un article.
     * @OA\Response(
     *     response=204,
     *     description="Retourne un contenu vide",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="Aucun article dans la base de données ne porte l'ID fourni en paramètre",
     *     @OA\JsonContent(
     *        type="string",
     *        @OA\Items(ref=@Model(type=Article::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="article_delete")
     */
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
