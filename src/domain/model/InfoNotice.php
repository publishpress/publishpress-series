<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

/**
 * InfoNotice
 *
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class InfoNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'info';
    }
}
