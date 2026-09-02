<?php
namespace Wikidata\Suggester\Reconciliation;

class ReconciliationPersons extends AbstractReconciliation
{
    /** Q5: human. */
    const TYPE = 'Q5';

    /**
     * Suggest Wikidata entities that are people.
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
