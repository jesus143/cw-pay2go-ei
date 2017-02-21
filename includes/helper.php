<?php

function helper_spgateway_pay2go_invoice_trigger_invoice($orderId, $session, $post) {


//        print "Test";
//    exit;

//    require ABSPATH . '/wp-content/plugins/cw-pay2go-ei/includes/class-cw-pay2goe-ei-spgateway.php';

    $date = new DateTime();
    $data = new CWP2GEI_SPGATEWAY();

    $session  = $session['spgateway_args'];
    //    print "<pre>";
    //    print " session <br>";
    //    print_r($session);
    //    print "<br> post <br>";
    //    print_r($post);
    //    print "</pre>";

    $order = new WC_Order($orderId);


    $buyerUbn = '';

    if($data->taxtype == 1) {
        $TaxRatePercent = 0.05;
        $TaxType = 1;
    } else if ($data->taxtype == 1.1) {
        $TaxRatePercent = 0;
        $TaxType = 2;
    } else {
        $TaxRatePercent = 0;
        $TaxType = 2;
    }

    if(!empty($order->billing_company) and !empty($order->billing_uniform_numbers)) {
        $Category = 'B2B';
        $buyerName = $order->billing_company;
        $buyerUbn  = $order->billing_uniform_numbers;

    } else {
        $buyerName = $order->billing_first_name;
        $Category  = 'B2C';
    }


    $billingAddressArray = $order->get_address();
    $count = $session['Count'];
    $TaxRate = ($data->taxtype == 1) ? 5 : 0;
    $Amt   =  $order->get_total() - ($order->get_total() * $TaxRatePercent); //490;
    $TaxAmt = ($order->get_total() * $TaxRatePercent);
    $TotalAmt =  $order->get_total(); //500;
    $CarrierType = '';
    $CarrierNum = rawurlencode("");
    $LoveCode = '';
    $PrintFlag = "Y";
    $ItemName =   helper_spgateway_separate_order_results($count, 'Title', $session);       //"商品一|商品二";
    $ItemCount =  helper_spgateway_separate_order_results($count, 'Item Count', $session);  // "1|2";
    $ItemUnit =   helper_spgateway_separate_order_results($count, 'Item Unit', $session);  // "個|個";
    $ItemPrice =  helper_spgateway_separate_order_results($count, 'Price', $session); //"300|100";
    $ItemAmt =    helper_spgateway_separate_order_results($count, 'Item Amount', $session); //"300|200";
    $Comment = "";
    $Status = "1";
    $CreateStatusTime = '';
    $NotifyEmail =   ($data->enable === true) ? 1 : 0;
    $BuyerAddress = $billingAddressArray['address_1']; //. ', ' . $billingAddressArray['address_2'] . ', ' .  $billingAddressArray['city'] . ', ' .  $billingAddressArray['city'] . ', ' .  $billingAddressArray['postcode'] . ', ' . $billingAddressArray['country'];

    $testData = [
        "RespondType" => "JSON",
        "Version" => "1.4",
        "TimeStamp" => time(), //請以  time()  格式
        "TransNum" => $post['TradeNo'],
        "MerchantOrderNo" => $post['MerchantOrderNo'],  //"201409170000009",
        "BuyerName" =>$buyerName, ///$order->get_formatted_billing_full_name(),
        "BuyerUBN" => $buyerUbn,
        "BuyerAddress" => $BuyerAddress,
        "BuyerEmail" => $order->billing_email,
        "BuyerPhone" => $order->billing_phone,
        "Category" => $Category,
        "TaxType" => $TaxType,
        "TaxRate" => $TaxRate,
        "Amt" => $Amt,
        "TaxAmt" => $TaxAmt,
        "TotalAmt" => $TotalAmt,
        "CarrierType" => $CarrierType,
        "CarrierNum" => $CarrierNum,
        "LoveCode" => $LoveCode,
        "PrintFlag" => $PrintFlag,
        "ItemName" => $ItemName, //多項商品時，以「|」分開
        "ItemCount" => $ItemCount, //多項商品時，以「|」分開
        "ItemUnit" => $ItemUnit, //多項商品時，以「|」分開
        "ItemPrice" => $ItemPrice, //多項商品時，以「|」分開
        "ItemAmt" => $ItemAmt, //多項商品時，以「|」分開
        "Comment" => $Comment,
        "Status" => $Status, //1=立即開立，0=待開立，3=延遲開立
        "CreateStatusTime" => $CreateStatusTime,
        "NotifyEmail" => $NotifyEmail, //1=通知，0=不通知
    ];


//    print "set parameter";
    $data->setParameter($testData);
    // print "<pre>";
    // print_r($testData);
    // print_r($data->post_data_array);
    // print "</pre>";


    // get order status here
    $oder_status =  helper_spgateway_pay2go_invoice_get_order_status($orderId);

//        print "status is  " . $oder_status;
    if(	$data->status == 0 and $oder_status == 'processing' ) {
//                print "send invoice because status is send invoice when processing "  . $data->status;
        $data->postInvoice();
    } else if ( $data->status == 100 && $oder_status == 'completed') {
        $data->postInvoice();
//                print " send invoice because order is completed and status is send when product is completed " . $data->status;
    } else {
//                print " not time to send invoice" . $data->status;
    }

    // print "exit???";
    // exit;
}

function helper_spgateway_separate_order_results($count, $fieldName, $post) {

    $str = '';

    // print " count  $count field name  $fieldName ";

    for($i=1; $i<=$count; $i++) {

        if($fieldName == 'Item Count') {
            $str .=  $post['Qty' . $i];
        } else if($fieldName == 'Item Unit') {
            $str .= '個';
        } else if ($fieldName == 'Item Amount') {
            $quantity     = $post['Qty' . $i];
            $price        = $post['Price' . $i];
            $subTotal     = $quantity * $price;
            $str         .= $subTotal;
        } else {
            $str .= $post[$fieldName . $i];
        }

        if ($i != $count) {
            $str .= '|';
        }

        // print " field name $fieldName i $i count $count ";
    }
    // print " str compose  1 " . $str . ' count ' . $count . ' field name ' . $fieldName;
    // exit;
    // print " title " . $str ;
    return $str;

}

function helper_spgateway_pay2go_invoice_get_order_status($orderId)
{
    $order = new WC_Order($orderId);

    return $order->get_status();
}


function helper_spgateway_pay2go_invoice_set_order_completed($orderId)
{
    $order = new WC_Order($orderId);
    WC()->cart->empty_cart(true);
    $order->update_status('completed');
}
