<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DiscountControllerTest extends WebTestCase
{
    public function testEndpointWithInvalidParameters(): void
    {
        $this->expectException(\Exception::class);
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/discount', [
            'payment_date' => '01-01-2024', 
            'amount' => '10000',
            'journey_start_date' => '01-10-2024',
        ]);
        $response = $client->getResponse();
        $this->assertStringContainsStringIgnoringCase('validation_failed', $response->getContent());
    }

    public function testEndpointWithValidParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/discount', [
            'payment_date' => '01-01-2024', 
            'amount' => '10000',
            'journey_start_date' => '01-10-2024',
            'birthdate' => '01-10-2020',
        ]);
        $response = $client->getResponse();
        $this->assertStringContainsStringIgnoringCase('discounted_amount', $response->getContent());
    }
}
