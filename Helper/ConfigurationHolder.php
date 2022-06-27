<?php

namespace Bookboon\ApiBundle\Helper;


class ConfigurationHolder
{
    private array $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    public function getId(): string
    {
        return $this->_config['id'] ?? '';
    }

    public function getSecret(): string
    {
        return $this->_config['secret'] ?? '';
    }

    public function getLanguages(): array
    {
        return $this->_config['languages'] ?? [];
    }

    public function getScopes(): array
    {
        return $this->_config['scopes'] ?? [];
    }

    public function getBranding(): ?string
    {
        return $this->_config['branding'] ?? null;
    }

    public function getRotation(): ?string
    {
        return $this->_config['rotation'] ?? null;
    }

    public function getCurrency(): ?string
    {
        return $this->_config['currency'] ?? null;
    }

    public function getImpersonatorId(): ?string
    {
        return $this->_config['impersonator_id'] ?? null;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->_config['redirect'] ?? null;
    }

    public function getPremiumLevel(): ?string
    {
        return $this->_config['premium_level'] ?? null;
    }

    public function getOverrideAuthUri(): ?string
    {
        return $this->_config['override_auth_uri'] ?? null;
    }
}