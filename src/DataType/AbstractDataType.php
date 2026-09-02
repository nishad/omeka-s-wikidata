<?php
namespace Wikidata\DataType;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\DataType\AbstractDataType as BaseAbstractDataType;
use Omeka\Entity\Value;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Renderer\PhpRenderer;

abstract class AbstractDataType extends BaseAbstractDataType implements DataTypeInterface
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @param ServiceManager $services
     */
    public function __construct(ServiceManager $services)
    {
        $this->services = $services;
    }

    public function getOptgroupLabel()
    {
        return 'Wikidata'; // @translate
    }

    public function form(PhpRenderer $view)
    {
        $labelInput = new Hidden('wikidata-label');
        $labelInput->setAttributes([
            'data-value-key' => 'o:label',
        ]);

        $idInput = new Hidden('wikidata-id');
        $idInput->setAttributes([
            'data-value-key' => '@id',
        ]);

        $valueInput = new Hidden('wikidata-value');
        $valueInput->setAttributes([
            'data-value-key' => '@value',
        ]);

        $languageInput = new Text('wikidata-language');
        $languageInput->setAttributes([
            'data-value-key' => '@language',
            'class' => 'value-language',
            // Omeka renders a <datalist id="value-languages"> of the languages
            // already used in the installation; core's own language inputs
            // reference it and so should this one.
            'list' => 'value-languages',
        ]);

        $rdfLabel = $this->getLabel();

        return $view->partial('common/data-type/wikidata-suggested', [
            'labelInput' => $labelInput,
            'idInput' => $idInput,
            'valueInput' => $valueInput,
            'languageInput' => $languageInput,
            'rdfLabel' => $rdfLabel,
        ]);
    }

    public function isValid(array $valueObject)
    {
        if (isset($valueObject['@id'])
            && is_string($valueObject['@id'])
            && '' !== trim($valueObject['@id'])
        ) {
            return true;
        }
        if (isset($valueObject['@value'])
            && is_string($valueObject['@value'])
            && '' !== trim($valueObject['@value'])
        ) {
            return true;
        }
        return false;
    }

    public function hydrate(array $valueObject, Value $value, AbstractEntityAdapter $adapter)
    {
        $uriStr = null;
        $valueStr = null;
        $langStr = null;

        if (isset($valueObject['@id'])) {
            $uriStr = $valueObject['@id'];
            if (isset($valueObject['o:label'])) {
                $valueStr = $valueObject['o:label'];
            }
        } elseif (isset($valueObject['@value'])) {
            $valueStr = $valueObject['@value'];
        }
        // Omeka's own JSON-LD uses "@language" for literal-shaped values and
        // "o:lang" for URI-shaped ones. Accept both, or the language is dropped
        // whenever a value arrives from the API, an import, or a re-import of
        // Omeka's own export rather than from this module's widget.
        if (isset($valueObject['@language'])) {
            $langStr = $valueObject['@language'];
        } elseif (isset($valueObject['o:lang'])) {
            $langStr = $valueObject['o:lang'];
        }

        $value->setUri($uriStr);
        $value->setValue($valueStr);
        $value->setLang($langStr);
        $value->setValueResource(null);
    }

    public function render(PhpRenderer $view, ValueRepresentation $value)
    {
        if ($value->uri()) {
            if ('' !== trim((string) $value->value())) {
                return $view->hyperlink($value->value(), $value->uri(), ['class' => 'uri-value-link']);
            }
            return $view->hyperlink($value->uri(), $value->uri(), ['class' => 'uri-value-link']);
        }
        return $value->value();
    }

    public function getJsonLd(ValueRepresentation $value)
    {
        $jsonLd = [];
        if ($value->uri()) {
            $jsonLd['@id'] = $value->uri();
            if ('' !== trim((string) $value->value())) {
                $jsonLd['o:label'] = $value->value();
            }
        } else {
            $jsonLd['@value'] = $value->value();
        }
        // Always "@language", for either shape. The resource form seeds its
        // inputs from this array by their data-value-key, so the key used here
        // has to be the one the form partial declares, or the language is
        // dropped from the field every time an existing value is edited.
        // hydrate() additionally accepts "o:lang" for data arriving from
        // Omeka's own URI-shaped exports.
        if ($value->lang()) {
            $jsonLd['@language'] = $value->lang();
        }
        return $jsonLd;
    }
}
