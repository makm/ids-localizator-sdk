<?php

namespace Ids\Localizator\Client\Response\Catalogs\PostCatalogsItems;

class Translation
{
    protected string $applicationId;
    protected string $organizationId;
    protected string $languageCode;
    protected string $translation;

    /**
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @return string
     */
    public function getTranslation(): string
    {
        return $this->translation;
    }
}