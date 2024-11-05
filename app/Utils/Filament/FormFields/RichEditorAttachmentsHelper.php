<?php

namespace App\Utils\Filament\FormFields;

class RichEditorAttachmentsHelper
{
    public static function processContent($content)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $figures = $dom->getElementsByTagName('figure');

        foreach ($figures as $figure) {
            $dataTrixAttachment = $figure->getAttribute('data-trix-attachment');
            $attachment = json_decode(html_entity_decode($dataTrixAttachment), true);

            if (isset($attachment['contentType']) && strpos($attachment['contentType'], 'image') === 0) {
                // Remove caption for images
                $captions = $figure->getElementsByTagName('figcaption');
                foreach ($captions as $caption) {
                    $caption->parentNode->removeChild($caption);
                }

                // Set data-lightbox attribute for JS event delegation
                $links = $figure->getElementsByTagName('a');
                foreach ($links as $link) {
                    $imageUrl = $link->getAttribute('href');
                    $link->setAttribute('data-lightbox', $imageUrl);
                    $link->setAttribute('target', '_blank');
                }
            } elseif (strpos($attachment['contentType'], 'application') === 0) {
                // documents in a new tab and add custom styling
                $links = $figure->getElementsByTagName('a');
                foreach ($links as $link) {
                    $link->setAttribute('target', '_blank');
                    $link->setAttribute('class', 'w-full block p-4 bg-gray-100 border border-gray-200 rounded hover:bg-gray-200 dark:text-white dark:bg-gray-900');
                }

                // Add custom styling class
                $figure->setAttribute('class', 'w-full sm:w-1/2 lg:w-1/3 p-1 attachment attachment--file attachment--pdf custom-document-style');
            }
        }

        return $dom->saveHTML();
    }
}
