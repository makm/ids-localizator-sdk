<?php

namespace Ids\Localizator;

use Ids\Localizator\Client\Client;
use Ids\Localizator\Client\ClientBuilder;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class TranslatorFactory
{
    private int $organizationId;
    private ?int $applicationId;
    private ?string $productId;
    private ?string $localizatorUrl;
    private Client $client;
    private CacheItemPoolInterface $cache;

    public function __construct(
        int $organizationId = -1,
        ?int $applicationId = null,
        ?string $productId = null,
        ?string $localizatorUrl = null
    ) {
        $this->organizationId = $organizationId;
        $this->applicationId = $applicationId;
        $this->productId = $productId;
        $this->localizatorUrl = $localizatorUrl;
        $this->configureDefaultCacheAdapter();
    }

    public static function create(
        int $organizationId = -1,
        int $applicationId = null,
        int $productId = null,
        string $localizatorUrl = null
    ): self {
        return new static($organizationId, $applicationId, $productId, $localizatorUrl);
    }

    public function configureDefaultCacheAdapter(): void
    {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @param  CacheItemPoolInterface  $cacheItemPool
     * @return TranslatorFactory
     */
    public function setCache(CacheItemPoolInterface $cacheItemPool): TranslatorFactory
    {
        $this->cache = $cacheItemPool;

        return $this;
    }

    /**
     * @param  Client  $client
     * @return TranslatorFactory
     */
    public function setClient(Client $client): TranslatorFactory
    {
        $this->client = $client;

        return $this;
    }

    public function build(): Translator
    {
        if ( ! isset($this->client)) {
            $this->client = ClientBuilder::create($this->localizatorUrl)->build();
        }

        if ( ! isset($this->cache)) {
            $this->configureDefaultCacheAdapter();
        }

        return new Translator(
            $this->client, $this->cache, $this->organizationId, $this->applicationId, $this->productId
        );
    }
}
