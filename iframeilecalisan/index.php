<?php session_start();?>
<html>
<head>
<meta http-equiv="Content-Language" content="tr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Sanalpos</title>
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
#popup {
    display: none;
    border: 1px black solid;
    width: 650px;
    height: 450px;
    top:50%;
    left:50%;
    transform: translate(-50%, -50%);
    background-color: white;
    z-index: 10;
    padding: 2em;
    position: fixed;
}
#page {
    display: block;
    width: 100%; height: 100%;
    top:0px; left:0px;
    z-index: 1;
    padding: 2em;
    position: absolute;
}

.darken {filter: blur(2px); }

#iframe { border: 0;
    width: 600px;
    height: 400px;}

html, body, #page { height: 100%; }
</style>

</head>
<body>
<div id="page">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<form id="sanalposform" method="POST" action="islem.php">
        <tr>
            <td width="100" height="25" class="Basliklar">Banka Seçiniz</td>
            <td width="1" class="AltCizgi">:</td>
            <td class="AltCizgi">
                <select name="PosBanka" id="PosBanka">
                    <option value="vakifbank">Vakıf Bank</option>
                    <option value="isbank">İş Bankası</option>
                    <option value="kuveytturk">KuveytTurk</option>
                </select>
            </td>
        </tr>
        <tr>
            <td width="100" height="25" class="Basliklar">Kart Tipi</td>
            <td width="1" class="AltCizgi">:</td>
            <td class="AltCizgi">
                <select name="KartMarka" id="KartMarka">
                    <option value="100">VISA</option>
                    <option value="200" selected>MASTER CARD</option>
                    <option value="300">TROY</option>
                    <option value="400">AMERICAN EXPRESS</option>
                </select>
            </td>
        </tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart No</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="KartNo" id="KartNo" size="49" value="xxxx xxxx xxxx xxxx"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Son K.Tarihi</td>
			<td width="1" class="AltCizgi"></td>
			<td class="AltCizgi">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td width="33">
					<input class="input_yeni" type="text" name="KartAy" id="KartAy" size="5" value="xx"></td>
					<td style="padding-left: 5px">
					<input class="input_yeni" type="text" name="KartYil" id="KartYil" size="12" value="xxxx"></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart Cvv</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="KartCvv" id="KartCvv" size="12" value="xxx"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Tutar</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
            <input class="input_yeni" type="text" name="Tutar" id="Tutar" size="12" value="1.00"></td>
		</tr>
		<tr>
            <td width="100" height="25" class="Basliklar">ParaKodu</td>
            <td width="1" class="AltCizgi">:</td>
            <td class="AltCizgi">
            <select name="ParaKodu" id="ParaKodu">
                <option value="949">TRY</option>
                <option value="840">USD</option>
                <option value="978">EUR</option>
                <option value="826">GBP</option>
                <option value="392">JPY</option>
            </select>
            </td>
		</tr>
        <tr>
            <td width="100" height="25" class="Basliklar">Taksit</td>
            <td width="1" class="AltCizgi">:</td>
            <td class="AltCizgi">
                <select name="Taksit" id="Taksit">
                    <option value="1">Tek Çekim</option>
                    <option value="2">2 Taksit</option>
                    <option value="3">3 Taksit</option>
                    <option value="4">4 Taksit</option>
                    <option value="5">5 Taksit</option>
                    <option value="6">6 Taksit</option>
                </select>
            </td>
        </tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Siparis No</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="IslemNo" id="IslemNo" size="50" value="<?php echo rand() ?>"></td>
		</tr>
		<tr>
			<td><input class="input_yeni" type="hidden" name="IslemTipi" id="IslemTipi" size="50" value="Sale"></td>
		</tr>
        <tr>
            <td width="100" height="25" class="Basliklar">3D Secure ile Ödeme Yap</td>
            <td width="1" class="AltCizgi">:</td>
            <td width="1" class="AltCizgi"><!--<input type="checkbox" id="checkbox3d" name="checkbox3d" value="yes" checked> -->
                <!-- Checkbox valuesu sadece yes ise yollanıyor. onun yerine hidden inputbox koyduk. javascript ile içeriğini değiştirdik. -->
                <select name="secure3d" id="secure3d">
                    <option value="3d">3D Güvenlik ile Ödeme</option>
                    <option value="non3d">Normal Ödeme</option>
                </select></td>
            <!--   <input type="hidden" id="secure3d" name="secure3d" value="3d" checked></td> -->
       </tr>
       <tr>
           <td width="100" height="25" class="Basliklar">&nbsp;</td>
           <td width="1" class="AltCizgi"></td>
           <td class="AltCizgi"><input type="submit" value="Gonder" name="B1"></td>
       </tr>
   </form>
</table>
</div>


<div id="popup">
    <iframe id="iframe" allowpaymentrequest allow-scripts></iframe>
</div>
<script>
/*   document.getElementById("checkbox3d").addEventListener('change', function () {
       if (this.checked) {
           //document.getElementById("sanalposform").action = "satis3d.php"
           document.getElementById("secure3d").value = "3d"
       } else {
           //document.getElementById("sanalposform").action = "satis.php"
           document.getElementById("secure3d").value = "non3d"
       }
   });*/

var xhttp  = new XMLHttpRequest();
document.getElementById('sanalposform').addEventListener('submit',function (e) {
    e.preventDefault();
    //console.log(e);
    var formobj = e.srcElement;
    //console.log(formobj);
    var url = formobj.action;
    var PosBanka = document.getElementById('PosBanka').value;
    var KartMarka = document.getElementById('KartMarka').value;
    var KartNo = document.getElementById('KartNo').value;
    var KartAy = document.getElementById('KartAy').value;
    var KartYil = document.getElementById('KartYil').value;
    var KartCvv = document.getElementById('KartCvv').value;
    var Tutar = document.getElementById('Tutar').value;
    var ParaKodu = document.getElementById('ParaKodu').value;
    var Taksit = document.getElementById('Taksit').value;
    var IslemNo = document.getElementById('IslemNo').value;
    var IslemTipi = document.getElementById('IslemTipi').value;
    var secure3d = document.getElementById('secure3d').value;
    console.log(PosBanka);
    console.log(KartMarka);
    console.log(KartNo);
    console.log(KartAy);
    console.log(KartYil);
    console.log(KartCvv);
    console.log(Tutar);
    console.log(ParaKodu);
    console.log(Taksit);
    console.log(IslemNo);
    console.log(IslemTipi);
    console.log(secure3d);
    console.log(url);
    console.log(`${url}?islem=${secure3d}`);

    xhttp.open('POST',`${url}?islem=${secure3d}`,true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    
    //xhttp.send("fname=Henry&lname=Ford");
    xhttp.send(`PosBanka=${PosBanka}&KartMarka=${KartMarka}&KartNo=${KartNo}&KartAy=${KartAy}&KartYil=${KartYil}&KartCvv=${KartCvv}&Tutar=${Tutar}&ParaKodu=${ParaKodu}&Taksit=${Taksit}&IslemNo=${IslemNo}&IslemTipi=${IslemTipi}&secure3d=${secure3d}`);
})
xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        document.getElementById("popup").showpopup(this.responseText);
        //document.getElementById("popup").innerHTML =
        //    this.responseText;
    }
};

document.getElementById("popup").showpopup = function(htmlkodu) {
    document.getElementById("popup").style.display = "block";
    //document.getElementById('iframe').src = "http://example.com";
    var iframeObj = document.getElementById('iframe');
    var iframeDocument = iframeObj.contentWindow.document;
    //iframeDocument.body.innerHTML = htmlkodu;
    iframeObj.srcdoc = htmlkodu;
    document.getElementById('page').className = "darken";
    document.getElementById("page").style.display = "block";
}

document.getElementById('page').onclick = function() {
    if(document.getElementById("popup").style.display == "block") {
        document.getElementById("popup").style.display = "none";
        document.getElementById("page").style.display = "block";
        document.getElementById('page').className = "";
    }
};

</script>
</body>

</html>
