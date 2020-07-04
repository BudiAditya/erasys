<!DOCTYPE HTML>
<html>
<?php
/** @var $invoice Invoice */ /** @var $sales Karyawan[] */
$counter = 0;
?>
<head>
    <title>ERASYS - Entry Nota Penjualan (Invoicing)</title>
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

    <script type="text/javascript" src="<?php print($helper->path("public/js/qz/dependencies/rsvp-3.1.0.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/qz/dependencies/sha-256.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/qz/qz-tray.js")); ?>"></script>

    <style scoped>
        .f1{
            width:200px;
        }
    </style>
    <script type="text/javascript">
        $( function() {
            var userCabId,custId,custLevel,salesId,invoiceId,userCompId,userLevel;
            userCabId = "<?php print($invoice->CabangId > 0 ? $invoice->CabangId : $userCabId);?>";
            custId = "<?php print($invoice->CustomerId);?>";
            custLevel = "<?php print($invoice->CustLevel > 0 ? $invoice->CustLevel : 0);?>";
            salesId = "<?php print($invoice->SalesId);?>";
            gudangId = "<?php print($invoice->GudangId > 0 ? $invoice->GudangId : $userCabId);?>";
            invoiceId = "<?php print($invoice->Id);?>";
            userCompId = "<?php print($invoice->EntityId > 0 ? $invoice->EntityId : $userCompId);?>";
            userLevel = "<?php print($userLevel);?>";
            var addetail = ["aItemSearch", "aItemCode", "aQty","aPrice", "aDiscFormula", "aDiscAmount", "aSubTotal", "bSaveDetail"];
            BatchFocusRegister(addetail);
            //var addmaster = ["CabangId", "InvoiceDate","CustomerId", "SalesId", "InvoiceDescs", "PaymentType","CreditTerms","BaseAmount","Disc1Pct","Disc1Amount","TaxPct","TaxAmount","OtherCosts","OtherCostsAmount","TotalAmount","bUpdate","bKembali"];
            //BatchFocusRegister(addmaster);
            $("#InvoiceDate").customDatePicker({ showOn: "focus" });
            $('#GudangId').combobox({
                onChange: function(data){
                    console.log(data);
                    gudangId = data;
                    var urz = "<?php print($helper->site_url("ar.invoice/getitempricestock_json/"));?>"+custLevel+'/'+gudangId;
                    $('#aItemSearch').combogrid('grid').datagrid('load',urz);
                }
            });

            $('#CustomerId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("master.contacts/getjson_contacts/1"));?>",
                idField:'id',
                textField:'contact_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'contact_code',title:'Kode',width:40},
                    {field:'contact_name',title:'Nama Customer',width:100},
                    {field:'address',title:'Alamat',width:100},
                    {field:'city',title:'Kota',width:60},
                    {field:'contactlevel',title:'Level',width:20},
                    {field:'credit_terms',title:'Terms',width:20}
                ]],
                onSelect: function(index,row){
                    var cid = row.id;
                    console.log(cid);
                    custId = cid;
                    var lvl = row.contactlevel;
                    console.log(lvl);
                    $('#CustLevel').val(lvl);
                    var crt = row.credit_terms;
                    console.log(crt);
                    $('#CreditTerms').val(crt);
                    var crl = row.creditlimit;
                    console.log(crl);
                    $('#CreditLimit').val(crl);
                    custLevel = lvl;
                    var urz = "<?php print($helper->site_url("ar.invoice/getitempricestock_json/"));?>"+lvl+'/'+gudangId;
                    $('#aItemSearch').combogrid('grid').datagrid('load',urz);
                }
            });

            $('#aItemSearch').combogrid({
                panelWidth:500,
                url: "<?php print($helper->site_url("ar.invoice/getitempricestock_json/"));?>"+custLevel+'/'+gudangId,
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode',width:50},
                    {field:'item_name',title:'Nama Barang',width:150},
                    {field:'satuan',title:'Satuan',width:40},
                    {field:'qty_stock',title:'Stock',width:40,align:'right'},
                    {field:'hrg_jual',title:'Harga',width:40,align:'right'}
                ]],
                onSelect: function(index,row){
                    var bid = row.item_id;
                    console.log(bid);
                    var bkode = row.item_code;
                    console.log(bkode);
                    var bnama = row.item_name;
                    console.log(bnama);
                    var satuan = row.satuan;
                    console.log(satuan);
                    var harga = row.hrg_jual;
                    console.log(harga);
                    var bqstock = row.qty_stock;
                    console.log(bqstock);
                    var hbeli = row.hrg_beli;
                    console.log(hbeli);
                    $('#aItemId').val(bid);
                    $('#aItemCode').val(bkode);
                    $('#aItemDescs').val(bnama);
                    $('#aSatuan').val(satuan);
                    $('#aPrice').val(harga);
                    $('#aQtyStock').val(bqstock);
                    $('#aDiscFormula').val(0);
                    $('#aDiscAmount').val(0);
                    $('#aItemHpp').val(hbeli);
                    if(bqstock >= 0){
                        $('#aQty').val(1);
                        //$('#aQty').focus();
                        hitDetail();
                    }else{
                        $('#aQty').val(0);
                        //$('#aQty').focus();
                        alert('Maaf, Stock tidak cukup!');
                    }
                }
            });

            $("#aItemCode").change(function(e){
                //$ret = "OK|".$setprice->ItemId.'|'.$items->Bnama.'|'.$items->Bsatbesar.'|'.$setprice->QtyStock.'|'.$setprice->HrgBeli.'|'.$setprice->HrgJual1;
                var itc = $("#aItemCode").val();
                var lvl = $("#CustLevel").val();
                var cbi = gudangId;
                var url = "<?php print($helper->site_url("ar.invoice/getitempricestock_plain/"));?>"+cbi+"/"+itc+"/"+lvl;
                if (itc != ''){
                    $.get(url, function(data, status){
                        //alert("Data: " + data + "\nStatus: " + status);
                        if (status == 'success'){
                            var dtx = data.split('|');
                            if (dtx[0] == 'OK'){
                                $('#aItemId').val(dtx[1]);
                                $('#aItemDescs').val(dtx[2]);
                                $('#aSatuan').val(dtx[3]);
                                $('#aItemHpp').val(dtx[5]);
                                $('#aPrice').val(dtx[6]);
                                $('#aDiscFormula').val(0);
                                $('#aDiscAmount').val(0);
                                $('#aQtyStock').val(Number(dtx[4]));
                                if (Number(dtx[4]) >= 0){
                                    if ($('#aQty').val()=='' || Number($('#aQty').val())==0){
                                        $('#aQty').val(1);
                                    }
                                    hitDetail();
                                    $('#aQty').focus();
                                }else{
                                    $('#aQty').val(0);
                                    alert('Maaf, Stock tidak cukup!');
                                    $('#aQty').focus();
                                }
                            }else{
                                alert('Data Harga Barang ini tidak ditemukan!');
                            }
                        }else{
                            alert('Data Harga Barang ini tidak ditemukan!');
                        }
                    });
                }
            });

            $("#aQty").change(function(e){
                var stk = Number($('#aQtyStock').val());
                var qty = $('#aQty').val();
                if (stk > 0 && stk >= qty) {
                    hitDetail();
                }else if (stk >= 0 && stk < qty){
                    if (confirm('Maaf, Stock tidak mencukupi!\nApakah tetap diproses?')){
                        hitDetail();
                    }else{
                        $('#aQty').val(0);
                    }
                }else{
                    alert('Maaf, Stock barang ini kosong!');
                    $('#aQty').val(0);
                }
            });

            $("#aPrice").change(function(e){
                hitDetail();
            });

            $("#aDiscFormula").change(function(e){
                var cds = $("#aDiscFormula").val();
                if (userLevel < 3 && cds > 0) {
                    var url = "<?php print($helper->site_url("ar.invoice/getDiscPrivileges/9"));?>";
                    $.get(url, function (data) {
                        if (Number(data) == -1) {
                            alert('Maaf, Anda tidak diijinkan mengisi discount!')
                            $("#aDiscFormula").val(0);
                            $('#aDiscAmount').val(0)
                        } else {
                            if (cds > Number(data)) {
                                alert('Maaf, Max discount yg diijinkan <= ' + data + ' %')
                                $("#aDiscFormula").val(Number(data));
                            }
                        }
                        hitDetail();
                    });
                }else{
                    hitDetail();
                }
            });

            $("#aDiscAmount").change(function(e){
                var subTotal = (Number($("#aQty").val()) * Number($("#aPrice").val()));
                var discAmount = Number($('#aDiscAmount').val());
                var totalDetail = subTotal - discAmount;
                $('#aSubTotal').val(totalDetail);
            });

            $("#Disc1Pct").change(function(e){
                //getDiscPrivileges
                var cds = $("#Disc1Pct").val();
                if (userLevel < 3 && cds > 0) {
                    var url = "<?php print($helper->site_url("ar.invoice/getDiscPrivileges/9"));?>";
                    $.get(url, function (data) {
                        if (Number(data) == -1) {
                            alert('Maaf, Anda tidak diijinkan mengisi discount!')
                            $("#Disc1Pct").val(0);
                            $("#Disc1Amount").val(0);
                        } else {
                            if (cds > Number(data)) {
                                alert('Maaf, Max discount yg diijinkan <= ' + data + ' %')
                                $("#Disc1Pct").val(Number(data));
                            }
                        }
                        hitMaster();
                    });
                }else{
                    hitMaster();
                }
            });

            $("#TaxPct").change(function(e){
                hitMaster();
            });

            $("#OtherCostsAmount").change(function(e){
                hitMaster();
            });

            $("#bUpdate").click(function(){
                //validasi master
                invoiceId = "<?php print($invoice->Id == null ? 0 : $invoice->Id);?>";
                salesId = $("#SalesId").combobox('getValue');
                gudangId = $("#GudangId").combobox('getValue');
                if (userCabId > 0 && custId > 0 && salesId > 0){
                    if (confirm('Update data invoice ini?')) {
                        var url = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>"+invoiceId;
                        //proses simpan dan update master
                        $.post(url, {
                            CabangId: userCabId,
                            GudangId: gudangId,
                            InvoiceDate: $("#InvoiceDate").val(),
                            InvoiceNo: $("#InvoiceNo").val(),
                            InvoiceDescs: $("#InvoiceDescs").val(),
                            CustomerId: custId,
                            CustLevel: $("#CustLevel").val(),
                            SalesId: salesId,
                            PaymentType: $("#PaymentType").val(),
                            CreditTerms: $("#CreditTerms").val(),
                            BaseAmount: $("#BaseAmount").val(),
                            Disc1Pct: $("#Disc1Pct").val(),
                            Disc1Amount: $("#Disc1Amount").val(),
                            TaxPct: $("#TaxPct").val(),
                            TaxAmount: $("#TaxAmount").val(),
                            OtherCosts: $("#OtherCosts").val(),
                            OtherCostsAmount: $("#OtherCostsAmount").val()
                        }).done(function(data) {
                            var rst = data.split('|');
                            if (rst[0] == 'OK') {
                                location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + invoiceId;
                            }else{
                                alert('Data Invoice gagal diupdate!');
                            }
                        });
                    }
                }else{
                    alert('Data Update tidak valid!');
                }
            });

            $("#bTambah").click(function(){
                if (confirm('Buat invoice baru?')){
                    location.href="<?php print($helper->site_url("ar.invoice/add")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan menghapus invoice ini?')){
                    location.href="<?php print($helper->site_url("ar.invoice/delete/").$invoice->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak Invoice ini?')){
                    printDirect()
                }
            });

            $("#bCetakPdf").click(function(){
                if (confirm('Cetak PDF Invoice ini?')){
                    window.open("<?php print($helper->site_url("ar.invoice/invoice_print/invoice/?&id[]=").$invoice->Id); ?>");
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ar.invoice")); ?>";
            });

            $("#aItemSearch").keyup(function(event){
                if(event.keyCode == 13){
                    $("#aQty").focus();
                }
            });

            $("#aSubTotal").keyup(function(event){
                if(event.keyCode == 13){
                    $("#bSaveDetail").click();
                }
            });

            $("#bSaveDetail").click(function(){
                //validasi master
                invoiceId = "<?php print($invoice->Id == null ? 0 : $invoice->Id);?>";
                salesId = $("#SalesId").combobox('getValue');
                gudangId = $("#GudangId").combobox('getValue');
                var aitd = Number($('#aItemId').val());
                var aqty = Number($('#aQty').val());
                var astt = Number($('#aSubTotal').val());
                var ahpp = Number($('#aItemHpp').val());
                var oke = true;
                if ((userCabId > 0 && custId > 0 && salesId > 0) && (aitd > 0 && aqty > 0 && astt > 0)){
                    // check credit limit disini
                    var creditlimit = Number($('#CreditLimit').val());
                    // credit limit = 0 berarti tidak terbatas limitnya
                    if ($("#PaymentType").val()==1 && creditlimit > 0) {
                        var urx = "<?php print($helper->site_url("master.contacts/get_credittodate/")); ?>" + custId;
                        $.get(urx, function (data) {
                            var creditodate = data;
                            if ((creditlimit - creditodate - astt) <= 0){
                                alert('Maaf, Transaksi ini sudah melebihi limit kredit!');
                                oke = false;
                                location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + invoiceId;
                            }else{
                                if (confirm('Apakah data input sudah benar?')) {
                                    var url = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>" + invoiceId;
                                    //proses simpan dan update master
                                    $.post(url, {
                                        CabangId: userCabId,
                                        GudangId: gudangId,
                                        InvoiceDate: $("#InvoiceDate").val(),
                                        InvoiceNo: $("#InvoiceNo").val(),
                                        InvoiceDescs: $("#InvoiceDescs").val(),
                                        CustomerId: custId,
                                        CustLevel: $("#CustLevel").val(),
                                        SalesId: salesId,
                                        PaymentType: $("#PaymentType").val(),
                                        CreditTerms: $("#CreditTerms").val(),
                                        BaseAmount: $("#BaseAmount").val(),
                                        Disc1Pct: $("#Disc1Pct").val(),
                                        Disc1Amount: $("#Disc1Amount").val(),
                                        TaxPct: $("#TaxPct").val(),
                                        TaxAmount: $("#TaxAmount").val(),
                                        OtherCosts: $("#OtherCosts").val(),
                                        OtherCostsAmount: $("#OtherCostsAmount").val()
                                    }).done(function (data) {
                                        var rst = data.split('|');
                                        if (rst[0] == 'OK') {
                                            //validasi detail
                                            var aivi = rst[2];
                                            if (aitd > 0 && aqty > 0 && astt > 0) {
                                                //proses simpan detail
                                                var urz = "<?php print($helper->site_url("ar.invoice/add_detail/")); ?>" + aivi;
                                                $.post(urz, {
                                                    aItemId: aitd,
                                                    aQty: aqty,
                                                    aPrice: Number($('#aPrice').val()),
                                                    aDiscFormula: $('#aDiscFormula').val(),
                                                    aDiscAmount: $('#aDiscAmount').val(),
                                                    aSubTotal: astt,
                                                    aItemHpp: ahpp
                                                }).done(function (data) {
                                                    var rsx = data.split('|');
                                                    if (rsx[0] == 'OK') {
                                                        location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + aivi;
                                                    } else {
                                                        alert(data);
                                                    }
                                                });
                                            } else {
                                                alert('Data Detail tidak valid!');
                                                location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + aivi;
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }else {
                        if (confirm('Apakah data input sudah benar?')) {
                            var url = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>" + invoiceId;
                            //proses simpan dan update master
                            $.post(url, {
                                CabangId: userCabId,
                                GudangId: gudangId,
                                InvoiceDate: $("#InvoiceDate").val(),
                                InvoiceNo: $("#InvoiceNo").val(),
                                InvoiceDescs: $("#InvoiceDescs").val(),
                                CustomerId: custId,
                                CustLevel: $("#CustLevel").val(),
                                SalesId: salesId,
                                PaymentType: $("#PaymentType").val(),
                                CreditTerms: $("#CreditTerms").val(),
                                BaseAmount: $("#BaseAmount").val(),
                                Disc1Pct: $("#Disc1Pct").val(),
                                Disc1Amount: $("#Disc1Amount").val(),
                                TaxPct: $("#TaxPct").val(),
                                TaxAmount: $("#TaxAmount").val(),
                                OtherCosts: $("#OtherCosts").val(),
                                OtherCostsAmount: $("#OtherCostsAmount").val()
                            }).done(function (data) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    //validasi detail
                                    var aivi = rst[2];
                                    if (aitd > 0 && aqty > 0 && astt > 0) {
                                        //proses simpan detail
                                        var urz = "<?php print($helper->site_url("ar.invoice/add_detail/")); ?>" + aivi;
                                        $.post(urz, {
                                            aItemId: aitd,
                                            aQty: aqty,
                                            aPrice: Number($('#aPrice').val()),
                                            aDiscFormula: $('#aDiscFormula').val(),
                                            aDiscAmount: $('#aDiscAmount').val(),
                                            aSubTotal: astt,
                                            aItemHpp: ahpp
                                        }).done(function (data) {
                                            var rsx = data.split('|');
                                            if (rsx[0] == 'OK') {
                                                location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + aivi;
                                            } else {
                                                alert(data);
                                            }
                                        });
                                    } else {
                                        alert('Data Detail tidak valid!');
                                        location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + aivi;
                                    }
                                }
                            });
                        }
                    }
                }else{
                    alert('Data Input tidak valid!');
                }
            });
        });

        function hitDetail(){
            var subTotal = (Number($("#aQty").val()) * Number($("#aPrice").val()));
            var discAmount = Math.round(Number($("#aDiscFormula").val())/100 * subTotal);
            var totalDetail = subTotal - discAmount;
            $('#aDiscAmount').val(discAmount);
            $('#aSubTotal').val(totalDetail);
        }

        function hitMaster(){
            var bam = Number($("#BaseAmount").val().replace(/,/g,""));
            var dpc = Number($("#Disc1Pct").val().replace(/,/g,""));
            var tpc = Number($("#TaxPct").val().replace(/,/g,""));
            var oca = Number($("#OtherCostsAmount").val().replace(/,/g,""));
            var dam = 0;
            var tam = 0;
            var dpp = 0;
            if (bam > 0 && dpc > 0 ){
                dam = Math.round(bam * (dpc/100),0);
                $("#Disc1Amount").val(dam);
            }else{
                $("#Disc1Amount").val(0);
            }
            dpp = bam - dam;
            $("#DppAmount").val(dpp);
            if (dpp > 0 && tpc > 0 ){
                tam = Math.round(dpp * (tpc/100),0);
                $("#TaxAmount").val(tam);
            }else{
                $("#TaxAmount").val(0);
            }
            $("#TotalAmount").val(dpp+tam+oca);
        }
        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var kode = dtx[1];
            var barang = dtx[2];
            var urx = '<?php print($helper->site_url("ar.invoice/delete_detail/"));?>'+id;
            if (confirm('Hapus Data Detail Barang \nKode: '+kode+ '\nNama: '+barang+' ?')) {
                $.get(urx, function(data){
                    alert(data);
                    location.reload();
                });
            }
        }
        function printDirect() {
            qz.websocket.connect().then(function() {
                //alert("Not Connected!");
            });
            $.get('<?php print($helper->site_url("ar.invoice/printdirect/").$invoice->Id."/".$userCabRpm); ?>', function(ajson, status){
                var config = qz.configs.create("<?php print($userCabRpn);?>");
                var data = JSON.parse(ajson);
                //var datx = ['\x1B' + '\x40','\x1B' + '\x67','\x1B' + '\x47'];  //cetak double-strike
                //var datx = ['\x1B' + '\x40','\x1B' + '\x67','\x1B' + '\x43' + '\x1E']; //cetak biasa 15 cpi 30 lines
                var datx = ['\x1B' + '\x40','\x1B' + '\x67']; //cetak biasa 15 cpi
                for ( var i = 0; i < data.length; i++ ) {
                    datx.push(data[i]+'\n');
                }
                //datx.push('\x0C'); // form feed
                qz.print(config, datx).catch(function(e) { console.error(e); });
            });
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
$bpdf = base_url('public/images/button/').'pdf.png';
?>
<br />
<div id="p" class="easyui-panel" title="Entry Nota Penjualan" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($invoice->CabangCode != null ? $invoice->CabangCode : $userCabCode); ?>" disabled/></td>
            <td>Tanggal</td>
            <td><input type="text" size="12" id="InvoiceDate" name="InvoiceDate" value="<?php print($invoice->FormatInvoiceDate(JS_DATE));?>" <?php print($itemsCount == 0 ? 'required' : 'readonly');?>/></td>
            <td>No. Invoice</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '-'); ?>" readonly/></td>
        </tr>
        <tr>
            <td>Customer</td>
            <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" style="width: 250px" value="<?php print($invoice->CustomerId); ?>" autofocus/>
                <input type="hidden" id="CabangId" name="CabangId" value="<?php print($invoice->CabangId == null ? $userCabId : $invoice->CabangId);?>"/>
                <input type="hidden" id="CustLevel" name="CustLevel" value="<?php print($invoice->CustLevel);?>"/>
                <input type="hidden" id="CreditLimit" name="CreditLimit" value="<?php print($creditLimit);?>"/>
            </td>
            <td>Salesman</td>
            <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px">
                    <option value="">- Pilih Salesman -</option>
                    <?php
                    foreach ($sales as $salesman) {
                        if ($salesman->Id == $invoice->SalesId) {
                            printf('<option value="%d" selected="selected">%s</option>', $salesman->Id, $salesman->Nama);
                        } else {
                            printf('<option value="%d">%s</option>', $salesman->Id, $salesman->Nama);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="InvoiceStatus" name="InvoiceStatus" style="width: 150px">
                    <option value="0" <?php print($invoice->InvoiceStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($invoice->InvoiceStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($invoice->InvoiceStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                    <option value="3" <?php print($invoice->InvoiceStatus == 3 ? 'selected="selected"' : '');?>>3 - Terbayar</option>
                    <option value="4" <?php print($invoice->InvoiceStatus == 4 ? 'selected="selected"' : '');?>>4 - Batal</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td><b><input type="text" class="f1 easyui-textbox" id="InvoiceDescs" name="InvoiceDescs" style="width: 250px" value="<?php print($invoice->InvoiceDescs != null ? $invoice->InvoiceDescs : '-'); ?>" required/></b></td>
            <td>Gudang</td>
            <td>
                <?php if ($itemsCount == 0){?>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px">
                <?php }else{ ?>
                    <input type="hidden" name="GudangId" id="GudangId" value="<?php print($invoice->GudangId);?>"/>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" disabled>
                <?php } ?>
                    <option value="">- Pilih Gudang -</option>
                        <?php
                        foreach ($gudangs as $gudang) {
                            if ($invoice->GudangId > 0){
                                if ($gudang->Id == $invoice->GudangId) {
                                    printf('<option value="%d" selected="selected">%s</option>', $gudang->Id, $gudang->Kode);
                                }else {
                                    printf('<option value="%d">%s</option>', $gudang->Id, $gudang->Kode);
                                }
                            }else{
                                if ($gudang->Id == $userCabId) {
                                    printf('<option value="%d" selected="selected">%s</option>', $gudang->Id, $gudang->Kode);
                                }else {
                                    printf('<option value="%d">%s</option>', $gudang->Id, $gudang->Kode);
                                }
                            }
                        }
                        ?>
                    </select>
            </td>
            <td>Cara Bayar</td>
            <td><select id="PaymentType" name="PaymentType" required>
                    <option value="1" <?php print($invoice->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                    <option value="0" <?php print($invoice->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                </select>
                &nbsp
                Kredit
                <input type="text" id="CreditTerms" name="CreditTerms" size="2" maxlength="5" value="<?php print($invoice->CreditTerms != null ? $invoice->CreditTerms : 0); ?>" style="text-align: right" required/>&nbsphari</td>
        </tr>
        <tr>
            <td colspan="7">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="9">DETAIL BARANG YANG DIJUAL</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Disc(%)</th>
                        <th>Diskon</th>
                        <th>Jumlah</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="right">Cari Data Barang -->></td>
                        <td colspan="9"><input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:500px"/></td>
                    </tr>
                    <tr class="bold">
                        <td>&nbsp;</td>
                        <td>
                            <input type="text" id="aItemCode" name="aItemCode" size="15" value="" required/>
                            <input type="hidden" id="aItemId" name="aItemId" value="0"/>
                            <input type="hidden" id="aId" name="aId" value="0"/>
                            <input type="hidden" id="aQtyStock" name="aQtyStock" value="0"/>
                            <input type="hidden" id="aItemHpp" name="aItemHpp" value="0"/>
                        </td>
                        <td>
                            <input type="text" id="aItemDescs" name="aItemDescs" size="38" value="" disabled/>
                        </td>
                        <td>
                            <input class="right" type="text" id="aQty" name="aQty" size="5" value="0"/>
                        </td>
                        <td>
                            <input type="text" id="aSatuan" name="aSatuan" size="5" value="" disabled/>
                        </td>
                        <td>
                            <input class="right" type="text" id="aPrice" name="aPrice" size="10" value="0"/>
                        </td>
                        <td>
                            <input class="right" type="text" id="aDiscFormula" name="aDiscFormula" size="3" value="0"/>
                        </td>
                        <td>
                            <input class="right" type="text" id="aDiscAmount" name="aDiscAmount" size="10" value="0"/>
                        </td>
                        <td>
                            <input class="right" type="text" id="aSubTotal" name="aSubTotal" style="width:150px" value="0" readonly/>
                        </td>
                        <td class='center'><?php printf('<img src="%s" alt="Simpan" title="Simpan" id="bSaveDetail" style="cursor: pointer;"/>',$badd);?></td>
                    </tr>
                    </tr>
                    <?php
                    $counter = 0;
                    $total = 0;
                    $dta = null;
                    $dtx = null;
                    foreach($invoice->Details as $idx => $detail) {
                        $counter++;
                        print("<tr class='bold'>");
                        printf('<td class="right">%s.</td>', $counter);
                        printf('<td>%s</td>', $detail->ItemCode);
                        printf('<td>%s</td>', $detail->ItemDescs);
                        printf('<td class="right">%s</td>', number_format($detail->Qty,0));
                        printf('<td>%s</td>', $detail->SatBesar);
                        printf('<td class="right">%s</td>', number_format($detail->Price,0));
                        printf('<td class="right">%s</td>', $detail->DiscFormula);
                        printf('<td class="right">%s</td>', number_format($detail->DiscAmount,0));
                        printf('<td class="right">%s</td>', number_format($detail->SubTotal,0));
                        print("<td class='center'>");
                        $dta = addslashes($detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs));
                        printf('&nbsp<img src="%s" alt="Hapus barang" title="Hapus barang" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                        print("</td>");
                        print("</tr>");
                        $total += $detail->SubTotal;
                    }
                    ?>
                    <tr>
                        <td colspan="8" align="right">Sub Total :</td>
                        <td><input type="text" class="right bold" style="width: 150px" id="BaseAmount" name="BaseMount" value="<?php print($invoice->BaseAmount != null ? number_format($invoice->BaseAmount,0) : 0); ?>" readonly/></td>
                        <?php if ($acl->CheckUserAccess("ar.invoice", "add")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Simpan Data" title="Simpan data master" id="bUpdate" style="cursor: pointer;"/>',$bsubmit);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="8" align="right">Diskon (%) :</td>
                        <td><input type="text" class="right bold" style="width: 30px" id="Disc1Pct" name="Disc1Pct" value="<?php print($invoice->Disc1Pct != null ? number_format($invoice->Disc1Pct,1) : 0); ?>"/>
                            <input type="text" class="right bold" style="width: 110px" id="Disc1Amount" name="Disc1Amount" value="<?php print($invoice->Disc1Amount != null ? number_format($invoice->Disc1Amount,0) : 0); ?>" readonly/></td>
                        <?php if ($acl->CheckUserAccess("ar.invoice", "add")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Invoice Baru" title="Buat invoice baru" id="bTambah" style="cursor: pointer;"/>',$baddnew);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="8" align="right">D P P :</td>
                        <td><input type="text" class="right bold" style="width: 150px" id="DppAmount" name="DppAmount" value="<?php print(number_format($invoice->BaseAmount - $invoice->Disc1Amount,0)); ?>" readonly/></td>
                        <?php if ($acl->CheckUserAccess("ar.invoice", "delete")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Hapus Invoice" title="Proses hapus invoice" id="bHapus" style="cursor: pointer;"/>',$bdelete);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="8" align="right">Pajak (%) :</td>
                        <td><input type="text" class="right bold" style="width: 30px" id="TaxPct" name="TaxPct" value="<?php print($invoice->TaxPct != null ? $invoice->TaxPct : 0); ?>"/>
                            <input type="text" class="right bold" style="width: 110px" id="TaxAmount" name="TaxAmount" value="<?php print($invoice->TaxAmount != null ? number_format($invoice->TaxAmount,0) : 0); ?>"/></td>
                        <?php if ($acl->CheckUserAccess("ar.invoice", "print")) { ?>
                            <td class='center'><?php printf('<img src="%s" id="bCetakPdf" alt="Cetak PDF Invoice" title="Proses cetak PDF invoice" style="cursor: pointer;"/>',$bpdf);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="2" align="right">Biaya Lain :</td>
                        <td colspan="6"><b><input type="text" class="bold" id="OtherCosts" name="OtherCosts" size="60" maxlength="150" value="<?php print($invoice->OtherCosts != null ? $invoice->OtherCosts : '-'); ?>"/></b></td>
                        <td><input type="text" class="right bold" style="width: 150px" id="OtherCostsAmount" name="OtherCostsAmount" value="<?php print($invoice->OtherCostsAmount != null ? number_format($invoice->OtherCostsAmount,0) : 0); ?>"/></td>
                        <?php if ($acl->CheckUserAccess("ar.invoice", "print")) { ?>
                            <td class='center'><?php printf('<img src="%s" id="bCetak" alt="Cetak Invoice" title="Proses cetak invoice" style="cursor: pointer;"/>',$bcetak);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="8" align="right">Grand Total :</td>
                        <td><input type="text" class="right bold" style="width: 150px;" id="TotalAmount" name="TotalAmount" value="<?php print($invoice->TotalAmount != null ? number_format($invoice->TotalAmount,0) : 0); ?>" readonly/></td>
                        <td class='center'><?php printf('<img src="%s" id="bKembali" alt="Daftar Invoice" title="Kembali ke daftar invoice" style="cursor: pointer;"/>',$bkembali);?></td>
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
