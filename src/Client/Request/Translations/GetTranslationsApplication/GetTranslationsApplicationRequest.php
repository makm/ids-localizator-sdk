<?php

namespace Ids\Localizator\Client\Request\Translations\GetTranslationsApplication;

use JMS\Serializer\Annotation as Serializer;

class GetTranslationsApplicationRequest
{
    /**
     * @Serializer\SerializedName("application")
     */
    protected int $applicationId;

    /**
     * @Serializer\SerializedName("parentType")
     */
    protected ?string $parentType = 'C';

    /**
     * @Serializer\SerializedName("product")
     */
    protected ?int $productId = null;

    /**
     * @Serializer\SerializedName("parentLevel")
     */
    protected ?string $parentLevel = null;

    public function __construct(
        string $applicationId = null,
        ?string $parentType = null,
        ?string $productId = null,
        ?string $parentLevel = null
    ) {
        $this->applicationId = $applicationId;
        $this->parentType = $parentType;
        $this->productId = $productId;
        $this->parentLevel = $parentLevel;
    }
}
