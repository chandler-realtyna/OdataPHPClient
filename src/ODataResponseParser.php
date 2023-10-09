<?php

namespace Realtyna\OData;

use Exception;
use Realtyna\OData\Exceptions\ODataResponseException;

class ODataResponseParser
{
    /**
     * Parse the response JSON from the OData service.
     *
     * @param string $responseJson The JSON response from the OData service.
     *
     * @return array|null An array representing the parsed response data, or null on failure.
     * @throws ODataResponseException
     */
    public function parseResponse(string $responseJson): ?array
    {
        try {
            $data = json_decode($responseJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ODataResponseException('Error parsing JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (Exception $e) {
            // Handle any exceptions that may occur during parsing
            throw $e;
        }
    }

    /**
     * Extract and return the value of a specific property from the parsed response.
     *
     * @param array|null $parsedResponse The parsed response data.
     * @param string $propertyName The name of the property to extract.
     *
     * @return mixed|null The value of the specified property, or null if not found.
     */
    public function extractProperty(?array $parsedResponse, string $propertyName): mixed
    {
        if (is_array($parsedResponse) && array_key_exists($propertyName, $parsedResponse)) {
            return $parsedResponse[$propertyName];
        }

        return null;
    }

    /**
     * Extract and return a collection of entities from the parsed response.
     *
     * @param array|null $parsedResponse The parsed response data.
     * @param string $entityType The name of the entity type to extract.
     *
     * @return array An array of entity data, or an empty array if not found.
     */
    public function extractEntities(?array $parsedResponse, string $entityType): array
    {
        $entities = [];

        if (is_array($parsedResponse) && array_key_exists('value', $parsedResponse)) {
            foreach ($parsedResponse['value'] as $entity) {
                if (isset($entity['@odata.type']) && $entity['@odata.type'] === '#' . $entityType) {
                    $entities[] = $entity;
                }
            }
        }

        return $entities;
    }
}
