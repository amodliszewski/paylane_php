<?php
declare(strict_types=1);

namespace PayLane\Client;

use PayLane\Exception\ConnectionException;
use PayLane\Exception\ErrorException;
use PayLane\Exception\ValidationException;

class RestClient
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';

    const HTTP_ERRORS = [
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
    ];

    /** @var array */
    const HTTP_ALLOWED_METHODS = array(
        self::HTTP_METHOD_GET,
        self::HTTP_METHOD_PUT,
        self::HTTP_METHOD_POST,
        self::HTTP_METHOD_DELETE,
    );

    /** @var string */
    protected $apiUrl;

    /** @var string */
    protected $username = null;

    /** @var string */
    protected $password = null;

    /** @var boolean */
    protected $sslVerify = true;

    /** @var bool */
    protected $isSuccess = false;

    public function __construct(
        string $username,
        string $password,
        string $endpoint = 'https://direct.paylane.com/rest/'
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->apiUrl = $endpoint;
    }

    public function setSSLverify(bool $sslVerify): void
    {
        $this->sslVerify = $sslVerify;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function cardSale(array $params): array
    {
        return $this->call(
            'cards/sale',
            self::HTTP_METHOD_POST,
             $params
        );
    }

    public function cardSaleByToken(array $params): array
    {
        return $this->call(
            'cards/saleByToken',
            self::HTTP_METHOD_POST,
             $params
        );
    }

    public function cardAuthorization(array $params): array
    {
        return $this->call(
            'cards/authorization',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function cardAuthorizationByToken(array $params): array
    {
        return $this->call(
            'cards/authorizationByToken',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function paypalAuthorization(array $params): array
    {
        return $this->call(
            'paypal/authorization',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function captureAuthorization(array $params): array
    {
        return $this->call(
            'authorizations/capture',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function closeAuthorization(array $params): array
    {
        return $this->call(
            'authorizations/close',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function refund(array $params): array
    {
        return $this->call(
            'refunds',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function getSaleInfo(array $params): array
    {
        return $this->call(
            'sales/info',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function getAuthorizationInfo(array $params): array
    {
        return $this->call(
            'authorizations/info',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function checkSaleStatus(array $params): array
    {
        return $this->call(
            'sales/status',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function directDebitSale(array $params): array
    {
        return $this->call(
            'directdebits/sale',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function sofortSale(array $params): array
    {
        return $this->call(
            'sofort/sale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function idealSale(array $params): array
    {
        return $this->call(
            'ideal/sale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function idealBankCodes(): array
    {
        return $this->call(
            'ideal/bankcodes',
            self::HTTP_METHOD_GET,
            []
        );
    }

    public function bankTransferSale(array $params): array
    {
        return $this->call(
            'banktransfers/sale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function paypalSale(array $params): array
    {
        return $this->call(
            'paypal/sale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function paypalStopRecurring(array $params): array
    {
        return $this->call('paypal/stopRecurring',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function resaleBySale(array $params): array
    {
        return $this->call(
            'resales/sale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function resaleByAuthorization(array $params): array
    {
        return $this->call(
            'resales/authorization',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function checkCard3DSecure(array $params): array
    {
        return $this->call(
            '3DSecure/checkCard',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function checkCard3DSecureByToken(array $params): array
    {
        return $this->call(
            '3DSecure/checkCardByToken',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function saleBy3DSecureAuthorization(array $params): array
    {
        return $this->call(
            '3DSecure/authSale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function authorizeBy3DSecureAuthorization(array $params): array
    {
        return $this->call(
            '3DSecure/auth',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function checkCard(array $params): array
    {
        return $this->call(
            'cards/check',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function checkCardByToken(array $params): array
    {
        return $this->call(
            'cards/checkByToken',
            self::HTTP_METHOD_GET,
            $params
        );
    }

    public function applePaySale(array $params): array
    {
        return $this->call(
            'applepay/sale',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    public function applePayAuthorization(array $params): array
    {
        return $this->call(
            'applepay/authorization',
            self::HTTP_METHOD_POST,
            $params
        );
    }

    protected function call(string $method, string $request, array $params): array
    {
        $this->isSuccess = false;

        if (!$this->checkRequestMethod($request)) {
            throw new ValidationException('Not allowed request method type');
        }

        $response = $this->pushData($method, $request, json_encode($params));

        $response = json_decode($response, true);

        if (isset($response['success']) && $response['success'] === true)
        {
            $this->isSuccess = true;
        }

        return $response;
    }

    protected function checkRequestMethod(string $method): bool
    {
        return in_array($method, self::HTTP_ALLOWED_METHODS);
    }

    protected function pushData(string $method, string $methodType, string $request): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $method);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($methodType));
        curl_setopt($ch, CURLOPT_HTTPAUTH, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (isset(self::HTTP_ERRORS[$httpCode]))
        {
            throw new ErrorException('Response Http Error - ' . self::HTTP_ERRORS[$httpCode], $httpCode);
        }

        if (0 < curl_errno($ch))
        {
            throw new ConnectionException('Unable to connect to ' . $this->apiUrl . ' Error: ' . curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        return $response;
    }
}
