<?php

namespace Ids\Localizator\Client\Response\Translation\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class GetTranslationsApplicationResult
{
    /**
     * @Serializer\Type("array<Ids\Localizator\Client\Response\Translation\GetTranslationsApplication\GetTranslationsApplicationUIItem>")
     * @Serializer\SerializedName("UI items")
     */
    protected array $UIitems = [];

    /**
     * @return GetTranslationsApplicationUIItem[]
     */
    public function getUIitems(): array
    {
        return $this->UIitems;
    }
}
