<?php

namespace Ids\Localizator\Tests\Functional;


use GuzzleHttp\Exception\GuzzleException;
use Ids\Localizator\Translator;
use Ids\Localizator\TranslatorFactory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class TranslateTest extends TestCase
{
    private Translator $translator;

    public function setUp(): void
    {
        $this->translator = TranslatorFactory::create(5, null, null, 'http://localhost:8001')->build();
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
            'rus',
            'my_catalog',
            'my_code',
            'my_translation'
        );

        $result = $this->translator->translate('rus', 'my_catalog', 'my_code');
        $this->assertEquals('my_translation', $result);
    }
}
