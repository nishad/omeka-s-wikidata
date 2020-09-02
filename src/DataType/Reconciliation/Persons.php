<?php
namespace Wikidata\DataType\Reconciliation;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\Reconciliation\ReconciliationPersons;

class Persons extends AbstractDataType
{
    public function getSuggester()
    {
        return new ReconciliationPersons($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:reconciliation:persons';
    }

    public function getLabel()
    {
        return 'Persons : Persons from Wikidata'; // @translate
    }
}
