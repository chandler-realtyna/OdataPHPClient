<?php

namespace Realtyna\OData;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Realtyna\OData\Exceptions\ODataHttpClientException;
use Realtyna\OData\Interfaces\AuthenticatorInterface;

class ODataHttpClient
{
    private string $baseUri;
    private ODataResponseParser $responseParser;
    private AuthenticatorInterface $authenticator;

    public function __construct($baseUri, AuthenticatorInterface $authenticator)
    {
        $this->baseUri = $baseUri;
        $this->responseParser = new ODataResponseParser();
        $this->authenticator = $authenticator;
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
        $request = new Request('GET', $this->baseUri . $endpoint);

        $authenticatedRequest = $this->authenticator->authenticate($request);

        try {
            $client = new Client();
            $response = $client->send($authenticatedRequest);
            $response = $response->getBody()->getContents();

            return $this->responseParser->parseResponse($response);
        } catch (GuzzleException $e) {
            throw new ODataHttpClientException('HTTP request failed: ' . $e->getMessage());
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
