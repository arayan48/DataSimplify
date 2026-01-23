<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TimeService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCurrentYear(): int
    {
        try {
            $response = $this->httpClient->request('GET', 'https://worldtimeapi.org/api/timezone/Europe/Paris');
            
            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                $datetime = new \DateTime($data['datetime']);
                return (int) $datetime->format('Y');
            }
        } catch (\Exception $e) {
            return (int) date('Y');
        }

        return (int) date('Y');
    }
}
