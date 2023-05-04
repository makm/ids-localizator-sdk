<?php

namespace Ids\Localizator\Client\Request\Translations\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class GetTranslationsApplicationRequest
{
    /**
     * @Serializer\SerializedName("application")
     */
    protected int $applicationId;
    protected string $parentType = 'C';
    /**
     * @Serializer\SerializedName("product")
     */
    protected ?int $productId = null;

    protected ?string $parentLevel = null;

    public function __construct(
        string $applicationId = null,
        string $parentType = 'C',
        ?string $productId = null,
        ?string $parentLevel = null
    ) {
        $this->applicationId = $applicationId;
        $this->parentType = $parentType;
        $this->productId = $productId;
        $this->parentLevel = $parentLevel;
    }
}