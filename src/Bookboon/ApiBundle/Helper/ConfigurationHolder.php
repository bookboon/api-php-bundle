<?php

namespace Bookboon\ApiBundle\Helper;


class ConfigurationHolder
{
    private $_config;

    /**
     * ConfigurationHolder constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_config['id'];
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->_config['secret'];
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->_config['languages'];
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->_config['scopes'];
    }

    /**
     * @return string
     */
    public function getBranding()
    {
        return isset($this->_config['branding']) ? $this->_config['branding'] : null;
    }

    /**
     * @return string
     */
    public function getRotation()
    {
        return isset($this->_config['rotation']) ? $this->_config['rotation'] : null;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return isset($this->_config['currency']) ? $this->_config['currency'] : null;
    }

    /**
     * @return string
     */
    public function getImpersonatorId()
    {
        return isset($this->_config['impersonator_id']) ? $this->_config['impersonator_id'] : null;
    }


    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return isset($this->_config['redirect']) ? $this->_config['redirect'] : null;
    }

    /**
     * @return int
     */
    public function getPremiumLevel()
    {
        return isset($this->_config['premiumlevel']) ? $this->_config['premiumlevel'] : null;
    }
}