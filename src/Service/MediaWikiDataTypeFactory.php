<?php
namespace Wikidata\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class MediaWikiDataTypeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $name = ucfirst(strtolower(substr($requestedName, strrpos($requestedName, ':') + 1)));
        $dataType = sprintf('Wikidata\DataType\\MediaWiki\\%s', $name);
        if (!class_exists($dataType)) {
            throw new ServiceNotFoundException(sprintf('The "%s" data type is not available.', $requestedName));
        }
        return new $dataType($services);
    }
}
