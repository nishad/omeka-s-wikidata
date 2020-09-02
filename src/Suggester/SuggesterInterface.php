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
     * @param string $query The query
     * @param string $lang The language code
     * @return array
     */
    public function getSuggestions($query, $lang = null);
}
