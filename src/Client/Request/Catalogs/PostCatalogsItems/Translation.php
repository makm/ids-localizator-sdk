<?php

namespace Ids\Localizator\Client\Request\Catalogs\PostCatalogsItems;

class Translation
{
    protected string $languageCode;
    protected string $translation;

    public function __construct(string $languageCode, string $translation)
    {
        $this->languageCode = $languageCode;
        $this->translation = $translation;
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