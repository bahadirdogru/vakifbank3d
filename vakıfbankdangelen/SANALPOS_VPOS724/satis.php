<?php
$PostUrl      = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i. Testlerinizi test ortamýnda gerçekleþtiriyorsanýz dokümandaki test URL ini kullanmalýsýnýz.
$IsyeriNo     = $_POST["IsyeriNo"];
$TerminalNo   = $_POST["TerminalNo"];
$IsyeriSifre  = $_POST["IsyeriSifre"];
$KartNo       = $_POST["KartNo"];
$KartAy       = $_POST["KartAy"];
$KartYil      = $_POST["KartYil"];
$KartCvv      = $_POST["KartCvv"];
$Tutar        = $_POST["Tutar"];
$SiparID      = $_POST["SiparID"];
$IslemTipi    = $_POST["IslemTipi"];
$TutarKodu    = $_POST["TutarKodu"];
$ClientIp     = "212.2.199.55"; // ödemeyi gerçekleþtiren kullanýcýnýn IP bilgisi alýnarak bu alanda gönderilmelidir.
//$Taksit     = $_POST["InstallmentCount"];

$PosXML = 'prmstr=<VposRequest><MerchantId>'.$IsyeriNo.'</MerchantId><Password>'.$IsyeriSifre.'</Password><TerminalNo>'.$TerminalNo.'</TerminalNo><TransactionType>'.$IslemTipi.'</TransactionType><TransactionId>'.$SiparID.'</TransactionId><CurrencyAmount>'.$Tutar.'</CurrencyAmount><CurrencyCode>'.$TutarKodu.'</CurrencyCode><Pan>'.$KartNo.'</Pan><Cvv>'.$KartCvv.'</Cvv><Expiry>'.$KartYil.$KartAy.'</Expiry><TransactionDeviceSource>0</TransactionDeviceSource><ClientIp>'.$ClientIp.'</ClientIp></VposRequest>';

echo '<h1>Vpos Request</h1>';
echo $PostUrl."<br>";
echo '<textarea rows="15" cols="60">'.$PosXML.'</textarea>';
$ch = curl_init();
						   
curl_setopt($ch, CURLOPT_URL,$PostUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$PosXML);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 59);
curl_setopt($ch, curl.options,array("CURLOPT_SSLVERSION"=>"CURL_SSLVERSION_TLSv1_1"));
// curl_setopt ($ch, CURLOPT_CAINFO, "c:/php/ext/cacert.pem");

$result = curl_exec($ch);

// Check for errors and display the error message
if($errno = curl_errno($ch)) {
    $error_message = curl_strerror($errno);
    echo "cURL error ({$errno}):\n {$error_message}";
}
curl_close($ch);

echo '<h1>Vpos Response</h1>';
echo '<textarea rows="15" cols="60">'.$result.'</textarea>';
?>
