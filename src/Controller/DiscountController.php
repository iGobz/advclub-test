<?php

namespace App\Controller;

use App\Discount;
use App\Requests\DiscountRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DiscountController extends AbstractController
{
    #[Route('/discount', name: 'app_discount', methods: ['GET'])]
    public function calculate(DiscountRequest $request)
    {
        $request->validate();
        $discount = new Discount($request->payment_date, $request->amount, $request->journey_start_date, $request->birthdate);
        
        return $this->json($discount->toArray());
    }
}
