<?php
namespace Wikidata\DataType\Reconciliation;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\Reconciliation\ReconciliationLanguages;

class Languages extends AbstractDataType
{
    public function getSuggester()
    {
        return new ReconciliationLanguages($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:reconciliation:languages';
    }

    public function getLabel()
    {
        return 'Languages : Languages from Wikidata'; // @translate
    }
}
