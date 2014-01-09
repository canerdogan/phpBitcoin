<?php

/**
 * Class Bitcoin
 */
class Bitcoin
{
    /** @var string */
    private $callbackUrl = "";
    /** @var string */
    private $secret = "0123456789";
    /** @var string */
    private $address = "1FjhhWrzAvb9YD4tVdbE6wrSoHSwxMJoWe";
    /** @var callable */
    private $callback = null;
    /** @var string */
    private $blockchainPrefix = "https://blockchain.info";

    /**
     * @param string $callbackUrl
     * @param string $address
     * @param string $secret
     * @param callable $callback
     *
     * callback($orderID, $inputAddress, $btc, $confirmations, $inputTxHash, $param)
     */
    public function __construct($callbackUrl, $address, $secret, callable $callback)
    {
        $this->callbackUrl = $callbackUrl;
        $this->callback = $callback;
        if($address)
            $this->address = $address;
        if($secret)
            $this->secret = $secret;
    }

    /**
     * @param string $orderID
     * @param array $param
     * @return string
     */
    public function genAddress($orderID, $param = [])
    {
        $param["_id"] = $orderID;
        $param["_token"] = md5("{$orderID}{$this->secret}");

        $param = $this->dataEncoder($param);
        $callbackUrl = urlencode("{$this->callbackUrl}?{$param}");

        $url = "{$this->blockchainPrefix}/api/receive?method=create&address={$this->address}&callback={$callbackUrl}";

        $result = json_decode(file_get_contents($url), true);
        return $result["input_address"];
    }

    /**
     * @return string
     */
    public function doCallback()
    {
        $param = $_GET;
        $token = $param["_token"];
        $orderID = $param["_id"];

        if(md5("{$orderID}{$this->secret}") == $token)
        {
            $btc = $param["value"] / 100000000;
            $inputAddress = $param["input_address"];
            $confirmations = $param["confirmations"];
            $inputTxHash = $param["input_transaction_hash"];

            $result = $this->callback($orderID, $inputAddress, $btc, $confirmations, $inputTxHash, $param);

            return $result ? "*OK*" : "";
        }

        return "Bad Token";
    }

    /**
     * @param array $data
     * @return string
     */
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
