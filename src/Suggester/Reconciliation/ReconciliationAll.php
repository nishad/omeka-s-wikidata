<?php
namespace Wikidata\Suggester\Reconciliation;

class ReconciliationAll extends AbstractReconciliation
{
    /**
     * Suggest any Wikidata entity by prefix.
     *
     * @param string $query
     * @param string $lang
     * @return array|null Suggestions, or null if the service could not be reached
     */
    public function getSuggestions($query, $lang = null)
    {
        $language = $this->normalizeLanguage($lang);
        $results = $this->suggest('entity', $query, $language);
        return (null === $results) ? null : $this->buildSuggestions($results, $language);
    }
}
