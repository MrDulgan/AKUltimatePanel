<?php
class PayPalAPI {
    private $clientId;
    private $secret;
    private $sandbox;
    private $accessToken;
    private $apiUrl;

    public function __construct($clientId, $secret, $sandbox = true) {
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->sandbox = $sandbox;
        $this->apiUrl = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Accept-Language: en_US"
        ]);
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ":" . $this->secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $responseArray = json_decode($response, true);
        curl_close($ch);

        if (isset($responseArray['access_token'])) {
            $this->accessToken = $responseArray['access_token'];
            return $this->accessToken;
        } else {
            throw new Exception('Could not obtain PayPal access token.');
        }
    }

    public function createPayment($amount, $points, $bonus) {
        $accessToken = $this->getAccessToken();

        $paymentData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => PAYPAL_CURRENCY,
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => "Purchase of $points points with a bonus of $bonus."
                ]
            ],
            'application_context' => [
                'return_url' => SITE_URL . '/page/donation',
                'cancel_url' => SITE_URL . '/page/donation',
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $accessToken",
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $paymentResponse = curl_exec($ch);
        $payment = json_decode($paymentResponse, true);
        curl_close($ch);

        if (isset($payment['links'])) {
            foreach ($payment['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return ['success' => true, 'redirect_url' => $link['href']];
                }
            }
        }

        return ['success' => false, 'message' => 'Payment creation failed.'];
    }

    public function capturePayment($orderId) {
        $accessToken = $this->getAccessToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . "/v2/checkout/orders/$orderId/capture");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $accessToken",
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $captureResponse = curl_exec($ch);
        $captureData = json_decode($captureResponse, true);
        curl_close($ch);

        if (isset($captureData['status']) && $captureData['status'] === 'COMPLETED') {
            return ['success' => true, 'data' => $captureData];
        } else {
            return ['success' => false, 'message' => 'Payment capture failed.'];
        }
    }
}
?>