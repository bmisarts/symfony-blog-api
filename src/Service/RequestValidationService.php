<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class RequestValidationService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateRequest(Request $request, array $rules): array
    {
        $validator = Validation::createValidator();
        $errors = [];
    
        foreach ($rules as $field => $constraints) {
            $value = $request->request->get($field);
    
            $violations = $validator->validate($value, $constraints);
    
            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors[$field][] = $violation->getMessage();
                }
            }
        }
    
        return $errors;
    }
}