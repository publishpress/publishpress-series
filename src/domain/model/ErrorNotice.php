<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

/**
 * ErrorNotice
 *
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class ErrorNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'error';
    }
}
