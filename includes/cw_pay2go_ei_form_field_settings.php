<?php
/*<makotostudio />*/

$arrSettings=array(

	'enabled'=>array(
		'title'		=>__('Enable/Disable', 'woocommerce'),
		'type'		=>'checkbox',
		'label'		=>'啟動智付寶電子發票',
		'default'	=>'yes'),

	'sandbox'=>array(
		'title'		=>'測試模式',
		'type'		=>'checkbox',
		'label'		=>'啟動測試模式',
		'default'	=>'no'), 

	'title'=>array(
		'title'				=>'智付寶電子發票標題',
		'type'				=>'text', 
		'description'	=>'使用者結帳時的標題',
		'default'			=>'智付寶電子發票'),

	'MerchantID'=>array(
		'title'				=>'Merchant ID',
		'type'				=>'text',
		'description'	=>'請輸入智付寶提供的商店代號'), 

	'HashKey'=>array(
		'title'				=>'Hash Key',
		'type'				=>'text',
		'description'	=>'請輸入智付寶提供的 HashKey'),

	'HashIV' => array(
		'title'				=>'Hash IV',
		'type'				=>'text',
		'description'	=>'請輸入智付寶提供的 HashIV'), 

	'InvoiceFlag'=>array(
		'title'				=>'可用載具', 
		'type'				=>'multiselect', 
		'class'				=>'chosen_select',
		'css'					=>'',
		'default'			=>'',
		'description'	=>'可用載具',
		'options'			=>array(

			'2'		=>'會員載具',
			'0'		=>'手機條碼',
			'1'		=>'自然人憑證條碼',
			'3'		=>'捐贈發票',
			'-1'	=>'索取紙本發票'), 

		'custom_attributes'=>array(
			'data-placeholder'=>'選擇可用載具')),

	/*
	'LoveCode'=>array(
		'title'				=>'發票捐贈團體', 
		'type'				=>'multiselect', 
		'class'				=>'chosen_select',
		'css'					=>'',
		'default'			=>'',
		'description'	=>'發票捐贈團體',
		'options'			=>array(
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
			''	=>'',
		), 

		'custom_attributes'=>array(
			'data-placeholder'=>'發票捐贈團體')),
	*/

	'TaxType'=>array(
		'title'		=>'稅別',
		'type'		=>'select',
		'options'	=>array(
			'1'		=>'應稅 ( 5% )',
			'1.1'	=>'應稅 ( 0% )',
			'2'		=>'零稅率',
			'3'		=>'免稅')),

	'Status'=>array(
		'title'		=>'開立發票方式',
		'type'		=>'select',
		'options' =>array(
			'1'	=>'立即開立發票', 
			'3'	=>'預約開立發票')),

	'CreateStatusTime'=>array(
		'title'				=>'延遲開立發票 ( 天 )',
		'type'				=>'text',
		'description'	=>'此參數需在「開立發票方式」選擇「預約開立發票」才有用',
		'default'			=>7));

return $arrSettings;