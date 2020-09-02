<?php
namespace Wikidata\DataType\Reconciliation;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\Reconciliation\ReconciliationLocations;

class Locations extends AbstractDataType
{
    public function getSuggester()
    {
        return new ReconciliationLocations($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:reconciliation:locations';
    }

    public function getLabel()
    {
        return 'Locations : Locations from Wikidata'; // @translate
    }
}
