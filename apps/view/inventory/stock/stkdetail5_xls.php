<?php
// Skrip berikut ini adalah skrip yang bertugas untuk meng-export data tadi ke excell
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=rekap_mutasi_hpp.xls");
?>
<h3>MUTASI HPP PERSEDIAAN</h3>
<table cellpadding="2" cellspacing="1" border="1">
    <?php
        print('
        <tr valign="middle" class="bold">
            <th rowspan="3">No.</th>
            <th rowspan="3" nowrap>Kode Barang</th>
            <th rowspan="3" nowrap>Nama Barang</th>
            <th rowspan="3">Satuan</th>
            <th rowspan="2" colspan="3">Stock Awal</th>
            <th colspan="9">Barang Masuk</th>
            <th colspan="9">Barang Keluar</th>
            <th rowspan="2" colspan="3">Stock Akhir</th>
        </tr>
        <tr valign="middle" class="bold">
            <th colspan="3">Pembelian</th>
            <th colspan="3">Retur</th>
            <th colspan="3">Saldo</th>
            <th colspan="3">Penjualan</th>
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
                    print('</tr>');
                }
            }
        }
    ?>
</table>
