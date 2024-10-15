<?php

declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Configuration\Dto;

readonly class Configuration
{
    public function __construct(
        public Authentication $authentication,
        public Profile $profile
    )
    {

    }
}
