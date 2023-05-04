<?php

namespace Ids\Localizator;

class TranslationString
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function renderWith(...$data): string
    {
        return \sprintf($this->value, ...$data);
    }

    public function __toString()
    {
        return $this->value;
    }

    public function __invoke(...$data): string
    {
        return $this->renderWith(...$data);
    }
}
