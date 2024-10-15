<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Event;

use Psr\Http\Message\ServerRequestInterface;
use StudioMitte\TypesenseSearch\Configuration\Dto\Configuration;

final class RequestProxyEvent
{
    public function __construct(
        private readonly Configuration $configuration,
        private readonly ServerRequestInterface $request,
        private readonly int $contentElementId = 0,
        private array $payload = []
    )
    {
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getContentElementId(): int
    {
        return $this->contentElementId;
    }

}
