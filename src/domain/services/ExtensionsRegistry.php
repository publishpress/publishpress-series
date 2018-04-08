<?php
namespace OrganizeSeries\domain\services;

use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\application\Root;
use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\interfaces\HasHooksInterface;
use OrganizeSeries\domain\model\ExtensionIdentifier;
use OrganizeSeries\domain\model\LicenseKeyRepository;
use OrganizeSeries\domain\model\RegisteredExtensions;
use OrganizeSeries\libraries\edd\PluginUpdater;

/**
 * ExtensionsRegistry
 * Addons should include this in their bootstrap process to register themselves.
 * This provides an interface for Organize Series Extensions to register with core as well as
 * automatically doing update checks using the PluginUpdater class.
 *
 * @package OrganizeSeries\domain\services
 * @author  Darren Ethier
 * @since   1.0.0
 */
class ExtensionsRegistry implements HasHooksInterface
{
    /**
     * @var RegisteredExtensions
     */
    private $registered_extensions;


    /**
     * @var LicenseKeyRepository
     */
    private $license_key_repository;


    /**
     * ExtensionsRegistry constructor.
     *
     * @param RegisteredExtensions $registered_extensions
     */
    public function __construct(RegisteredExtensions $registered_extensions, LicenseKeyRepository $license_keys)
    {
        $this->registered_extensions = $registered_extensions;
        $this->license_key_repository = $license_keys;
    }


    /**
     * Extensions should interface with this method to register themselves with Organize Series core.
     * @param ExtensionIdentifier $extension_identifier
     * @throws InvalidEntityException
     */
    public function registerExtension(ExtensionIdentifier $extension_identifier)
    {
        $this->registered_extensions->add($extension_identifier);
    }


    /**
     * Set hooks
     *
     * @param IncomingRequest $request
     */
    public function setHooks(IncomingRequest $request)
    {
        add_action('admin_init', array($this, 'loadPluginUpdaterForExtensions'));
    }


    /**
     * Loops through all registered extensions and instantiates a PluginUpdater object for each one.
     * @throws InvalidEntityException
     */
    public function loadPluginUpdaterForExtensions()
    {
        /** @var ExtensionIdentifier $extension */
        foreach ($this->registered_extensions as $extension) {
            $this->instantiatePluginUpdaterForExtension($extension);
        }
    }


    /**
     * Instantiates an instance of PluginUpdater object form the given extension.
     * @param ExtensionIdentifier $extension
     * @throws InvalidEntityException
     */
    private function instantiatePluginUpdaterForExtension(ExtensionIdentifier $extension)
    {
        new PluginUpdater(
            Root::coreMeta()->licensingApiUri(),
            $extension->getMainFilePath(),
            array(
                'version' => $extension->getVersion(),
                'license' => $this->getLicenseKeyForExtension($extension),
                'item_id' => $extension->getProductId(),
                'author' => 'Darren Ethier',
                'url' => home_url()
            )
        );
    }


    /**
     * @param ExtensionIdentifier $extension
     * @return string
     * @throws InvalidEntityException
     */
    private function getLicenseKeyForExtension(ExtensionIdentifier $extension) {
        $license_key = $this->license_key_repository->getLicenseKeyByExtension($extension->getSlug());
        return $license_key->getLicenseKey();
    }
}