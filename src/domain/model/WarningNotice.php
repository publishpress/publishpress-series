<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

class WarningNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'warning';
    }
}