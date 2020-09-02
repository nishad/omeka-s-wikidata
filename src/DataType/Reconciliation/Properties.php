<?php
namespace Wikidata\DataType\Reconciliation;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\Reconciliation\ReconciliationProperties;

class Properties extends AbstractDataType
{
    public function getSuggester()
    {
        return new ReconciliationProperties($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:reconciliation:properties';
    }

    public function getLabel()
    {
        return 'Properties : All Wikidata Properties'; // @translate
    }
}
