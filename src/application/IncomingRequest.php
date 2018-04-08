<?php
namespace OrganizeSeries\application;

use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use function wp_doing_ajax;

/**
 * IncomingRequest
 * Used to pass around data from the incoming request and interface to
 * low-level details about the request (is ajax? is rest? etc).
 *
 * @package OrganizeSeries\application
 * @author  Darren Ethier
 * @since   1.0.0
 */
class IncomingRequest
{

    const GET = 'get';
    const POST = 'post';
    const REQUEST = 'request';
    const COOKIE = 'cookie';

    /**
     * @var array incoming GET parameters.
     */
    private $get;


    /**
     * @var array incoming POST parameters.
     */
    private $post;


    /**
     * @var array  incoming COOKIE parameters.
     */
    private $cookie;


    /**
     * @var bool Whether the request is ajax or not.
     */
    private $is_ajax;


    /**
     * Merged array of POST and GET parameters with POST overriding GET.
     * @var array
     */
    private $parameters;


    /**
     * Source IP address for incoming request.
     * @var
     */
    private $ip_address;


    /**
     * IncomingRequest constructor.
     *
     * @param array $get
     * @param array $post
     * @param array $cookie
     */
    public function __construct(array $get, array $post, array $cookie)
    {
        $this->get = $get;
        $this->post = $post;
        $this->cookie = $cookie;
        $this->is_ajax = wp_doing_ajax();
        $this->parameters = array_merge($this->get, $this->post);
        $this->ip_address = $this->deriveIpAddress();
    }


    /**
     * Returns the value for an incoming request key if the given key exists.
     *
     * @param        $key
     * @param null   $default Indicate the default tha tis returned in the value doesn't exist for the key.
     * @param string $type    What HTTP array type to look for the value on. Defaults to the incoming merged array.
     * @return mixed
     */
    public function get($key, $default = null, $type = self::REQUEST)
    {
        return $this->requestParameterDrillDown(
            $key,
            $this->parametersForType($type),
            $default
        );
    }


    /**
     * Returns whether the parameter array being checked has the given key.
     * @param        $key
     * @param string $type
     * @return bool
     */
    public function hasKey($key, $type = self::REQUEST)
    {
        return $this->requestParameterDrillDown(
            $key,
            $this->parametersForType($type),
            null,
            true
        );
    }


    /**
     * Returns the source ip address for the incoming request.
     * @return string
     */
    public function ipAddress()
    {
        return $this->ip_address;
    }


    /**
     * Returns whether the request is an ajax request or not.
     * @return bool
     */
    public function doingAjax()
    {
        return $this->is_ajax;
    }


    /**
     * Return the parameter values for the given type.
     * @param string $type
     * @return array
     */
    private function parametersForType($type = self::REQUEST)
    {
        $parameters = array();
        switch ($type) {
            case self::COOKIE:
                $parameters = $this->cookie;
                break;
            case self::GET:
                $parameters = $this->get;
                break;
            case self::POST:
                $parameters = $this->post;
                break;
            case self::REQUEST:
                $parameters = $this->parameters;
                break;
        }
        return $parameters;
    }


    /**
     * Attempt to get IP address for the source of the incoming request
     * @see http://stackoverflow.com/a/2031935/1475279
     * @return string
     */
    private function deriveIpAddress()
    {
        $visitor_ip = '0.0.0.0';
        $server_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );
        foreach ($server_keys as $key) {
            if (isset($_SERVER[$key])) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if ($ip === '127.0.0.1' || filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        $visitor_ip = $ip;
                    }
                }
            }
        }
        return $visitor_ip;
    }


    /**
     * The supplied key can be a simple string to represent a "top-level" request parameter
     * or represent a key for a request parameter that is nested deeper within the request parameter array,
     * by using square brackets to surround keys for deeper array elements.
     * For example :
     * if the supplied $key was: "first[second][third]"
     * then this will attempt to drill down into the request parameter array to find a value.
     * Given the following request parameters:
     *  array(
     *      'first' => array(
     *          'second' => array(
     *              'third' => 'has a value'
     *          )
     *      )
     *  )
     * would return true
     *
     * @param string       $key     The key being requested.
     * @param array  $request_params The request parameters being searched.
     * @param null   $default       Default to use if key doesn't exist.
     * @param bool   $is_set        Defaults to false.  If true, then just checking if the key is set.  If false, then
     *                              Returning the value (or $default if key isn't set.
     * @return bool|mixed|null
     */
    private function requestParameterDrillDown(
        $key,
        array $request_params,
        $default = null,
        $is_set = false
    ) {
        $request_params = ! empty($request_params)
            ? $request_params
            : $this->parameters;
        // does incoming key represent an array like 'first[second][third]'  ?
        if (strpos($key, '[') !== false) {
            // turn it into an actual array
            $key = str_replace(']', '', $key);
            $keys = explode('[', $key);
            $key = array_shift($keys);
            // check if top level key exists
            if (isset($request_params[$key])) {
                // build a new key to pass along like: 'second[third]'
                // or just 'second' depending on depth of keys
                $key_string = array_shift($keys);
                if (! empty($keys)) {
                    $key_string .= '[' . implode('][', $keys) . ']';
                }
                return $this->requestParameterDrillDown(
                    $key_string,
                    $request_params[$key],
                    $default,
                    $is_set
                );
            }
        }
        if ($is_set) {
            return isset($request_params[$key]);
        }
        return isset($request_params[$key])
            ? $request_params[$key]
            : $default;
    }


    /**
     * Simply validates the incoming nonce against the provided nonce_key.
     *
     * @param string $nonce_action
     * @param string $nonce_key
     * @return false|int
     */
    public function validateNonce($nonce_action, $nonce_key)
    {
        return wp_verify_nonce($this->get(sanitize_key($nonce_key)), sanitize_key($nonce_action));
    }


    /**
     * Helper for returning whether the incoming request is an ajax request and has any of the given actions in the
     * request.
     *
     * @param $actions_to_check
     * @return bool
     */
    public function hasAjaxActions(array $actions_to_check)
    {
        return $this->is_ajax
            && $this->get('action', false)
            && in_array($this->get('action'), $actions_to_check, true);
    }
}