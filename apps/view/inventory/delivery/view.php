<!DOCTYPE HTML>
<html>
<?php
/** @var $delivery Delivery */ ?>
<head>
    <title>ERASYS - Entry Delivery Order (D/O)</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

<style scoped>
    .f1{
        width:200px;
    }
</style>
<script type="text/javascript">

    $( function() {
        //var addetail = ["aItemCode", "aQty","aPrice", "aDiscFormula", "aDiscAmount", "aSubTotal"];
        //BatchFocusRegister(addetail);
        //var addmaster = ["CabangId", "ArRreturnDate","CustomerId", "SalesId", "ArRreturnDescs", "PaymentType","CreditTerms","BaseAmount","Disc1Pct","Disc1Amount","TaxPct","TaxAmount","OtherCosts","OtherCostsAmount","TotalAmount","bUpdate","bKembali"];
        //BatchFocusRegister(addmaster);
        //$("#RjDate").customDatePicker({ showOn: "focus" });

        $('#CustomerId').combogrid({
            panelWidth:600,
            url: "<?php print($helper->site_url("master.contacts/getjson_contacts/1"));?>",
            idField:'id',
            textField:'contact_name',
            mode:'remote',
            fitColumns:true,
            columns:[[
                {field:'contact_code',title:'Kode',width:30},
                {field:'contact_name',title:'Nama Customer',width:100},
                {field:'address',title:'Alamat',width:100},
                {field:'city',title:'Kota',width:60}
            ]]
        });

        $("#bEdit").click(function(){
            if (confirm('Ubah data D/O ini?')){
                location.href="<?php print($helper->site_url("inventory.delivery/edit/").$delivery->Id); ?>";
            }
        });

        $("#bTambah").click(function(){
            if (confirm('Buat D/O baru?')){
                location.href="<?php print($helper->site_url("inventory.delivery/add")); ?>";
            }
        });

        $("#bHapus").click(function(){
            if (confirm('Anda yakin akam menghapus D/O ini?')){
                location.href="<?php print($helper->site_url("inventory.delivery/delete/").$delivery->Id); ?>";
            }
        });

        $("#bKembali").click(function(){
            location.href="<?php print($helper->site_url("inventory.delivery")); ?>";
        });
    });
</script>
<style type="text/css">
    #fd{
        margin:0;
        padding:5px 10px;
    }
    .ftitle{
        font-size:14px;
        font-weight:bold;
        padding:5px 0;
        margin-bottom:10px;
        border-bottom:1px solid #ccc;
    }
    .fitem{
        margin-bottom:5px;
    }
    .fitem label{
        display:inline-block;
        width:100px;
    }
    .numberbox .textbox-text{
        text-align: right;
        color: blue;
    }
</style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php }
$badd = base_url('public/images/button/').'add.png';
$bsave = base_url('public/images/button/').'accept.png';
$bcancel = base_url('public/images/button/').'cancel.png';
$bview = base_url('public/images/button/').'view.png';
$bedit = base_url('public/images/button/').'edit.png';
$bdelete = base_url('public/images/button/').'delete.png';
$bclose = base_url('public/images/button/').'close.png';
$bsearch = base_url('public/images/button/').'search.png';
$bkembali = base_url('public/images/button/').'back.png';
$bcetak = base_url('public/images/button/').'printer.png';
$bsubmit = base_url('public/images/button/').'ok.png';
$baddnew = base_url('public/images/button/').'create_new.png';
?>
<br />
<div id="p" class="easyui-panel" title="View Delivery Order (D/O)" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($delivery->CabangCode != null ? $delivery->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($delivery->CabangId == null ? $userCabId : $delivery->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="10" id="DoDate" name="DoDate" value="<?php print($delivery->FormatDoDate(JS_DATE));?>" readonly/></td>
                <td>No. D/O</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="DoNo" name="DoNo" value="<?php print($delivery->DoNo != null ? $delivery->DoNo : '-'); ?>" readonly/></td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="DoStatus1" name="DoStatus1" style="width: 100px" disabled>
                        <option value="0" <?php print($delivery->DoStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($delivery->DoStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($delivery->DoStatus == 2 ? 'selected="selected"' : '');?>>2 - Closed</option>
                        <option value="3" <?php print($delivery->DoStatus == 3 ? 'selected="selected"' : '');?>>3 - Void</option>
                    </select>
                    <input type="hidden" id="DoStatus" name="DoStatus" value="<?php print($delivery->DoStatus);?>"/>
                </td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" value="<?=$delivery->CustomerId;?>" style="width: 250px" readonly/></td>
                <td>No. Plat</td>
                <td><input class="easyui-textbox" id="VehicleNumber" name="VehicleNumber" value="<?=$delivery->VehicleNumber;?>" style="width: 100px" readonly/></td>
                <td>Sopir</td>
                <td><input class="easyui-textbox" id="DriverName" name="DriverName" value="<?=$delivery->DriverName;?>" style="width: 150px" readonly/></td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="3"><b><input type="text" class="easyui-textbox" id="DoDescs" name="DoDescs" style="width: 420px" value="<?php print($delivery->DoDescs != null ? $delivery->DoDescs : '-'); ?>" readonly/></b></td>
                <td>Expedisi</td>
                <td><select class="easyui-combobox" id="ExpeditionId" name="ExpeditionId" style="width: 150px" disabled>
                        <option value="0"></option>
                        <?php
                        /** @var $expeditions Expedition[] */
                        foreach ($expeditions as $expedisi){
                            if ($delivery->ExpeditionId == $expedisi->Id){
                                printf('<option value="%d" selected="selected">%s</option>',$expedisi->Id,$expedisi->ExpName);
                            }else{
                                printf('<option value="%d">%s</option>',$expedisi->Id,$expedisi->ExpName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="7">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th colspan="9">DETAIL BARANG YANG DIKIRIM</th>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>Ex. Invoice No.</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Qty Order</th>
                            <th>Qty Kirim</th>
                            <th>Satuan</th>
                        </tr>
                        <?php
                        $counter = 0;
                        $total = 0;
                        $dta = null;
                        foreach($delivery->Details as $idx => $detail) {
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf('<td>%s</td>', $detail->ExInvoiceNo);
                            printf('<td>%s</td>', $detail->ItemCode);
                            printf('<td>%s</td>', $detail->ItemDescs);
                            printf('<td class="right">%s</td>', number_format($detail->QtyOrder,0));
                            printf('<td class="right">%s</td>', number_format($detail->QtyDelivered,0));
                            printf('<td>%s</td>', $detail->SatBesar);
                            print("</tr>");
                        }
                        ?>
                        <tr>
                            <td colspan="7" class="right">
                                <?php
                                if ($acl->CheckUserAccess("inventory.delivery", "edit")) {
                                    printf('<img src="%s" alt="Edit Data" title="Edit Data" id="bEdit" style="cursor: pointer;"/> &nbsp',$bedit);
                                }
                                if ($acl->CheckUserAccess("inventory.delivery", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("inventory.delivery", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                printf('<img src="%s" id="bKembali" alt="Daftar D/O" title="Kembali ke daftar D/O" style="cursor: pointer;"/>',$bkembali);
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2016 - 2020  <a href='http://rekasys.com'><b>Rekasys Inc</b></a>
</div>
<!-- </body> -->
</html>
