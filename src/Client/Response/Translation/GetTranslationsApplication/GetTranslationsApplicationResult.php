<?php

namespace Ids\Localizator\Client\Response\Translation\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class GetTranslationsApplicationResult
{
    /**
     * @Serializer\Type("array<Ids\Localizator\Client\Response\Translation\GetTranslationsApplication\TranslationItem>")
     * @Serializer\SerializedName("catalogsAll")
     */
    // @todo временно не мапим.
     protected array $catalogs = [];

    /**
     * @Serializer\Type("array<Ids\Localizator\Client\Response\Translation\GetTranslationsApplication\TranslationItem>")
     * @Serializer\SerializedName("UI items")
     */
    protected array $UIitems = [];

    /**
     * @return TranslationItem[]
     */
    public function getCatalogs(): array
    {
        return $this->catalogs;
    }

    /**
     * @return TranslationItem[]
     */
    public function getUIitems(): array
    {
        return $this->UIitems;
    }
}
