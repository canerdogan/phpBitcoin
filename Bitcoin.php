<?php

class Bitcoin
{
    private $callbackUrl = "";
    private $secret = "0123456789";
    private $address = "1FjhhWrzAvb9YD4tVdbE6wrSoHSwxMJoWe";
    private $callback = null;
    private $blockchainPrefix = "https://blockchain.info";

    public function __construct($callbackUrl, $address, $secret, callable $callback)
    {
        $this->callbackUrl = $callbackUrl;
        $this->callback = $callback;
        if(!$address)
            $this->address = $address;
        if(!$secret)
            $this->secret = $secret;
    }

    public function genAddress($param = [])
    {
        $param["secret"] = $this->secret;

        $param = $this->dataEncoder($param);
        $callbackUrl = urlencode("{$this->callbackUrl}?{$param}");

        $url = "{$this->blockchainPrefix}/api/receive?method=create&address={$this->address}&callback={$callbackUrl}";

        $result = json_decode(file_get_contents($url), true);
        return $result["input_address"];
    }

    public function doCallback()
    {
        $param = $_GET;
        $value = $param["value"];
        $input_address = $param["input_address"];
        $confirmations = $param["confirmations"];
        $input_transaction_hash = $param["input_transaction_hash"];

        $result = $this->callback($input_address, $value, $confirmations, $input_transaction_hash, $param);

        return $result ? "*OK*" : "";
    }

    private function dataEncoder(array $data)
    {
        $result = "";
        foreach($data as $k => &$v)
        {
            $v = urlencode($v);
            if($result)
                $result .= "&{$k}={$v}";
            else
                $result = "{$k}={$v}";
        }
        return $result;
    }
} 
