<?php

namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\exceptions\EntityNotFoundException;
use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\interfaces\AbstractCollection;

/**
 * RegisteredExtensions
 * Simple collection used to hold all registered extensions for Organize Series.
 * Collection holds instances of ExtensionIdentifier
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class RegisteredExtensions extends AbstractCollection {

    public function __construct()
    {
        parent::__construct(
            new ClassOrInterfaceFullyQualifiedName(
                ExtensionIdentifier::class
            )
        );
    }


    /**
     * Retrieves any set ExtensionIdentifier for the given slug.
     *
     * @param $extension_slug
     * @return ExtensionIdentifier
     * @throws EntityNotFoundException
     */
    public function getExtensionBySlug($extension_slug)
    {
        $this->rewind();
        while ($this->valid()) {
            /** @var ExtensionIdentifier $extension_identifier */
            $extension_identifier = $this->current();
            if ($extension_identifier->getSlug() === $extension_slug) {
                $this->rewind();
                return $extension_identifier;
            }
            $this->next();
        }
        throw new EntityNotFoundException(ExtensionIdentifier::class);
    }
}