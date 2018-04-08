<?php
namespace OrganizeSeries\domain\services;

use OrganizeSeries\domain\model\AjaxJsonResponse;
use OrganizeSeries\domain\model\CombinedNoticeCollection;
use OrganizeSeries\domain\model\SingleNoticeCollection;

/**
 * AjaxJsonResponseManager
 * Responsible for packaging up consistent json response objects for Organize Series
 * Ajax requests.
 *
 * @package EventEspresso\domain\services
 * @author  Darren Ethier
 * @since   1.0.0
 */
class AjaxJsonResponseManager
{
    /**
     * @var NoticeManager;
     */
    private $notice_manager;


    /**
     * AjaxJsonResponseManager constructor.
     *
     * @param NoticeManager $notice_manager
     */
    public function __construct(NoticeManager $notice_manager)
    {
        $this->notice_manager = $notice_manager;
    }



    /**
     * Returns json response for given object.
     * @param AjaxJsonResponse $response
     */
    public function returnJson(AjaxJsonResponse $response)
    {
        $json_response = array(
            'notices' => $this->notice_manager->getAllNotices(),
            'data' => $response->getData(),
            'content' => $response->getContent(),
            'nonce' => $response->getNonce(),
            'success' => $response->isSuccess()
        );
        wp_send_json($json_response, 200);
    }
}