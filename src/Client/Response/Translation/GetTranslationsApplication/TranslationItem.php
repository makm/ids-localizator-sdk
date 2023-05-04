<?php

namespace Ids\Localizator\Client\Response\Translation\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class TranslationItem
{
    protected int $productId;

    /**
     * @Serializer\Type("array")
     */
    protected array $translations = [];

    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
