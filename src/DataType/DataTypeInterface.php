<?php
namespace Wikidata\DataType;

interface DataTypeInterface
{
    /**
     * Get the suggestor needed to retrieve suggestions.
     *
     * @return \Wikidata\Suggester\SuggesterInterface
     */
    public function getSuggester();
}
