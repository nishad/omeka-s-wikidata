<?php
namespace Wikidata\Suggester;

interface SuggesterInterface
{
    /**
     * Get an array of suggestions given a query.
     *
     * Implementations should return an array in the following format:
     *
     * [
     *   [
     *     'value' => <value>,
     *     'data' => [
     *       'uri' => <uri>,
     *       'info' => <info>,
     *     ],
     *   ],
     *   <...>
     * ]
     *
     * <value>: (required) the suggestion text, preferably in the passed language
     * <uri>: (optional) the suggestion's canonical URI
     * <info>: (optional) any disambiguating text, preferably in the passed language
     *
     * Implementations must return null, not an empty array, when the upstream
     * service could not be reached or answered with an error. The caller caches
     * results, and an empty array is indistinguishable from "no matches": a
     * cached failure would be served for the lifetime of the cache entry.
     *
     * @param string $query The query
     * @param string $lang The language code
     * @return array|null Suggestions, or null if the lookup failed
     */
    public function getSuggestions($query, $lang = null);
}
