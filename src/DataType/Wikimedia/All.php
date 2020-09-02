<?php
namespace Wikidata\DataType\Wikimedia;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\Wikimedia\WikimediaAll;

class All extends AbstractDataType
{
    public function getSuggester()
    {
        return new WikimediaAll($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:wikimedia:all';
    }

    public function getLabel()
    {
        return 'Items : All Wikidata items'; // @translate
    }
}
