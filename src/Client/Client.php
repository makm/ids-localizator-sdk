<?php

namespace Ids\Localizator\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\PostCatalogsItemsRequest;
use Ids\Localizator\Client\Request\Translations\GetTranslationsApplication\GetTranslationsApplicationRequest;
use Ids\Localizator\Client\Response\Catalogs\PostCatalogsItems\PostCatalogsItemsResult;
use Ids\Localizator\Client\Response\Translation\GetTranslationsApplication\GetTranslationsApplicationResult;
use JMS\Serializer\SerializerInterface;

class Client
{
    private ClientInterface $client;
    private SerializerInterface $serializer;

    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @throws GuzzleException
     */
    public function getGetTranslationsApplication(
        GetTranslationsApplicationRequest $request
    ): GetTranslationsApplicationResult {

        $response = $this->client->get(
            '/api/translations/for-application',
            [
                RequestOptions::JSON => $this->serializer->toArray($request),
            ]
        );


        $data = $this->serializer->deserialize(
            $response->getBody()->getContents(),
            'array<string,' . GetTranslationsApplicationResult::class . '>',
            'json'
        );

        return $data['data'];
    }

    /**
     * @throws GuzzleException
     */
    public function postCatalogItems(PostCatalogsItemsRequest $request): PostCatalogsItemsResult
    {
        $response = $this->client->post(
            '/api/localizer/catalogs/items',
            [
                RequestOptions::BODY => $this->serializer->serialize($request, 'json'),
            ]
        );

        $data = $this->serializer->deserialize(
            $response->getBody(),
            'array<string,' . PostCatalogsItemsResult::class . '>',
            'json'
        );

        return $data['data'];
    }
}
