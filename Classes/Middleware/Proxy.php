<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StudioMitte\TypesenseSearch\Configuration\ConfigurationManager;
use StudioMitte\TypesenseSearch\Configuration\Dto\Configuration;
use StudioMitte\TypesenseSearch\Event\RequestProxyEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Proxy implements MiddlewareInterface
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly ClientInterface $client,
        private readonly ConfigurationManager $configurationManager,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $combinedProfileIdentifier = $this->getProfile($request);
        if ($combinedProfileIdentifier) {
            $split = explode('___', $combinedProfileIdentifier, 2);

            // error handling
            $configuration = $this->configurationManager->getBySite($request->getAttribute('site'), $split[1]);
            try {
                $responseOfBackend = $this->createRequestToBackend($request, $configuration, (int)$split[0]);
                $response = $this->responseFactory->createResponse()
                    ->withHeader('Content-Type', 'application/json; charset=utf-8');
                $response->getBody()->write(($responseOfBackend));
                return $response;
            } catch (\Exception $e) {
                // todo: handle error
                die('ERROR: ' . $e->getMessage());
            }
        }
        return $handler->handle($request);
    }

    protected function createRequestToBackend(ServerRequestInterface $request, Configuration $configuration, int $contentElementId): string
    {
        $authentication = $configuration->authentication;
        $uri = $request->getUri()->withHost($authentication->host)->withPort($authentication->port)->withScheme($authentication->protocol);
        $uri = $uri->withQuery('x-typesense-api-key=' . $authentication->apiKeyRead);

        $json = (array)json_decode($request->getBody()->getContents(), true);

        $requestProxyEvent = new RequestProxyEvent($configuration, $request, $contentElementId, $json);
        $this->eventDispatcher->dispatch($requestProxyEvent);
        $json = $requestProxyEvent->getPayload();

        $requestToBackend = $this->requestFactory->createRequest('POST', $uri);
        $body = new Stream('php://temp', 'rw');
        $body->write(json_encode($json, JSON_THROW_ON_ERROR));

        $requestToBackend = $requestToBackend->withBody($body);
        $responseOfBackend = $this->client->sendRequest($requestToBackend);

        return $responseOfBackend->getBody()->getContents();
    }

    protected function getProfile(ServerRequestInterface $request): string
    {
        return $request->getQueryParams()['x-typesense-api-key'] ?? '';
    }
}
