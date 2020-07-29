<!DOCTYPE HTML>
<html>
<head>
    <title>REKASYS - A/R & Sales Statistic</title>
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
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>
    <!-- ChartJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

    <style type="text/css">
        .chart-container-100 {
            width: 100%;
            height:250px
        }
        .chart-container-70 {
            width: 70%;
            height:250px
        }

        .chart-container-50 {
            width: 50%;
            height:250px
        }
    </style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<br />
<div id="mainPanel" class="easyui-panel" title="A/R & Sales Statistic" style="width:100%;height:100%;padding:5px;" data-options="footer:'#ft'">
    <table border="1" cellspacing="1" style="width: 100%">
        <tr>
            <td colspan="2" style="width: 80%;height: 250px">
                <canvas id="myLineChart"></canvas>
            </td>
            <td align="center" style="width: 20%;height: 250px">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>Bulan</th>
                        <th>OMSET (Rp)</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>January</td>
                        <td align="right"><?=number_format($dataInvMonthly["January"],0);?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>February</td>
                        <td align="right"><?=number_format($dataInvMonthly["February"],0);?></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Maret</td>
                        <td align="right"><?=number_format($dataInvMonthly["March"],0);?></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>April</td>
                        <td align="right"><?=number_format($dataInvMonthly["April"],0);?></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Mei</td>
                        <td align="right"><?=number_format($dataInvMonthly["May"],0);?></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Juni</td>
                        <td align="right"><?=number_format($dataInvMonthly["June"],0);?></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Juli</td>
                        <td align="right"><?=number_format($dataInvMonthly["July"],0);?></td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Agustus</td>
                        <td align="right"><?=number_format($dataInvMonthly["August"],0);?></td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>September</td>
                        <td align="right"><?=number_format($dataInvMonthly["September"],0);?></td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>Oktober</td>
                        <td align="right"><?=number_format($dataInvMonthly["October"],0);?></td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td>Nopember</td>
                        <td align="right"><?=number_format($dataInvMonthly["November"],0);?></td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td>Desember</td>
                        <td align="right"><?=number_format($dataInvMonthly["December"],0);?></td>
                    </tr>
                    <tr>
                        <td colspan="2">Total</td>
                        <td align="right"><?=number_format($dataInvMonthly["January"]+$dataInvMonthly["February"]+$dataInvMonthly["March"]+$dataInvMonthly["April"]+$dataInvMonthly["May"]+$dataInvMonthly["June"]+$dataInvMonthly["July"]+$dataInvMonthly["August"]+$dataInvMonthly["September"]+$dataInvMonthly["October"]+$dataInvMonthly["November"]+$dataInvMonthly["December"],0);?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;height: 250px">
                <canvas id="myCustomerChart"></canvas>
            </td>
            <td colspan="2" align="center" style="width: 30%;height: 250px">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>KODE</th>
                        <th>TOP 10 CUSTOMER</th>
                        <th>NILAI (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    while ($row = $dataOmsetCustomer->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["customer_code"]);
                        printf("<td>%s</td>",$row["customer_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["omset"],0));
                        print("</tr>");
                    }
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;height: 250px">
                <canvas id="myItemChart"></canvas>
            </td>
            <td colspan="2" align="center" style="width: 30%;height: 250px">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>KODE</th>
                        <th>TOP 10 PRODUK</th>
                        <th>NILAI (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    while ($row = $dataOmsetItem->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["item_code"]);
                        printf("<td>%s</td>",$row["item_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["nilai"],0));
                        print("</tr>");
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - 2020 PT. Reka Sistem Teknologi
</div>
<script>
    var ctxLine = document.getElementById('myLineChart').getContext('2d');
    var myLineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels  : ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            datasets: [{
                label               : 'Penjualan',
                backgroundColor     : 'rgba(139, 29, 65, 0.8)',
                borderColor			: 'rgba(139, 29, 65, 0.8)',
                border              : 1,
                fill				: false,
                data                : [<?= $dataInvoices?>]
            },
                {
                    label               : 'Penerimaan Piutang',
                    backgroundColor     : 'rgba(105, 120, 12, 0.8)',
                    borderColor			: 'rgba(105, 120, 12, 0.8)',
                    border              : 1,
                    fill				: false,
                    data                : [<?= $dataReceipts?>]
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'GRAFIK PENJUALAN TAHUN <?=$dataTahun?>'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });

    //grafik penjualan top 10 customer
    $.ajax({
        url: "<?php print($helper->site_url("ar.dashboard/top10customerdata"));?>",
        method: "GET",
        success: function(response) {
            console.log(response);
            data = JSON.parse(response);
            console.log(data);
            var label = [];
            var nilai = [];
            var warna = [];

            for(var i=0; i<data.length;i++) {
                label.push(data[i].kode);
                nilai.push(data[i].nilai);
                warna.push(data[i].warna);
            }

            var ctx = document.getElementById('myCustomerChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: label,
                    datasets: [{
                        label: 'Omset Customer',
                        backgroundColor     : warna,
                        borderColor			: 'rgba(139, 29, 65, 0.8)',
                        data: nilai
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    title: {
                        display: true,
                        text: 'TOP 10 CUSTOMER <?=$dataTahun?>'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });
        }
    });

    //grafik penjualan top 10 customer
    $.ajax({
        url: "<?php print($helper->site_url("ar.dashboard/top10itemdata"));?>",
        method: "GET",
        success: function(response) {
            console.log(response);
            data = JSON.parse(response);
            console.log(data);
            var label = [];
            var nilai = [];
            var warna = [];

            for(var i=0; i<data.length;i++) {
                label.push(data[i].kode);
                nilai.push(data[i].nilai);
                warna.push(data[i].warna);
            }

            var ctx = document.getElementById('myItemChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: label,
                    datasets: [{
                        label: 'Omset Produk',
                        backgroundColor     : warna,
                        borderColor			: 'rgba(139, 29, 65, 0.8)',
                        data: nilai
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    title: {
                        display: true,
                        text: 'TOP 10 PRODUCT <?=$dataTahun?>'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });
        }
    });
</script>
<!-- </body> -->
</html>
