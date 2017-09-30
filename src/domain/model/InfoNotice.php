<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

class InfoNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'info';
    }
}