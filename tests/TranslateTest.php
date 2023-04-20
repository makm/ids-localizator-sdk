<?php

namespace Ids\Localizator\Tests;


use GuzzleHttp\Exception\GuzzleException;
use Ids\Localizator\Client\Client;
use Ids\Localizator\Translator;
use Ids\Localizator\TranslatorFactory;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class TranslateTest extends TestCase
{
    private Client $translatorClient;
    private Translator $translator;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->translatorClient = $this->createMock(Client::class);

        $this->translator = TranslatorFactory::create()
            ->setCache(new RedisAdapter(new \Redis()))
            ->setClient($this->translatorClient)
            ->build();
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function testCheckEmptyTranslate(): void
    {
        $result = $this->translator->translate('rus', 'my_catalog', 'my_code');
        $this->assertNull($result);
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @codeCoverageIgnore 
     * @todo Допасать после интеграции
     */
    public function testPostAndCheckTranslate(): void
    {
        $this->translator->addTranslation('rus', 'my_catalog', 'my_code', 'my_translation');
        $result = $this->translator->translate('rus', 'my_catalog', 'my_code');
        $this->assertEquals('my_translation', $result);
    }
}
