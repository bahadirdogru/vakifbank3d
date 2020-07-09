<html>

<?php
error_reporting(E_ALL ^ E_NOTICE);

// MPI'dan dönen XML cevabýný yorumlayýp gerekli alanlarý bir dizi içerisinde döndürür
function SonucuOku($result)
{
	$resultDocument = new DOMDocument();
	$resultDocument->loadXML($result);		

	//Status Bilgisi okunuyor
	$statusNode = $resultDocument->getElementsByTagName("Status")->item(0);				
	$status = "";	
	if( $statusNode != null )
		$status = $statusNode->nodeValue;							
	
	//PAReq Bilgisi okunuyor
	$PAReqNode = $resultDocument->getElementsByTagName("PaReq")->item(0);			
	$PaReq = "";
	if( $PAReqNode != null )
		$PaReq = $PAReqNode->nodeValue;
	
	//ACSUrl Bilgisi okunuyor
	$ACSUrlNode = $resultDocument->getElementsByTagName("ACSUrl")->item(0);
	$ACSUrl = "";
	if( $ACSUrlNode != null )
		$ACSUrl = $ACSUrlNode->nodeValue;
		
	//Term Url Bilgisi okunuyor
	$TermUrlNode = $resultDocument->getElementsByTagName("TermUrl")->item(0);
	$TermUrl = "";
	if( $TermUrlNode != null )
		$TermUrl = $TermUrlNode->nodeValue;
	
	//MD Bilgisi okunuyor
	$MDNode = $resultDocument->getElementsByTagName("MD")->item(0);
	$MD = "";
	if( $MDNode != null )
		$MD = $MDNode->nodeValue;
	
	//MessageErrorCode Bilgisi okunuyor
	$messageErrorCodeNode = $resultDocument->getElementsByTagName("MessageErrorCode")->item(0);
	$messageErrorCode = "";
	if( $messageErrorCodeNode != null )
		$messageErrorCode = $messageErrorCodeNode->nodeValue;

	// Sonuç dizisi oluþturuluyor
	$result = array
	(
		"Status"=>$status,
		"PaReq"=>$PaReq,
		"ACSUrl"=>$ACSUrl,
		"TermUrl"=>$TermUrl,
		"MerchantData"=>$MD	,
		"MessageErrorCode"=>$messageErrorCode
	);
	return $result;	
}


	if($_POST["form"]=="send"){
	
		$mpiServiceUrl=	""; // Dokümandaki Enrollment URLi
		$krediKartiNumarasi = $_POST["pan"];
		$sonKullanmaTarihi = $_POST["ExpiryDate"];
		$kartTipi = $_POST["BrandName"];
		$tutar = $_POST["PurchaseAmount"];
		$paraKodu = $_POST["Currency"];
		$taksitSayisi = $_POST["InstallmentCount"];
		$islemNumarasi = $_POST["VerifyEnrollmentRequestId"];
		$uyeIsyeriNumarasi = $_POST["MerchantId"];
		$uyeIsYeriSifresi = $_POST["MerchantPassword"];
		$SuccessURL = $_POST["SuccessURL"];
		$FailureURL = $_POST["FailureURL"];
		$ekVeri = $_POST["SessionInfo"]; // Optional
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$mpiServiceUrl);
		curl_setopt($ch,CURLOPT_POST,TRUE);	
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type"=>"application/x-www-form-urlencoded"));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,"Pan=$krediKartiNumarasi&ExpiryDate=$sonKullanmaTarihi&PurchaseAmount=$tutar&Currency=$paraKodu&BrandName=$kartTipi&VerifyEnrollmentRequestId=$islemNumarasi&SessionInfo=$ekVeri&MerchantId=$uyeIsyeriNumarasi&MerchantPassword=$uyeIsYeriSifresi&SuccessUrl=$SuccessURL&FailureUrl=$FailureURL&InstallmentCount=$taksitSayisi");
			
		// Ýþlem isteði MPI'a gönderiliyor
		$resultXml = curl_exec($ch);	
		curl_close($ch);		

		// Sonuç XML'i yorumlanýp döndürülüyor
		$result = SonucuOku($resultXml);
		
	if($result["Status"]=="Y")
	{
		// Kart 3D-Secure Programýna Dahil		
		echo $result["Status"];
?>
<html>
	<head>
		<title>Get724 Mpi 3D-Secure Ýþlem Sayfasý</title>
	</head>
	<body>
	
		<form name="downloadForm" action="<?php echo $result['ACSUrl']?>" method="POST">
<!--		<noscript>-->
		<br>
		<br>
		<div id="image1" style="position:absolute; overflow:hidden; left:0px; top:0px; width:180px; height:180px; z-index:0"><img src="" alt="" title="" border=0 width=180 height=180></div>
		<center>
		<h1>3-D Secure Ýþleminiz yapýlýyor</h1>
		<h2>
		Tarayýcýnýzda Javascript kullanýmý engellenmiþtir.
		<br></h2>
		<h3>
			3D-Secure iþleminizin doðrulama aþamasýna geçebilmek için Gönder butonuna basmanýz gerekmektedir
		</h3>
		<input type="submit" value="Gönder">
		</center>
<!--</noscript>-->
		<input type="hidden" name="PaReq" value="<?php echo $result['PaReq']?>">
		<input type="hidden" name="TermUrl" value="<?php echo $result['TermUrl']?>">
		<input type="hidden" name="MD" value="<?php echo $result['MerchantData']?>">
		</form>
	<SCRIPT LANGUAGE="Javascript" >
		//document.downloadForm.submit();
	</SCRIPT>
	</body>
</html>				
<?php				
		} else  {
			print("3D-Secure Verify Enrollment Sonucu :");print($result["Status"] + ": " + $result["MessageErrorCode"]);print("<br>");
			print("Ýþlem Ýsteðini Sanal Pos'a gönderiniz.");		
		}
	} else {
	?>
<style>
.input_yeni {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9pt;
	font-weight: bold;
	color: #666666;
	background-color: #FFFFFF;
	padding: 5px;
	border: 1px solid #CCCCCC;
}
.Basliklar {
	font-family: Tahoma;
	font-size: 9pt;
	font-weight: bold;
	color: #666666;
	padding-left: 10px;
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #EBEBEB;
	padding-top: 2px;
	text-align: right;
}
.AltCizgi {
	font-family: Tahoma;
	font-size: 9pt;
	font-weight: normal;
	color: #666666;
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #EBEBEB;
	padding-top: 2px;
	padding-bottom: 2px;
	padding-left: 5px;
}
</style>
<body>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<form action="" method="post">
			<input type="hidden" name="form" value="send">
			<tr>
			<td width="10" height="10" bgcolor="#FBFBFB" class="Basliklar">&nbsp;</td>
			<td width="1" bgcolor="#FBFBFB" class="AltCizgi">&nbsp;</td>
			<B><td bgcolor="#FBFBFB" class="AltCizgi">3D Entegrasyon Vakifbank Pos islem Uye is yeri bilgileri</td></B>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Uye Isyeri No</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="MerchantId" size="49" value="0"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Ýsyeri Sifre</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="MerchantPassword" size="49" value="0"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">SuccessURL</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="SuccessURL" size="50" value="http://127.0.0.1:8080/VAKIFPHP/MPI/TempSuccessUrl.php"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">FailureURL</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="FailureURL" size="50" value="http://127.0.0.1:8080/VAKIFPHP/MPI/TempSuccessUrl.php"></td>
		</tr>
		<td width="10" height="10" bgcolor="#FBFBFB" class="Basliklar">&nbsp;</td>
			<td width="1" bgcolor="#FBFBFB" class="AltCizgi">&nbsp;</td>
		<B><td bgcolor="#FBFBFB" class="AltCizgi">Kart Bilgileri</td></B>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart No</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="pan" size="49" value="0"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Son K.Tarihi</td>
			<td width="1" class="AltCizgi"></td>
			<td class="AltCizgi">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td width="33">
					<input class="input_yeni" type="text" name="ExpiryDate" size="12" value="1412"></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart Cvv</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="KartCvv" size="12" value="123"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Tutar</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="PurchaseAmount" size="12" value="18.00"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart Tipi</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="BrandName" size="12" value="100"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Para Türü</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="Currency" size="50" value="840"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Taksit</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="InstallmentCount" size="4" value=""></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Siparis No</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="VerifyEnrollmentRequestId" size="50" value="SIP_ID12345698700020"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Session Info</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="SessionInfo" size="50" value="1"></td>
		</tr>
		            <br/> <input type="submit">
        </form>
	<?php } ?>
</html>