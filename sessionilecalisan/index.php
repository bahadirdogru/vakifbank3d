<?php session_start()?>
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
</style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<form id="sanalposform" method="POST" action="islem.php">
        <tr>
            <td width="100" height="25" class="Basliklar">Banka Seçiniz</td>
            <td width="1" class="AltCizgi">:</td>
            <td class="AltCizgi">
                <select name="PosBanka">
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
                <select name="KartMarka">
                    <option value="100">VISA</option>
                    <option value="200">MASTER CARD</option>
                    <option value="300">TROY</option>
                    <option value="400">AMERICAN EXPRESS</option>
                </select>
            </td>
        </tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart No</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
			<input class="input_yeni" type="text" name="KartNo" size="49" value="xxxxxxxxxxxxxxxx"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Son K.Tarihi</td>
			<td width="1" class="AltCizgi"></td>
			<td class="AltCizgi">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td width="33">
					<input class="input_yeni" type="text" name="KartAy" size="5" value="xx"></td>
					<td style="padding-left: 5px">
					<input class="input_yeni" type="text" name="KartYil" size="12" value="xxxx"></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Kart Cvv</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi"><input class="input_yeni" type="text" name="KartCvv" size="12" value="xxx"></td>
		</tr>
		<tr>
			<td width="100" height="25" class="Basliklar">Tutar</td>
			<td width="1" class="AltCizgi">:</td>
			<td class="AltCizgi">
            <input class="input_yeni" type="text" name="Tutar" size="12" value="1.00"></td>
		</tr>
		<tr>
            <td width="100" height="25" class="Basliklar">ParaKodu</td>
            <td width="1" class="AltCizgi">:</td>
            <td class="AltCizgi">
            <select name="ParaKodu">
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
                <select name="Taksit">
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
			<input class="input_yeni" type="text" name="IslemNo" size="50" value="<?= session_id() ?>"></td>
		</tr>
		<tr>
			<td><input class="input_yeni" type="hidden" name="IslemTipi" size="50" value="Sale"></td>
		</tr>
        <tr>
            <td width="100" height="25" class="Basliklar">3D Secure ile Ödeme Yap</td>
            <td width="1" class="AltCizgi">:</td>
            <td width="1" class="AltCizgi"><!--<input type="checkbox" id="checkbox3d" name="checkbox3d" value="yes" checked> -->
                <!-- Checkbox valuesu sadece yes ise yollanıyor. onun yerine hidden inputbox koyduk. javascript ile içeriğini değiştirdik. -->
                <select name="secure3d">
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

</script>
</body>

</html>
