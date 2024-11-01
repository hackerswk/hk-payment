<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PaymentApiClient
{
    private $client;

    /**
     * Constructor initializes Guzzle HTTP client
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://payment.holkee.com',
            'timeout' => 10.0,
        ]);
    }

    /**
     * Create a new payment order
     *
     * @param array $params
     * @return array
     */
    public function createPayment(array $params): array
    {
        try {
            $response = $this->client->post('/payment/api/create', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Payment creation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Send webhook data for virtual account
     *
     * @param string $rawData XML formatted data as a string
     * @return string
     */
    public function virtualAccountWebhook(string $rawData): string
    {
        try {
            $response = $this->client->post('/payment/api/virtualaccount/webhook', [
                'body' => $rawData,
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return 'Webhook failed: ' . $e->getMessage();
        }
    }

    /**
     * Get the exchange rate between currencies
     *
     * @param string $from From currency code
     * @param string $to To currency code
     * @param float $fromAmount Amount in the 'from' currency
     * @param string $type Type of rounding ('up' or 'down')
     * @param int $point Decimal point precision
     * @return array
     */
    public function getExchangeRate(string $from, string $to, float $fromAmount, string $type = 'up', int $point = 3): array
    {
        $query = [
            'from' => $from,
            'to' => $to,
            'from_amount' => $fromAmount,
            'type' => $type,
            'point' => $point,
        ];

        try {
            $response = $this->client->get('/currency/api/exchangerate', [
                'query' => $query,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Exchange rate retrieval failed: ' . $e->getMessage()];
        }
    }
}
