<?php

namespace Ids\Localizator\Client\Response\Catalogs\PostCatalogsItems;

use JMS\Serializer\Annotation as Serializer;

class PostCatalogsItemsResult
{
    protected int $id;
    protected string $itemId;
    protected string $itemValue;
    protected string $localizationKey;

    /**
     * @Serializer\Type("array<Ids\Localizator\Client\Response\Catalogs\PostCatalogsItems\Translation>")
     * @var Translation[]
     */
    protected array $translations = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * @return string
     */
    public function getItemValue(): string
    {
        return $this->itemValue;
    }

    /**
     * @return string
     */
    public function getLocalizationKey(): string
    {
        return $this->localizationKey;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}