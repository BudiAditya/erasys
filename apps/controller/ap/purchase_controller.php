<?php
class PurchaseController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $isGlobalItems;

    protected function Initialize() {
        require_once(MODEL . "ap/purchase.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->isGlobalItems = $this->persistence->LoadState("is_global_items");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();
        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        //$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Entity", "width" => 30);
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 80);
        $settings["columns"][] = array("name" => "a.grn_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.grn_no", "display" => "No. Purchase", "width" => 80);
        $settings["columns"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier", "width" => 200);
        $settings["columns"][] = array("name" => "a.grn_descs", "display" => "Keterangan", "width" => 150);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Cara Bayar", "width" => 50);
        $settings["columns"][] = array("name" => "format(a.total_amount,0)", "display" => "Pembelian", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.return_amount,0)", "display" => "Retur", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.paid_amount,0)", "display" => "Terbayar", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.balance_amount,0)", "display" => "OutStanding", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.due_date", "display" => "JTP", "width" => 60);
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 60);
        $settings["columns"][] = array("name" => "if(a.grn_status = 0,'Draft',if(a.grn_status = 1,'Posted',if(a.grn_status = 2,'Approved','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.grn_no", "display" => "No. Purchase");
        $settings["filters"][] = array("name" => "a.grn_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier");
        $settings["filters"][] = array("name" => "if(a.grn_status = 0,'Draft',if(a.grn_status = 3,'Void','Posted'))", "display" => "Status");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_purchase"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Pembelian Barang";

            if ($acl->CheckUserAccess("ap.purchase", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.purchase/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.purchase", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.purchase/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Purchase terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.purchase", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ap.purchase/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.purchase", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.purchase/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Purchase terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.purchase", "print")) {
                $settings["actions"][] = array("Text" => "Print Bukti", "Url" => "ap.purchase/grn_print","Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "Cetak Bukti Pembelian yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.purchase", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.purchase/report", "Class" => "bt_report", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "Statistik", "Url" => "ap.dashboard", "Class" => "bt_report", "ReqId" => 0);
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.purchase", "approve")) {
                $settings["actions"][] = array("Text" => "Approve Pembelian", "Url" => "ap.purchase/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Pembelian terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data pembelian yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approve", "Url" => "ap.purchase/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Pembelian terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data pembelian yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }

        } else {
            $settings["from"] = "vw_ap_purchase_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.grn_date) = ".$this->trxYear." And month(a.grn_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add($purchaseId = 0) {
        require_once(MODEL . "master/cabang.php");
        //require_once(MODEL . "master/contacts.php");
        $loader = null;
        $log = new UserAdmin();
        $acl = AclManager::GetInstance();
		$purchase = new Purchase();
		if ($purchaseId > 0){
            $purchase = $purchase->LoadById($purchaseId);
            if($purchase == null){
                $this->persistence->SaveState("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ap.purchase");
            }
            if($purchase->GrnStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -TERBAYAR-",$purchase->GrnNo));
                redirect_url("ap.purchase");
            }
            if($purchase->GrnStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -VOID-",$purchase->GrnNo));
                redirect_url("ap.purchase/view/".$purchaseId);
            }
        }
        // load details
        $purchase->LoadDetails();
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        if ($cabang->CabType == 2){
            $this->persistence->SaveState("error", "Maaf Cabang %s dalam mode Gudang, tidak boleh digunakan untuk transaksi!",$cabang->Kode);
            redirect_url("ap.purchase");
        }
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Cabang();
        $gudangs = $loader->LoadByType($this->userCompanyId,1,"<>");
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("purchase", $purchase);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->GrnItemsCount($purchaseId));
	}

    public function proses_master($purchaseId = 0) {
        $acl = AclManager::GetInstance();
        $loader = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        if (count($this->postData) > 0) {
            $purchase->Id = $purchaseId;
            $purchase->CabangId = $this->GetPostValue("CabangId");
            $purchase->GudangId = $this->GetPostValue("GudangId");
            $purchase->GrnDate = date('Y-m-d', strtotime($this->GetPostValue("GrnDate")));
            $purchase->ReceiptDate = date('Y-m-d', strtotime($this->GetPostValue("ReceiptDate")));
            $purchase->GrnNo = $this->GetPostValue("GrnNo");
            $purchase->GrnDescs = $this->GetPostValue("GrnDescs");
            $purchase->SupplierId = $this->GetPostValue("SupplierId");
            $purchase->SalesName = $this->GetPostValue("SalesName");
            if ($this->GetPostValue("GrnStatus") == null || $this->GetPostValue("GrnStatus") == 0){
                $purchase->GrnStatus = 1;
            }else{
                $purchase->GrnStatus = $this->GetPostValue("GrnStatus");
            }
            $purchase->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->GetPostValue("PaymentType") == null){
                $purchase->PaymentType = 0;
            }else{
                $purchase->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $purchase->CreditTerms = 0;
            }else{
                $purchase->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            $purchase->Disc1Pct = $this->GetPostValue("Disc1Pct");
            $purchase->TaxPct = $this->GetPostValue("TaxPct");
            $purchase->OtherCosts = $this->GetPostValue("OtherCosts");
            $purchase->OtherCostsAmount = str_replace(",","",$this->GetPostValue("OtherCostsAmount"));
            if ($purchaseId == 0) {
                $purchase->GrnNo = $purchase->GetGrnDocNo();
                $rs = $purchase->Insert();
                if ($rs != 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Add New Purchase',$purchase->GrnNo,'Failed');
                    printf("ER|A|%d",$purchase->Id);
                }else {
                    $log = $log->UserActivityWriter($this->userCabangId, 'ap.purchase', 'Add New Purchase', $purchase->GrnNo, 'Success');
                    printf("OK|A|%d|%s",$purchase->Id,$purchase->GrnNo);
                }
            }else{
                $rs = $purchase->Update($purchase->Id);
                if ($rs != 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Update Purchase',$purchase->GrnNo,'Failed');
                    printf("ER|U|%d",$purchase->Id);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Update Purchase',$purchase->GrnNo,'Success');
                    printf("OK|U|%d|%s",$purchase->Id,$purchase->GrnNo);
                }
            }
        }else{
            printf("ER|X|%d",$purchaseId);
        }
    }

	public function view($purchaseId = null) {
        require_once(MODEL . "master/cabang.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $purchase = new Purchase();
        $purchase = $purchase->LoadById($purchaseId);
        if($purchase == null){
            $this->persistence->SaveState("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.purchase");
        }
        // load details
        $purchase->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Cabang();
        $gudangs = $loader->LoadByType($this->userCompanyId,1,"<>");
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("purchase", $purchase);
        $this->Set("acl", $acl);
	}

    public function delete($purchaseId) {
        // Cek datanya
        $ExPoNo = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        $purchase = $purchase->FindById($purchaseId);
        if($purchase == null){
            $this->Set("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.purchase");
        }
        // periksa status po
        $ExPoNo = $purchase->ExPoNo;
        if($purchase->GrnStatus < 2){
            $purchase->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($purchase->Delete($purchaseId,$ExPoNo) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Purchase No: %s sudah berhasil dihapus", $purchase->GrnNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s gagal dihapus", $purchase->GrnNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s sudah berstatus -TERBAYAR-", $purchase->GrnNo));
        }
        redirect_url("ap.purchase");
    }

    public function void($purchaseId) {
        // Cek datanya
        $ExPoNo = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        $purchase = $purchase->FindById($purchaseId);
        if($purchase == null){
            $this->Set("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.purchase");
        }
        if($purchase->GrnStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -VOID-",$purchase->GrnNo));
            redirect_url("ap.purchase");
        }
        $ExPoNo = $purchase->ExPoNo;
        // periksa status po
        if($purchase->GrnStatus < 2){
            $purchase->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($purchase->Void($purchaseId,$ExPoNo) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Purchase No: %s sudah berhasil batalkan", $purchase->GrnNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s gagal dibatalkan", $purchase->GrnNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s sudah berstatus -TERBAYAR-", $purchase->GrnNo));
        }
        redirect_url("ap.purchase");
    }

	public function add_detail($purchaseId = null) {
        require_once(MODEL . "master/items.php");
        $log = new UserAdmin();
        $purchase = new Purchase($purchaseId);
        $purchasedetail = new PurchaseDetail();
        $purchasedetail->GrnId = $purchaseId;
        $purchasedetail->GrnNo = $purchase->GrnNo;
        $purchasedetail->CabangId = $purchase->CabangId;
        $items = null;
        if (count($this->postData) > 0) {
            $purchasedetail->ItemId = $this->GetPostValue("aItemId");
            $purchasedetail->ExPoNo = $this->GetPostValue("aExPoNo");
            $purchasedetail->PurchaseQty = $this->GetPostValue("aQty");
            $purchasedetail->ReturnQty = 0;
            $purchasedetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $purchasedetail->DiscFormula = 0;
            }else{
                $purchasedetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $purchasedetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $purchasedetail->SubTotal = $this->GetPostValue("aSubTotal");
            $purchasedetail->IsFree = $this->GetPostValue("aIsFree");
            $items = new Items($purchasedetail->ItemId);
            if ($items != null){
                $purchasedetail->ItemCode = $items->Bkode;
                $purchasedetail->ItemDescs = $items->Bnama;
                $purchasedetail->Lqty = 0;
                $purchasedetail->Sqty = 0;
                // insert ke table
                $rs = $purchasedetail->Insert()== 1;
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Add Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Success');
                    print('OK|Proses simpan data berhasil!');
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Add Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Failed');
                    print('ER|Gagal proses simpan data!');
                }
            }else{
                print('ER|Data barang tidak ditemukan!');
            }
        }
	}

    public function edit_detail($purchaseId = null) {
        require_once(MODEL . "master/items.php");
        $log = new UserAdmin();
        $purchase = new Purchase($purchaseId);
        $purchasedetail = new PurchaseDetail();
        $purchasedetail->GrnId = $purchaseId;
        $purchasedetail->GrnNo = $purchase->GrnNo;
        $purchasedetail->CabangId = $purchase->CabangId;
        $items = null;
        if (count($this->postData) > 0) {
            $purchasedetail->Id = $this->GetPostValue("aId");
            $purchasedetail->ExPoNo = $this->GetPostValue("aExPoNo");
            $purchasedetail->ItemId = $this->GetPostValue("aItemId");
            $purchasedetail->PurchaseQty = $this->GetPostValue("aQty");
            $purchasedetail->ReturnQty = $this->GetPostValue("rQty");
            $purchasedetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $purchasedetail->DiscFormula = 0;
            }else{
                $purchasedetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $purchasedetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $purchasedetail->SubTotal = $this->GetPostValue("aSubTotal");
            $purchasedetail->IsFree = $this->GetPostValue("aIsFree");
            $items = new Items($purchasedetail->ItemId);
            if ($items != null){
                $purchasedetail->ItemCode = $items->Bkode;
                $purchasedetail->ItemDescs = $items->Bnama;
                // insert ke table
                $rs = $purchasedetail->Update($purchasedetail->Id);
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Update Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Success');
                    print('OK|Proses update data berhasil!');
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Update Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Failed');
                    print('OK|Proses update data gagal!');
                }
            }else{
                print('ER|Data barang tidak ditemukan!');
            }
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $purchasedetail = new PurchaseDetail();
        $purchasedetail = $purchasedetail->FindById($id);
        if ($purchasedetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($purchasedetail->Delete($id) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchasedetail->GrnNo,'Success');
            printf("Data Detail Purchase ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchasedetail->GrnNo,'Failed');
            printf("Maaf, Data Detail Purchase ID: %d gagal dihapus!",$id);
        }
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "master/contacts.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sContactsId = $this->GetPostValue("ContactsId");
            $sStatus = $this->GetPostValue("Status");
            $sPaymentStatus = $this->GetPostValue("PaymentStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $purchase = new Purchase();
            if ($sJnsLaporan == 1) {
                $reports = $purchase->Load4Reports($this->userCompanyId, $sCabangId, $sContactsId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $purchase->Load4ReportsDetail($this->userCompanyId, $sCabangId, $sContactsId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate);
            }else{
                $reports = $purchase->Load4ReportsRekapItem($this->userCompanyId, $sCabangId, $sContactsId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sStatus = -1;
            $sPaymentStatus = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sJnsLaporan = 1;
            $sOutput = 0;
            $reports = null;
        }
        $supplier = new Contacts();
        $supplier = $supplier->LoadSupplierByEntity($this->userCompanyId);
        $company = new Company($this->userCompanyId);
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        if ($this->userLevel > 3){
            $cabang = $loader->LoadByEntityId($this->userCompanyId);
        }else{
            $cabang = $loader->LoadById($this->userCabangId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("suppliers",$supplier);
        $this->Set("CabangId",$sCabangId);
        $this->Set("ContactsId",$sContactsId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Status",$sStatus);
        $this->Set("PaymentStatus",$sPaymentStatus);
        $this->Set("JnsLaporan",$sJnsLaporan);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("company_name", $company->CompanyName);
    }

    public function getjson_grnlists($cabangId,$supplierId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $purchases = new Purchase();
        $grnlists = $purchases->GetJSonGrns($cabangId,$supplierId,$filter);
        echo json_encode($grnlists);
    }

    public function getjson_grnitems($grnId = 0){
        $purchases = new Purchase();
        $itemlists = $purchases->GetJSonGrnItems($grnId);
        echo json_encode($itemlists);
    }

    public function GrnItemsCount($grnId){
        $purchases = new Purchase();
        $rows = $purchases->GetGrnItemCount($grnId);
        return $rows;
    }

    //proses cetak bukti pembelian
    public function grn_print($doctype = 'grn') {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Harap pilih data yang akan dicetak !");
            redirect_url("ap.purchase");
            return;
        }
        $report = array();
        foreach ($ids as $id) {
            $grn = new Purchase();
            $grn = $grn->LoadById($id);
            $grn->LoadDetails();
            $report[] = $grn;
        }

        $this->Set("doctype", $doctype);
        $this->Set("report", $report);
    }

    public function getitemprices_json($order="a.bnama"){
        require_once(MODEL . "master/setprice.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $setprice = new SetPrice();
        $itemlists = $setprice->GetJSonItemPrice($this->userCompanyId,$this->userCabangId,$this->isGlobalItems,$filter,$order);
        echo json_encode($itemlists);
    }

    public function getitemprices_plain($cabangId,$bkode){
        require_once(MODEL . "master/setprice.php");
        require_once(MODEL . "master/items.php");
        $ret = 'ER|0';
        if($bkode != null || $bkode != ''){
            /** @var $setprice SetPrice */
            /** @var $items Items  */
            $items = new Items();
            $items = $items->LoadByKode($bkode);
            $hrg_beli = 0;
            $hrg_jual = 0;
            $setprice = null;
            if ($items != null){
                $setprice = new SetPrice();
                $setprice = $setprice->FindByKode($cabangId,$bkode);
                if ($setprice != null){
                    $hrg_beli = $setprice->HrgBeli;
                    $hrg_jual = $setprice->HrgJual1;
                }
                if($hrg_beli == null){
                    $hrg_beli = 0;
                }
                if($hrg_jual == null){
                    $hrg_jual = 0;
                }
                $ret = "OK|".$items->Bid.'|'.$items->Bnama.'|'.$items->Bsatbesar.'|'.$hrg_beli.'|'.$hrg_jual;
            }
        }
        print $ret;
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di Approve !");
            redirect_url("ap.purchase");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $purchase = new Purchase();
            $log = new UserAdmin();
            $purchase = $purchase->FindById($id);
            /** @var $purchase Purchase */
            // process purchase
            if($purchase->GrnStatus == 1){
                $rs = $purchase->Approve($purchase->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Approve Pembelian',$purchase->GrnNo,'Success');
                    $infos[] = sprintf("Data Pembelian No: '%s' (%s) telah berhasil di-approve.", $purchase->GrnNo, $purchase->GrnDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Approve Pembelian',$purchase->GrnNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Pembelian: '%s'. Message: %s", $purchase->GrnNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Pembelian No.%s sudah berstatus -Approved- !",$purchase->GrnNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.purchase");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.purchase");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $purchase = new Purchase();
            $log = new UserAdmin();
            $purchase = $purchase->FindById($id);
            /** @var $purchase Purchase */
            // process invoice
            if($purchase->GrnStatus == 2){
                $rs = $purchase->Unapprove($purchase->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Un-approve Pembelian',$purchase->GrnNo,'Success');
                    $infos[] = sprintf("Approval Data Pembelian No: '%s' (%s) telah berhasil di-batalkan.", $purchase->GrnNo, $purchase->GrnDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Un-approve Pembelian',$purchase->GrnNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Pembelian: '%s'. Message: %s", $purchase->GrnNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($purchase->GrnStatus == 1){
                    $errors[] = sprintf("Data Pembelian No.%s masih berstatus -POSTED- !",$purchase->GrnNo);
                }else{
                    $errors[] = sprintf("Data Pembelian No.%s masih berstatus -DRAFT- !",$purchase->GrnNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.purchase");
    }

    public function getjson_contacts($ctype = 0,$eti = 0){
        require_once (MODEL . "master/contacts.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $contacts = new Contacts();
        $contlists = $contacts->GetJSonContacts($ctype,$eti,$filter);
        echo json_encode($contlists);
    }

    public function getjson_polists($cabangId,$supplierId){
        require_once (MODEL . "ap/order.php");
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new Order();
        $polists = $order->GetActivePoList($cabangId,$supplierId);
        echo json_encode($polists);
    }

    public function getjson_poitems($poNo,$gdId){
        require_once (MODEL . "ap/order.php");
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new Order();
        $poitems = $order->GetItemPoItems($poNo,$gdId);
        echo json_encode($poitems);
    }
}


// End of File: estimasi_controller.php
