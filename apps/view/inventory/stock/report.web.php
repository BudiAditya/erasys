<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<head>
	<title>REKASYS | Rekapitulasi Stock Produk</title>
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

        $(document).ready(function() {

            $('#SupplierCode').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("master.contacts/getjson_contacts/2"));?>",
                idField:'contact_code',
                textField:'contact_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'contact_code',title:'Kode',width:30},
                    {field:'contact_name',title:'Nama Supplier',width:100},
                    {field:'address',title:'Alamat',width:100},
                    {field:'city',title:'Kota',width:60}
                ]]
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
            bpurchase-bottom:1px solid #ccc;
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
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr class="left">
            <th colspan="4"><b>REKAPITULASI STOCK BARANG: <?php print($company_name);?></b></th>
            <th align="right">Jenis Laporan:</th>
            <th>
                <select id="ReportType" name="ReportType" required>
                    <option value="0" <?php print($userReportType == 0 ? 'selected="selected"' : '');?>>Tanpa PO & SO</option>
                    <option value="1" <?php print($userReportType == 1 ? 'selected="selected"' : '');?>>Termasuk PO saja</option>
                    <option value="2" <?php print($userReportType == 2 ? 'selected="selected"' : '');?>>Termasuk SO saja</option>
                    <option value="3" <?php print($userReportType == 3 ? 'selected="selected"' : '');?>>Termasuk PO & SO</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Cabang/Gudang</th>
            <th>Jenis Produk</th>
            <th>Supplier</th>
            <th>Type Harga</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" required>
                <?php
                    printf('<option value="%d">%s</option>', $userCabId, $userCabCode);
                ?>
                </select>
            </td>
            <td>
                <select name="JenisProduk" class="text2" id="JenisProduk" required>
                   <option value="-">- Semua Jenis Produk-</option>
                    <?php
                    foreach ($jenis as $jns) {
                        if ($jns->JnsBarang == $userJenisProduk) {
                            printf('<option value="%s" selected="selected">%s</option>', $jns->JnsBarang, $jns->JnsBarang);
                        } else {
                            printf('<option value="%s">%s</option>', $jns->JnsBarang, $jns->JnsBarang);
                        }
                    }
                    ?>
                </select>
            </td>
            <td><input class="easyui-combogrid" id="SupplierCode" name="SupplierCode" value="<?php print($userSupplierCode);?>" style="width: 250px"/></td>
            <td>
                <select id="TypeHarga" name="TypeHarga" required>
                    <option value="0" <?php print($userTypeHarga == 0 ? 'selected="selected"' : '');?>>Tanpa Harga</option>
                    <option value="1" <?php print($userTypeHarga == 1 ? 'selected="selected"' : '');?>>Harga Beli/HPP</option>
                    <option value="2" <?php print($userTypeHarga == 2 ? 'selected="selected"' : '');?>>Harga Jual</option>
                </select>
            </td>
            <td>
                <select id="Output" name="Output" required>
                    <option value="0" <?php print($output == 0 ? 'selected="selected"' : '');?>>0 - Web Html</option>
                    <option value="1" <?php print($output == 1 ? 'selected="selected"' : '');?>>1 - Excel</option>
                </select>
            </td>
            <td><button type="submit" formaction="<?php print($helper->site_url("inventory.stock/report")); ?>"><b>Proses</b></button>
                <a href="<?php print($helper->site_url("inventory.stock")); ?>">Daftar Stock</a>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<?php  if ($reports != null){
    $ket = null;
    if ($scabangCode != null){
        $ket.= 'Cabang/Gudang: '.$scabangCode;
    }else{
        $ket.= 'Semua Cabang/Gudang';
    }
    if ($userJenisProduk != '-'){
        $ket.= ' - Jenis Produk : '.$userJenisProduk;
    }
    print('<h2>Rekapitulasi Stock Produk</h2>');
    if ($ket != null){
        printf('<h3>%s</h3>',$ket);
    }
?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Kode</th>
            <th>Nama Produk</th>
            <th>Satuan</th>
            <th>Stock</th>
            <?php
            if($userTypeHarga == 1){
                print('<th>Harga Beli</th>');
                print('<th>Nilai Stock</th>');
            }elseif($userTypeHarga == 2){
                print('<th>Harga Jual</th>');
                print('<th>Nilai Stock</th>');
            }
            if ($userSupplierCode <> null){
                print('<th>Supplier</th>');
            }
            if($userReportType == 1){
                print('<th>PO QTY</th>');
                print('<th>Stock + PO</th>');
            }elseif ($userReportType == 2){
                print('<th>SO QTY</th>');
                print('<th>Stock - SO</th>');
            }elseif ($userReportType == 3) {
                print('<th>PO QTY</th>');
                print('<th>SO QTY</th>');
                print('<th>Stock + PO - SO</th>');
            }
            ?>
        </tr>
        <?php
            $nmr = 1;
            $tOtal = 0;
            while ($row = $reports->FetchAssoc()) {
                print("<tr valign='Top'>");
                printf("<td>%s</td>",$nmr);
                printf("<td nowrap='nowrap'>%s</td>",$row["item_code"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["bnama"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["bsatbesar"]);
                printf("<td align='right'>%s</td>",decFormat($row["qty_stock"],2));
                if ($userTypeHarga == 1) {
                    printf("<td align='right'>%s</td>", decFormat($row["hrg_beli"], 0));
                    printf("<td align='right'>%s</td>", decFormat(round($row["qty_stock"] * $row["hrg_beli"], 0), 0));
                    $tOtal+= round($row["qty_stock"] * $row["hrg_beli"],0);
                }elseif($userTypeHarga == 2){
                    printf("<td align='right'>%s</td>", decFormat($row["hrg_jual"], 0));
                    printf("<td align='right'>%s</td>", decFormat(round($row["qty_stock"] * $row["hrg_jual"], 0), 0));
                    $tOtal+= round($row["qty_stock"] * $row["hrg_jual"],0);
                }
                if ($userSupplierCode <> null){
                    printf("<td nowrap='nowrap'>%s</td>",$row["supplier_name"]);
                }
                if($userReportType == 1){
                    printf("<td align='right'>%s</td>",decFormat($row["po_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["qty_stock"] + $row["po_qty"],2));
                }elseif ($userReportType == 2){
                    printf("<td align='right'>%s</td>",decFormat($row["so_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["qty_stock"] - $row["so_qty"],2));
                }elseif ($userReportType == 3) {
                    printf("<td align='right'>%s</td>",decFormat($row["po_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["so_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["qty_stock"] + $row["po_qty"] - $row["so_qty"],2));
                }
                print("</tr>");
                $nmr++;
            }
        print("<tr>");
        if ($userTypeHarga > 0) {
            print("<td colspan='6' align='right'>Total Nilai Stock&nbsp;</td>");
            printf("<td align='right'>%s</td>", decFormat($tOtal, 0));
            if ($userSupplierCode <> null) {
                print('<td>&nbsp</td>');
            }
            if ($userReportType == 1) {
                print('<td colspan="2">&nbsp;</td>');
            } elseif ($userReportType == 2) {
                print('<td colspan="2">&nbsp;</td>');
            } elseif ($userReportType == 3) {
                print('<td colspan="3">&nbsp;</td>');
            }
        }
        print("</tr>");
        ?>
    </table>
<!-- end web report -->
<?php } ?>
</body>
</html>
