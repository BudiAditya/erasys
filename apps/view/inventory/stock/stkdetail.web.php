<!DOCTYPE HTML>
<html>
<head>
    <title>Erasys - Stock Barang Per Periode</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //var elements = ["CabangId", "OpDate","ItemType", "ItemId", "PartId", "OpQty", "OpPrice"];
            //BatchFocusRegister(elements);
            $("#startDate").customDatePicker({ showOn: "focus" });
            $("#endDate").customDatePicker({ showOn: "focus" });
        });

    </script>
</head>
<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
    <legend><b>Stock Barang Per Periode</b></legend>
    <form id="frm" action="<?php print($helper->site_url("inventory.stock/stkdetail")); ?>" method="post">
        <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <td>Cabang</td>
                <td>
                    <select name="cabangId" class="text2" id="cabangId" required>
                        <?php if($userLevel > 3){
                            foreach ($cabangs as $cab) {
                                if ($cab->Id == $cabangId) {
                                    printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                                } else {
                                    printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                                }
                            }
                            ?>
                        <?php }else{
                            printf('<option value="%d">%s - %s</option>', $userCabId, $userCabCode, $userCabName);
                        }?>
                    </select>
                </td>
                <td>Dari Tgl :</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="startDate" name="startDate" value="<?php print(is_int($startDate) ? date(JS_DATE,$startDate) : null);?>" /></td>
                <td>S/D Tgl :</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="endDate" name="endDate" value="<?php print(is_int($endDate) ? date(JS_DATE,$endDate) : null);?>" /></td>
                <td>Jenis :</td>
                <td><select id="rType" name="rType">
                        <option value="1" <?php print($rType == 1 ? 'Selected="Selected"' : '');?>>1 - Detail</option>
                        <option value="2" <?php print($rType == 2 ? 'Selected="Selected"' : '');?>>2 - Rekapitulasi</option>
                        <option value="3" <?php print($rType == 3 ? 'Selected="Selected"' : '');?>>3 - Rekap + Hrg Beli</option>
                        <option value="4" <?php print($rType == 4 ? 'Selected="Selected"' : '');?>>4 - Rekap + Hrg Jual</option>
                        <option value="5" <?php print($rType == 5 ? 'Selected="Selected"' : '');?>>5 - Rekapitulasi HPP</option>
                    </select>
                </td>
                <td>Output :</td>
                <td><select id="outPut" name="outPut">
                        <option value="0" <?php print($outPut == 0 ? 'Selected="Selected"' : '');?>>HTML</option>
                        <option value="1" <?php print($outPut == 1 ? 'Selected="Selected"' : '');?>>Excel</option>
                    </select>
                </td>
                <td colspan="4" class="left">
                    <button type="submit">Proses</button>
                    <a href="<?php print($helper->site_url("inventory.stock")); ?>">Daftar Stock</a>
                </td>
            </tr>
        </table>
        <br>
        <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
            <?php
            $rtp = $rType;
            if ($rtp < 5) {
                if ($rtp == 1) { ?>
                    <tr class="bold">
                        <th rowspan="2">No.</th>
                        <th rowspan="2">Kode Barang</th>
                        <th rowspan="2">Nama Barang</th>
                        <th rowspan="2">Satuan</th>
                        <th rowspan="2">Awal</th>
                        <th colspan="4">Stock Masuk</th>
                        <th colspan="4">Stock Keluar</th>
                        <th rowspan="2">Koreksi</th>
                        <th rowspan="2">Saldo</th>
                    </tr>
                    <?php
                } else { ?>
                    <tr class="bold">
                        <th>No.</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Awal</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Koreksi</th>
                        <th>Saldo</th>
                        <?php
                        if ($rtp == 3) {
                            print('<th>*Hrg Beli*</th>');
                            print('<th>Nilai Stock</th>');
                        } elseif ($rtp == 4) {
                            print('<th>*Hrg Jual*</th>');
                            print('<th>Nilai Stock</th>');
                        }
                        ?>
                    </tr>
                    <?php
                }
                if ($rtp == 1) { ?>
                    <tr>
                        <th>Pembelian</th>
                        <th>Produksi</th>
                        <th>Kiriman</th>
                        <th>Retur</th>
                        <th>Penjualan</th>
                        <th>Produksi</th>
                        <th>Dikirim</th>
                        <th>Retur</th>
                    </tr>
                    <?php
                }
                if ($mstock != null) {
                    $nmr = 0;
                    $awl = 0;
                    $mbl = 0;
                    $mxi = 0;
                    $mrj = 0;
                    $kjl = 0;
                    $kxo = 0;
                    $krb = 0;
                    $kor = 0;
                    $ain = 0;
                    $aot = 0;
                    $sld = 0;
                    $ssl = 0;
                    $tqt = 0;
                    $nsb = 0;
                    $nsj = 0;
                    while ($row = $mstock->FetchAssoc()) {
                        $tqt = ($row["sAwal"] + $row["sBeli"] + $row["sAsyin"] + $row["sXin"] + $row["sRjual"]) + ($row["sJual"] + $row["sAsyout"] + $row["sXout"] + $row["sRbeli"]) + $row["sKoreksi"];
                        if ($tqt <> 0) {
                            $nmr++;
                            print('<tr>');
                            printf('<td class="center">%d</td>', $nmr);
                            printf('<td>%s</td>', $row["item_code"]);
                            printf('<td>%s</td>', $row["item_name"]);
                            printf('<td>%s</td>', $row["satuan"]);
                            printf('<td class="right">%s</td>', decFormat($row["sAwal"], 0));
                            if ($rtp == 1) {
                                printf('<td class="right">%s</td>', $row["sBeli"] > 0 ? decFormat($row["sBeli"]) : '');
                                printf('<td class="right">%s</td>', $row["sAsyin"] > 0 ? decFormat($row["sAsyin"]) : '');
                                printf('<td class="right">%s</td>', $row["sXin"] > 0 ? decFormat($row["sXin"]) : '');
                                printf('<td class="right">%s</td>', $row["sRjual"] > 0 ? decFormat($row["sRjual"]) : '');
                                printf('<td class="right">%s</td>', $row["sJual"] > 0 ? decFormat($row["sJual"]) : '');
                                printf('<td class="right">%s</td>', $row["sAsyout"] > 0 ? decFormat($row["sAsyout"]) : '');
                                printf('<td class="right">%s</td>', $row["sXout"] > 0 ? decFormat($row["sXout"]) : '');
                                printf('<td class="right">%s</td>', $row["sRbeli"] > 0 ? decFormat($row["sRbeli"]) : '');
                            } else {
                                printf('<td class="right">%s</td>', $row["sBeli"] + $row["sAsyin"] + $row["sXin"] + $row["sRjual"] > 0 ? decFormat($row["sBeli"] + $row["sAsyin"] + $row["sXin"] + $row["sRjual"]) : '');
                                printf('<td class="right">%s</td>', $row["sJual"] + $row["sAsyout"] + $row["sXout"] + $row["sRbeli"] > 0 ? decFormat($row["sJual"] + $row["sAsyout"] + $row["sXout"] + $row["sRbeli"]) : '');
                            }
                            printf('<td class="right">%s</td>', $row["sKoreksi"] <> 0 ? decFormat($row["sKoreksi"]) : '');
                            $sld = ($row["sAwal"] + $row["sBeli"] + $row["sAsyin"] + $row["sXin"] + $row["sRjual"]) - ($row["sJual"] + $row["sAsyout"] + $row["sXout"] + $row["sRbeli"]) + $row["sKoreksi"];
                            printf('<td class="right">%s</td>', decFormat($sld));
                            if ($rtp == 3) {
                                printf('<td class="right">%s</td>', $row["hrg_beli"] > 0 ? decFormat($row["hrg_beli"]) : 0);
                                printf('<td class="right">%s</td>', round($row["hrg_beli"] * $sld, 0) > 0 ? decFormat(round($row["hrg_beli"] * $sld, 0)) : 0);
                            } elseif ($rtp == 4) {
                                printf('<td class="right">%s</td>', $row["hrg_jual"] > 0 ? decFormat($row["hrg_jual"]) : 0);
                                printf('<td class="right">%s</td>', round($row["hrg_jual"] * $sld, 0) > 0 ? decFormat(round($row["hrg_jual"] * $sld, 0)) : 0);
                            }
                            print('</tr>');
                            $awl += $row["sAwal"];
                            $mbl += $row["sBeli"];
                            $mxi += $row["sXin"];
                            $mrj += $row["sRjual"];
                            $kjl += $row["sJual"];
                            $kxo += $row["sXout"];
                            $krb += $row["sRbeli"];
                            $kor += $row["sKoreksi"];
                            $ain += $row["sAsyin"];
                            $aot += $row["sAsyout"];
                            $nsb += round($row["hrg_beli"] * $sld, 2);
                            $nsj += round($row["hrg_jual"] * $sld, 2);
                            $ssl += $sld;
                        }
                    }
                    printf('<tr>');
                    printf('<td class="bold right" colspan="4">Total Mutasi</td>');
                    printf('<td class="bold right">%s</td>', decFormat($awl, 2));
                    if ($rtp == 1) {
                        printf('<td class="bold right">%s</td>', decFormat($mbl, 2));
                        printf('<td class="bold right">%s</td>', decFormat($ain, 2));
                        printf('<td class="bold right">%s</td>', decFormat($mxi, 2));
                        printf('<td class="bold right">%s</td>', decFormat($mrj, 2));
                        printf('<td class="bold right">%s</td>', decFormat($kjl, 2));
                        printf('<td class="bold right">%s</td>', decFormat($aot, 2));
                        printf('<td class="bold right">%s</td>', decFormat($kxo, 2));
                        printf('<td class="bold right">%s</td>', decFormat($krb, 2));
                    } else {
                        printf('<td class="bold right">%s</td>', decFormat($mbl + $ain + $mxi + $mrj, 2));
                        printf('<td class="bold right">%s</td>', decFormat($kjl + $aot + $kxo + $krb, 2));
                    }
                    printf('<td class="bold right">%s</td>', decFormat($kor, 2));
                    printf('<td class="bold right">%s</td>', decFormat($ssl, 2));
                    if ($rtp == 3) {
                        print('<td>&nbsp;</td>');
                        printf('<td class="bold right">%s</td>', decFormat($nsb, 2));
                    } elseif ($rtp == 4) {
                        print('<td>&nbsp;</td>');
                        printf('<td class="bold right">%s</td>', decFormat($nsj, 2));
                    }
                    printf('</tr>');
                }
            }else {
                print('
                <tr valign="middle" class="bold">
                    <th rowspan="3">No.</th>
                    <th rowspan="3" nowrap>Kode Barang</th>
                    <th rowspan="3" nowrap>Nama Barang</th>
                    <th rowspan="3">Satuan</th>
                    <th rowspan="2" colspan="3">Stock Awal</th>
                    <th colspan="15">Barang Masuk</th>
                    <th colspan="15">Barang Keluar</th>
                    <th rowspan="2" colspan="3">Adjustment</th>
                    <th rowspan="2" colspan="3">Stock Akhir</th>
                </tr>
                <tr valign="middle" class="bold">
                    <th colspan="3">Pembelian</th>
                    <th colspan="3">Produksi</th>
                    <th colspan="3">Transfer</th>
                    <th colspan="3">Retur</th>
                    <th colspan="3">Saldo</th>
                    <th colspan="3">Penjualan</th>
                    <th colspan="3">Produksi</th>
                    <th colspan="3">Transfer</th>
                    <th colspan="3">Retur</th>
                    <th colspan="3">Saldo</th>
                </tr>
                <tr valign="middle" class="bold">
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                </tr>
                ');
                if ($mstock != null) {
                    $nmr = 0;
                    $awl = 0;
                    $mbl = 0;
                    $mxi = 0;
                    $mrj = 0;
                    $kjl = 0;
                    $kxo = 0;
                    $krb = 0;
                    $kor = 0;
                    $ain = 0;
                    $aot = 0;
                    $sld = 0;
                    $ssl = 0;
                    $tqt = 0;
                    $nsb = 0;
                    $nsj = 0;
                    $qsa = 0;
                    $nsa = 0;
                    while ($row = $mstock->FetchAssoc()) {
                        $tqt = ($row["sAwal"] + $row["sBeli"] + $row["sAsyin"] + $row["sXin"] + $row["sRjual"]) + ($row["sJual"] + $row["sAsyout"] + $row["sXout"] + $row["sRbeli"]) + $row["sKoreksi"];
                        if ($tqt <> 0) {
                            $nmr++;
                            print('<tr>');
                            printf('<td class="center">%d</td>', $nmr);
                            printf('<td nowrap>%s</td>', $row["item_code"]);
                            printf('<td nowrap>%s</td>', $row["item_name"]);
                            printf('<td>%s</td>', $row["satuan"]);
                            if ($row["sAwal"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sAwal"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsAwal"] / $row["sAwal"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsAwal"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sBeli"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sBeli"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsBeli"] / $row["sBeli"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsBeli"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sAsyin"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sAsyin"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsAsyin"] / $row["sAsyin"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsAsyin"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sXin"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sXin"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsXin"] / $row["sXin"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsXin"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sRbeli"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sRbeli"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsRbeli"] / $row["sRbeli"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsRbeli"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sBeli"] - $row["sRbeli"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sBeli"] - $row["sRbeli"], 2));
                                printf('<td class="right">%s</td>', number_format(round(($row["nsBeli"] - $row["nsRbeli"]) / ($row["sBeli"] - $row["sRbeli"]), 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsBeli"] - $row["nsRbeli"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sJual"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sJual"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsJual"] / $row["sJual"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsJual"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sAsyout"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sAsyout"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsAsyout"] / $row["sAsyout"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsAsyout"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sXout"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sXout"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsXout"] / $row["sXout"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsXout"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sRjual"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sRjual"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsRjual"] / $row["sRjual"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsRjual"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sJual"] - $row["sRjual"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sJual"] - $row["sRjual"], 2));
                                printf('<td class="right">%s</td>', number_format(round(($row["nsJual"] - $row["nsRjual"]) / ($row["sJual"] - $row["sRjual"]), 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsJual"] - $row["nsRjual"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            if ($row["sKoreksi"] <> 0) {
                                printf('<td class="right">%s</td>', number_format($row["sKoreksi"], 2));
                                printf('<td class="right">%s</td>', number_format(round($row["nsKoreksi"] / $row["sKoreksi"], 2), 2));
                                printf('<td class="right">%s</td>', number_format($row["nsKoreksi"], 2));
                            } else {
                                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                            }
                            $qsa = ($row["sAwal"]+$row["sBeli"]+$row["sAsyin"]+$row["sXin"]-$row["sRbeli"])-($row["sJual"]+$row["sXout"]+$row["sAsyout"]-$row["sRjual"])+$row["sKoreksi"];
                            $nsa = ($row["nsAwal"]+$row["nsBeli"]+$row["nsAsyin"]+$row["nsXin"]-$row["nsRbeli"])-($row["nsJual"]+$row["nsXout"]+$row["nsAsyout"]-$row["nsRjual"])+$row["nsKoreksi"];
                            printf('<td class="right">%s</td>', number_format($qsa, 2));
                            if ($nsa <> 0 && $qsa <> 0) {
                                printf('<td class="right">%s</td>', number_format(round($nsa / $qsa, 2), 2));
                            }else{
                                print('<td class="right">0.00</td>');
                            }
                            printf('<td class="right">%s</td>', number_format($nsa, 2));
                            print('</tr>');
                        }
                    }
                }
            }
            ?>
        </table>
    </form>
</fieldset>
<!-- </body> -->
</html>
