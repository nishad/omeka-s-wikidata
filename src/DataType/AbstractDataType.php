<?php
namespace Wikidata\DataType;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\DataType\AbstractDataType as BaseAbstractDataType;
use Omeka\DataType\ConversionTargetInterface;
use Omeka\DataType\ValueAnnotatingInterface;
use Omeka\Entity\Value;
use Wikidata\Suggester\AbstractSuggester;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Renderer\PhpRenderer;

abstract class AbstractDataType extends BaseAbstractDataType implements DataTypeInterface, ConversionTargetInterface, ValueAnnotatingInterface
{
    /**
     * Wikidata entity URIs this module will adopt when converting a value.
     *
     * Covers the canonical concept URI, the https form the reconciliation
     * suggesters minted before 1.5.0, and the human-readable wiki page URL that
     * a cataloguer is most likely to have pasted by hand.
     */
    const ENTITY_URI_PATTERN = '~^https?://(?:www\\.)?wikidata\\.org/(?:entity|wiki)/([QPL]\\d+)$~i';

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
        return $view->partial('common/data-type/wikidata-suggested', $this->formVariables());
    }

    /**
     * Prepare the view to enable annotating a value with a Wikidata entity.
     *
     * Nothing to do: Module::prepareResourceForm already adds this module's
     * stylesheet and scripts to every page that renders the resource form, and
     * the annotation sidebar lives on those same pages.
     *
     * @param PhpRenderer $view
     */
    public function valueAnnotationPrepareForm(PhpRenderer $view)
    {
    }

    /**
     * Get the markup for annotating a value with a Wikidata entity.
     *
     * @param PhpRenderer $view
     * @return string
     */
    public function valueAnnotationForm(PhpRenderer $view)
    {
        return $view->partial('common/data-type/wikidata-value-annotation', $this->formVariables());
    }

    /**
     * Build the form elements shared by the value and annotation widgets.
     *
     * @return array
     */
    protected function formVariables()
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

        return [
            'labelInput' => $labelInput,
            'idInput' => $idInput,
            'valueInput' => $valueInput,
            'languageInput' => $languageInput,
            'rdfLabel' => $this->getLabel(),
        ];
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

    /**
     * Adopt a value that already carries a Wikidata entity URI.
     *
     * Used by the batch edit "Convert data type" tool. Conversion succeeds only
     * where an entity can actually be identified: marking arbitrary text as a
     * Wikidata value would assert something untrue, and returning false lets the
     * value hydrator log the value as unconvertible instead.
     *
     * This is the only way to recover values damaged before 1.5.0 that have
     * since been re-saved. The upgrade step repairs rows still carrying the old
     * capitalised type name, but a value that was saved again while degraded is
     * stored as a plain "uri" with nothing to distinguish it.
     *
     * @param Value $valueObject
     * @param string $dataTypeTarget
     * @return bool
     */
    public function convert(Value $valueObject, string $dataTypeTarget): bool
    {
        $uri = $this->wikidataEntityUri($valueObject->getUri());
        if (null !== $uri) {
            // Keep the existing value, which is the entity's label.
            $valueObject->setUri($uri);
            $valueObject->setValueResource(null);
            return true;
        }

        // A URI typed into a text field. Move it across, as core's "uri" data
        // type does, rather than leaving the value unconvertible.
        $uri = $this->wikidataEntityUri($valueObject->getValue());
        if (null !== $uri) {
            $valueObject->setUri($uri);
            $valueObject->setValue(null);
            $valueObject->setValueResource(null);
            return true;
        }

        return false;
    }

    /**
     * Normalise a Wikidata entity URI, or return null if this is not one.
     *
     * @param string|null $candidate
     * @return string|null
     */
    protected function wikidataEntityUri($candidate)
    {
        if (!is_string($candidate) || '' === trim($candidate)) {
            return null;
        }
        if (!preg_match(self::ENTITY_URI_PATTERN, trim($candidate), $matches)) {
            return null;
        }
        return AbstractSuggester::ENTITY_URI_PREFIX . strtoupper($matches[1]);
    }
}
