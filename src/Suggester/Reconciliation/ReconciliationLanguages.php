<?php
namespace Wikidata\Suggester\Reconciliation;

class ReconciliationLanguages extends AbstractReconciliation
{
    /**
     * Q34770: language, "a particular system of communication".
     *
     * Not Q315, which is the human faculty of language and which individual
     * languages are not instances of. Ranking against Q315 offered "condom"
     * for "french" and nothing at all for "german".
     */
    const TYPE = 'Q34770';

    /**
     * Suggest Wikidata entities that are languages.
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
