<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Configuration\Dto;

readonly class Profile
{
    public string $label;
    public string $collection;
    public array $searchParameters;

    public function __construct(public string $identifier, array $settings)
    {
        $this->collection = $settings['collection'] ?? '';
        $this->label = $settings['label'] ?? '';
        $this->searchParameters = $settings['searchParameters'] ?? [];
    }

    public function getFullLabel(): string
    {
        return sprintf('%s - %s [%s]', $this->label, $this->identifier, $this->collection);
    }
}
