<?php

namespace helpers\Security;

use base\Application;

/**
 *
 *
 *      csrf 安全处理 来源ci
 *      $csrf = new Csrf();
        $csrf_token_name  = $csrf->get_csrf_token_name();
        $csrf_token = $csrf->get_csrf_hash();
        $csrf->csrf_set_cookie();//往cookie写token信息
        $csrf->csrf_verify();//验证
 *
 * Class Csrf
 * @package helpers\Security
 */
class Csrf {

    /**
     * XSS Hash
     *
     * Random Hash for protecting URLs.
     *
     * @var	string
     */
    protected $_xss_hash;

    /**
     * CSRF Hash
     *
     * Random hash for Cross Site Request Forgery protection cookie
     *
     * @var	string
     */
    protected $_csrf_hash;

    /**
     * CSRF Expire time
     *
     * Expiration time for Cross Site Request Forgery protection cookie.
     * Defaults to two hours (in seconds).
     *
     * @var	int
     */
    protected $_csrf_expire =	7200;

    /**
     * CSRF Token name
     *
     * Token name for Cross Site Request Forgery protection cookie.
     *
     * @var	string
     */
    protected $_csrf_token_name =	'wei_csrf_token';

    /**
     * CSRF Cookie name
     *
     * Cookie name for Cross Site Request Forgery protection cookie.
     *
     * @var	string
     */
    protected $_csrf_cookie_name =	'wei_csrf_token';


    public $config = array();

    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct($config="")
    {
        if(empty($config)){
            $this->config = Application::getInstance()->config['config'];
        }else{
            $this->config = $config;
        }
        // Set the CSRF hash
        $this->_csrf_set_hash();
    }

    // --------------------------------------------------------------------

    /**
     * CSRF Verify
     *
     * @return	CI_Security
     */
    public function csrf_verify()
    {
        // If it's not a POST request we will set the CSRF cookie
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
        {
            return $this->csrf_set_cookie();
        }

        // Do the tokens exist in both the _POST and _COOKIE arrays?
        if ( ! isset($_POST[$this->_csrf_token_name], $_COOKIE[$this->_csrf_cookie_name])
            OR $_POST[$this->_csrf_token_name] !== $_COOKIE[$this->_csrf_cookie_name]) // Do the tokens match?
        {
            $this->csrf_show_error();
        }
        // We kill this since we're done and we don't want to polute the _POST array
        unset($_POST[$this->_csrf_token_name]);
        $this->_csrf_set_hash();
        $this->csrf_set_cookie();
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * CSRF Set Cookie
     *
     * @codeCoverageIgnore
     * @return	CI_Security
     */
    public function csrf_set_cookie()
    {
        $expire = time() + $this->_csrf_expire;
        $secure_cookie = (bool) $this->config['config']['cookie_secure'];
        setcookie(
            $this->_csrf_cookie_name,
            $this->_csrf_hash,
            $expire,
            $this->config['config']['cookie_path'],
            $this->config['config']['cookie_domain'],
            $secure_cookie,
            $this->config['config']['cookie_httponly']
        );
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Show CSRF Error
     *
     * @return	void
     */
    public function csrf_show_error()
    {
        throw new \Exception('The action you have requested is not allowed.');
    }

    // --------------------------------------------------------------------

    /**
     * Get CSRF Hash
     *
     * @see		CI_Security::$_csrf_hash
     * @return 	string	CSRF hash
     */
    public function get_csrf_hash()
    {
        return $this->_csrf_hash;
    }

    // --------------------------------------------------------------------

    /**
     * Get CSRF Token Name
     *
     * @see		CI_Security::$_csrf_token_name
     * @return	string	CSRF token name
     */
    public function get_csrf_token_name()
    {
        return $this->_csrf_token_name;
    }

    // --------------------------------------------------------------------
    /**
     * Set CSRF Hash and Cookie
     *
     * @return	string
     */
    protected function _csrf_set_hash()
    {
        if ($this->_csrf_hash === NULL)
        {
            // If the cookie exists we will use its value.
            // We don't necessarily want to regenerate it with
            // each page load since a page could contain embedded
            // sub-pages causing this feature to fail
            if (isset($_COOKIE[$this->_csrf_cookie_name]) && is_string($_COOKIE[$this->_csrf_cookie_name])
                && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->_csrf_cookie_name]) === 1)
            {
                return $this->_csrf_hash = $_COOKIE[$this->_csrf_cookie_name];
            }
            $this->_csrf_hash =    md5(uniqid(mt_rand(), TRUE));
        }

        return $this->_csrf_hash;
    }

}
