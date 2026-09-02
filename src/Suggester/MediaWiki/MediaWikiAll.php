<?php
namespace Wikidata\Suggester\MediaWiki;

use Wikidata\Suggester\AbstractSuggester;

class MediaWikiAll extends AbstractSuggester
{
    const API_URI = 'https://www.wikidata.org/w/api.php';

    /**
     * Retrieve suggestions from the Wikidata MediaWiki API.
     *
     * @see https://www.wikidata.org/w/api.php?action=help&modules=wbsearchentities
     * @param string $query
     * @param string $lang
     * @return array|null Suggestions, or null if the API could not be reached
     */
    public function getSuggestions($query, $lang = null)
    {
        $language = $this->normalizeLanguage($lang);

        $results = $this->search($query, $language);

        // Wikidata answers an unknown language tag with a "badvalue" error
        // rather than a failure status, so retry once with the primary subtag:
        // it knows pt-br and zh-hant but not fr-ca or es-mx.
        if (isset($results['error']['code']) && 'badvalue' === $results['error']['code']) {
            $fallback = $this->primarySubtag($language);
            if ($fallback !== $language) {
                $language = $fallback;
                $results = $this->search($query, $language);
            }
        }

        if (null === $results) {
            return null;
        }
        if (!isset($results['search']) || !is_array($results['search'])) {
            return [];
        }

        $suggestions = [];
        foreach ($results['search'] as $result) {
            if (!isset($result['id'])) {
                continue;
            }
            $info = [sprintf('ID: %s', $result['id'])];
            foreach (['label' => 'Label', 'description' => 'Description'] as $key => $caption) {
                if (isset($result[$key]) && '' !== $result[$key]) {
                    $info[] = sprintf('%s: %s', $caption, $result[$key]);
                }
            }

            // An entity with no label in the requested language still has an id;
            // showing that beats showing a blank row the user could select.
            $value = (isset($result['label']) && '' !== trim((string) $result['label']))
                ? $result['label']
                : $result['id'];
            $uri = isset($result['concepturi']) && '' !== $result['concepturi']
                ? $result['concepturi']
                : $this->entityUri($result['id']);
            $info[] = sprintf('URI: %s', $uri);

            $suggestions[] = $this->suggestion(
                $value,
                $uri,
                $info,
                $this->previewUrl($language, $result['id'])
            );
        }

        return $suggestions;
    }

    /**
     * @param string $query
     * @param string $language
     * @return array|null
     */
    protected function search($query, $language)
    {
        return $this->fetch(self::API_URI, [
            'action' => 'wbsearchentities',
            'search' => $query,
            'limit' => 50,
            'format' => 'json',
            'language' => $language,
        ]);
    }
}
