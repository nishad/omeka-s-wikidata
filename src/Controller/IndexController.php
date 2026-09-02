<?php
namespace Wikidata\Controller;

use Omeka\DataType\Manager as DataTypeManager;
use Wikidata\DataType\DataTypeInterface;
use Wikidata\Suggester\SuggesterInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class IndexController extends AbstractActionController
{
    /**
     * How long a cached suggestion set stays valid, in seconds.
     */
    const CACHE_TTL = 3600;

    /**
     * Cache entries older than this are removed when the cache is swept.
     */
    const CACHE_MAX_AGE = 86400;

    /**
     * How often, on average, a request also sweeps stale cache files. Nothing
     * else prunes the directory, so one request in this many pays for it.
     */
    const CACHE_SWEEP_ODDS = 200;

    protected $dataTypes;

    /**
     * @var string
     */
    protected $cacheDir;

    public function __construct(DataTypeManager $dataTypes, $cacheDir)
    {
        $this->dataTypes = $dataTypes;
        $this->cacheDir = $cacheDir;
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
            return $response->setStatusCode(400)
                ->setContent('The request must be a XMLHttpRequest.');
        }

        $type = (string) $this->params()->fromQuery('type');
        if ('' === trim($type)) {
            return $response->setStatusCode(400)
                ->setContent('The request must include a data type.');
        }

        try {
            $dataType = $this->dataTypes->get($type);
        } catch (ServiceNotFoundException $e) {
            return $response->setStatusCode(400)
                ->setContent(sprintf('The "%s" data type not found.', $type));
        }
        if (!$dataType instanceof DataTypeInterface) {
            return $response->setStatusCode(400)
                ->setContent(sprintf('The "%s" data type does not implement Wikidata\DataType\DataTypeInterface.', $type));
        }

        $suggester = $dataType->getSuggester();
        if (!$suggester instanceof SuggesterInterface) {
            return $response->setStatusCode(500)
                ->setContent(sprintf('The "%s" suggester does not implement Wikidata\Suggester\SuggesterInterface.', $type));
        }

        $query = (string) $this->params()->fromQuery('query');
        $lang = (string) $this->params()->fromQuery('lang');

        $cacheKey = $this->cacheKey($query, $lang, $type);
        $suggestions = $this->readCache($cacheKey);
        $cached = (null !== $suggestions);

        if (!$cached) {
            $suggestions = $suggester->getSuggestions($query, '' === $lang ? null : $lang);

            if (null === $suggestions) {
                // The lookup failed rather than matching nothing. Answer with an
                // error the client can react to, and cache nothing: a cached
                // failure would keep this query dead for the whole TTL.
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                return $response->setStatusCode(502)->setContent(json_encode([
                    'error' => 'The Wikidata service did not return a usable response.',
                    'suggestions' => [],
                ]));
            }
            if (!is_array($suggestions)) {
                return $response->setStatusCode(500)
                    ->setContent(sprintf('The "%s" data type must return an array; %s given.', $type, gettype($suggestions)));
            }

            $this->writeCache($cacheKey, $suggestions);
        }

        // Set the response format defined by Ajax Autocomplete.
        // @see https://github.com/devbridge/jQuery-Autocomplete#response-format
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        return $response->setContent(json_encode([
            'cached' => $cached,
            'suggestions' => $suggestions,
        ]));
    }

    /**
     * Build the cache key for a lookup.
     *
     * The parts are hashed separately rather than concatenated: with a plain
     * concatenation ("tokyo" + "en") and ("tokyoen" + "") produce the same key
     * and serve each other's results.
     *
     * @param string $query
     * @param string $lang
     * @param string $type
     * @return string
     */
    protected function cacheKey($query, $lang, $type)
    {
        return hash('sha256', json_encode([$query, $lang, $type]));
    }

    /**
     * Read a fresh cache entry.
     *
     * @param string $key
     * @return array|null The cached suggestions, or null if absent or unusable
     */
    protected function readCache($key)
    {
        $file = $this->cacheFile($key);
        if (!is_file($file) || (time() - (int) filemtime($file)) >= self::CACHE_TTL) {
            return null;
        }
        $contents = @file_get_contents($file);
        if (false === $contents) {
            return null;
        }
        // JSON rather than unserialize(): the cached data is plain scalar
        // arrays, and decoding it must never be able to instantiate an object.
        $suggestions = json_decode($contents, true);
        return is_array($suggestions) ? $suggestions : null;
    }

    /**
     * Store a cache entry, best effort.
     *
     * @param string $key
     * @param array $suggestions
     */
    protected function writeCache($key, array $suggestions)
    {
        if (!$this->prepareCacheDir()) {
            return;
        }
        // LOCK_EX so that concurrent writers cannot interleave and leave a torn
        // file behind, which would then be served for the rest of the TTL.
        @file_put_contents($this->cacheFile($key), json_encode($suggestions), LOCK_EX);
        $this->sweepCache();
    }

    /**
     * @param string $key
     * @return string
     */
    protected function cacheFile($key)
    {
        return $this->cacheDir . '/' . $key . '.json';
    }

    /**
     * Create the cache directory if it is usable.
     *
     * @return bool
     */
    protected function prepareCacheDir()
    {
        if (is_dir($this->cacheDir)) {
            return is_writable($this->cacheDir);
        }
        if (is_link($this->cacheDir) || file_exists($this->cacheDir)) {
            // Something that is not a directory occupies the path.
            return false;
        }
        return @mkdir($this->cacheDir, 0700, true) && is_writable($this->cacheDir);
    }

    /**
     * Occasionally drop cache entries that can no longer be served.
     */
    protected function sweepCache()
    {
        if (1 !== random_int(1, self::CACHE_SWEEP_ODDS)) {
            return;
        }
        $files = @glob($this->cacheDir . '/*.json');
        if (!$files) {
            return;
        }
        $cutoff = time() - self::CACHE_MAX_AGE;
        foreach ($files as $file) {
            if (@filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
