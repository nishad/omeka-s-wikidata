<?php
namespace Wikidata\Suggester\Reconciliation;

use Wikidata\Suggester\AbstractSuggester;

/**
 * Shared behaviour for the Wikidata OpenRefine reconciliation suggesters.
 *
 * @see https://wikidata.reconci.link/
 * @see https://www.w3.org/community/reports/reconciliation/CG-FINAL-specs-0.2-20230410/
 */
abstract class AbstractReconciliation extends AbstractSuggester
{
    /**
     * Query the prefix-completion endpoints.
     *
     * These rank by prefix and return a fixed-size set; they have no limit
     * parameter, so none is sent.
     *
     * @param string $kind "entity" or "property"
     * @param string $query
     * @param string $language
     * @return array|null
     */
    protected function suggest($kind, $query, $language)
    {
        $uri = sprintf('%s/%s/suggest/%s', self::RECONCILIATION_BASE, rawurlencode($language), $kind);
        $results = $this->fetch($uri, ['prefix' => $query]);
        if (null === $results) {
            return null;
        }
        return (isset($results['result']) && is_array($results['result'])) ? $results['result'] : [];
    }

    /**
     * Query the reconciliation endpoint, optionally constrained to a type.
     *
     * Unlike the prefix endpoints this one honours "limit"; the service
     * itself returns 25 when none is given. Measured, asking for 50 rather
     * than 25 costs nothing in latency here (the endpoint is query-bound),
     * so the wider window is kept.
     *
     * @param string $query
     * @param string $language
     * @param string|null $type Wikidata Q-id to rank against
     * @param int $limit
     * @return array|null
     */
    protected function reconcile($query, $language, $type = null, $limit = 50)
    {
        $params = ['query' => $query, 'limit' => $limit];
        if (null !== $type) {
            $params['type'] = $type;
        }
        $uri = sprintf('%s/%s/api', self::RECONCILIATION_BASE, rawurlencode($language));
        $results = $this->fetch($uri, ['query' => json_encode($params)]);
        if (null === $results) {
            return null;
        }
        return (isset($results['result']) && is_array($results['result'])) ? $results['result'] : [];
    }

    /**
     * Convert reconciliation results into suggestions.
     *
     * @param array $results
     * @param string $language
     * @return array
     */
    protected function buildSuggestions(array $results, $language)
    {
        $suggestions = [];
        foreach ($results as $result) {
            if (!isset($result['id'])) {
                continue;
            }
            $info = [sprintf('ID: %s', $result['id'])];
            if (isset($result['name']) && '' !== $result['name']) {
                $info[] = sprintf('Title: %s', $result['name']);
            }
            if (isset($result['description']) && '' !== $result['description']) {
                $info[] = sprintf('Description: %s', $result['description']);
            }

            $value = (isset($result['name']) && '' !== trim((string) $result['name']))
                ? $result['name']
                : $result['id'];

            $suggestions[] = $this->suggestion(
                $value,
                $this->entityUri($result['id']),
                $info,
                $this->previewUrl($language, $result['id'])
            );
        }
        return $suggestions;
    }
}
