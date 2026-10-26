<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

\defined('ABSPATH') || exit;

class InfoNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'info';
    }
}
