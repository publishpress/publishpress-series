<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

\defined('ABSPATH') || exit;

class WarningNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'warning';
    }
}
