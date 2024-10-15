<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class SearchController extends ActionController
{

    public function indexAction(): ResponseInterface
    {
        if (!$this->settings['profile']) {
            return $this->htmlResponse('No profile configured');
        }

        $contentObjectData = $this->request->getAttribute('currentContentObject');

        $uri = $this->request->getUri();
        $this->view->assignMultiple([
            'endpoint' => [
                'protocol' => $uri->getScheme(),
                'host' => $uri->getHost(),
            ],
            'geosearch' => GeneralUtility::inList($this->settings['facetBy'] ?? '', '_geoloc'),
            'profile' => implode('___', [($contentObjectData ? $contentObjectData->data['uid'] : 0), $this->settings['profile']]),
        ]);
        $this->provideTranslations();;
        return $this->htmlResponse();
    }

    private function provideTranslations(): void
    {
        $languageFiles = GeneralUtility::trimExplode(chr(10), $this->settings['languageFiles'] ?? '', true);
        if (empty($languageFiles)) {
            return;
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        foreach ($languageFiles as $languageFile) {
            $pageRenderer->addInlineLanguageLabelFile($languageFile);
        }
    }
}
