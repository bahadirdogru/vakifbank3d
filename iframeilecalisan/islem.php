<?php
$PostUrl      = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i.
$mpiServiceUrl=	"https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx"; // Dokümandaki Enrollment URLi
$SuccessURL   = "http://127.0.0.1/islem.php";
$FailureURL   = "http://127.0.0.1/islem.php";
// Banka iş yeri bilgileri:
$IsyeriNo     = "000000000XXXXXX"; // Burası üye iş yerine göre doldurulur.
$TerminalNo   = "VP0XXXXX"; // Burası üye iş yerine göre doldurulur.
$IsyeriSifre  = "XXXXXXXXXX"; // Burası üye iş yerine göre doldurulur.
$ClientIp     = getRealIpAddr(); // ödemeyi gerçekleştiren kullanıcının IP bilgisi alınarak bu alanda gönderilmelidir.

function getRealIpAddr(){
    // Client Ip adresini getir.
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}


if (isset($_GET["islem"]) && !empty($_GET["islem"])) {
    $islem = $_GET['islem'];

    $PosBanka = $_POST["PosBanka"];
    $secure3d = $_POST["secure3d"];
    // Kart ve sipariş bilgileri:
    $KartTipi = $_POST["KartMarka"]; // Visa / Master vs.
    $KartNo = $_POST["KartNo"];
    $KartAy = $_POST["KartAy"];
    $KartYil = $_POST["KartYil"];
    $KartSonkul = (substr($_POST["KartYil"], 2)) . $_POST["KartAy"]; //2020 03 için 2003 yazılıyor. 3d için kullanılacak.
    $KartCvv = $_POST["KartCvv"];
    $Tutar = $_POST["Tutar"];
    $IslemNo = $_POST["IslemNo"]; // Unique olmak zorunda boş bıraklılabilir non3d için(kendi oluşturuyor.) ama 3d için olmak zorunda.
    $IslemTipi = $_POST["IslemTipi"]; // Sale - Refund gibi.
    $ParaKodu = $_POST["ParaKodu"];
    $TaksitSayisi = $_POST["Taksit"];
    $SessionInfo = "Pan=$KartNo&Ay=$KartAy&Yil=$KartYil&Cvv=$KartCvv&Tutar=$Tutar";
    $SessionInfo = base64_encode($SessionInfo);

    switch ($islem) {
        case "3d":
            echo "istek: 3d<hr>";

            $postedilecek = "Pan=$KartNo"
                ."&ExpiryDate=$KartSonkul"
                ."&PurchaseAmount=$Tutar"
                ."&Currency=$ParaKodu"
                ."&BrandName=$KartTipi"
                ."&VerifyEnrollmentRequestId=$IslemNo"
                ."&SessionInfo=$SessionInfo"
                ."&MerchantId=$IsyeriNo"
                ."&MerchantPassword=$IsyeriSifre"
                ."&SuccessUrl=$SuccessURL"
                ."&FailureUrl=$FailureURL";
            if ($TaksitSayisi > 1){
                $postedilecek.="&InstallmentCount=$TaksitSayisi";
            }

            file_put_contents("postedilecek.txt",$postedilecek); // log için

            $sonuc3d = postet3d($mpiServiceUrl,$postedilecek);
            if (false != $sonuc3d ) {
                echo $sonuc3d;
                break;
            }
                echo "sonuc3d=".$sonuc3d."<hr>";
            break;

        case "non3d":
            echo "istek: non3d";
            $PosXML = 'prmstr=<VposRequest><MerchantId>'
                .$IsyeriNo.'</MerchantId><Password>'
                .$IsyeriSifre.'</Password><TerminalNo>'
                .$TerminalNo.'</TerminalNo><TransactionType>'
                .$IslemTipi.'</TransactionType><TransactionId>'
                .$IslemNo.'</TransactionId><CurrencyAmount>'
                .$Tutar.'</CurrencyAmount><CurrencyCode>'
                .$ParaKodu.'</CurrencyCode><Pan>'
                .$KartNo.'</Pan><Cvv>'
                .$KartCvv.'</Cvv><Expiry>'
                .$KartYil.$KartAy.'</Expiry><TransactionDeviceSource>0</TransactionDeviceSource><ClientIp>'
                .$ClientIp.'</ClientIp>';
            if ($TaksitSayisi > 1){
                $PosXML.='<NumberOfInstallments>$TaksitSayisi</NumberOfInstallments></VposRequest>';
            }else {
                $PosXML.='</VposRequest>';
            }
            echo $PosXML;
            PoxRequest($PostUrl,$PosXML);
            break;
    }
}
else {
    // Bankadan geri dönüş oldu:
    if (isset($_POST["Status"]) && !empty($_POST["Status"]) && $_POST["Status"]=="Y") {
        echo "Bankadan bilgiler geldi işlem yap";
        $Durum = $_POST["Status"]; // geri dönen durum:
        // Y:Doğrulama başarılı
        // A:Doğrulama tamamlanamadı ancak doğrulama denemesini kanıtlayan CAVV üretildi
        // U:Doğrulama tamamlanamadı
        // E:Doğrulama başarısız.
        // N:Doğrulama başarısız, işlem reddedildi
        $IslemNo = $_POST["VerifyEnrollmentRequestId"]; // işlem numarası unique
        $KartNo = $_POST["Pan"]; // Kredi Kartı numarası
        $KartSonkul = $_POST["Expiry"];
        $ParaKodu = $_POST["PurchCurrency"]; // Para kodu:
        // 949: TRY
        // 840: USD
        // 978: EUR
        // 826: GBP
        // JPY: 392
        $TaksitSayisi = $_POST["InstallmentCount"]; // Taksit Sayısı
        $Xid = $_POST["Xid"]; // MPI tarafından üretilen 20 byte değerindeki alan. İşlem Sanal POS a gönderilir.
        $ECI = $_POST["Eci"]; // Bankalar arası kart merkezinden gelen Elektronik ticaret belirteci. Status Y ise 05 A ise 06 döner.
        $CAVV = $_POST["Cavv"]; // Bankalar arası kart merkezinden gelen (ACS) 28 byte büyüklüğünde kart sahibi doğrulama değeri.
        // Eğer KARD VISA ise;
        // Status Y ve ECI 05:
        //  Otorizasyon mesajına ECI ve CAVV bilgilerini yerleştirerek işleme devam et.
        // Status A ve ECI 06:
        //  Otorizasyon akışını durdur veya Half Secure Ödeme almak için Otorizasyon mesajına ECI ve CAVV bilgilerini yerleştirerek işleme devam et.
        // Status U ve ECI 07:
        //  Otorizasyon akışını durdur veya İşleme Non Secure olarak devam edilecekse Otorizasyon mesajına ECI bilgisini yerleştirerek işleme devam edilebilir.
        // Status E: Otorizasyon akışını durdur!
        // Status N: Otorizasyon akışını durdur!

        // Eğer KARD MasterCard ise;
        // Status Y ve ECI 02:
        //  Otorizasyon mesajına ECI ve CAVV bilgilerini yerleştirerek işleme devam et.
        // Status A ve ECI 01:
        //  Otorizasyon akışını durdur veya Half Secure Ödeme almak için Otorizasyon mesajına ECI ve CAVV bilgilerini yerleştirerek işleme devam et
        // Status U ve ECI 00:
        //  Otorizasyon akışını durdur veya İşleme Non Secure olarak devam edilecekse Otorizasyon mesajına ECI bilgisini yerleştirerek işleme devam edilebilir.
        // Status E: Otorizasyon akışını durdur!
        // Status N: Otorizasyon akışını durdur!

        // Eğer KARD TROY ise;
        // Status Y ve ECI 02:
        //  Otorizasyon mesajına ECI ve CAVV bilgilerini yerleştirerek işleme devam et
        // Status A ve ECI 01:
        //  Otorizasyon akışını durdur veya Half Secure Ödeme almak için Otorizasyon mesajına ECI ve CAVV bilgilerini yerleştirerek işleme devam et
        // Status U ve ECI 00:
        //  Otorizasyon akışını durdur veya İşleme Non Secure olarak devam edilecekse Otorizasyon mesajına ECI bilgisini yerleştirerek işleme devam edilebilir.

        //$KartSonkul = $_SESSION['KartYil'].$_SESSION['KartAy']; //$_POST["Expiry"]; // Son kullanma tarihi
        //$Tutar = $_SESSION['Tutar'];  //$_POST["PurchAmount"]; // Sipariş tutarı
        //$Tutar = sagdan2noktala($Tutar); // 100 gelen datayı 1.00 şekline çevirir.

        $GelenSession = base64_decode($_POST['SessionInfo']);
        parse_str($GelenSession);
        echo "Pan: ".$Pan; // Gelen Sessiondan Parse edilen
        echo "Ay: ".$Ay; // Gelen Sessiondan Parse edilen
        echo "Yil: ".$Yil; // Gelen Sessiondan Parse edilen
        echo "Tutar: ".$Tutar; // Gelen Sessiondan Parse edilen
        echo "Cvv: ".$Cvv; // Gelen Sessiondan Parse edilen
        $KartSonkul = $Yil.$Ay;
        $Pos3dXML = 'prmstr=<VposRequest><MerchantId>'
            .$IsyeriNo.'</MerchantId><Password>'
            .$IsyeriSifre.'</Password><TerminalNo>'
            .$TerminalNo.'</TerminalNo><TransactionType>Sale</TransactionType><CurrencyAmount>'
            .$Tutar.'</CurrencyAmount><CurrencyCode>'
            .$ParaKodu.'</CurrencyCode><Pan>'
            .$KartNo.'</Pan><Expiry>'
            .$KartSonkul.'</Expiry><Cvv>'
            .$Cvv.'</Cvv><ECI>'
            .$ECI.'</ECI><CAVV>'
            .$CAVV.'</CAVV><MpiTransactionId>'
            .$IslemNo.'</MpiTransactionId><ClientIp>'
            .$ClientIp.'</ClientIp><TransactionDeviceSource>0</TransactionDeviceSource>';
        if ($TaksitSayisi > 1){
            $Pos3dXML.='<NumberOfInstallments>$TaksitSayisi</NumberOfInstallments></VposRequest>';
        }else {
            $Pos3dXML.='</VposRequest>';
        }
        file_put_contents("Pos3dXML.txt",$Pos3dXML); // log için
        PoxRequest($PostUrl,$Pos3dXML);

    } else {
        echo "Bankadan gerekli bilgi gelmedi!";
        ?>
        <script>
            var popupobj = document.getElementById('popup');
            popupobj = null;
        </script>
        <?php
    }
}

/**
 * @param $mpiServiceUrl
 * @param $postedilecek
 *
 * @return string of html code
 */
function postet3d($mpiServiceUrl,$postedilecek){
    // Autherizasyon için MPI(MerchantPlugin3d)'e post eder.
    echo "posted3dbasladi<hr>";
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$mpiServiceUrl);
    curl_setopt($ch,CURLOPT_POST,TRUE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type"=>"application/x-www-form-urlencoded"));
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postedilecek);

    // İşlem isteği MPI'a gönderiliyor
    $resultXml = curl_exec($ch);
    $errorCurl = curl_error($ch);
    curl_close($ch);
    if ($errorCurl){
        echo "cURL Error #:" . $errorCurl;
        exit();
    }
    // Sonuç XML'i yorumlanıp döndürülüyor
    $result = SonucuOku3d($resultXml);
    file_put_contents("result.txt",$result); // log için
    if($result["Status"]=="Y")
    {
        // Kart 3D-Secure Programına Dahil
        echo "Status: ".$result["Status"];
        echo "<hr>".$result["ACSUrl"];
        //header("Access-Control-Allow-Origin:".$result['ACSUrl']);
        ?>
        <html>
        <head>
            <title>Get724 Mpi 3D-Secure İşlem Sayfası</title>
        </head>
        <body>
        <form name="downloadForm" action="<?php echo $result['ACSUrl']; ?>" method="POST">
            <!--		<noscript>-->
            <center>
                <h1>3-D Secure İşleminiz yapılıyor</h1>
                <h2>
                    Tarayıcınızda Javascript kullanımı engellenmiştir.
                    <br></h2>
                <h3>
                    3D-Secure işleminizin doğrulama aşamasına geçebilmek için Gönder butonuna basmanız gerekmektedir
                </h3>
                <input type="submit" value="Gönder">
            </center>
            <!--</noscript>-->
            <input type="hidden" name="PaReq" value="<?php echo $result['PaReq'];?>">
            <input type="hidden" name="TermUrl" value="<?php echo $result['TermUrl'];?>">
            <input type="hidden" name="MD" value="<?php echo $result['MerchantData'];?>">
        </form>
        <SCRIPT LANGUAGE="Javascript" >
        window.addEventListener('DOMContentLoaded', function(e) {
          console.log('DOM fully loaded and parsed kod1');
          document.downloadForm.submit();
        }
      );     
        </SCRIPT>
        </body>
        </html>
<?php
    } else  {
        return "3D-Secure Verify Enrollment Sonucu :".$result["Status"].": ".$result["MessageErrorCode"];
        //print("İşlem İsteğini Sanal Pos'a gönderiniz.(non3d olarak)");
    }
} // postet3d sonu

function SonucuOku3d($result){
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

    // Sonuç dizisi oluşturuluyor
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
} // SonucuOku3d sonu

function PoxRequest($PostUrl,$PosXML){
    // Pos bilgilerini alıp bankaya yollar non3d
    echo '<h1>Vpos Request</h1>';
    echo $PostUrl."<br>";
    echo '<textarea rows="15" cols="60">'.$PosXML.'</textarea><br>';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,$PostUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$PosXML);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 59);
    curl_setopt($ch, CURLOPT_SSLVERSION,5); // CURL_SSLVERSION_TLSv1_1 = 5, CURL_SSLVERSION_TLSv1_2 = 6
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
} // PoxRequest sonu
