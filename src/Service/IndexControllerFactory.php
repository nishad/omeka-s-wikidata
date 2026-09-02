<?php
namespace Wikidata\Service;

use Wikidata\Controller\IndexController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $config = $services->get('Config');
        $tempDir = (isset($config['temp_dir']) && '' !== $config['temp_dir'])
            ? $config['temp_dir']
            : sys_get_temp_dir();

        // Namespace the cache per installation. Several Omeka instances can
        // share one temp directory, and an un-namespaced path would let them
        // serve each other's cached suggestions.
        $cacheDir = sprintf(
            '%s/omeka-wikidata-cache-%s',
            rtrim($tempDir, '/'),
            substr(hash('sha256', OMEKA_PATH), 0, 16)
        );

        return new IndexController($services->get('Omeka\DataTypeManager'), $cacheDir);
    }
}
