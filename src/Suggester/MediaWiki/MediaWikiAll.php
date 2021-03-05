<?php
namespace Wikidata\Suggester\MediaWiki;

use Wikidata\Suggester\SuggesterInterface;
use Laminas\Http\Client;

class MediaWikiAll implements SuggesterInterface
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve suggestions from the Wikidata MediaWiki API.
     *
     * @see https://www.wikidata.org/w/api.php
     * @see https://www.wikidata.org/w/api.php?action=help&modules=wbsearchentities
     * @param string $query
     * @param string $lang
     * @return array
     */
    public function getSuggestions($query, $lang = "en")
    {
        $params = [
            'action' => 'wbsearchentities',
            'search' => $query, 
            'limit' => 50, 
            'format' => 'json'];
        if ($lang) {
            // Same as in Geonames code, using an ISO-639 2-letter language code. // Remove the first underscore and anything after it ("zh_CN" becomes "zh").
            $params['language'] = strstr($lang, '_', true) ?: $lang;
        } else {
            // Fall back to English
            $params['language'] = 'en';
        };
        $response = $this->client
        ->setUri('https://www.wikidata.org/w/api.php')
        ->setParameterGet($params)
        ->send();
        if (!$response->isSuccess()) {
            return [];
        }

        // Parse the JSON response.
        $suggestions = [];
        $results = json_decode($response->getBody(), true);
        // return $results;
        // die();
        foreach ($results['search'] as $result) {
            $info = [];
            if (isset($result['id']) && $result['id']) {
                $info[] = sprintf('ID: %s', $result['id']);
            }
            if (isset($result['label']) && $result['label']) {
                $info[] = sprintf('Label: %s', $result['label']);
            }
            if (isset($result['title']) && $result['title']) {
                $info[] = sprintf('Title: %s', $result['title']);
            }
            if (isset($result['description']) && $result['description']) {
                $info[] = sprintf('Description: %s', $result['description']);
            }
            if (isset($result['concepturi']) && $result['concepturi']) {
                $info[] = sprintf('URI: %s',$result['concepturi']);
            }
            $suggestions[] = [
                'value' => $result['label'],
                'data' => [
                    'uri' => $result['concepturi'],
                    'info' => implode("\n", $info),
                    'preview' => sprintf('https://wikidata.reconci.link/%s/preview?id=%s', $params['language'], $result['id']),
                ],
            ];
        }

        return $suggestions;
    }
}
