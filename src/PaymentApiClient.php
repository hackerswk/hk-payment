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
            'timeout'  => 10.0,
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
                'json'        => $params,
                'http_errors' => false, // ✅ 防止 Guzzle 把 400 當成異常
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            // ✅ 允許 `status: 0` (成功) & `status: 2` (查詢成功)
            if (isset($responseBody['status']) && in_array($responseBody['status'], [0, 2])) {
                return $responseBody;
            }

            // ⚠️ 其他狀態則返回錯誤
            return [
                'status'  => 'failure',
                'message' => 'TapPay API error: ' . json_encode($responseBody),
            ];
        } catch (RequestException $e) {
            return [
                'status'  => 'failure',
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
            'from'        => $from,
            'to'          => $to,
            'from_amount' => $fromAmount,
            'type'        => $type,
            'point'       => $point,
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

    /**
     * Create a new TapPay Merchant Partner Account
     *
     * @param array $params Partner account details
     * @return array
     */
    public function createPartnerAccount(array $params): array
    {
        try {
            $response = $this->client->post('/tappay-merchant/api/create-partner', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Partner account creation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Submit Merchant Qualification
     *
     * @param array $params Qualification details
     * @return array
     */
    public function submitQualification(array $params): array
    {
        try {
            $response = $this->client->post('/tappay-merchant/api/submit-qualification', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Qualification submission failed: ' . $e->getMessage()];
        }
    }

    /**
     * Query Merchant Status
     *
     * @param array $params Query parameters
     * @return array
     */
    public function queryMerchantStatus(array $params): array
    {
        try {
            $response = $this->client->get('/tappay-merchant/api/query-status', [
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Merchant status query failed: ' . $e->getMessage()];
        }
    }

    /**
     * Add a Payment Service to the Merchant Account
     *
     * @param array $params Payment service details
     * @return array
     */
    public function addPaymentService(array $params): array
    {
        try {
            $response = $this->client->post('/tappay-merchant/api/add-payment-service', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Add payment service failed: ' . $e->getMessage()];
        }
    }

    /**
     * Platform - Bind a credit card to a user account
     *
     * @param array $params Card binding details
     * @return array
     */
    public function platformBindCard(array $params): array
    {
        try {
            $response = $this->client->post('/tappay-platform/api/bind-card', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Card binding failed: ' . $e->getMessage()];
        }
    }

    /**
     * Platform - Pay using a Prime token
     *
     * @param array $params Payment details
     * @return array
     */
    public function platformPayByPrime(array $params): array
    {
        try {
            $response = $this->client->post('/tappay-platform/api/pay-by-prime', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Payment by Prime failed: ' . $e->getMessage()];
        }
    }

    /**
     * Platform - Pay using a Card Token
     *
     * @param array $params Payment details
     * @return array
     */
    public function platformPayByCardToken(array $params): array
    {
        try {
            $response = $this->client->post('/tappay-platform/api/pay-by-token', [
                'json' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => 'Payment by Card Token failed: ' . $e->getMessage()];
        }
    }

    /**
     * ATM API - Pay by Prime
     *
     * @param array $params Payment details
     * @return array
     */
    public function atmPayByPrime(array $params): array
    {
        return $this->sendATMRequest('pay-by-prime', $params);
    }

    /**
     * ATM API - Get Trade Records
     *
     * @param array $filters Trade filters
     * @return array
     */
    public function atmGetTradeRecords(array $params): array
    {
        return $this->sendATMRequest('record', $params);
    }

    /**
     * ATM API - Get Trade History
     *
     * @param string $recTradeId Trade ID
     * @return array
     */
    public function atmGetTradeHistory(array $params): array
    {
        return $this->sendATMRequest('trade-history', $params);
    }

    /**
     * ATM API - Reconciliation
     *
     * @param array $reconcileParams Reconciliation parameters
     * @return array
     */
    public function atmReconciliation(array $reconcileParams): array
    {
        return $this->sendATMRequest('reconciliation', $reconcileParams);
    }

    /**
     * ATM API - Simulate Payment
     *
     * @param string $recTradeId Trade ID
     * @return array
     */
    public function atmSimulatePayment(array $params): array
    {
        return $this->sendATMRequest('simulate-paid', $params);
    }

    /**
     * Common method to handle ATM API requests
     *
     * @param string $action API action
     * @param array $data Request data
     * @return array
     */
    private function sendATMRequest(string $action, array $data): array
    {
        try {
            $data['action'] = $action;
            $response       = $this->client->post('/tappay/atm-api', [
                'json'        => $data,
                'http_errors' => false,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (isset($responseBody['status']) && in_array($responseBody['status'], [0, 2])) {
                return $responseBody;
            }

            return [
                'status'  => 'failure',
                'message' => 'ATM API error: ' . json_encode($responseBody),
            ];
        } catch (RequestException $e) {
            return [
                'status'  => 'failure',
                'message' => 'ATM API request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Upload qualification files for TapPay merchant verification.
     *
     * @param array $files Associative array with document keys and file paths.
     * @param string $partnerAccount Partner account identifier.
     * @param string $platformKey Platform key for TapPay.
     * @return array
     */
    public function uploadQualificationFiles(array $files, string $partnerAccount, string $platformKey): array
    {
        $baseUri = $this->client->getConfig('base_uri') ?? '';
        $url = rtrim($baseUri, '/') . '/tappay-merchant/api/upload-qualification';

        error_log("[UPLOAD] Sending request to: " . $url);

        // ✅ 準備 multipart/form-data
        $multipart = [
            ['name' => 'platform_key', 'contents' => $platformKey],
            ['name' => 'partner_account', 'contents' => $partnerAccount],
        ];

        foreach ($files as $paramName => $file) {
            if (!is_a($file, \Illuminate\Http\UploadedFile::class)) {
                return ['status' => 'failure', 'message' => "Invalid file: {$paramName}"];
            }

            $filePath = $file->getPathname();

            if (!file_exists($filePath)) {
                return ['status' => 'failure', 'message' => "File not found: {$filePath}"];
            }

            $multipart[] = [
                'name'     => $paramName,
                'contents' => fopen($filePath, 'r'),
                'filename' => $file->getClientOriginalName(),
                'headers'  => ['Content-Type' => $file->getMimeType()],
            ];
        }

        error_log("[UPLOAD] Prepared Multipart Data: " . print_r($multipart, true));

        try {
            // ✅ 使用 Guzzle 進行 POST 請求
            $response = $this->client->post($url, [
                'multipart'   => $multipart,
                'http_errors' => false, // ✅ 避免 Guzzle 將 4xx/5xx 當作錯誤拋出
                'debug'       => true,  // ✅ 啟用 debug，檢查請求內容
            ]);

            $statusCode = $response->getStatusCode();
            $body       = $response->getBody()->getContents();

            error_log("[UPLOAD RESPONSE] HTTP $statusCode: " . $body);

            if ($statusCode !== 200) {
                return [
                    'status'  => 'failure',
                    'message' => "Upload failed: HTTP " . $statusCode,
                ];
            }

            // ✅ 確保 JSON 解析成功
            $decodedResponse = json_decode($body, true);

            if (is_null($decodedResponse)) {
                return [
                    'status'  => 'failure',
                    'message' => 'Invalid JSON response from server',
                    'raw'     => $body,
                ];
            }

            return $decodedResponse;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            error_log("[UPLOAD ERROR] RequestException: " . $e->getMessage());
            return ['status' => 'failure', 'message' => 'Upload failed: ' . $e->getMessage()];
        } catch (\Exception $e) {
            error_log("[UPLOAD ERROR] General Exception: " . $e->getMessage());
            return ['status' => 'failure', 'message' => 'Unexpected error: ' . $e->getMessage()];
        }
    }
}
