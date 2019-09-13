<?php
namespace App\Entity;

class Token
{
    protected $debug;
    
    protected $authKey;

    protected $shopPath;

    public function setDebug(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function isDebug() : ?bool
    {
        return $this->debug;
    }

    public function setAuthKey(string $authKey)
    {
        $this->authKey = $authKey;
    }

    public function getAuthKey() : ?string
    {
        return $this->authKey;
    }

    public function setShopPath(string $shopPath)
    {
    	$this->shopPath = $shopPath;
    }

    public function getShopPath() : ?string
    {
    	return $this->shopPath;
    }
}