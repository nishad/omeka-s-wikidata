<?php
namespace Wikidata\Controller;

use Omeka\DataType\Manager as DataTypeManager;
use Wikidata\DataType\DataTypeInterface;
use Wikidata\Suggester\SuggesterInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Crypt\Hash;
use Laminas\Cache\StorageFactory;

class IndexController extends AbstractActionController
{
    protected $dataTypes;

    public function __construct(DataTypeManager $dataTypes)
    {
        $this->dataTypes = $dataTypes;
    }

    /**
     * Generic proxy for suggest requests.
     *
     * Responsible for accepting an AJAX request, retrieving suggestions from
     * the data type, and formatting the response according to specs.
     */
    public function proxyAction()
    {
        $response = $this->getResponse();
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $response->setStatusCode('415')
                ->setContent('The request must be a XMLHttpRequest.');
        }

        $type = $this->params()->fromQuery('type');
        if ('' === trim($type)) {
            return $response->setStatusCode('400')
                ->setContent(sprintf('The request must include a data type.', $type));
        }

        try {
            $dataType = $this->dataTypes->get($type);
        } catch (ServiceNotFoundException $e) {
            return $response->setStatusCode('400')
                ->setContent(sprintf('The "%s" data type not found.', $type));
        }
        if (!$dataType instanceof DataTypeInterface) {
            return $response->setStatusCode('500')
                ->setContent(sprintf('The "%s" data type does not implement Wikidata\DataType\DataTypeInterface.', $type));
        }

        $suggester = $dataType->getSuggester();
        if (!$suggester instanceof SuggesterInterface) {
            return $response->setStatusCode('500')
                ->setContent(sprintf('The "%s" suggester does not implement Wikidata\Suggester\SuggesterInterface.', $type));
        }

        $cacheKey = Hash::compute('md5', $this->params()->fromQuery('query').$this->params()->fromQuery('lang').$this->params()->fromQuery('type'));

        $cache   = StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array('ttl' => 3600)
            ),
            'plugins' => array(
                // Don't throw exceptions on cache errors
                'exception_handler' => array(
                    'throw_exceptions' => false
                ),
                // We store database rows on filesystem so we need to serialize them
            'Serializer'
            )
        ));

        $suggestions = $cache->getItem($cacheKey, $success);
        $cashed = true;
        if (!$success) {
            $suggestions = $suggester->getSuggestions(
                $this->params()->fromQuery('query'),
                $this->params()->fromQuery('lang') ?: null
            );
            $cashed = false;
            $cache->setItem($cacheKey, $suggestions);
        }

        if (!is_array($suggestions)) {
            return $response->setStatusCode('500')
                ->setContent(sprintf('The "%s" data type must return an array; %s given.', $type, gettype($suggestions)));
        }

        // Set the response format defined by Ajax Autocomplete.
        // @see https://github.com/devbridge/jQuery-Autocomplete#response-format
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $suggestionOutput = json_encode([
            'cached' => $cashed,
            'key' => $cacheKey,
            'suggestions' => $suggestions]);
        return $response->setContent($suggestionOutput);
    }
}
