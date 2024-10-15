<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Configuration\Dto;

readonly class Authentication
{
    public string $apiKeyRead;
    public string $apiKeyWrite;
    public string $host;
    public int $port;
    public string $protocol;

    public function __construct(
        array $configuration
    )
    {
        // todo error handling
        if (!isset($configuration['apiKey'])) {
            throw new \RuntimeException('apiKey is missing');
        }
        if (is_string($configuration['apiKey'])) {
            $this->apiKeyWrite = $configuration['apiKey'];
            $this->apiKeyRead = $configuration['apiKey'];
        } elseif (is_array($configuration['apiKey'])) {
            if (!isset($configuration['apiKey']['read'])) {
                throw new \RuntimeException('apiKey read is missing');
            }
            if (!isset($configuration['apiKey']['write'])) {
                throw new \RuntimeException('apiKey write is missing');
            }
            $this->apiKeyRead = $configuration['apiKey']['read'];
            $this->apiKeyWrite = $configuration['apiKey']['write'];
        } else {
            throw new \RuntimeException('apiKey is not a string or an array');
        }

        $this->host = $configuration['host'];
        $this->port = $configuration['port'];
        $this->protocol = $configuration['protocol'];
    }
}
