<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractCollection;
use OrganizeSeries\domain\interfaces\AbstractNotice;

/**
 * CombinedNoticeCollection
 * Collection holding notices that should be combined into one notice when printed.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class CombinedNoticeCollection extends AbstractCollection
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