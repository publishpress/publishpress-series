<?php

namespace OrganizeSeries\domain\services;

use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\interfaces\AbstractNotice;
use OrganizeSeries\domain\model\CombinedNoticeCollection;
use OrganizeSeries\domain\model\ErrorNotice;
use OrganizeSeries\domain\model\InfoNotice;
use OrganizeSeries\domain\model\SingleNoticeCollection;
use OrganizeSeries\domain\model\SuccessNotice;
use OrganizeSeries\domain\model\WarningNotice;

/**
 * NoticeManager
 * Interface for all notice management/retrieval
 *
 * @package OrganizeSeries\domain\services
 * @author  Darren Ethier
 * @since   1.0.0
 */
class NoticeManager
{
    /**
     * @var CombinedNoticeCollection
     */
    private $combined_notices;

    /**
     * @var SingleNoticeCollection
     */
    private $single_notices;


    public function __construct(CombinedNoticeCollection $combined_notices, SingleNoticeCollection $single_notices)
    {
        $this->combined_notices = $combined_notices;
        $this->single_notices = $single_notices;
    }


    /**
     * Returns all notices in a single string
     * @return string
     */
    public function getAllNotices() {
        return $this->getCombinedNotices() . $this->getSingleNotices();
    }


    /**
     * Interface for registering a single notice.
     *
     * @param AbstractNotice $notice
     * @throws InvalidEntityException
     */
    public function addSingleNotice(AbstractNotice $notice){
        $this->single_notices->add($notice);
    }


    /**
     * Interface for registering a combined notice.
     *
     * @param AbstractNotice $notice
     * @throws InvalidEntityException
     */
    public function addCombinedNotice(AbstractNotice $notice) {
        $this->combined_notices->add($notice);
    }


    /**
     * Returns all combined notices grouped by notice type as a single string.
     * @return string
     */
    public function getCombinedNotices() {
        $all_notices = array(
            'error' => array(),
            'success' => array(),
            'warning' => array(),
            'info' => array(),
        );
        foreach ($this->combined_notices as $notice) {
            switch (true) {
                case $notice instanceof SuccessNotice:
                    $all_notices['success'][] = '<p>' . $notice->getMessage() . '</p>';
                    break;
                case $notice instanceof ErrorNotice:
                    $all_notices['error'][] = '<p>' . $notice->getMessage() . '</p>';
                    break;
                case $notice instanceof WarningNotice:
                    $all_notices['warning'][] = '<p>' . $notice->getMessage() . '</p>';
                    break;
                case $notice instanceof InfoNotice:
                    $all_notices['info'][] = '<p>' . $notice->getMessage() . '</p>';
                    break;
            }
        }
        $combined_notices = '';
        foreach ($all_notices as $notice_type => $notices) {
            if ($notices) {
                $combined_notices = '<div class="notices notice-' . $notice_type . '">';
                foreach ($notices as $notice) {
                    $combined_notices .= $notice;
                }
                $combined_notices = '</div>';
            }
        }
        return $combined_notices;
    }


    /**
     * Returns all single notices as a single string
     * @return string
     */
    public function getSingleNotices() {
        $single_notices = '';
        /** @var AbstractNotice $notice */
        foreach ($this->single_notices as $notice) {
            $single_notices .= $notice->getNotice();
        }
        return $single_notices;
    }
}