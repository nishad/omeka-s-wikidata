<?php
namespace Wikidata\DataType\Reconciliation;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\Reconciliation\ReconciliationAll;

class All extends AbstractDataType
{
    public function getSuggester()
    {
        return new ReconciliationAll($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:reconciliation:all';
    }

    public function getLabel()
    {
        return 'Entities : All Wikidata entities'; // @translate
    }
}
