<?php

namespace Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems;

use JMS\Serializer\Annotation as Serializer;

class PostCatalogsItemsRequest
{
    protected int $applicationId;
    protected string $catalogName;
    protected string $itemId;
    protected ?int $organizationId = null;
    protected ?int $productId = null;
    protected ?int $catalogDescription = null;
    protected ?string $itemValue = null;
    /**
     * @Serializer\Type("array<Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems\Translation>")
     * @var Translation[]
     */
    protected array $translations = [];

    public function __construct(
        int $applicationId,
        string $catalogName,
        string $itemId,
        ?string $itemValue = null,
        array $translations = [],
        ?int $organizationId = -1,
        ?int $productId = null,
        ?int $catalogDescription = null
    ) {
        $this->organizationId = $organizationId;
        $this->applicationId = $applicationId;
        $this->productId = $productId;
        $this->catalogName = $catalogName;
        $this->catalogDescription = $catalogDescription;
        $this->itemId = $itemId;
        $this->itemValue = $itemValue;
        $this->translations = $translations;
    }
}
