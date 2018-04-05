<?php

namespace OrganizeSeries\domain\services\admin;

use DomainException;
use OrganizeSeries\application\Root;
use OrganizeSeries\domain\services\AjaxJsonResponseManager;
use OrganizeSeries\domain\services\NoticeManager;
use Exception;
use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\interfaces\HasHooksInterface;
use OrganizeSeries\domain\model\AjaxJsonResponse;
use OrganizeSeries\domain\model\ErrorNotice;
use OrganizeSeries\domain\model\ExtensionIdentifier;
use OrganizeSeries\domain\model\LicenseKeyAjaxRequest;
use OrganizeSeries\domain\model\LicenseKeyAjaxResponse;
use OrganizeSeries\domain\model\RegisteredExtensions;
use OrganizeSeries\domain\model\LicenseKeyRepository;
use OrganizeSeries\domain\model\SuccessNotice;
use OrganizeSeries\domain\services\AssetRegistry;


/**
 * LicenseKeyFormManager
 * This takes care of generating and processing license key forms.
 *
 * @package OrganizeSeries\domain\services\admin
 * @author  Darren Ethier
 * @since   1.0.0
 */
class LicenseKeyFormManager implements HasHooksInterface
{

    /**
     * @var LicenseKeyRepository
     */
    private $license_key_repository;


    /**
     * @var RegisteredExtensions
     */
    private $registered_extensions;


    /**
     * @var AssetRegistry
     */
    private $asset_registry;


    /**
     * @var NoticeManager
     */
    private $notice_manager;


    /**
     * @var AjaxJsonResponseManager
     */
    private $response_manager;


    /**
     * @var LicenseKeyForm[]  indexed by extension slug.
     */
    private $license_key_forms = array();


    /**
     * @var IncomingRequest
     */
    private $request;


    /**
     * LicenseKeyFormManager constructor.
     *
     * @param LicenseKeyRepository    $license_key_repository
     * @param RegisteredExtensions    $registered_extensions
     * @param AssetRegistry           $asset_registry
     * @param AjaxJsonResponseManager $response_manager
     * @param NoticeManager           $notice_manager
     */
    public function __construct(
        LicenseKeyRepository $license_key_repository,
        RegisteredExtensions $registered_extensions,
        AssetRegistry $asset_registry,
        AjaxJsonResponseManager $response_manager,
        NoticeManager $notice_manager
    ) {
        $this->license_key_repository = $license_key_repository;
        $this->registered_extensions = $registered_extensions;
        $this->asset_registry = $asset_registry;
        $this->notice_manager = $notice_manager;
        $this->response_manager = $response_manager;
    }


    /**
     * This will be called by the Router and provides an incoming request object for usage in the class.
     * @param IncomingRequest $incoming_request
     */
    public function setHooks(IncomingRequest $incoming_request)
    {
        $this->request = $incoming_request;
        $this->loadAssets();
        $this->registerAjaxCallbacks();
        $this->registerFiltersAndActions();
    }


    /**
     * Used to setup all the assets on AssetsRegistry and register data..
     *
     */
    private function loadAssets()
    {
        $this->asset_registry->pushI18n(
            array(
                'deactivateButtonText' => esc_html__('Deactivate License', 'organize-series'),
                'activateButtonText' => esc_html__('Activate License', 'organize-series'),
            )
        );
        $this->asset_registry->registerOnDemandCallback(function(){
           wp_enqueue_script(
               'os-admin-settings',
               $this->asset_registry->getAssetJs(AssetRegistry::ASSET_NAMESPACE, 'admin-settings'),
               array('osjs-core', 'jquery'),
               null,
               true
           );
        });
    }


    /**
     * Used to register any ajax callbacks for the forms.
     */
    private function registerAjaxCallbacks()
    {
        add_action('wp_ajax_os_license_key_activation', array($this, 'activateLicenseKey'));
        add_action('wp_ajax_os_license_key_deactivation', array($this, 'deactivateLicenseKey'));
    }


    /**
     * Hook into any filters/actions for the forms.
     */
    private function registerFiltersAndActions()
    {
        add_action('AHOS__extension_license_key_fields', array($this, 'printLicenseKeyForms'));
    }


    /**
     * Retrieves (and instantiates if necessary) all LicenseKeyForm objects for the registered extensions.
     * @return LicenseKeyForm[]
     * @throws InvalidEntityException
     */
    private function getLicenseKeyForms()
    {
        if (empty($this->license_key_forms)) {
            /** @var ExtensionIdentifier $extension */
            foreach ($this->registered_extensions as $extension) {
                $slug = $extension->getSlug();
                $this->license_key_forms[$slug] = new LicenseKeyForm(
                    $this->license_key_repository->getLicenseKeyByExtension($extension),
                    $slug
                );
            }
        }
        return $this->license_key_forms;
    }


    /**
     * Returns the LicenseKeyForm object for the given extension slug.
     *
     * @param string $extension_slug
     * @return null|LicenseKeyForm
     * @throws InvalidEntityException
     */
    private function getLicenseKeyFormForExtension($extension_slug)
    {
        $license_key_forms = $this->getLicenseKeyForms();
        return isset($license_key_forms[$extension_slug])
            ? $license_key_forms[$extension_slug]
            : null;
    }


    /**
     * Callback for `OSA_extension_license_key_fields` action that outputs the license key forms.
     *
     * @throws InvalidEntityException
     * @throws DomainException
     */
    public function printLicenseKeyForms()
    {
        foreach ($this->getLicenseKeyForms() as $license_key_form) {
            $license_key_form->printForm();
        }
    }


    public function activateLicenseKey()
    {
        $this->doLicenseKeyRequest();
    }


    public function deactivateLicenseKey()
    {
        $this->doLicenseKeyRequest(false);
    }


    /**
     * @param bool $activation
     * @throws InvalidEntityException
     */
    private function doLicenseKeyRequest($activation = true)
    {
        $state_change = $activation
            ? LicenseKeyRepository::ACTION_LICENSE_KEY_ACTIVATION
            : LicenseKeyRepository::ACTION_LICENSE_KEY_DEACTIVATION;
        try {
            $request              = new LicenseKeyAjaxRequest($this->request);
            $extension_identifier = $this->registered_extensions->getExtensionBySlug($request->getExtension());
            $this->license_key_repository->remoteLicenseKeyVerification(
                $extension_identifier,
                $request->getLicenseKey(),
                $state_change
            );
            $license_key = $this->license_key_repository->getLicenseKeyByExtension($extension_identifier);
            $this->notice_manager->addSingleNotice(
                new SuccessNotice(
                    $activation
                        ? esc_html__('License Key successfully activated.', 'organize-series')
                        : esc_html__('License Key successfully deactivated.', 'organize-series')
                )
            );
            $this->response_manager->returnJson(
                new LicenseKeyAjaxResponse(
                    $request->getNonceAction(),
                    $license_key
                )
            );
        } catch (Exception $exception) {
            //do something for all other exceptions.
            $this->notice_manager->addSingleNotice(
                new ErrorNotice(
                    $exception->getMessage()
                )
            );
            $this->response_manager->returnJson(
                new AjaxJsonResponse(
                    $request->getNonceAction(),
                    false
                )
            );
        }
    }
}