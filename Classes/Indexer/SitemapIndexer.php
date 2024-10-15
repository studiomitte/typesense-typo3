<?php

declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Indexer;

use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;
use StudioMitte\TypesenseSearch\Api\Client;
use StudioMitte\TypesenseSearch\Indexer\Helper\Typo3PageContentExtractor;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use vipnytt\SitemapParser;

class SitemapIndexer
{
    protected \Typesense\Client $client;


    public function index(Site $site, string $url): int
    {
        $client = new Client();
        $this->client = $client->get($site);
        return $this->fetchSitemap($url);
    }

    private function fetchSitemap(string $url): int
    {
        $count = 0;
        $parser = new SitemapParser();
        $parser->parse($url);

        foreach ($parser->getURLs() as $singleUrl) {
            $this->parseUrl($singleUrl['loc']);
            $count++;
        }
        return $count;
    }

    private function parseUrl(string $url)
    {
        $urlInformation = parse_url($url);

        try {
            $this->fetchSingleUrl($url);

            $response = $this->fetchSingleUrl($url);
            $html = $response->getBody()->getContents();


            $extractor = GeneralUtility::makeInstance(Typo3PageContentExtractor::class, $html);

            $headers = $response->getHeaders();

            $pageId = $this->getHeader($headers, 'X-Typesense-Pageuid', 'int');
            $language = $this->getHeader($headers, 'X-Typesense-Language');


            $document = [
                'id' => implode('_', [$urlInformation['host'], $pageId, $language]),
                'table' => 'content',
                'site' => $this->getHeader($headers, 'X-Typesense-Site'),
                'sitelanguage' => $language,
                'languageid' => $this->getHeader($headers, 'X-Typesense-Languageid', 'int'),
                'uid' => $pageId,
                'pid' => $this->getHeader($headers, 'X-Typesense-Pagepid', 'int'),
                'url' => $url,
                'title' => $this->getHeader($headers, 'X-Typesense-Pagetitle'),
                'content' => $extractor->getIndexableContent(),
                'content_full' => $extractor->getContentMarkedForIndexing(),
            ];
//        print_r($document);die;

//        $this->addCategoryToDocument($document, (string)($headers['X-Typesense-Rootline'] ?? ''));
            $r = $this->client->collections['global']->documents->upsert($document);
        } catch (\UnexpectedValueException $exception) {
            return;
        }
    }

    private function getHeader(array $headers, string $key, string $type = 'string'): mixed
    {
        $value = $headers[$key][0] ?? null;
        switch ($type) {
            case 'int':
                return (int)$value;
            case 'string':
                return (string)$value;
            default:
                return $value;
        }
    }

    private function fetchSingleUrl(string $url): ResponseInterface
    {
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        try {
            $response = $requestFactory->request($url);

            if ($response->getStatusCode() !== 200) {
                throw new \UnexpectedValueException('Status code is not 200');
            }
        } catch (TransferException $exception) {
            throw new \UnexpectedValueException('Could not fetch URL: ' . $exception->getMessage(), 1728377179, $exception);
        }

        return $response;
    }

    /**
     * todo
     */
    protected function addCategoryToDocument(array $document, string $category)
    {
        $split = explode('___', $category);
        foreach ($split as $level) {

        }

    }

}
