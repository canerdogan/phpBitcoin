<?php

$callback = function($orderID, $inputAddress, $btc, $confirmations, $inputTxHash, $param) {
    $order = DB::select("order", ["id" => $orderID]);
    if($order["status"] == "wait_payment")
    {
        if($confirmations >= 3)
        {
            if($btc == doubleval($order["price"]))
            {
                DB::update("order", ["id" => $orderID], ["status" => "finish"]);
                return true;
            }
            else
            {
                DB::update("order", ["id" => $orderID], ["status" => "error"]);
                return true;
            }
        }

        DB::update("order", ["id" => $orderID], ["status" => "error"]);
        return true;
    }

    return false;
};

$btc = new Bitcoin("http://127.0.0.1/", "13v2BTCMZMHg5v87susgg86HFZqXERuwUd", "0987654321", $callback);

// genAddress

$orderID = DB::insert("order", ["status" => "wait_genAddress", "price" => "0.1"]);
$address = $btc->genAddress($orderID);
DB::update("order", ["id" => $orderID], ["address" => $address, "status" => "wait_payment"]);

echo "Please pay to {$address}";

// callback

echo $btc->doCallback();
