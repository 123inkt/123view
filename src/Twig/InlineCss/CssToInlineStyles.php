<?php
declare(strict_types=1);

namespace DR\Review\Twig\InlineCss;

use DOMDocument;

class CssToInlineStyles extends \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles
{
    /**
     * Tijs Verkoyen's library automatically formats the html which is undesired in combination with style="whitespace:pre". Overwrite
     * the create document method to disable the formatting.
     * @inheritDoc
     */
    protected function createDomDocumentFromHtml($html): DOMDocument
    {
        $document                     = parent::createDomDocumentFromHtml($html);
        $document->formatOutput       = false;
        $document->preserveWhiteSpace = true;

        return $document;
    }
}
