<?php

namespace App\Tests;

use App\Discount;
use PHPUnit\Framework\TestCase;

class DiscountTest extends TestCase
{
    public function test80PercentDiscount(): void
    {
        $discount = new Discount('01.01.2024', '10000', '01.01.2024', '01.01.2020');
        $this->assertEquals($discount->discountedAmount, 2000.0);
    }

    public function test4500Discount(): void
    {
        $discount = new Discount('01.01.2024', '100000', '01.01.2024', '01.01.2018');
        $this->assertEquals($discount->discountedAmount, 95500.0);
    }    

    public function test30PercentDiscount(): void
    {
        $discount = new Discount('01.01.2024', '10000', '01.01.2024', '01.01.2018');
        $this->assertEquals($discount->discountedAmount, 7000.0);
    }    

    public function test10PercentDiscount(): void
    {
        $discount = new Discount('01.01.2024', '10000', '01.01.2024', '01.01.2010');
        $this->assertEquals($discount->discountedAmount, 9000.0);
    }      

    public function testBirthdateException(): void
    {
        $this->expectExceptionMessage('Invalid birthdate');
        $discount = new Discount('01.01.2024', '10000', '01.01.2024', '01.01.2024');
    }

    public function testInvalidPaymentDateException(): void
    {
        $this->expectException(\Exception::class);
        $discount = new Discount('01.01.2026', '10000', '01.01.2025', '01.01.2024');
    }    

    public function test7PercentDiscount(): void
    {
        $discount = new Discount('01.01.2025', '10000', '01.10.2025', '01.01.2000');
        $this->assertEquals($discount->discountedAmount, 9300.0);
    }

    public function test5PercentDiscount(): void
    {
        $discount = new Discount('01.04.2025', '10000', '01.10.2025', '01.01.2000');
        $this->assertEquals($discount->discountedAmount, 9500.0);
    }    

    public function test3PercentDiscount(): void
    {
        $discount = new Discount('01.05.2025', '10000', '01.10.2025', '01.01.2000');
        $this->assertEquals($discount->discountedAmount, 9700.0);
    }    

    public function testNoDiscount(): void
    {
        $discount = new Discount('01.06.2025', '10000', '01.10.2025', '01.01.2000');
        $this->assertEquals($discount->discountedAmount, 10000.0);
    }    
}
