<?php
require_once "E:/xampp/htdocs/wp-load.php";
require 'E:/xampp/htdocs/wp-content/plugins/cw-pay2go-ei/includes/class-cw-pay2goe-ei-spgateway.php';

$data = new CWP2GEI_SPGATEWAY();

$data->setParameter(
    [
        "RespondType" => "JSON",
        "Version" => "1.4",
        "TimeStamp" => time(), //請以  time()  格式
        "TransNum" => "",
        "MerchantOrderNo" => $date->getTimestamp(),  //"201409170000009",
        "BuyerName" => "王大品",
        "BuyerUBN" => "99112233",
        "BuyerAddress" => "台北市南港區南港路一段 99 號",
        "BuyerEmail" => "mrjesuserwinsuarez@gmail.com",
        "BuyerPhone" => "0955221144",
        "Category" => "B2B",
        "TaxType" => "1",
        "TaxRate" => "5",
        "Amt" => "490",
        "TaxAmt" => "10",
        "TotalAmt" => "500",
        "CarrierType" => "",
        "CarrierNum" => rawurlencode(""),
        "LoveCode" => "",
        "PrintFlag" => "Y",
        "ItemName" => "商品一|商品二", //多項商品時，以「|」分開
        "ItemCount" => "1|2", //多項商品時，以「|」分開
        "ItemUnit" => "個|個", //多項商品時，以「|」分開
        "ItemPrice" => "300|100", //多項商品時，以「|」分開
        "ItemAmt" => "300|200", //多項商品時，以「|」分開
        "Comment" => "TEST，備註說明",
        "Status" => "1", //1=立即開立，0=待開立，3=延遲開立
        "CreateStatusTime" => "",
        "NotifyEmail" => "1", //1=通知，0=不通知
    ]
);
$data->postInvoice();




