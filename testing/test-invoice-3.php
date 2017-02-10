<?php 
// Get current date time
$date = new DateTime();
 
// parameters
$arrPostData=array( 
     'url' 						=> 'https://cinv.pay2go.com/API/invoice_issue',
    'HashIV'					=>'YRgKDp8vWjEYADgh',
    'HashKey'					=>'f2VyML8oSxa82gYhGV9t20CjO51trVWH', 
    'MerchantID_'				=> '3405394', 
    'PostData_'					=> '',
	'RespondType' 				=> 'String',
	'Version' 					=> '1.4', 
	'TimeStamp' 				=> $date->getTimestamp(),   
    'TransNum'					=>1234,
    'MerchantOrderNo'		    =>$date->getTimestamp(),
    'Status'                    =>1,
    // 'CreateStatusTime'          =>'2014-10-05',
    'Category'					=>'B2B',
    'BuyerName'					=>'Jesus Erwin Suarez',
    'BuyerUBN'					=>12345678,
    'BuyerAddress'			    =>'mimbalot buru un iligan city',
    'BuyerEmail'				=>'mrjesuserwinsuarez@gmail.com',
    // 'CarrierType'               => 1,
    // 'CarrierNum'				=>5234567554432345678, 
    'LoveCode'                  => '', 
    'PrintFlag'					=>'件', 
    'TaxType'				    =>1,
    'TaxRate'				    =>5,
    'CustomsClearance'          =>'',
    'Amt'				   	    =>95,
    // 'AmtSales'				    =>0,
    // 'AmtZero'				    =>0,
    // 'AmtFree'				    =>0,
    'TaxAmt'				    =>5,
    'TotalAmt'					=>100,
    'CarrierType'				=>2,
    'ItemName'					=>'Send Right Product',
    'ItemCount'					=>1,
    'ItemUnit'					=>'A',
    'ItemPrice'					=>95,
    'ItemAmt'				    =>95,
    // 'ItemTaxType'               =>1,
    'Comment'				    =>'This is comment', 
    'RandomNum'				    =>1234567890,
    'InvoiceTransNo'            =>14061313541640927, 
);

// check code value initialized   
$Check_code = array(     
    "MerchantID_" => $arrPostData['MerchantID_'],//商店代號     
    "MerchantOrderNo" => $arrPostData['MerchantOrderNo'],    //商店自訂單號(訂單編號)     
    "InvoiceTransNo" => $arrPostData['TransNum'],    //智付寶電子發票開立序號     
    "TotalAmt" => $arrPostData['TotalAmt'],    //發票金額     
    "RandomNum" => $arrPostData['RandomNum'],    //發票防偽隨機碼 
);
 
// check code convert encryption
Ksort($Check_code);
$Check_str = http_build_query($Check_code, '', '&'); 
$CheckCode = "HashIV='".$arrPostData['HashIV']."'&$Check_str&HashKey=" . $arrPostData['HashKey'];
$CheckCode = strtoupper (hash ("sha256", $CheckCode));
$arrPostData['PostData_'] = $CheckCode;

// display fields and value to user and use table to make it more readable
print "<table>";
print '<form action="'.$arrPostData['url'].'" method="post" >'; 
$i = 0; 
foreach ($arrPostData as $fieldName => $value) {
	$i++;
	print "<td>";
	print $fieldName;
	print "</td>"; 
	print "<td>"; 
    print  '<input style="width:200%" type="text" name="' . htmlspecialchars($fieldName) . '" value="' . $value . '" /><br>';
   print "</td>"; 
    if($i % 1 == 0) {
    	print "<tr>";
    } 
}  
print "<tr><td><input type='submit' value='submit' /></td>";
print '</form>';
print "</table>"; 