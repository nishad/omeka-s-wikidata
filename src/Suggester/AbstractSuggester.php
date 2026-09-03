<?php
namespace Wikidata\Suggester;

use Laminas\Http\Client;

abstract class AbstractSuggester implements SuggesterInterface
{
    /**
     * Identifies this module to the Wikimedia APIs.
     *
     * Wikimedia rate-limits generic library user agents and keys the limit on
     * the agent string, so requests sent as the Laminas default share one
     * bucket with every other unidentified client and are throttled quickly.
     *
     * @see https://foundation.wikimedia.org/wiki/Policy:User-Agent_policy
     */
    const USER_AGENT = 'OmekaSWikidataModule/1.6.0 (https://github.com/nishad/omeka-s-wikidata)';

    /**
     * Canonical Wikidata entity IRI prefix.
     *
     * This is the "http" form the MediaWiki API itself returns as concepturi
     * and the one used in Wikidata's RDF exports. All suggesters must mint the
     * same string so that a given entity has one URI within an installation.
     */
    const ENTITY_URI_PREFIX = 'http://www.wikidata.org/entity/';

    /**
     * Base URI of the Wikidata reconciliation service.
     */
    const RECONCILIATION_BASE = 'https://wikidata.reconci.link';

    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Normalize a language tag into the form the Wikidata APIs accept.
     *
     * Omeka language fields hold BCP 47 tags, which are conventionally written
     * with a capitalised script or region subtag ("pt-BR", "zh-Hant"). Both the
     * MediaWiki API and the reconciliation service reject that casing: the
     * former answers with a "badvalue" error, the latter silently returns an
     * empty result set. Both accept the lowercase form.
     *
     * @param string|null $lang
     * @return string
     */
    protected function normalizeLanguage($lang)
    {
        $lang = strtolower(str_replace('_', '-', trim((string) $lang)));
        return ('' === $lang) ? 'en' : $lang;
    }

    /**
     * Reduce a language tag to its primary subtag ("pt-br" becomes "pt").
     *
     * Used as a fallback: Wikidata knows some region variants (pt-br, zh-hant)
     * but not others (fr-ca, es-mx), and rejects the ones it does not know.
     *
     * @param string $lang
     * @return string
     */
    protected function primarySubtag($lang)
    {
        return strstr($lang, '-', true) ?: $lang;
    }

    /**
     * Build the canonical entity URI for a Wikidata identifier.
     *
     * @param string $id
     * @return string
     */
    protected function entityUri($id)
    {
        return self::ENTITY_URI_PREFIX . $id;
    }

    /**
     * Build the hover-preview URL for a Wikidata identifier.
     *
     * @param string $lang
     * @param string $id
     * @return string
     */
    protected function previewUrl($lang, $id)
    {
        return sprintf('%s/%s/preview?id=%s', self::RECONCILIATION_BASE, rawurlencode($lang), rawurlencode($id));
    }

    /**
     * Perform a GET request and decode the JSON response.
     *
     * Returns null when the request could not be completed or the response was
     * not usable. Callers must distinguish that from an empty result set: the
     * controller caches empty results, and caching a failure would serve it for
     * the lifetime of the cache entry.
     *
     * @param string $uri
     * @param array $params
     * @return array|null Decoded response, or null on failure
     */
    protected function fetch($uri, array $params)
    {
        try {
            $response = $this->client
                ->setOptions(['useragent' => self::USER_AGENT])
                ->setUri($uri)
                ->setParameterGet($params)
                ->send();
        } catch (\Exception $e) {
            // A DNS failure, refused connection, TLS error or read timeout must
            // not surface as a 500 from the suggest proxy.
            return null;
        }

        if (!$response->isSuccess()) {
            return null;
        }

        $results = json_decode($response->getBody(), true);
        return is_array($results) ? $results : null;
    }

    /**
     * Assemble one suggestion in the format the autocomplete client expects.
     *
     * @param string $value
     * @param string $uri
     * @param array $info Lines of disambiguating text
     * @param string $preview
     * @return array
     */
    protected function suggestion($value, $uri, array $info, $preview)
    {
        return [
            'value' => $value,
            'data' => [
                'uri' => $uri,
                'info' => implode("\n", $info),
                'preview' => $preview,
            ],
        ];
    }
}
