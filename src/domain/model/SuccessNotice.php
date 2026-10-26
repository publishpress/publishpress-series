<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

\defined('ABSPATH') || exit;

class SuccessNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'success';
    }
}
