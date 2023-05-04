<?php

namespace Ids\Localizator;

use DateTimeInterface;
use GuzzleHttp\Exception\GuzzleException;
use Ids\Localizator\Client\Client;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\PostCatalogsItemsRequest;
use Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\Translation;
use Ids\Localizator\Client\Request\Translations\GetTranslationsApplication\GetTranslationsApplicationRequest;
use Ids\Localizator\Exception\CantDetermineProductIdException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class Translator
{
    private const DEFAULT_EXPIRES_AFTER = '10 years';
    private const LAST_WARMING_TIME_KEY = 'translator_last_warming_time';
    public const PARENT_TYPE_CATALOG = 'C';
    public const PARENT_TYPE_UI_ITEM = 'I';
    private bool $warmCacheIfEmpty = false;

    private Client $client;
    private CacheItemPoolInterface $itemPool;
    private int $applicationId;
    private ?int $defaultProductId;
    private string $currentLang;
    private ?int $organizationId;

    public function __construct(
        Client $client,
        CacheItemPoolInterface $itemPool,
        int $applicationId,
        ?string $currentLang = 'rus',
        ?int $defaultProductId = null,
        ?int $organizationId = null
    ) {
        $this->client = $client;
        $this->itemPool = $itemPool;
        $this->applicationId = $applicationId;
        $this->currentLang = $currentLang;
        $this->defaultProductId = $defaultProductId;
        $this->organizationId = $organizationId;
    }

    private function getCacheKey(
        string $type,
        string $lang,
        string $categoryName,
        string $code,
        int $productId = null
    ): string {
        return sprintf(
            '%s:%s-%s_%s-%s-%s',
            $type,
            $this->applicationId ?: 'no-app',
            $productId ?? $this->defaultProductId,
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
     * @param bool $warmCacheIfEmpty
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
    private function getTranslationByType(
        string $type,
        string $catalogName,
        string $code,
        int $productId = null
    ) {
        if ($this->warmCacheIfEmpty && $this->getLatestWarming() === null) {
            $this->warmCache();
        }

        $key = $this->getCacheKey(
            $type,
            $this->currentLang,
            $catalogName,
            $code,
            $this->getProductIdForUse($productId)
        );

        if ($this->itemPool->hasItem($key)) {
            return $this->itemPool->getItem($key)->get();
        }

        return null;
    }

    public function setDefaultProductId(?int $defaultProductId): Translator
    {
        $this->defaultProductId = $defaultProductId;
        return $this;
    }



    /**
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public function translate(string $catalogName, string $code, int $productId = null): ?TranslationString
    {
        $translation = $this->getTranslationByType(self::PARENT_TYPE_CATALOG, $catalogName, $code, $productId);
        return $translation ? new TranslationString($translation) : null;
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function translateUi(string $catalogName, string $code, int $productId = null): ?TranslationString
    {
        $translation = $this->getTranslationByType(self::PARENT_TYPE_UI_ITEM, $catalogName, $code, $productId);
        return $translation ? new TranslationString($translation) : null;
    }

    private function getProductIdForUse(?int $productId): int
    {
        $useProductId = $productId ?: $this->defaultProductId;
        if ($useProductId === null) {
            throw new CantDetermineProductIdException('Can\'t determine productId for use');
        }

        return $useProductId;
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function addTranslation(
        string $catalogName,
        string $code,
        string $value,
        int $productId = null,
        string $type = 'I'
    ): void {
        $useProductId = $this->getProductIdForUse($productId);
        $postRequest = new PostCatalogsItemsRequest(
            $this->applicationId,
            $catalogName,
            $code,
            null,
            [
                new Translation($this->currentLang, $value),
            ],
            $this->organizationId,
            $useProductId
        );

        $result = $this->client->postCatalogItems($postRequest);
        foreach ($result->getTranslations() as $translation) {
            $this->saveItem(
                $translation->getLanguageCode(),
                $catalogName,
                $result->getItemId(),
                $translation->getTranslation(),
                $useProductId,
                $type
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
            new GetTranslationsApplicationRequest($this->applicationId)
        );

        foreach ($result->getUIitems() as $UIItem) {
            $productId = $UIItem->getProductId();
            foreach ($UIItem->getTranslations() as $langCode => $landTranslation) {
                foreach ($landTranslation as $catalogName => $catalogTranslation) {
                    foreach ($catalogTranslation as $code => $translation) {
                        $this->saveItem($langCode, $catalogName, $code, $translation, $productId, self::PARENT_TYPE_UI_ITEM);
                    }
                }
            }
        }

        foreach ($result->getCatalogs() as $catalogItem) {
            $productId = $catalogItem->getProductId();
            foreach ($catalogItem->getTranslations() as $langCode => $landTranslation) {
                foreach ($landTranslation as $catalogName => $catalogTranslation) {
                    foreach ($catalogTranslation as $code => $translation) {
                        $this->saveItem($langCode, $catalogName, $code, $translation, $productId, self::PARENT_TYPE_CATALOG);
                    }
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
    private function saveItem(
        string $lang,
        string $catalogName,
        string $code,
        string $translation,
        int $productId,
        string $type
    ): void {
        $key = $this->getCacheKey($type, $lang, $catalogName, $code, $productId);
        $item = $this->itemPool->getItem($key);
        $item
            ->set($translation)
            ->expiresAfter($this->getExpAfter());
        $this->itemPool->save($item);
    }
}
