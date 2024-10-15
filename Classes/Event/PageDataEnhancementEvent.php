<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Event;

use Psr\Http\Message\ServerRequestInterface;

final class PageDataEnhancementEvent
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private array $headers
    )
    {
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

}
