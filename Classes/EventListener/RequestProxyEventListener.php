<?php
declare(strict_types=1);

namespace StudioMitte\TypesenseSearch\EventListener;

use StudioMitte\TypesenseSearch\Configuration\Dto\Profile;
use StudioMitte\TypesenseSearch\Event\RequestProxyEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class RequestProxyEventListener
{

    public function __invoke(RequestProxyEvent $event): void
    {
        $profile = $event->getConfiguration()->profile;
        $payload = $event->getPayload();
//        print_r($payload);
        $payload = $this->extendPayloadByProfile($payload, $profile);
        $payload = $this->extendPayloadByPlugin($payload, $event->getContentElementId());
//        print_r($payload);die;
        $event->setPayload($payload);
    }

    private function extendPayloadByPlugin(array $payload, int $contentElementId): array
    {
        $row = BackendUtility::getRecord('tt_content', $contentElementId, 'pi_flexform', 'list_type="typesensesearch_search"');
        $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
        $settings = $flexformService->convertFlexFormContentToArray((string)($row['pi_flexform'] ?? ''));

        foreach (['facetBy' => 'facet_by', 'queryBy' => 'query_by'] as $fieldInPlugin => $fieldInBackend) {
            if ($settings['settings'][$fieldInPlugin] ?? false) {
                foreach ($payload['searches'] as $k => &$search) {
                    $search[$fieldInBackend] = (empty($search[$fieldInBackend] ?? null) || $search[$fieldInBackend] === '*')
                        ? $settings['settings'][$fieldInPlugin]
                        : ($search[$fieldInBackend] . ',' . $settings['settings'][$fieldInPlugin]);
                }
            }
        }
        return $payload;
    }

    private function extendPayloadByProfile(array $payload, Profile $profile): array
    {
        if (is_array($payload['searches'] ?? null)) {
            foreach ($payload['searches'] as $k => &$search) {
                // todo make it more robust
                if (is_array($profile->searchParameters['filter_by'] ?? null)) {
                    $extraFilter = implode(' && ', $profile->searchParameters['filter_by']);
                    $search['filter_by'] = isset($search['filter_by']) ? ($search['filter_by'] . ' && ' . $extraFilter) : $extraFilter;
                }
            }
        }
        return $payload;
    }

}
