<?php
namespace Wikidata\Suggester\Reconciliation;

class ReconciliationLocations extends AbstractReconciliation
{
    /** Q2221906: geographic location. */
    const TYPE = 'Q2221906';

    /**
     * Suggest Wikidata entities that are places.
     *
     * @param string $query
     * @param string $lang
     * @return array|null Suggestions, or null if the service could not be reached
     */
    public function getSuggestions($query, $lang = null)
    {
        $language = $this->normalizeLanguage($lang);
        $results = $this->reconcile($query, $language, self::TYPE);
        return (null === $results) ? null : $this->buildSuggestions($results, $language);
    }
}
