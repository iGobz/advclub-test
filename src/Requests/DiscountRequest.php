<?php

namespace App\Requests;
use App\Requests\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class DiscountRequest extends BaseRequest
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{2}\-\d{2}\-\d{4}$/', message: 'Payment date value is invalid')]
    protected $payment_date;

    #[Assert\NotBlank]
    #[Assert\Positive(message: 'The amount should be a positive number')]
    protected $amount;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{2}\-\d{2}\-\d{4}$/', message: 'Journey start date value is invalid')]
    protected $journey_start_date;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{2}\-\d{2}\-\d{4}$/', message: 'Birthdate value is invalid')]
    protected $birthdate;

    public function __get($property)
    {
        return $this->{$property};
    }

}