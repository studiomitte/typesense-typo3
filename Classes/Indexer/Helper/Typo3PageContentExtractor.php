<?php

namespace StudioMitte\TypesenseSearch\Indexer\Helper;

use ApacheSolrForTypo3\Solr\System\Logging\SolrLogManager;
use DOMDocument;
use DOMXPath;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function libxml_use_internal_errors;

/**
 * Thanks to ApacheSolrForTypo3\Solr\Typo3PageContentExtractor
 * Content extraction class for TYPO3 pages.
 */
class Typo3PageContentExtractor
{

    public function __construct(protected string $content)
    {
    }

    /**
     * Unicode ranges which should get stripped before sending a document to solr.
     * This is necessary if a document (PDF, etc.) contains unicode characters which
     * are valid in the font being used in the document but are not available in the
     * font being used for displaying results.
     *
     * This is often the case if PDFs are being indexed where special fonts are used
     * for displaying bullets, etc. Usually those bullets reside in one of the unicode
     * "Private Use Zones" or the "Private Use Area" (plane 15 + 16)
     *
     * @see http://en.wikipedia.org/wiki/Unicode_block
     */
    protected static array $stripUnicodeRanges = [
        ['FFFD', 'FFFD'],
        // Replacement Character (�) @see http://en.wikipedia.org/wiki/Specials_%28Unicode_block%29
        ['E000', 'F8FF'],
        // Private Use Area (part of Plane 0)
        ['F0000', 'FFFFF'],
        // Supplementary Private Use Area (Plane 15)
        ['100000', '10FFFF'],
        // Supplementary Private Use Area (Plane 16)
    ];


    /**
     * Mapping of HTML tags to Solr document fields.
     */
    protected array $tagToFieldMapping = [
        'h1' => 'tagsH1',
        'h2' => 'tagsH2H3',
        'h3' => 'tagsH2H3',
        'h4' => 'tagsH4H5H6',
        'h5' => 'tagsH4H5H6',
        'h6' => 'tagsH4H5H6',
        'u' => 'tagsInline',
        'b' => 'tagsInline',
        'strong' => 'tagsInline',
        'i' => 'tagsInline',
        'em' => 'tagsInline',
        'a' => 'tagsA',
    ];


    /**
     * Shortcut method to retrieve the raw content marked for indexing.
     *
     * @return string Content marked for indexing.
     */
    public function getContentMarkedForIndexing(): string
    {
        return $this->extractContentMarkedForIndexing($this->content);
    }

    /**
     * Extracts the markup wrapped with TYPO3SEARCH_begin and TYPO3SEARCH_end
     * markers.
     *
     * @param string $html HTML markup with TYPO3SEARCH markers for content that should be indexed
     * @return string HTML markup found between TYPO3SEARCH markers
     */
    protected function extractContentMarkedForIndexing(string $html): string
    {
        preg_match_all(
            '/<!--\s*?TYPO3SEARCH_begin\s*?-->.*?<!--\s*?TYPO3SEARCH_end\s*?-->/mis',
            $html,
            $indexableContents
        );
        $indexableContent = implode('', $indexableContents[0]);

        $indexableContent = $this->excludeContentByClass($indexableContent);

        return $indexableContent;
    }

    /**
     * Exclude some html parts by class inside content wrapped with TYPO3SEARCH_begin and TYPO3SEARCH_end
     * markers.
     *
     * @param string $indexableContent HTML markup
     * @return string HTML
     */
    public function excludeContentByClass(string $indexableContent, array $excludeClasses = []): string
    {
        if (empty(trim($indexableContent))) {
            return $indexableContent;
        }

        if (count($excludeClasses) === 0) {
            return $indexableContent;
        }

        $isInContent = self::containsOneOfTheStrings($indexableContent, $excludeClasses);
        if (!$isInContent) {
            return $indexableContent;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $indexableContent);
        $xpath = new DOMXPath($doc);
        foreach ($excludeClasses as $excludePart) {
            $elements = $xpath->query("//*[contains(@class,'" . $excludePart . "')]");
            if (count($elements) == 0) {
                continue;
            }

            foreach ($elements as $element) {
                $element->parentNode->removeChild($element);
            }
        }
        $html = $doc->saveHTML($doc->documentElement->parentNode);
        // remove XML-Preamble, newlines and doctype
        $html = preg_replace('/(<\?xml[^>]+\?>|\r?\n|<!DOCTYPE.+?>)/imS', '', $html);
        return str_replace(['<html>', '</html>', '<body>', '</body>'], ['', '', '', ''], $html);
    }

    /**
     * Returns the cleaned indexable content from the page's HTML markup.
     *
     * The content is cleaned from HTML tags and control chars Solr could
     * stumble on.
     *
     * @return string Indexable, cleaned content ready for indexing.
     */
    public function getIndexableContent(): string
    {
        // @extensionScannerIgnoreLine
        $content = $this->extractContentMarkedForIndexing($this->content);

        // clean content
        $content = self::cleanContent($content);
        $content = trim($content);
        // reduce multiple spaces to one space and return
        return preg_replace('!\s+!u', ' ', $content);
    }

    /**
     * Strips html tags, and tab, new-line, carriage-return, &nbsp; whitespace
     * characters.
     *
     * @param string $content String to clean
     * @return string String cleaned from tags and special whitespace characters
     */
    public static function cleanContent(string $content): string
    {
        $content = self::stripControlCharacters($content);
        // remove Javascript
        $content = preg_replace('@<script[^>]*>.*?</script>@msi', '', $content);

        // remove internal CSS styles
        $content = preg_replace('@<style[^>]*>.*?</style>@msi', '', $content);

        // prevents concatenated words when stripping tags afterward
        $content = str_replace(['<', '>'], [' <', '> '], $content);
        $content = str_replace(["\t", "\n", "\r", '&nbsp;'], ' ', $content);
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        $content = self::stripUnicodeRanges($content);
        $content = preg_replace('/\s{2,}/u', ' ', $content);

        return trim($content);
    }


    /**
     * Strips control characters that cause Jetty/Solr to fail.
     *
     * @param string $content the content to sanitize
     * @return string the sanitized content
     * @see http://w3.org/International/questions/qa-forms-utf-8.html
     */
    public static function stripControlCharacters(string $content): string
    {
        // Printable utf-8 does not include any of these chars below x7F
        return preg_replace('@[\x00-\x08\x0B\x0C\x0E-\x1F]@', ' ', $content);
    }

    /**
     * Strips unusable unicode ranges
     *
     * @param string $content Content to sanitize
     * @return string Sanitized content
     */
    public static function stripUnicodeRanges(string $content): string
    {
        foreach (self::$stripUnicodeRanges as $range) {
            $content = self::stripUnicodeRange($content, $range[0], $range[1]);
        }
        return $content;
    }

    /**
     * Strips a UTF-8 character range
     *
     * @param string $content Content to sanitize
     * @param string $start Unicode range start character as uppercase hexadecimal string
     * @param string $end Unicode range end character as uppercase hexadecimal string
     * @return string Sanitized content
     */
    public static function stripUnicodeRange(string $content, string $start, string $end): string
    {
        return preg_replace(
            '/[\x{' . $start . '}-\x{' . $end . '}]/u',
            '',
            $content
        );
    }

    public static function containsOneOfTheStrings(
        string $haystack,
        array $needles,
    ): bool {
        foreach ($needles as $needle) {
            $position = strpos($haystack, $needle);
            if ($position !== false) {
                return true;
            }
        }

        return false;
    }

}
