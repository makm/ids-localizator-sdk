<?php

namespace Ids\Localizator\Client\Response\Translation\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class GetTranslationsApplicationResult
{
    protected string $productId;

    /**
     * @Serializer\Type("array")
     */
    protected array $translations;

    /**
     * @return string
     */
    public function getProductId(): string
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