<?php

namespace OrganizeSeries\domain\model;

use OrganizeSeries\application\Root;
use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\exceptions\LicenseKeyRequestError;
use stdClass;

/**
 * LicenseKeyRepository
 * Used to interact with LicenseKey entities/retrieving and persisting to the wp_option.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class LicenseKeyRepository {

    /**
     * Prefix for the option saving the key.  OS extensions should provide the extension name.
     */
    const OPTION_PREFIX_LICENSE_KEY = 'os_license_key_';


    /**
     * Prefix for the option saving the license key data. OS extensions should provide the extension name.
     */
    const OPTION_PREFIX_LICENSE_KEY_DATA = 'os_license_key_data_';


    /**
     * Action used to indicate activating license.
     */
    const ACTION_LICENSE_KEY_ACTIVATION = 'activate_license';


    /**
     * Action used to indicate deactivating license.
     */
    const ACTION_LICENSE_KEY_DEACTIVATION = 'deactivate_license';



	/**
	 * @var LicenseKeyCollection
	 */
	private $collection;
	
	
	/**
	 * @var LicenseKeyFactory
	 */
	private $factory;
	
	
	public function __construct(
	    LicenseKeyCollection $collection,
        LicenseKeyFactory $factory
    ) {
		$this->collection = $collection;
		$this->factory = $factory;
	}


    /**
     * Retrieve a License Key object from the wp_option for the given extension.
     *
     * @param ExtensionIdentifier $extension_identifier
     * @return LicenseKey
     * @throws InvalidEntityException
     */
	public function getLicenseKeyByExtension(ExtensionIdentifier $extension_identifier)
    {
        if ($this->collection->has($extension_identifier->getSlug())) {
            return $this->collection->get($extension_identifier->getSlug());
        }

        return $this->getFromOption($extension_identifier);
    }


    /**
     * Persists the license key and license data to the wp_options table.
     *
     * @param ExtensionIdentifier $extension_identifier
     * @throws InvalidEntityException
     */
    public function updateLicenseKeyByExtension(ExtensionIdentifier $extension_identifier)
    {
        $license_key = $this->getLicenseKeyByExtension($extension_identifier);
        update_option(
            self::OPTION_PREFIX_LICENSE_KEY_DATA . $extension_identifier->getSlug(),
            $license_key->forStorage()
        );
        update_option(
            self::OPTION_PREFIX_LICENSE_KEY . $extension_identifier->getSlug(),
            $license_key->getLicenseKey()
        );
    }


    /**
     * Used to verify the license key data via the remote api.
     *
     * @param ExtensionIdentifier $extension
     * @param string              $license_key
     * @param string              $action
     * @throws InvalidEntityException
     * @throws LicenseKeyRequestError
     */
    public function remoteLicenseKeyVerification(ExtensionIdentifier $extension, $license_key, $action)
    {
        // data to send in our API request
        $api_params = array(
            'edd_action' => $action,
            'license'    => $license_key,
            'item_id'    => $extension->getProductId(),
			'url'        => home_url()
		);

        // Call the custom API.
        $response = wp_remote_post(
            Root::coreMeta()->licensingApiUri(),
            array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params )
        );
        // make sure the response came back okay
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) )
                ? $response->get_error_message()
                : esc_html__( 'An error occurred, please try again.', 'organize-series' );
            throw new LicenseKeyRequestError($message);
        }
        $this->replaceInCollection(
            $extension->getSlug(),
            $this->factory->create(
                json_decode( wp_remote_retrieve_body( $response ) ),
                $license_key,
                $extension
            )
        );
        $this->updateLicenseKeyByExtension($extension);
    }


    /**
     * This replaces (or adds) the given LicenseKey object to the collection
     *
     * @param string     $extension_slug
     * @param LicenseKey $license_key
     * @throws InvalidEntityException
     */
    private function replaceInCollection($extension_slug, LicenseKey $license_key) {
	    if ($this->collection->has($extension_slug)) {
	        $this->collection->detach(
	            $this->collection->get($extension_slug)
            );
        }
        $this->collection->add($license_key, $extension_slug);
    }


    /**
     * This uses the factory to create a LicenseKey object from the wp_options for the given extension_slug.
     *
     * @param ExtensionIdentifier$extension_identifier
     * @return LicenseKey
     * @throws InvalidEntityException
     */
    private function getFromOption(ExtensionIdentifier $extension_identifier)
    {
        $this->maybeInitializeOptions($extension_identifier->getSlug());
        $this->replaceInCollection(
            $extension_identifier->getSlug(),
            $this->factory->create(
                get_option(self::OPTION_PREFIX_LICENSE_KEY_DATA . $extension_identifier->getSlug()),
                get_option(self::OPTION_PREFIX_LICENSE_KEY . $extension_identifier->getSlug()),
                $extension_identifier
            )
        );
        return $this->getLicenseKeyByExtension($extension_identifier);
    }


    /**
     * Always called when first getting license key data and license key from the option to ensure
     * we aren't autolaoding these.
     *
     * @param string $extension_slug
     */
    private function maybeInitializeOptions($extension_slug)
    {
        // we just have to check one of the options because if one is initialized, they both are!
        if (false === get_option(self::OPTION_PREFIX_LICENSE_KEY . $extension_slug)) {
            add_option(self::OPTION_PREFIX_LICENSE_KEY . $extension_slug, '', '', 'no');
            add_option(self::OPTION_PREFIX_LICENSE_KEY_DATA . $extension_slug, new stdClass, '', 'no');
        }
    }
}