<?php
namespace OrganizeSeries\domain\model;

use const FILTER_VALIDATE_BOOLEAN;

/**
 * AjaxJsonResponse
 * Value object for a Ajax Json Response;
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class AjaxJsonResponse
{
    /**
     * This is the identifier action for this ajax response.  This is used for `getNonce` to create
     * a nonce that gets returned in the response.
     * @var string
     */
    private $action;


    /**
     * This is whatever gets returned as the content property.  Can be html.
     * @var string
     */
    private $content;



    /**
     * Whether the response represents success or not.
     * @var
     */
    private $success;


    /**
     * An arbitrary array of data that may be specific to javascript using it.
     * @var array
     */
    private $data;

    public function __construct($action, $success, $content = '', array $data = array())
    {
        $this->setAction($action);
        $this->setSuccess($success);
        $this->setContent($content);
        $this->setData($data);
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return wp_create_nonce($this->getAction());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = (string) $content;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    private function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param bool $success
     */
    private function setSuccess($success)
    {
        $this->success = filter_var($success, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string
     */
    private function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    private function setAction($action)
    {
        $this->action = (string) $action;
    }
}