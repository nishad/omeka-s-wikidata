<?php
namespace Wikidata;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    /**
     * Data type names as they were written into value.type before 1.5.0.
     *
     * Until 1.5.0 each data type's getName() returned a capitalised name while
     * the service was registered in lowercase. Omeka stores getName() in
     * value.type and resolves it case-sensitively, so those rows could never be
     * resolved again and silently fell back to "uri" or "literal".
     */
    const LEGACY_DATA_TYPES = [
        'Wikidata:mediawiki:all' => 'wikidata:mediawiki:all',
        'Wikidata:reconciliation:all' => 'wikidata:reconciliation:all',
        'Wikidata:reconciliation:persons' => 'wikidata:reconciliation:persons',
        'Wikidata:reconciliation:properties' => 'wikidata:reconciliation:properties',
        'Wikidata:reconciliation:locations' => 'wikidata:reconciliation:locations',
        'Wikidata:reconciliation:languages' => 'wikidata:reconciliation:languages',
    ];

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'Wikidata\Controller\Index', 'proxy');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        if (version_compare($oldVersion, '1.5.0', '<')) {
            $this->repairDataTypeNames($serviceLocator);
        }
    }

    /**
     * Restore values written under the pre-1.5.0 capitalised data type names.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    protected function repairDataTypeNames(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        foreach (self::LEGACY_DATA_TYPES as $legacy => $current) {
            $connection->executeStatement(
                'UPDATE value SET type = :current WHERE type = :legacy',
                ['current' => $current, 'legacy' => $legacy]
            );
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $controllers = [
            'Omeka\Controller\Admin\Item',
            'Omeka\Controller\Admin\ItemSet',
            'Omeka\Controller\Admin\Media',
        ];
        foreach ($controllers as $controller) {
            foreach (['view.add.after', 'view.edit.after'] as $event) {
                $sharedEventManager->attach($controller, $event, [$this, 'prepareResourceForm']);
            }
        }
    }

    /**
     * Prepare resource forms for value suggest.
     *
     * @see https://github.com/devbridge/jQuery-Autocomplete
     * @param Event $event
     */
    public function prepareResourceForm(Event $event)
    {
        $view = $event->getTarget();
        $assetUrl = $view->plugin('assetUrl');
        $view->headLink()->appendStylesheet($assetUrl('css/wikidata.css', 'Wikidata'));
        $view->headLink()->appendStylesheet($assetUrl('css/tipped.css', 'Wikidata'));
        $view->headScript()->appendFile($assetUrl('js/jQuery-Autocomplete/1.4.11/jquery.autocomplete.min.js', 'Wikidata'));
        $view->headScript()->appendFile($assetUrl('js/tipped/tipped.min.js', 'Wikidata'));
        $view->headScript()->appendFile($assetUrl('js/wikidata.js', 'Wikidata'));
        $view->headScript()->appendScript(sprintf(
            'var wikidataProxyUrl = "%s";',
            $view->escapeJs($view->url('admin/wikidata/proxy'))
        ));
    }
}
