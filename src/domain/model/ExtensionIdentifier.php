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
     * ExtensionIdentifier constructor.
     *
     * @param string $slug
     * @param int    $product_id
     * @param string $description
     */
    public function __construct($slug, $product_id, $description = '')
    {
        $this->setSlug($slug);
        $this->setProductId($product_id);
        $this->setDescription($description);
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
}