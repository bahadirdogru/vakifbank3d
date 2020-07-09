<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php error_reporting(E_ALL ^ E_NOTICE); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head id="Head1" runat="server">
    <title></title>
    <style>
        .mainDiv
        {
            width:360px;
            height:300px;
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
            width:175px;
            float:left;
            padding:4px 0 0 0;
        }
    </style>
</head>
<body>
   <form id="form1" runat="server" method="post" action="TempSucessUrl.aspx">
    <div class="mainDiv">
        <div>
            <span class="innerSpan">Status</span>
            <span><input type="text" id="Status" name="Status" value="<?=$_POST["Status"];?>" /></span>
         </div>
        <div>
            <span class="innerSpan">Merchant Id</span>
            <span><input type="text" id="MerchantId" name="MerchantId" value="<?=$_POST["MerchantId"];?>" /></span>
         </div>
         <div>
            <span class="innerSpan">VerifyEnrollmentRequest Id</span>
            <span><input type="text" id="VerifyEnrollmentRequestId" name="VerifyEnrollmentRequestId" value="<?=$_POST["VerifyEnrollmentRequestId"];?>" /></span>
         </div>
         <div>
            <span class="innerSpan">Purchase Amount</span>
            <span><input type="text" id="PurchAmount" name="PurchAmount" value="<?=$_POST["PurchAmount"];?>" /></span>
         </div>
          <div>
            <span class="innerSpan">Xid</span>
            <span><input type="text" id="Xid" name="Xid" value="<?=$_POST["Xid"];?>" /></span>
         </div>
          <div>
            <span class="innerSpan">InstallmentCount</span>
            <span><input type="text" id="InstallmentCount" name="InstallmentCount" value="<?=$_POST["InstallmentCount"];?>" /></span>
         </div>
           <div>
            <span class="innerSpan">Session Info</span>
            <span><input type="text" id="SessionInfo" name="SessionInfo" value="<?=$_POST["SessionInfo"];?>" /></span>
         </div>
         <div>
            <span class="innerSpan">Purchase Currency</span>
            <span><input type="text" id="PurchCurrency" name="PurchCurrency" value="<?=$_POST["PurchCurrency"];?>" /></span>
         </div>
         <div>
            <span class="innerSpan">Pan</span>
            <span><input type="text" id="Pan" name="Pan" value="<?=$_POST["Pan"];?>"/></span>
         </div>
          <div>
            <span class="innerSpan">Expire Date</span>
            <span><input type="text" id="ExpiryDate" name="ExpiryDate" value="<?=$_POST["Expiry"];?>" /></span>
         </div>
         <div>
            <span class="innerSpan">Eci</span>
            <span><input type="text" id="Eci" name="Eci" value="<?=$_POST["Eci"];?>" /></span>
         </div>
         <div>
            <span class="innerSpan">Cavv</span>
            <span><input type="text" id="Cavv" name="Cavv" value="<?=$_POST["Cavv"];?>" /></span>
         </div>
    </div>
    </form>
</body>
</html>
