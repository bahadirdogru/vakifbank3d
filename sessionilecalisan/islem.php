<?php
/**
 * Sanalpos islemleri için oluşturulan php 3D ve Non3D
 * User: Bahadır
 * Date: 14.03.2019
 * Time: 17:10
 */
session_start();
error_reporting(E_ALL ^ E_NOTICE);
// Tanımlar:
$PostUrl      = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i.
$mpiServiceUrl=	"https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx"; // Dokümandaki Enrollment URLi
$SuccessURL   = "https://127.0.0.1/islem.php";
$FailureURL   = "https://127.0.0.1/islem.php";
// Banka iş yeri bilgileri:
$IsyeriNo     = "000000000111111"; // Burası üye iş yerine göre doldurulur.
$TerminalNo   = "VP999999"; // Burası üye iş yerine göre doldurulur.
$IsyeriSifre  = "3XTgER89as"; // Burası üye iş yerine göre doldurulur.
$ClientIp     = getRealIpAddr(); // ödemeyi gerçekleştiren kullanıcının IP bilgisi alınarak bu alanda gönderilmelidir.

function sagdan2noktala($verilen){
    $verilen = substr_replace($verilen, ".", strlen($verilen)-2, 0);
    return $verilen;
}

if (isset($_POST["secure3d"]) && !empty($_POST["secure3d"])) {
    //echo "Yes, secure3d is set";
    // Kredi Kartı formundan gelen post bilgileri:
    // *******************************************
    $secure3d     = $_POST["secure3d"];

    // Kart ve sipariş bilgileri:
    $KartTipi     = $_POST["KartMarka"]; // Visa / Master vs.
    $KartNo       = $_POST["KartNo"];
    $KartAy       = $_POST["KartAy"];
    $KartYil      = $_POST["KartYil"];
    $KartSonkul   = (substr($_POST["KartYil"],2)).$_POST["KartAy"]; //2020 03 için 2003 yazılıyor. 3d için kullanılacak.
    $KartCvv      = $_POST["KartCvv"];
    $Tutar        = $_POST["Tutar"];
    $IslemNo      = $_POST["IslemNo"]; // Unique olmak zorunda boş bıraklılabilir non3d için(kendi oluşturuyor.) ama 3d için olmak zorunda.
    $IslemTipi    = $_POST["IslemTipi"]; // Sale - Refund gibi.
    $ParaKodu     = $_POST["ParaKodu"];
    $TaksitSayisi = $_POST["Taksit"];
    // Üye iş yeri için bırakılan alan
    $SessionInfo = session_id(); // Optional programcılara verilen işaretleme alanı. Session_id ile kontrol sağlanıyor.

    $_SESSION['SessionInfo'] = $SessionInfo;
    $_SESSION['secure3d'] = $secure3d;
    $_SESSION['KartTipi'] = $KartTipi;
    $_SESSION['KartNo'] = $KartNo;
    $_SESSION['KartAy'] = $KartAy;
    $_SESSION['KartYil'] = $KartYil;
    $_SESSION['KartSonkul'] = $KartSonkul;
    $_SESSION['KartCvv'] = $KartCvv;
    $_SESSION['Tutar'] = $Tutar;
    $_SESSION['IslemNo'] = $IslemNo;
    $_SESSION['IslemTipi'] = $IslemTipi;
    $_SESSION['ParaKodu'] = $ParaKodu;
    $_SESSION['TaksitSayisi'] = $TaksitSayisi;

    switch ($secure3d) {
        case "3d":
            echo "case3d";
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
            if ($taksitSayisi > 1){
                $postedilecek.="&InstallmentCount=$TaksitSayisi";
            }
            file_put_contents("postedilecek.txt",$postedilecek); // log için
            postet3d($mpiServiceUrl,$postedilecek);
            break;

        case "non3d":
            echo "case non3d";
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

    } //Switch case sonu

} else {
    echo "N0, secure3d is not set";
    // ACS(Bankalar arası kart merkezin)den gelen datayı karşılama durumu
    // ACS (Bankalar arası kart merkezi)nden gelen post bilgileri:
    // *******************************************
    $SessionInfo = $_POST["SessionInfo"]; // Optional ek veri. 1024byte

    If ($SessionInfo != $_SESSION['SessionInfo']) { // Session id ile güvenlik kontrolü yapıyoruz.
        echo "gelen sessionid = ".$SessionInfo;
        echo "<br>";
        echo "phpdeki sessionid = ".$_SESSION['SessionInfo'];
        exit;
    }


    $Durum = $_POST["Status"]; // geri dönen durum:
    // Y:Doğrulama başarılı
    // A:Doğrulama tamamlanamadı ancak doğrulama denemesini kanıtlayan CAVV üretildi
    // U:Doğrulama tamamlanamadı
    // E:Doğrulama başarısız.
    // N:Doğrulama başarısız, işlem reddedildi
    $IslemNo = $_POST["VerifyEnrollmentRequestId"]; // işlem numarası unique
    $KartNo = $_POST["Pan"]; // Kredi Kartı numarası
    $KartSonkul = $_SESSION['KartYil'].$_SESSION['KartAy']; //$_POST["Expiry"]; // Son kullanma tarihi
    $Tutar = $_SESSION['Tutar'];  //$_POST["PurchAmount"]; // Sipariş tutarı
    //$Tutar = sagdan2noktala($Tutar); // 100 gelen datayı 1.00 şekline çevirir.
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
    $KartCvv = $_SESSION['KartCvv'];
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
    if (isset($_POST["VerifyEnrollmentRequestId"]) && !empty($_POST["VerifyEnrollmentRequestId"])) {
    echo "3d bilgisi geldi işlemler yap";
        $Pos3dXML = 'prmstr=<VposRequest><MerchantId>'
            .$IsyeriNo.'</MerchantId><Password>'
            .$IsyeriSifre.'</Password><TerminalNo>'
            .$TerminalNo.'</TerminalNo><TransactionType>Sale</TransactionType><CurrencyAmount>'
            .$Tutar.'</CurrencyAmount><CurrencyCode>'
            .$ParaKodu.'</CurrencyCode><Pan>'
            .$KartNo.'</Pan><Expiry>'
            .$KartSonkul.'</Expiry><Cvv>'
            .$KartCvv.'</Cvv><ECI>'
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
        // remove all session variables
        session_unset();

        // destroy the session
        session_destroy();
     ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head id="Head1" runat="server">
            <title></title>
            <style>
                .mainDiv
                {
                    width:500px;
                    height:350px;
                    position:absolute;
                    left:50%;
                    margin-left:-250px;
                    color: black;
                    border:6px inset red;
                    border-radius: 15px;
                    display: inline;
                    padding:15px;
                    box-shadow: 10px 10px 5px #888;
                }
                .innerSpan
                {
                    width:200px;
                    float:left;
                    padding:4px 0 0 0;
                }
                .innerSpanText
                {
                    width:200px;
                    float:right;
                    padding:4px 0 0 0;
                }
            </style>
        </head>
        <body>
        <form id="form1" runat="server" method="post" action="TempSucessUrl.aspx">
            <div class="mainDiv">
                <table>
                    <tbody>
                    <tr>
                        <td><span class="innerSpan">Status</span></td>
                        <td><span class="innerSpanText"><input type="text" id="Status" name="Status" value="<?=$_POST["Status"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Merchant Id</span></td>
                        <td><span class="innerSpanText"><input type="text" id="MerchantId" name="MerchantId" value="<?=$_POST["MerchantId"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">VerifyEnrollmentRequest Id</span></td>
                        <td><span class="innerSpanText"><input type="text" id="VerifyEnrollmentRequestId" name="VerifyEnrollmentRequestId" value="<?=$_POST["VerifyEnrollmentRequestId"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Purchase Amount</span></td>
                        <td><span class="innerSpanText"><input type="text" id="PurchAmount" name="PurchAmount" value="<?=$_POST["PurchAmount"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Xid</span></td>
                        <td><span class="innerSpanText"><input type="text" id="Xid" name="Xid" value="<?=$_POST["Xid"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">InstallmentCount</span></td>
                        <td><span class="innerSpanText"><input type="text" id="InstallmentCount" name="InstallmentCount" value="<?=$_POST["InstallmentCount"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Session Info</span></td>
                        <td><span class="innerSpanText"><input type="text" id="SessionInfo" name="SessionInfo" value="<?=$_POST["SessionInfo"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Purchase Currency</span></td>
                        <td><span class="innerSpanText"><input type="text" id="PurchCurrency" name="PurchCurrency" value="<?=$_POST["PurchCurrency"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Pan</span></td>
                        <td><span class="innerSpanText"><input type="text" id="Pan" name="Pan" value="<?=$_POST["Pan"];?>"/></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Expire Date</span></td>
                        <td><span class="innerSpanText"><input type="text" id="ExpiryDate" name="ExpiryDate" value="<?=$_POST["Expiry"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Eci</span></td>
                        <td><span class="innerSpanText"><input type="text" id="Eci" name="Eci" value="<?=$_POST["Eci"];?>" /></span></td>
                    </tr>
                    <tr>
                        <td><span class="innerSpan">Cavv</span></td>
                        <td><span class="innerSpanText"><input type="text" id="Cavv" name="Cavv" value="<?=$_POST["Cavv"];?>" /></span></td>
                    </tr>

                    </tbody>

                </table>
            </div>
        </form>
        </body>
        </html>
        <?php
    } // if sonu
    } // else sonu


    function postet3d($mpiServiceUrl,$postedilecek){
        // Autherizasyon için MPI(MerchantPlugin3d)'e post eder.
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
            echo $result["Status"];
            ?>
            <html>
            <head>
                <title>Get724 Mpi 3D-Secure İşlem Sayfası</title>
            </head>
            <body>

            <form name="downloadForm" action="<?php echo $result['ACSUrl']?>" method="POST">
                <!--		<noscript>-->
                <br>
                <br>
                <div id="image1" style="position:absolute; overflow:hidden; left:0px; top:0px; width:180px; height:180px; z-index:0"><img src="" alt="" title="" border=0 width=180 height=180></div>
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
                <input type="hidden" name="PaReq" value="<?php echo $result['PaReq']?>">
                <input type="hidden" name="TermUrl" value="<?php echo $result['TermUrl']?>">
                <input type="hidden" name="MD" value="<?php echo $result['MerchantData']?>">
            </form>
            <SCRIPT LANGUAGE="Javascript" >
                document.downloadForm.submit();
            </SCRIPT>
            </body>
            </html>
            <?php
        } else  {
            print("3D-Secure Verify Enrollment Sonucu :");print($result["Status"] . ": " . $result["MessageErrorCode"]);print("<br>");
            print("İşlem İsteğini Sanal Pos'a gönderiniz.");
        }
    } // postet3d sonu

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
    }


    function SonucuOku3d($result)
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
    }

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
