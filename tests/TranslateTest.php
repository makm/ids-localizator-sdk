<?php

namespace Ids\Localizator\Tests;


use GuzzleHttp\Exception\GuzzleException;
use Ids\Localizator\Client\Client;
use Ids\Localizator\Translator;
use Ids\Localizator\TranslatorFactory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class TranslateTest extends TestCase
{
    private Client $translatorClientMock;
    private Translator $translator;
    private RedisAdapter $redisAdapterMock;

    public function setUp(): void
    {
        $this->translatorClientMock = $this->createMock(Client::class);

        $this->redisAdapterMock = $this->createMock(RedisAdapter::class);

        $this->translator = TranslatorFactory::create()
            ->setCache($this->redisAdapterMock)
            ->setClient($this->translatorClientMock)
            ->build();
    }


    private function mustHaveCache(string $key, string $value): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('get')->willReturn($value);
        $this->redisAdapterMock->method('hasItem')->with($key)->willReturn(true);
        $this->redisAdapterMock->method('getItem')->with($key)->willReturn($item);
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
        $this->mustHaveCache('no-app-no-prod_rus-my_catalog-my_code','my_translation');
        $result = $this->translator->translate('rus', 'my_catalog', 'my_code');
        $this->assertEquals('my_translation', $result);
    }
}
