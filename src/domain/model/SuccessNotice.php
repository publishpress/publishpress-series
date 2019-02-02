<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

/**
 * SuccessNotice
 *
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class SuccessNotice extends AbstractNotice
{

    /**
     * @return string
     */
    protected function getNoticeType()
    {
        return 'success';
    }
}
