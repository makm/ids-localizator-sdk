<?php

namespace Ids\Localizator\Client\Request\Translations\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class GetTranslationsApplicationRequest
{
    /**
     * @Serializer\SerializedName("application")
     */
    protected ?string $applicationId = null;
    /**
     * @Serializer\SerializedName("product")
     */
    protected ?string $productId = null;

    protected ?string $parentLevel = null;
    protected ?string $parentType = null;

    /**
     * @param string|null $applicationId
     * @param string|null $productId
     * @param string|null $parentLevel
     * @param string|null $parentType
     */
    public function __construct(
        ?string $applicationId = null,
        ?string $productId = null,
        ?string $parentLevel = null,
        ?string $parentType = null
    ) {
        $this->applicationId = $applicationId;
        $this->productId = $productId;
        $this->parentLevel = $parentLevel;
        $this->parentType = $parentType;
    }
}