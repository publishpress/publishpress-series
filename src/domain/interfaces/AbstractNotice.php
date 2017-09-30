<?php
namespace OrganizeSeries\domain\interfaces;

use const FILTER_VALIDATE_BOOLEAN;

/**
 * AbstractNotice
 * Abstract class for notices (child classes might be success, error, attention etc).
 *
 * @package OrganizeSeries\domain\interfaces
 * @author  Darren Ethier
 * @since   1.0.0
 */
abstract class AbstractNotice
{

    private $message;
    private $is_dismissible;


    public function __construct($message, $is_dismissible = false)
    {
        $this->message = (string) $message;
        $this->is_dismissible = filter_var($is_dismissible, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * This just returns the original message used on notice creation.
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }


    /**
     * Whether or not the notice is dismissible.
     * @return bool
     */
    private function is_dismissible()
    {
        return $this->is_dismissible;
    }



    /**
     * This returns message wrapped in a wp admin notice container.
     * @return string
     */
    public function getNotice()
    {
        $dismissible_class = $this->is_dismissible()
            ? ' is-dismissible'
            : '';
        return
        '<div class="notice notice-' . $this->getNoticeType() . $dismissible_class . '">'
            . '<p>' . $this->getMessage() . '</p>'
        . '</div>';
    }


    /**
     * This should return the notice type.
     * @return string
     */
    abstract protected function getNoticeType();
}