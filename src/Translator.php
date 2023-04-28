<?php

namespace Ids\Localizator;

use DateTimeInterface;
use GuzzleHttp\Exception\GuzzleException;
use Ids\Localizator\Client\Client;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\PostCatalogsItemsRequest;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\Translation;
use Ids\Localizator\Client\Request\Translations\GetTranslationsApplication\GetTranslationsApplicationRequest;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class Translator
{
    private const DEFAULT_EXPIRES_AFTER = '10 years';
    private const LAST_WARMING_TIME_KEY = 'translator_last_warming_time';
    private bool $warmCacheIfEmpty = false;

    private Client $client;
    private CacheItemPoolInterface $itemPool;
    private int $organizationId;
    private ?int $applicationId;
    private ?string $productId;

    /**
     * @param  Client  $client
     * @param  CacheItemPoolInterface  $itemPool
     * @param  int  $organizationId
     * @param  int|null  $applicationId
     * @param  string|null  $productId
     */
    public function __construct(
        Client $client,
        CacheItemPoolInterface $itemPool,
        int $organizationId,
        ?int $applicationId,
        ?string $productId
    ) {
        $this->client = $client;
        $this->itemPool = $itemPool;
        $this->organizationId = $organizationId;
        $this->applicationId = $applicationId;
        $this->productId = $productId;
    }


    private function getCacheKey(string $lang, string $categoryName, string $code): string
    {
        return sprintf(
            '%s-%s_%s-%s-%s',
            $this->applicationId ?: 'no-app',
            $this->productId ?: 'no-prod',
            strtolower($lang),
            $categoryName,
            $code
        );
    }

    private function getExpAfter(): \DateInterval
    {
        return \DateInterval::createFromDateString(self::DEFAULT_EXPIRES_AFTER);
    }

    /**
     * @param  bool  $warmCacheIfEmpty
     * @return Translator
     */
    public function setWarmCacheIfEmpty(bool $warmCacheIfEmpty): Translator
    {
        $this->warmCacheIfEmpty = $warmCacheIfEmpty;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public function translate(string $lang, string $catalogName, string $code): ?string
    {
        if ($this->warmCacheIfEmpty && $this->getLatestWarming() === null) {
            $this->warmCache();
        }

        $key = $this->getCacheKey($lang, $catalogName, $code);
        if ($this->itemPool->hasItem($key)) {
            return $this->itemPool->getItem($key)->get();
        }

        return null;
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function addTranslation(string $lang, string $catalogName, string $code, string $value): void
    {
        $postRequest = new PostCatalogsItemsRequest(
            $catalogName,
            $code,
            null,
            [
                new Translation($lang, $value),
            ],
            $this->organizationId,
            $this->applicationId,
            $this->productId
        );

        $result = $this->client->postCatalogItems($postRequest);
        foreach ($result->getTranslations() as $translation) {
            $this->saveItem(
                $translation->getLanguageCode(),
                $catalogName,
                $result->getItemId(),
                $translation->getTranslation()
            );
        }
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function reset(): void
    {
        $this->itemPool->clear();
        $this->warmCache();
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function warmCache(): void
    {
        $result = $this->client->getGetTranslationsApplication(
            new GetTranslationsApplicationRequest($this->applicationId, $this->productId)
        );

        foreach ($result->getTranslations() as $lang => $langTranslations) {
            foreach ($langTranslations as $catalogName => $parentItemTranslation) {
                foreach ($parentItemTranslation as $code => $translation) {
                    $this->saveItem($lang, $catalogName, $code, $translation);
                }
            }
        }

        $lastWarmingTimeItem = $this->itemPool->getItem(self::LAST_WARMING_TIME_KEY);
        $lastWarmingTimeItem->set((new \DateTime())->format(DateTimeInterface::ATOM));
        $this->itemPool->save($lastWarmingTimeItem);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getLatestWarming(): ?\DateTime
    {
        if ($this->itemPool->hasItem(self::LAST_WARMING_TIME_KEY)) {
            $lastWarmingTimeItem = $this->itemPool->getItem(self::LAST_WARMING_TIME_KEY);

            return \DateTime::createFromFormat(DateTimeInterface::ATOM, $lastWarmingTimeItem->get());
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function saveItem(string $lang, string $catalogName, string $code, string $translation): void
    {
        $key = $this->getCacheKey($lang, $catalogName, $code);
        $item = $this->itemPool->getItem($key);
        $item
            ->set($translation)
            ->expiresAfter($this->getExpAfter());
        $this->itemPool->save($item);
    }
}
