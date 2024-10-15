<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StudioMitte\TypesenseSearch\Event\PageDataEnhancementEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class PageData implements MiddlewareInterface
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            $context = $GLOBALS['TSFE']->getContext();
            // only show with specific header
            if (
                true
//                (!isset($GLOBALS['TSFE']->config['config']['enableContentLengthHeader']) || $GLOBALS['TSFE']->config['config']['enableContentLengthHeader'])
//                && !$context->getPropertyFromAspect('backend.user', 'isLoggedIn', false) && !$context->getPropertyFromAspect('workspace', 'isOffline', false)
            ) {
                $headers = $this->getAdditionalHeaderData($request);
                $pageDataEnhancementEvent = new PageDataEnhancementEvent($request, $headers);
                $this->eventDispatcher->dispatch($pageDataEnhancementEvent);
                foreach ($pageDataEnhancementEvent->getHeaders() as $key => $value) {
                    $response = $response->withHeader('X-Typesense-' . ucfirst($key), $value);
                }
            }
        }
        return $response;
    }

    private function getAdditionalHeaderData(ServerRequestInterface $request): array
    {
        return [
            'site' => $request->getAttribute('site')->getIdentifier(),
            'pageUid' => (string)$request->getAttribute('routing')->getPageid(),
            'pagePid' => (string)$GLOBALS['TSFE']->page['pid'],
            'pageTitle' => (string)GeneralUtility::makeInstance(PageRenderer::class)->getTitle(),
            'language' => (string)$request->getAttribute('language')->getLocale()->getLanguageCode(),
            'languageid' => (string)$request->getAttribute('language')->getLanguageId(),
            'rootline' => implode('___', array_reverse(array_map(fn($page) => ($page['title'] . '|||' . $page['uid']), $GLOBALS['TSFE']->rootLine))),
        ];
    }
}
