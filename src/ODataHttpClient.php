<?php

namespace Realtyna\OData;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Realtyna\OData\Exceptions\ODataHttpClientException;

class ODataHttpClient
{
    private string $baseUri;
    private string $apiKey;
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private ODataResponseParser $responseParser;

    public function __construct($baseUri, $apiKey, $clientId, $clientSecret)
    {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->responseParser = new ODataResponseParser();
    }

    /**
     * Authenticate using OAuth 2.0 and retrieve an access token.
     * @throws ODataHttpClientException
     */
    private function authenticate(): void
    {
        try {
            $client = new Client();

            $response = $client->post('https://realtyfeed-sso.auth.us-east-1.amazoncognito.com/oauth2/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'api/read',
                ],
            ]);

            $responseJson = $response->getBody()->getContents();
            $parsedResponse = json_decode($responseJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ODataHttpClientException('Error parsing JSON response: ' . json_last_error_msg());
            }

            $this->accessToken = $parsedResponse['access_token'];
        } catch (GuzzleException $e) {
            // Handle authentication failure
            throw new ODataHttpClientException('OAuth 2.0 authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Send an authenticated HTTP GET request.
     *
     * @param string $endpoint The API endpoint to request.
     *
     * @return array|null The parsed response data, or null on failure.
     * @throws ODataHttpClientException|Exceptions\ODataResponseException
     */
    public function get(string $endpoint): ?array
    {
        $this->authenticate();

        try {
            $client = new Client([
                'base_uri' => $this->baseUri,
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]);

            $response = $client->get($endpoint);
            $responseJson = $response->getBody()->getContents();

            return $this->responseParser->parseResponse($responseJson);
        } catch (GuzzleException $e) {
            // Handle HTTP request failure
            throw new OdataHttpClientException('HTTP request failed: ' . $e->getMessage());
        }
    }

    /**
     * @return ODataResponseParser
     */
    public function getResponseParser(): ODataResponseParser
    {
        return $this->responseParser;
    }
}
