<?php


$date = new DateTime();

$settings = [
    'HashKey'=>'f2VyML8oSxa82gYhGV9t20CjO51trVWH',
    'HashIV'=>'YRgKDp8vWjEYADgh',
];

$fields = [
    'MerchantID_' => '3405394',
    'PostData_' => '',
    'HashKey'=>$settings['HashKey'],
    'HashIV'=>$settings['HashIV'],
    'RespondType' => 'String',
    'Version' => 1.4,
    'TimeStamp' => $date->getTimestamp(),
    'MerchantOrderNo' => '201409170000001',
    'Status' => 1,
    'Category' => 'B2C', //  B2B = buyer for the businessPeople ( there TongBian ) . B2C = buyer is apeople
    'BuyerName' => 'Jesus Erwin Suarez',
    'BuyerEmail' => 'mrjesuserwinsuarez@gmail.com',
    'PrintFlag' => 'N',
    'TaxType' => 2,
    'TaxRate' => 0,
    'Amt' => 100,
    'TaxAmt' => 0,
    'TotalAmt' => 500,
    'ItemName' => 'Test Item',
    'ItemCount' => 2,
    'ItemUnit' => 'A',
    'ItemPrice' => 100,
    'ItemAmt' => 100,
];

$Check_code = array (
    "MerchantID_" => $fields['MerchantID_'] , // store code
    "MerchantOrderNo" => '201409170000001', //   Shop from the order number ( order number )
    "InvoiceTransNo "=> '14061313541640927', //   Electronic payment invoice to open the serial number
    "TotalAmt"=> 500,    // Invoice
    "RandomNum" =>0142, //   Invoice security random code
);



Ksort($Check_code);
$Check_str = http_build_query($Check_code, '', '&');
$CheckCode = "HashIV='".$settings['HashIV']."'&$Check_str&HashKey=" . $settings['HashKey'];
$CheckCode = strtoupper (hash ("sha256", $CheckCode));
$fields['PostData_'] = $CheckCode;


print '<form id="spgateway" name="spgateway" action="https://cinv.pay2go.com/API/invoice_issue" method="post" >';
    foreach ($fields as $key => $value) {
      print '' . $key . '<input type="text" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" /><br>';
    }
    print "<input type='submit' value='submit' />";
print '</form>';