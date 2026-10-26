<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

\defined('ABSPATH') || exit;

class ErrorNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'error';
    }
}
