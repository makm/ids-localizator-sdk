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

        $this->translator = TranslatorFactory::create(1, 'rus')
            ->setCache($this->redisAdapterMock)
            ->setClient($this->translatorClientMock)
            ->build();
    }


    private function mustHaveCache(string $key, string $value): void
    {
        //
        $itemLastTime = $this->createMock(CacheItemInterface::class);
        $itemLastTime->method('get')->willReturn((new \DateTime('+10 years'))->format(DATE_ATOM));


        //create items
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('get')->willReturn($value);

        //return value
        $this->redisAdapterMock->method('hasItem')->with('translator_last_warming_time')->willReturn(true);
        $this->redisAdapterMock->method('getItem')->with('translator_last_warming_time')->willReturn($itemLastTime);
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
        $this->translator->addTranslation(
            'my_catalog',
            'my_code',
            'my_translation',
            2
        );
        $this->mustHaveCache('-1-no-prod_rus-my_catalog-my_code', 'my_translation');
        $result = $this->translator->translate('rus', 'my_catalog', 2);
        $this->assertEquals('my_translation', (string)$result);
    }
}
