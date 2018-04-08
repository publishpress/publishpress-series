<?php
namespace OrganizeSeries\domain\model;

/**
 * ExtensionIdentifier
 * Extensions register themselves with OrganizeSeries via this identifier.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class ExtensionIdentifier
{
    /**
     * This represents the slug for the extension.
     * @vars string
     */
    private $slug;

    /**
     * This should represent the id of the extension on OrganizeSeries.com
     * @var int
     */
    private $product_id;

    /**
     * A brief description of the extension (optional
     * @var
     */
    private $description;


    /**
     * The main file path for the plugin (the path where the main file for the plugin is located).
     * @var string
     */
    private $main_file_path;


    /**
     * The current installed version for the extension.
     * @var string
     */
    private $version;


    /**
     * The nice name for the extension.
     * @var string
     */
    private $extension_name;

    /**
     * ExtensionIdentifier constructor.
     *
     * @param string $extension_name
     * @param string $slug
     * @param int    $product_id
     * @param string $main_file_path
     * @param string $version
     * @param string $description
     */
    public function __construct(
        $extension_name,
        $slug,
        $product_id,
        $main_file_path,
        $version,
        $description = ''
    ) {
        $this->setExtensionName($extension_name);
        $this->setSlug($slug);
        $this->setProductId($product_id);
        $this->setMainFilePath($main_file_path);
        $this->setVersion($version);
        $this->setDescription($description);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->extension_name;
    }


    /**
     * @param string $extension_name
     */
    private function setExtensionName($extension_name) {
        $this->extension_name = esc_html($extension_name);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    private function setSlug($slug)
    {
        $this->slug = sanitize_key($slug);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param string $product_id
     */
    private function setProductId($product_id)
    {
        $this->product_id = (int) $product_id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * No html allowed in the description, just plain text.
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = wp_kses($description, array());
    }

    /**
     * @return string
     */
    public function getMainFilePath()
    {
        return $this->main_file_path;
    }

    /**
     * @param string $main_file_path
     */
    private function setMainFilePath($main_file_path)
    {
        $this->main_file_path = (string) $main_file_path;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    private function setVersion($version)
    {
        $this->version = $version;
    }
}