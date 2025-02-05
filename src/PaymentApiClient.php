<?php

namespace Stanleysie\HkPayment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PaymentApiClient
{
    private $client;

    /**
     * Constructor initializes Guzzle HTTP client
     *
     * @param string $baseUri Base URI for the HTTP client
     */
    public function __construct(string $baseUri)
    {
        $this->client = new Client([
            'base_uri' => $baseUri, // Use parameterized base URI
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
                'json' => $params,
                'http_errors' => false, // ✅ 防止 Guzzle 把 400 當成異常
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            // ✅ 允許 `status: 0` (成功) & `status: 2` (查詢成功)
            if (isset($responseBody['status']) && in_array($responseBody['status'], [0, 2])) {
                return $responseBody;
            }

            // ⚠️ 其他狀態則返回錯誤
            return [
                'status' => 'failure',
                'message' => 'TapPay API error: ' . json_encode($responseBody),
            ];
        } catch (RequestException $e) {
            return [
                'status' => 'failure',
                'message' => 'TapPay action failed: ' . $e->getMessage(),
            ];
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
