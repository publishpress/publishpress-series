<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractNotice;

class SuccessNotice extends AbstractNotice
{
    protected function getNoticeType()
    {
        return 'success';
    }
}