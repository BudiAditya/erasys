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
            //var addmaster = ["CabangId", "DoDate","CustomerId", "DoDescs", "btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);
            $("#DoDate").customDatePicker({ showOn: "focus" });

            $('#CustomerId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("master.contacts/getjson_contacts/1/".$userCompId));?>",
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

            $('#aExInvoiceNo').combogrid({
                panelWidth:250,
                url: "<?php print($helper->site_url("ar.invoice/getjson_invoicedolists/".$delivery->CabangId.'/'.$delivery->CustomerId));?>",
                idField:'invoice_no',
                textField:'invoice_no',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'invoice_no',title:'No. Invoice',width:50},
                    {field:'invoice_date',title:'Tanggal',width:40}
                ]],
                onSelect: function(index,row){
                    var ivi = row.id;
                    console.log(ivi);
                    $("#aExInvoiceId").val(ivi);
                    var urz = "<?php print($helper->site_url("ar.invoice/getjson_invoicedoitems/"));?>"+ivi;
                    $('#aItemSearch').combogrid('grid').datagrid('load',urz);
                }
            });

            $('#aItemSearch').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.invoice/getjson_invoicedoitems/0"));?>",
                idField:'item_id',
                textField:'item_id',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode',width:30},
                    {field:'item_descs',title:'Nama Barang',width:70},
                    {field:'qty_jual',title:'QTY',width:20,align:'right'},
                    {field:'satuan',title:'Satuan',width:20}
                ]],
                onSelect: function(index,row){
                    var idi = row.id;
                    console.log(idi);
                    var iti = row.item_id;
                    console.log(iti);
                    var itc = row.item_code;
                    console.log(itc);
                    var itd = row.item_descs;
                    console.log(itd);
                    var qtj = row.qty_jual;
                    console.log(qtj);
                    var sat = row.satuan;
                    console.log(sat);
                    $('#aExInvDetailId').val(idi);
                    $('#aItemId').val(iti);
                    $('#aItemCode').val(itc);
                    $('#aItemDescs').val(itd);
                    $('#aSatuan').val(sat);
                    $('#aQtyOrder').val(qtj);
                    $('#aQtyDelivered').val('0');
                }
            });

            $("#bAdDetail").click(function(e){
                $('#aExInvDetailId').val(0);
                $('#aItemId').val('');
                $('#aItemCode').val('');
                $('#aItemDescs').val('');
                $('#aSatuan').val('');
                $('#aQtyOrder').val(0);
                $('#aQtyDelivered').val('0');
                $('#aExInvoiceNo').val(0);
                newItem();
            });                        

            $("#bUpdate").click(function(){
                if (confirm('Apakah data input sudah benar?')){
                    $('#frmMaster').submit();
                }
            });

            $("#bTambah").click(function(){
                if (confirm('Buat Retur Penjualan baru?')){
                    location.href="<?php print($helper->site_url("inventory.delivery/add")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan membatalkan D/O ini?')){
                    location.href="<?php print($helper->site_url("inventory.delivery/void/").$delivery->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak bukti retur ini?')){
                    location.href = "<?php print($helper->site_url("inventory.delivery/print_pdf/").$delivery->Id);?>";
                }
            });

            $("#bKembali").click(function(){
                location.href = "<?php print($helper->site_url("inventory.delivery")); ?>";
            });

            $("#aQtyDelivered").change(function(e){
                var qty = Number($('#aQtyOrder').val());
                var qtr = Number($('#aQtyDelivered').val());
                if (qtr > 0){
                    if (qtr > qty){
                        alert('Qty Retur tidak boleh melebihi Qty penjualan!');
                        $('#aQtyDelivered').val(qty);
                    }
                }
            });
        });
       
        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var kode = dtx[2];
            var barang = dtx[3];
            var urx = '<?php print($helper->site_url("inventory.delivery/delete_detail/"));?>'+id;
            if (confirm('Hapus Data Detail Barang \nKode: '+kode+ '\nNama: '+barang+' ?')) {
                $.get(urx, function(data){
                    alert(data);
                    location.reload();
                });
            }
        }        

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Barang yang dikirim');
            $('#fm').form('clear');
            url= "<?php print($helper->site_url("inventory.delivery/add_detail/".$delivery->Id));?>";
            $('#aItemCode').focus();
        }

        function saveDetail(){
            var rqty = Number($('#aQtyDelivered').val());
            if (rqty > 0){
                $('#fm').form('submit',{
                    url: url,
                    onSubmit: function(){
                        return $(this).form('validate');
                    },
                    success: function(result){
                        var result = eval('('+result+')');
                        if (result.errorMsg){
                            $.messager.show({
                                title: 'Error',
                                msg: result.errorMsg
                            });
                        } else {
                            location.reload();
                            $('#dlg').dialog('close');		// close the dialog
                        }
                    }
                });
            }else{
                alert('Data tidak valid!');
            }
        }

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
<div id="p" class="easyui-panel" title="Entry Delivery Order (D/O)" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("inventory.delivery/edit/".$delivery->Id)); ?>" method="post">
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
                <td><select class="easyui-combobox" id="ExpeditionId" name="ExpeditionId" style="width: 150px">
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
                            <th colspan="7">DETAIL BARANG YANG DIKIRIM</th>
                            <th rowspan="2">Action</th>
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
                            print("<td class='center'>");
                            $dta = addslashes($detail->Id.'|'.$detail->ExInvoiceNo.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs));
                            printf('&nbsp<img src="%s" alt="Hapus barang" title="Hapus barang" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                            print("</td>");
                            print("</tr>");
                        }
                        ?>
                        <tr>
                            <td colspan="7" align="right">&nbsp;</td>
                            <td class='center'><?php printf('<img src="%s" alt="Tambah Barang" title="Tambah barang" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        </tr>
                        <tr>
                            <td colspan="8" class="right">
                                <?php
                                if ($acl->CheckUserAccess("inventory.delivery", "edit")) {
                                    printf('<img src="%s" alt="Simpan Data" title="Simpan data master" id="bUpdate" style="cursor: pointer;"/> &nbsp',$bsubmit);
                                }
                                if ($acl->CheckUserAccess("inventory.delivery", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("inventory.delivery", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                if ($acl->CheckUserAccess("inventory.delivery", "print")) {
                                    printf('<img src="%s" alt="Cetak Bukti" title="Cetak Receipt" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                                }
                                printf('<img src="%s" id="bKembali" alt="Daftar Return" title="Kembali ke daftar D/O" style="cursor: pointer;"/>',$bkembali);
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2016 - 2020  <a href='http://rekasys.com'><b>Rekasys Inc</b></a>
</div>
<!-- Form Add ArRD/O Detail -->
<div id="dlg" class="easyui-dialog" style="width:1000px;height:150px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="font-size: 12px;font-family: tahoma;width: 100%">
            <tr>
                <th>Ex. Invoice No.</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty Order</th>
                <th>Qty Kirim</th>
                <th>Satuan</th>
            </tr>
            <tr>
                <td>
                    <input type="text" id="aExInvoiceNo" name="aExInvoiceNo" style="width: 150px;" value="" required/>
                    <input type="hidden" id="aExInvoiceId" name="aExInvoiceId" value="0"/>
                    <input type="hidden" id="aExInvDetailId" name="aExInvDetailId" value="0"/>
                </td>
                <td>
                    <input type="text" id="aItemCode" name="aItemCode" size="15" value="" required/>
                    <input id="aItemSearch" name="aItemSearch" style="width: 20px"/>
                    <input type="hidden" id="aItemId" name="aItemId" value="0"/>
                    <input type="hidden" id="aId" name="aId" value="0"/>
                </td>
                <td>
                    <input type="text" id="aItemDescs" name="aItemDescs" size="38" value="" readonly/>
                </td>
                <td>
                    <input class="right" type="text" id="aQtyOrder" name="aQtyOrder" size="5" value="0" readonly/>
                </td>
                <td>
                    <input class="right" type="text" id="aQtyDelivered" name="aQtyDelivered" size="5" value="0"/>
                </td>
                <td>
                    <input type="text" id="aSatuan" name="aSatuan" size="5" value="" readonly/>
                </td>
            </tr>
        </table>
    </form>
    <br>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Simpan</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Batal</a>
</div>
<!-- </body> -->
</html>
