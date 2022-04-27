<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CallApiService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getData($method,$url,$params): array|string
    {
        $response = $this->client->request(
            $method,
            $url,
            [
                'query' => $params,
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            ]);
        try {
            $TotalData = $response->toArray();
            $data = $TotalData['data'];
            while ($TotalData['next'] !== null) {
                $response = $this->client->request(
                    $method,
                    $TotalData['next'],
                    [
                        'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
                    ]);
                $TotalData = $response->toArray();
                $data = array_merge($data, $TotalData['data']);
            }

        } catch (ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $data = $e->getMessage();
        }
        return $data;
    }
}