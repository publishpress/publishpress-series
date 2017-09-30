<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractCollection;
use OrganizeSeries\domain\interfaces\AbstractNotice;

/**
 * SingleNoticeCollection
 * Collection of notices that should be displayed individually.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class SingleNoticeCollection extends AbstractCollection
{
    public function __construct()
    {
        parent::__construct(
            new ClassOrInterfaceFullyQualifiedName(
                AbstractNotice::class
            )
        );
    }
}