<?php

namespace App;

use DateTime;

class Discount implements \JsonSerializable
{
    public $appliedDiscounts = [
        'age_discount' => 0,
        'early_discount' => 0,
    ];
    protected $initialAmount;
    protected $journeyStartDate;
    protected $birthdate;
    public $discountedAmount;
    protected $paymentDate;
    protected $appliedPackage;

    private function ageDiscount(float $amount): float
    {
        $discount = 0;

        $age = $this->journeyStartDate->diff($this->birthdate)->y;

        if ($age < 3) {
            throw new \Exception('Invalid birthdate');
        }

        if ($age >= 3 && $age < 6) {

            $discount = $amount * 0.8;
            $this->appliedDiscounts['age_discount'] = [
                'name' => '3 - 6 years, 80%',
                'value' => 0.1,
                'amount' => $discount,
            ];
        }
        else if ($age >= 6 && $age < 12) {

            $discount = $amount * 0.3 < 4500 ? $amount * 0.3 : 4500;
            $this->appliedDiscounts['age_discount'] = [
                'name' => '6 - 12 years, 30%, max 4500',
                'value' => $amount * 0.3 < 4500 ? 0.3 : 4500,
                'amount'=> $discount,
            ];
        }
        else if ($age >= 12 && $age < 18) {

            $discount = $amount * 0.1;
            $this->appliedDiscounts['age_discount'] = [
                'name' => '12+ years, 10%',
                'value' => 0.1,
                'amount' => $discount,
            ];
        }
        return $amount - $discount;
    }

    private function earlyDiscount($amount): float
    {
        $packages = [
            'package1' => [
                'dates' => [
                    'start_date' => '01.04.next',
                    'end_date' => '30.09.next',
                ],
                'discounts' => [
                    'discount1' => [
                        'end_date' => '30.11.this',
                        'value' => 0.07,

                    ],
                    'discount2' => [
                        'end_date' => '31.12.this',
                        'value' => 0.05,
                    ],
                    'discount3' => [
                        'end_date' => '31.01.next',
                        'value' => 0.03,
                    ],
                ]
            ],
            'package2' => [
                'dates' => [
                    'start_date' => '01.10.this',
                    'end_date' => '14.01.next',
                ],
                'discounts' => [
                    'discount1' => [
                        'end_date' => '31.03.this',
                        'value' => 0.07,    
                    ],
                    'discount2' => [
                        'end_date' => '30.04.this',
                        'value' => 0.05,    
                    ],
                    'discount3' => [
                        'end_date' => '31.05.this',
                        'value' => 0.03,    
                    ],
                ]
            ],
            'package3' => [
                'dates' => [
                    'start_date' => '15.01.next',
                    'end_date' => '31.03.next'
                ],
                'discounts' => [
                    'discount1' => [
                        'end_date' => '31.08.this',
                        'value' => 0.07,    
                    ],
                    'discount2' => [
                        'end_date' => '30.09.this',
                        'value' => 0.05,    
                    ],
                    'discount3' => [
                        'end_date' => '31.10.this',
                        'value' => 0.03,    
                    ],
                ]                
            ]
        ];
        $discount = 0;

        $paymentYear = $this->paymentDate->format('Y');
        // print('Payment date: ' . $this->paymentDate->format('d-m-Y')."\n");
        // print('Journey start date: ' . $this->journeyStartDate->format('d-m-Y')."\n");

        foreach ($packages as $packageName => $packageData) {

            $packages[$packageName]['dates']['start_date'] = new DateTime(str_replace(['this', 'next'], [$paymentYear, $paymentYear + 1], $packageData['dates']['start_date']));

            if (! empty($packageData['dates']['end_date'])) {
                $packages[$packageName]['dates']['end_date'] = new DateTime(str_replace(['this', 'next'], [$paymentYear, $paymentYear + 1], $packageData['dates']['end_date']));
            }

            if ( (array_key_exists('end_date', $packageData['dates']) && 
                    $this->journeyStartDate >= $packages[$packageName]['dates']['start_date'] && 
                    $this->journeyStartDate < $packages[$packageName]['dates']['end_date']) ||
                    (! array_key_exists('end_date', $packageData['dates']) &&
                    $this->journeyStartDate >= $packages[$packageName]['dates']['start_date'])) {
                        $this->appliedPackage = $packageName;
                        // print("Applying package " . $packageName. "\n");
                        // print('Package start date: '.$packages[$packageName]['dates']['start_date']->format('d-m-Y')."\n");
            }            

            $discountApplyName = '';
            $minDays = 0;


            foreach ($packageData['discounts'] as $discountName => $discountValue) {
                $packages[$packageName]['discounts'][$discountName]['end_date'] =  new DateTime(str_replace(['this', 'next'], [$paymentYear, $paymentYear + 1], $discountValue['end_date']));

                if ($this->appliedPackage === $packageName && $this->paymentDate < $packages[$packageName]['discounts'][$discountName]['end_date']) {
                    $diffDays = $packages[$packageName]['discounts'][$discountName]['end_date']->diff($this->paymentDate)->days;
                    if ($minDays == 0 && $diffDays > 0 || $diffDays < $minDays && $minDays > 0) {
                        $minDays = $diffDays;
                        $discountApplyName = $discountName;
                    }
                }
            }

            if (!empty($discountApplyName)) {
                $discount = $packages[$packageName]['discounts'][$discountApplyName]['value'] * $amount;
                $amount  = $amount - $discount;

                $this->appliedDiscounts['early_discount'] = [
                    'name' => 'Payment date before ' . $packages[$packageName]['discounts'][$discountApplyName]['end_date']->format('d.m.Y') . ', ' . 
                                                        $packages[$packageName]['discounts'][$discountApplyName]['value'] * 100 . '%',
                    'value' => $packages[$packageName]['discounts'][$discountApplyName]['value'],
                    'amount' => $discount,
                ];                
            }
        }
        return $amount;
    }

    /**
     * 
     * @param string $paymentDate
     * @param string $amount
     * @param string $journeyStart
     * @param string $birthdate
     */
    public function __construct(string $paymentDate, string $amount, string $journeyStartDate, string $birthdate)
    {   
        $this->paymentDate = new DateTime($paymentDate);
        $this->journeyStartDate = new DateTime($journeyStartDate);
        $this->birthdate = new DateTime($birthdate);
        $this->initialAmount = (float) $amount;

        if ($this->paymentDate > $this->journeyStartDate) {
            throw new \Exception('Invalid payment or journey start date');
        }

        $this->discountedAmount = $this->ageDiscount($this->initialAmount);

        $this->discountedAmount = $this->earlyDiscount($this->discountedAmount);
    }

    public function jsonSerialize() : mixed
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        return [
            'initial_amount' => $this->initialAmount,
            'discounted_amount' => $this->discountedAmount,
            'applied_discounts' => $this->appliedDiscounts,
        ];
    }
}

// $discount = new Discount('01.01.2025', '10000', '01.04.2026', '01.01.2020');

// print_r(json_encode($discount));
// print($discount->discountedAmount. "\n");
