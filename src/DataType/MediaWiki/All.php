<?php
namespace Wikidata\DataType\MediaWiki;

use Wikidata\DataType\AbstractDataType;
use Wikidata\Suggester\MediaWiki\MediaWikiAll;

class All extends AbstractDataType
{
    public function getSuggester()
    {
        return new MediaWikiAll($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'Wikidata:mediawiki:all';
    }

    public function getLabel()
    {
        return 'Items : All Wikidata items'; // @translate
    }
}
