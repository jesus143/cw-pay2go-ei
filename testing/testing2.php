<?php


$date = new DateTime();

$arrPostData=array(
    'RespondType'				=>'JSON',
    'Version'				    =>'1.3',
    'TimeStamp'					=>$date->getTimestamp(),
    'TransNum'					=>'',
    'MerchantOrderNo'		    =>$date->getTimestamp(),
    'BuyerName'					=>'Jesus Erwin Suarez',
    'BuyerAddress'			    =>'mimbalot buru un iligan city',
    'BuyerEmail'				=>'mrjesuserwinsuarez@gmail.com',
    'BuyerPhone'				=>'+639069262984',
    'Category'					=>'B2C',
    'TaxType'				    =>3,
    'TaxRate'				    =>5,
    'Amt'				   	    =>100,
    'TaxAmt'				    =>0,
    'TotalAmt'					=>100,
    'CarrierType'				=>2,
    'CarrierNum'				=>5234567554432345678,
    'PrintFlag'					=>'N',
    'ItemName'					=>'This is the item name',
    'ItemCount'					=>1,
    'ItemUnit'					=>'A',
    'ItemPrice'					=>100,
    'ItemAmt'				    =>100,
    'Comment'				    =>'This is comment',
    'Status'				    =>1,
    'CreateStatusTime'	=>$date->getTimestamp(),
    'MerchantID_'	=> '3405394',
    'PostData_'		=> '',
    'HashIV'=>'YRgKDp8vWjEYADgh',
    'HashKey'=>'f2VyML8oSxa82gYhGV9t20CjO51trVWH'
);

$Check_code = array (
    "MerchantID_" => '3405394', // store code
    "MerchantOrderNo" => '201409170000001', //   Shop from the order number ( order number )
    "InvoiceTransNo "=> '14061313541640927', //   Electronic payment invoice to open the serial number
    "TotalAmt"=> 500,    // Invoice
    "RandomNum" =>0142, //   Invoice security random code
);

Ksort($Check_code);
$Check_str = http_build_query($Check_code, '', '&');
$CheckCode = "HashIV=YRgKDp8vWjEYADgh&$Check_str&HashKey=f2VyML8oSxa82gYhGV9t20CjO51trVWH";
$CheckCode = strtoupper (hash ("sha256", $CheckCode));
$arrPostData['PostData_'] = $CheckCode;

print "<pre>";
print_r($arrPostData);
print "</pre>";

print '<form action="https://cinv.pay2go.com/API/invoice_issue" method="post" >';
foreach ($arrPostData as $key => $value) {
    print '' . $key . '<input type="text" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" /><br>';
}

print "<input type='submit' value='submit' />";
print '</form>';