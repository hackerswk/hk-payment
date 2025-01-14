<?php

namespace Stanleysie\HkPayment;

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
     * Interact with TapPay API for various actions
     *
     * @param array $params
     * @return array
     */
    public function tappayAction(array $params): array
    {
        try {
            $response = $this->client->post('/tappay/api', [
                'json' => $params, // Use JSON encoding for TapPay requests
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'TapPay action failed: ' . $e->getMessage()];
        }
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
                'form_params' => $params, // Use form_params for x-www-form-urlencoded encoding
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Payment creation failed: ' . $e->getMessage()];
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
