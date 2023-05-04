<?php

namespace Ids\Localizator\Tests\Client;


use GuzzleHttp\Exception\GuzzleException;
use Ids\Localizator\Client\Client;
use Ids\Localizator\Client\ClientBuilder;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\PostCatalogsItemsRequest;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\Translation;
use Ids\Localizator\Client\Request\Translations\GetTranslationsApplication\GetTranslationsApplicationRequest;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ClientTest extends TestCase
{
    private MockObject $mockedGuzzleClient;
    private Client $client;

    public function setUp(): void
    {
        $this->mockedGuzzleClient = $this->createMock(\GuzzleHttp\Client::class);

        $this->client = ClientBuilder::create()->setGuzzleClient($this->mockedGuzzleClient)->build();
    }

    private function createStubStream(string $streamText)
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($streamText);

        return $stream;
    }

    /**
     * @throws \JsonException
     */
    private function createStabResponse(array $data): ResponseInterface
    {
        $responseStab = $this->createMock(ResponseInterface::class);

        $responseStab->method('getBody')->willReturn(
            $this->createStubStream(json_encode($data, JSON_THROW_ON_ERROR))
        );

        return $responseStab;
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    private function waitResponse(string $method, array $responseData, array $with = []): void
    {
        $builderInvocationMocker = $this->mockedGuzzleClient
            ->expects($this->once())
            ->method($method)
            ->willReturn($this->createStabResponse($responseData));

        if ($with) {
            $builderInvocationMocker->with(...$with);
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetTranslationsApplication(): void
    {
        $request = new GetTranslationsApplicationRequest(5, 'C', 6, 'books');
        $this->waitResponse(
            'get',
            [
                'data' => [
                    'product_id' => 1,
                    'translations' => [
                        'eng' => [
                            'parent_item_localization_code' => ['item1' => 'trItem1'],
                        ],
                    ],

                ],
            ],
            [
                '/api/translations/for-application',
                [
                    'query' => [
                        'application' => 5,
                        'product' => 6,
                        'parent_level' => 'books',
                        'parent_type' => 'C',
                    ],
                ],
            ]
        );

        $result = $this->client->getGetTranslationsApplication($request);
        // $this->assertInstanceOf(GetTranslationsApplicationResult::class, $result);
        $this->assertEquals(1, $result->getProductId());
        $this->assertEquals(
            [
                'eng' =>
                    [
                        'parent_item_localization_code' => ['item1' => 'trItem1'],
                    ],
            ],
            $result->getTranslations()
        );
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     * @throws \JsonException
     */
    public function testPostCatalogsItems(): void
    {
        $request = new PostCatalogsItemsRequest(
            1,
            'catalog_name',
            'items_id',
            'item_value',
            [
                new Translation('language_code', 'translation'),
            ]
        );

        $this->waitResponse(
            'post',
            [
                'data' => [
                    'id' => 2,
                    'item_id' => 'id_2',
                    'item_value' => 'value_2',
                    'localization_key' => 'localization_key_result_2',
                    'translations' => [
                        [
                            'organization_id' => '-1',
                            'application_id' => '-1',
                            'language_code' => 'rus',
                            'translation' => 'Перевод',
                        ],
                    ],
                ],
            ],
            [
                '/api/localizer/catalogs/items',
                [
                    'body' => '{"application_id":1,"catalog_name":"catalog_name","item_id":"items_id","organization_id":-1,"item_value":"item_value","translations":[{"language_code":"language_code","translation":"translation"}]}',
                ],
            ]
        );

        $result = $this->client->postCatalogItems($request);

        // $this->assertInstanceOf(PostCatalogsItemsResult::class, $result);
        $this->assertEquals(2, $result->getId());
        $this->assertEquals('id_2', $result->getItemId());
        $this->assertEquals('value_2', $result->getItemValue());

        $this->assertIsArray($result->getTranslations());
        foreach ($result->getTranslations() as $translation) {
            $this->assertEquals(-1, $translation->getOrganizationId());
            $this->assertEquals(-1, $translation->getApplicationId());
            $this->assertEquals('rus', $translation->getLanguageCode());
            $this->assertEquals('Перевод', $translation->getTranslation());
        }
    }
}
