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
 * @since   2.5.9
 */
class SingleNoticeCollection extends AbstractCollection
{

    /**
     * SingleNoticeCollection constructor.
     */
    public function __construct()
    {
        parent::__construct(AbstractNotice::class);
    }
}
