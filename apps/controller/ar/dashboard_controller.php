<?php
class DashboardController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ar/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function index() {
        //get invoice summary by month data
        $invoice = new Invoice();
        $dataInvoices = $invoice->GetInvoiceSumByYear($this->trxYear,$this->userCabangId);
        $this->Set("dataInvoices",$dataInvoices);
        $dataInvMonthly = $invoice->GetDataInvoiceSumByMonth($this->trxYear,$this->userCabangId);
        $this->Set("dataInvMonthly",$dataInvMonthly);
        $dataReceipts = $invoice->GetReceiptSumByYear($this->trxYear,$this->userCabangId);
        $this->Set("dataReceipts",$dataReceipts);
        $this->Set("dataTahun",$this->trxYear);
        //get data customer omset
        $loader = $invoice->LoadTop10Customer($this->userCabangId,$this->trxYear);
        $this->Set("dataOmsetCustomer",$loader);
        //get data item omset
        $loader = $invoice->LoadTop10Item($this->userCabangId,$this->trxYear);
        $this->Set("dataOmsetItem",$loader);
    }

    public function top10customerdata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonTop10Customer($this->userCabangId,$this->trxYear);
        echo json_encode($data);
    }

    public function top10itemdata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonTop10Item($this->userCabangId,$this->trxYear);
        echo json_encode($data);
    }
}


// End of File: ar.dashboard_controller.php
