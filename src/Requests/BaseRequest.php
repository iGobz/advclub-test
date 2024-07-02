<?php

namespace App\Requests;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;


abstract class BaseRequest
{
    public function __construct(protected ValidatorInterface $validator, protected RequestStack $requestStack)
    {
        $this->populate();
    }

    public function validate()
    {
        $errors = $this->validator->validate($this);

        $messages = ['message' => 'validation_failed', 'errors' => []];

        /** @var \Symfony\Component\Validator\ConstraintViolation  */
        foreach ($errors as $message) {
            $messages['errors'][] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }

        if (count($messages['errors']) > 0) {
            // $response = new JsonResponse($messages, 201);
            // $response->send();
            throw new Exception(json_encode($messages));
        }        
    }

    protected function populate(): void
    {
        $params = array_merge($this->getRequest()->query->all(), $this->getRequest()->getPayload()->all());
        foreach ($params as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }   
    
    public function getRequest(): Request
    {        
        return $this->requestStack->getCurrentRequest();
    }    
}