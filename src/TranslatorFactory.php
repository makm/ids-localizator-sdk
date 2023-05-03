<?php

namespace Ids\Localizator;

use Ids\Localizator\Client\Client;
use Ids\Localizator\Client\ClientBuilder;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class TranslatorFactory
{
    private int $applicationId;
    private string $currentLang;
    private ?string $localizatorUrl;
    private ?int $organizationId;
    private ?int $defaultProductId = null;
    private Client $client;
    private CacheItemPoolInterface $cache;

    public function __construct(
        int $applicationId,
        int $organizationId = null,
        ?string $currentLang = 'rus',
    ) {
        $this->applicationId = $applicationId;
        $this->organizationId = $organizationId;
        $this->currentLang = $currentLang;
        $this->configureDefaultCacheAdapter();
    }

    public static function create(
        int $applicationId,
        string $currentLang,
        int $organizationId = null
    ): self {
        return new static($applicationId, $organizationId, $currentLang ?? 'rus');
    }

    public function configureDefaultCacheAdapter(): void
    {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @param CacheItemPoolInterface $cacheItemPool
     * @return TranslatorFactory
     */
    public function setCache(CacheItemPoolInterface $cacheItemPool): TranslatorFactory
    {
        $this->cache = $cacheItemPool;

        return $this;
    }

    /**
     * @param Client $client
     * @return TranslatorFactory
     */
    public function setClient(Client $client): TranslatorFactory
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param string|null $localizatorUrl
     * @return TranslatorFactory
     */
    public function setLocalizatorUrl(?string $localizatorUrl): TranslatorFactory
    {
        $this->localizatorUrl = $localizatorUrl;
        return $this;
    }

    /**
     * @param int|null $defaultProductId
     * @return TranslatorFactory
     */
    public function setDefaultProductId(?int $defaultProductId): TranslatorFactory
    {
        $this->defaultProductId = $defaultProductId;
        return $this;
    }

    public function build(): Translator
    {
        if (!isset($this->client)) {
            $this->client = ClientBuilder::create($this->localizatorUrl)->build();
        }

        if (!isset($this->cache)) {
            $this->configureDefaultCacheAdapter();
        }

        $translator = new Translator(
            $this->client,
            $this->cache,
            $this->applicationId,
            $this->currentLang,
            $this->defaultProductId,
            $this->organizationId
        );

        $translator->setWarmCacheIfEmpty(true);
        return $translator;
    }
}
