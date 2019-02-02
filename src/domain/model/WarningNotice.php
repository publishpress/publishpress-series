<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

/**
 * WarningNotice
 *
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class WarningNotice extends AbstractNotice
{

    /**
     * @return string
     */
    protected function getNoticeType()
    {
        return 'warning';
    }
}
