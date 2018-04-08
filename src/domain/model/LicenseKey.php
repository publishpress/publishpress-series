<?php

namespace OrganizeSeries\domain\model;

use DateTime;
use Exception;
use stdClass;

class LicenseKey
{

    /**
     * Whether the license key was successfully validated or not.
     * If not then the error property will contain a string indicating the reason why.
     * @var bool
     */
    private $success = false;

    /**
     * If $success is false, then this will have a value indicating the reason why not a success.
     * Will be one of:
     * - expired
     * - revoked
     * - missing
     * - invalid
     * - site_inactive
     * - item_name_mismatch
     * - no_activations_left
     * @var string
     */
    private $error = '';

    /**
     * The status of the license, either invalid or valid.
     * (this is "license" in the incoming stdClass object).
     * @var string
     */
    private $status = '';


    /**
     * Item name (+ will be in this for spaces).
     * @var string
     */
    private $item_name = '';


    /**
     * Whether the license key was used on a "local" url (i.e a development site).
     * @var bool
     */
    private $is_local = false;


    /**
     * How many times the license key can be activated.
     * @var int
     */
    private $license_limit = 1;


    /**
     * How many sites this is activated on.
     * @var int
     */
    private $site_count = 0;


    /**
     * The expiry date for the license (mysql format timestamp)
     * @var string
     */
    private $expires = '';


    /**
     * How many activations are left.
     * @var int
     */
    private $activations_left = 0;


    /**
     * Checksum for the file downloaded that's attached to this license.
     * @var string
     */
    private $checksum = '';



    /**
     * The payment id for the license key (on Organize Series Website)
     * @var string
     */
    private $payment_id = '';


    /**
     * The customer name holding this license.
     * @var string
     */
    private $customer_name = '';


    /**
     * The email of the customer holding this license.
     * @var string
     */
    private $customer_email = '';


    /**
     * The id of the price on OrganizeSeries.com for this license.
     * @var string
     */
    private $price_id = '';


    /**
     * The actual license key.
     * @var string
     */
    private $license_key;


    /**
     * @var ExtensionIdentifier
     */
    private $extension_identifier;

    /**
     * LicenseKey constructor.
     *
     * @param stdClass            $license     This is whatever is returned from the stored option in the db.
     * @param string              $license_key The license key associated with this data.
     * @param ExtensionIdentifier $extension_identifier  Will be used as the fallback for item names etc if there is no
     *                                                   no info available yet.
     */
	public function __construct(stdClass $license, $license_key, ExtensionIdentifier $extension_identifier)
    {
        $this->setup($license);
        $this->extension_identifier = $extension_identifier;
        $this->license_key = $license_key;
    }



    /**
     * This populates this entities properties from the incoming stdClass.
     * @param stdClass $license
     */
    private function setup(stdClass $license)
    {
        foreach ($license as $key => $value) {
            if ($key === 'license') {
                $this->status = $value;
                continue;
            }
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Whether the license key was successfully validated or not.
     * If not then the error property will contain a string indicating the reason why.
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * If $success is false, then this will have a value indicating the reason why not a success.
     * Will be one of:
     * - expired
     * - revoked
     * - missing
     * - invalid
     * - site_inactive
     * - item_name_mismatch
     * - no_activations_left
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * The status of the license, either invalid or valid.
     * (this is "license" in the incoming stdClass object).
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Item name (+ will be in this for spaces).
     * @return string
     */
    public function getItemName()
    {
        return empty($this->item_name)
            ? $this->extension_identifier->getName()
            : $this->item_name;
    }

    /**
     * Whether the license key was used on a "local" url (i.e a development site).
     * @return bool
     */
    public function isLocal()
    {
        return $this->is_local;
    }

    /**
     * How many times the license key can be activated.
     * @return int
     */
    public function getLicenseLimit()
    {
        return $this->license_limit;
    }

    /**
     * How many sites this is activated on.
     * @return int
     */
    public function getSiteCount()
    {
        return $this->site_count;
    }

    /**
     * The expiry date for the license (mysql format timestamp)
     * @return string
     */
    public function getExpires()
    {
        //because EDD returns the expires value in different types (could be unix timestamp, could be formmatted date,
        //could be the string 'lifetime'.
        try {
            $expires = ! empty($this->expires) && $this->expires !== 'lifetime'
                ? new DateTime($this->expires)
                : $this->expires;
        } catch(Exception $e) {
            //basically if its a unix timestamp we don't want to convert it (currently anyways)
            $expires = '';
        }
        $expires = $this->expires === 'lifetime'
            ? 'lifetime'
            : $expires;
        return $expires instanceof DateTime
            ? $expires->format('F j, Y')
            : '';
    }

    /**
     * How many activations are left.
     * @return int
     */
    public function getActivationsLeft()
    {
        return $this->activations_left;
    }

    /**
     * Checksum for the file downloaded that's attached to this license.
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * The payment id for the license key (on Organize Series Website)
     * @return string
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * The customer name holding this license.
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * The email of the customer holding this license.
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customer_email;
    }

    /**
     * The id of the price on OrganizeSeries.com for this license.
     * @return string
     */
    public function getPriceId()
    {
        return $this->price_id;
    }

    /**
     * The actual license key.
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->license_key;
    }


    /**
     * Returns the license values as a stdClass that is used for storage.
     */
    public function forStorage()
    {
        $license = new stdClass;
        foreach (get_class_vars(get_class($this)) as $property => $value) {
            if ($property === 'license_key' || $property === 'extension_identifier') {
                continue;
            } elseif ($property === 'status') {
                $license->license = $this->{$property};
                continue;
            }
            $license->{$property} = $this->{$property};
        }
        return $license;
    }

    /**
     * @param string $license_key
     */
    public function setLicenseKey($license_key)
    {
        $this->license_key = trim($license_key);
    }
}

