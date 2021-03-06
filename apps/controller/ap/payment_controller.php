<?php
class PaymentController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ap/payment.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        //$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Entity", "width" => 30);
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 80);
        $settings["columns"][] = array("name" => "a.payment_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.payment_no", "display" => "No. Payment", "width" => 80);
        $settings["columns"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier", "width" => 150);
        $settings["columns"][] = array("name" => "a.payment_descs", "display" => "Keterangan", "width" => 160);
        $settings["columns"][] = array("name" => "if(a.payment_mode = 0,'Cash','Bank')", "display" => "Cara Bayar", "width" => 80);
        $settings["columns"][] = array("name" => "a.bank_name", "display" => "Kas/Bank", "width" => 80);
        $settings["columns"][] = array("name" => "format(a.payment_amount,0)", "display" => "Pembayaran", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.allocate_amount,0)", "display" => "Alokasi", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.payment_amount - a.allocate_amount,0)", "display" => "Sisa", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 80);
        $settings["columns"][] = array("name" => "a.status_desc", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");
        $settings["filters"][] = array("name" => "a.payment_no", "display" => "No. Payment");
        $settings["filters"][] = array("name" => "a.payment_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier");
        $settings["filters"][] = array("name" => "a.status_desc", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Pembayaran Hutang";

            if ($acl->CheckUserAccess("ap.payment", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.payment/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.payment", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.payment/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Payment terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.payment", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ap.payment/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.payment", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.payment/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Payment terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data payment","Confirm" => "");
            }
            /*
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.payment", "print")) {
                $settings["actions"][] = array("Text" => "Print Payment", "Url" => "ap.payment/print_pdf/%s", "Class" => "bt_pdf", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Payment terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data payment","Confirm" => "");
            }
            */
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.payment", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.payment/report", "Class" => "bt_report", "ReqId" => 0);
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.payment", "approve")) {
                $settings["actions"][] = array("Text" => "Approve Payment", "Url" => "ap.payment/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Pembayaran terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data pembayaran yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approve", "Url" => "ap.payment/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Pembayaran terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data pembayaran yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ap_payment_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.payment_date) = ".$this->trxYear." And month(a.payment_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add() {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/contacts.php");
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "master/warkattype.php");
        $loader = null;
        $log = new UserAdmin();
		$payment = new Payment();
        $payment->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
			$payment->CabangId = $this->GetPostValue("CabangId");
			$payment->PaymentDate = $this->GetPostValue("PaymentDate");
            $payment->PaymentNo = $this->GetPostValue("PaymentNo");
            $payment->PaymentDescs = $this->GetPostValue("PaymentDescs");
            $payment->CreditorId = $this->GetPostValue("CreditorId");
            $payment->PaymentMode = $this->GetPostValue("PaymentMode");
            $payment->BankId = $this->GetPostValue("BankId");
            $payment->WarkatTypeId = $this->GetPostValue("WarkatTypeId");
            $payment->WarkatBankId = $this->GetPostValue("WarkatBankId");
            $payment->WarkatNo = $this->GetPostValue("WarkatNo");
            $payment->WarkatDate = $this->GetPostValue("WarkatDate");
            $payment->ReturnNo = $this->GetPostValue("ReturnNo");
            $payment->PaymentAmount = $this->GetPostValue("PaymentAmount");
            $payment->AllocateAmount = $this->GetPostValue("AllocateAmount");
            if ($this->GetPostValue("PaymentStatus") == null || $this->GetPostValue("PaymentStatus") == 0){
                $payment->PaymentStatus = 1;
            }else{
                $payment->PaymentStatus = $this->GetPostValue("PaymentStatus");
            }
            $payment->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateMaster($payment)) {
                if ($payment->PaymentNo == null || $payment->PaymentNo == "-" || $payment->PaymentNo == ""){
                    $payment->PaymentNo = $payment->GetPaymentDocNo();
                }
                $rs = $payment->Insert();
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Add New Payment',$payment->PaymentNo,'Failed');
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Add New Payment',$payment->PaymentNo,'Sucess');
                    redirect_url("ap.payment/edit/".$payment->Id);
                }
			}
		}
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Bank();
        $banks = $loader->LoadByCabangId($this->userCabangId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        $this->Set("warkattypes", $warkattypes);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("payment", $payment);
        $this->Set("banks", $banks);
	}

	private function ValidateMaster(Payment $payment) {
        if ($payment->CreditorId == null || $payment->CreditorId == 0 || $payment->CreditorId == ''){
            $this->Set("error", "Data Supplier belum diisi!");
            return false;
        }
        if ($payment->WarkatTypeId > 1 && $payment->WarkatTypeId < 6){
            if ($payment->WarkatBankId < 2){
                $this->Set("error", "Data Bank belum diisi!");
                return false;
            }
            if ($payment->WarkatNo == null || $payment->WarkatNo == ''){
                $this->Set("error", "No. Warkat belum diisi!");
                return false;
            }
            if ($payment->WarkatDate == null || $payment->WarkatDate == ''){
                $this->Set("error", "Tanggal Warkat belum diisi!");
                return false;
            }
        }elseif ($payment->WarkatTypeId == 1){
            // $payment->WarkatBankId = 1;
            $payment->ReturnNo = '';
            $payment->WarkatNo = '';
            $payment->WarkatDate = null;
        }elseif ($payment->WarkatTypeId == 7){
            $payment->WarkatBankId = 0;
            $payment->WarkatNo = '';
            $payment->WarkatDate = null;
            if ($payment->ReturnNo == '-' || $payment->ReturnNo == '' || $payment->ReturnNo == null){
                $this->Set("error", "Data No Retur belum diisi!");
                return false;
            }
        }
        if ($payment->PaymentAmount == 0 || $payment->PaymentAmount == '' || $payment->PaymentAmount == null){
            $this->Set("error", "Jumlah Pembayaran belum diisi!");
            return false;
        }
        return true;
	}

    public function edit($paymentId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "master/warkattype.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $log = new UserAdmin();
        $payment = new Payment();
        if (count($this->postData) > 0) {
            $payment->Id = $paymentId;
            $payment->CabangId = $this->GetPostValue("CabangId");
            $payment->PaymentDate = $this->GetPostValue("PaymentDate");
            $payment->PaymentNo = $this->GetPostValue("PaymentNo");
            $payment->PaymentDescs = $this->GetPostValue("PaymentDescs");
            $payment->CreditorId = $this->GetPostValue("CreditorId");
            $payment->PaymentMode = $this->GetPostValue("PaymentMode");
            $payment->BankId = $this->GetPostValue("BankId");
            $payment->WarkatTypeId = $this->GetPostValue("WarkatTypeId");
            $payment->WarkatBankId = $this->GetPostValue("WarkatBankId");
            $payment->WarkatNo = $this->GetPostValue("WarkatNo");
            $payment->WarkatDate = $this->GetPostValue("WarkatDate");
            $payment->ReturnNo = $this->GetPostValue("ReturnNo");
            $payment->PaymentAmount = $this->GetPostValue("PaymentAmount");
            $payment->AllocateAmount = $this->GetPostValue("AllocateAmount");
            if ($this->GetPostValue("PaymentStatus") == null || $this->GetPostValue("PaymentStatus") == 0){
                $payment->PaymentStatus = 1;
            }else{
                $payment->PaymentStatus = $this->GetPostValue("PaymentStatus");
            }
            $payment->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateMaster($payment)) {
                $rs = $payment->Update($payment->Id);
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->persistence->SaveState("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Update Payment',$payment->PaymentNo,'Failed');
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Update Payment',$payment->PaymentNo,'Sucess');
                    $this->persistence->SaveState("info", sprintf("Data Payment No.: '%s' Tanggal: %s telah berhasil diubah..", $payment->PaymentNo, $payment->PaymentDate));
                    redirect_url("ap.payment/edit/".$payment->Id);
                }
            }
        }else{
            $payment = $payment->LoadById($paymentId);
            if($payment == null){
               $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ap.payment");
            }
            if($payment->PaymentStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Payment No. %s sudah berstatus -Approved-",$payment->PaymentNo));
                redirect_url("ap.payment");
            }
            if($payment->PaymentStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Payment No. %s sudah berstatus -Batal-",$payment->PaymentNo));
                redirect_url("ap.payment/view/".$paymentId);
            }
        }
        // load details
        $payment->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Bank();
        $banks = $loader->LoadByCabangId($this->userCabangId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        $this->Set("warkattypes", $warkattypes);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("payment", $payment);
        $this->Set("banks", $banks);
        $this->Set("acl", $acl);
    }

	public function view($paymentId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "master/warkattype.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $payment = new Payment();
        $payment = $payment->LoadById($paymentId);
        if($payment == null){
            $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        // load details
        $payment->LoadDetails();
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
        $loader = new Bank();
        $banks = $loader->LoadByCabangId($this->userCabangId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        $this->Set("warkattypes", $warkattypes);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("payment", $payment);
        $this->Set("banks", $banks);
        $this->Set("acl", $acl);
	}

    public function delete($paymentId) {
        // Cek datanya
        $log = new UserAdmin();
        $payment = new Payment();
        $payment = $payment->FindById($paymentId);
        if($payment == null){
            $this->Set("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        /** @var $payment Payment */
        if ($payment->Delete($paymentId) > 0) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Delete Payment',$payment->PaymentNo,'Sucess');
            $this->persistence->SaveState("info", sprintf("Data Payment No: %s sudah berhasil dihapus", $payment->PaymentNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Delete Payment',$payment->PaymentNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Payment No: %s gagal dihapus", $payment->PaymentNo));
        }
        redirect_url("ap.payment");
    }

    public function void($paymentId) {
        // Cek datanya
        $log = new UserAdmin();
        $payment = new Payment();
        $payment = $payment->FindById($paymentId);
        if($payment == null){
            $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        if($payment->PaymentStatus == 3){
            $this->persistence->SaveState("error", "Maaf, Data Payment sudah berstatus -VOID-!");
            redirect_url("ap.payment");
        }

        if($payment->AllocateAmount > 0){
            $this->persistence->SaveState("error", "Maaf, Hapus dulu detail paymentnya!");
            redirect_url("ap.payment");
        }

        /** @var $payment Payment */
        if ($payment->Void($paymentId) > 0) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Delete Payment',$payment->PaymentNo,'Sucess');
            $this->persistence->SaveState("info", sprintf("Data Payment No: %s sudah berhasil dibatalkan", $payment->PaymentNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Delete Payment',$payment->PaymentNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Payment No: %s gagal dibatalkan", $payment->PaymentNo));
        }
        redirect_url("ap.payment");
    }

	public function add_detail($paymentId = null) {
        $log = new UserAdmin();
        $payment = new Payment($paymentId);
        $paydetail = new PaymentDetail();
        $paydetail->PaymentId = $paymentId;
        $paydetail->PaymentNo = $payment->PaymentNo;
        $paydetail->CabangId = $payment->CabangId;
        if (count($this->postData) > 0) {
            $paydetail->GrnId = $this->GetPostValue("aGrnId");
            $paydetail->GrnNo = $this->GetPostValue("aGrnNo");
            $paydetail->GrnOutstanding = $this->GetPostValue("aGrnOutStanding");
            $paydetail->AllocateAmount = $this->GetPostValue("aAllocateAmount");
            $paydetail->GrnAmount = $this->GetPostValue("aAllocateAmount");
            $paydetail->PotPph = 0;
            $paydetail->PotLain = 0;
            $rs = $paydetail->Insert()== 1;
            if ($rs > 0) {
                $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Add Payment detail -> Payment No: '.$paydetail->PaymentNo.' = '.$paydetail->AllocateAmount,$paydetail->PaymentNo,'Sucess');
                echo json_encode(array());
            } else {
                $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Add Payment detail -> Payment No: '.$paydetail->PaymentNo.' = '.$paydetail->AllocateAmount,$paydetail->PaymentNo,'Failed');
                echo json_encode(array('errorMsg'=>'Some database errors occured.'));
            }
        }
	}    

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $paydetail = new PaymentDetail();
        $paydetail = $paydetail->FindById($id);
        if ($paydetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($paydetail->Delete($id) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Delete Payment detail -> Payment No: '.$paydetail->PaymentNo.' = '.$paydetail->AllocateAmount,$paydetail->PaymentNo,'Sucess');
            printf("Data Detail Payment ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Delete Payment detail -> Payment No: '.$paydetail->PaymentNo.' = '.$paydetail->AllocateAmount,$paydetail->PaymentNo,'Failed');
            printf("Maaf, Data Detail Payment ID: %d gagal dihapus!",$id);
        }
    }

    public function print_pdf($paymentId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/karyawan.php");
        $loader = null;
        $payment = new Payment();
        $payment = $payment->LoadById($paymentId);
        if($payment == null){
            $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        // load details
        $payment->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new Karyawan();
        $banks = $loader->LoadAll();
        $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
        //kirim ke view
        $this->Set("sales", $banks);
        $this->Set("cabangs", $cabang);
        $this->Set("payment", $payment);
        $this->Set("userName", $userName);
    }

    public function report(){
        require_once(MODEL . "master/contacts.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/bank.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sContactsId = $this->GetPostValue("ContactsId");
            $sBankId = $this->GetPostValue("BankId");
            $sPaymentStatus = $this->GetPostValue("PaymentStatus");
            $sPaymentMode = $this->GetPostValue("PaymentMode");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $payment = new Payment();
            $reports = $payment->Load4Reports($this->userCompanyId,$sCabangId,$sBankId,$sContactsId,$sPaymentMode,$sPaymentStatus,$sStartDate,$sEndDate);
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sBankId = 0;
            $sPaymentStatus = -1;
            $sPaymentMode = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $reports = null;
        }
        $supplier = new Contacts();
        $supplier = $supplier->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
        $loader = new Bank();
        $banks = $loader->LoadAll();
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
        // kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("suppliers",$supplier);
        $this->Set("banks",$banks);
        $this->Set("CabangId",$sCabangId);
        $this->Set("ContactsId",$sContactsId);
        $this->Set("BankId",$sBankId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("PaymentStatus",$sPaymentStatus);
        $this->Set("PaymentMode",$sPaymentMode);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
    }

    public function getPaymentItemRows($id){
        $payment = new Payment();
        $rows = $payment->GetPaymentItemRow($id);
        print($rows);
    }

    public function createTextPayment($id){
        $payment = new Payment($id);
        if ($payment <> null){
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $payment->CompanyName);
            fwrite($myfile, "\n".'FAKTUR PENJUALAN');

            fclose($myfile);
        }
    }

    public function getoutstandinggrns_plain($cabangId = 0,$supplierId = 0 ,$grnNo = null){
        require_once(MODEL . "ap/payment.php");
        $ret = 'ER|0';
        if($grnNo != null || $grnNo != ''){
            /** @var $payment Payment[] */
            $payment = new Payment();
            $payment = $payment->GetUnpaidGrns($cabangId,$supplierId,$grnNo);
            if ($payment != null){
                $ret = 'OK|'.$payment->Id.'|'.date(JS_DATE,$payment->GrnDate).'|'.date(JS_DATE,$payment->DueDate).'|'.$payment->BalanceAmount;
            }
        }
        print $ret;
    }

    public function getoutstandinggrns_json($cabangId,$supplierId){
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $payment = new Payment();
        $itemlists = $payment->GetJSonUnpaidGrns($cabangId,$supplierId);
        echo json_encode($itemlists);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di Approve !");
            redirect_url("ap.payment");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $payment = new Payment();
            $log = new UserAdmin();
            $payment = $payment->FindById($id);
            /** @var $payment Payment */
            // process payment
            if($payment->PaymentStatus == 1){
                $rs = $payment->Approve($payment->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Approve Pembayaran',$payment->PaymentNo,'Success');
                    $infos[] = sprintf("Data Pembayaran No: '%s' (%s) telah berhasil di-approve.", $payment->PaymentNo, $payment->PaymentDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Approve Pembayaran',$payment->PaymentNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Pembayaran: '%s'. Message: %s", $payment->PaymentNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Pembayaran No.%s tidak berstatus -Posted- !",$payment->PaymentNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.payment");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.payment");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $payment = new Payment();
            $log = new UserAdmin();
            $payment = $payment->FindById($id);
            /** @var $payment Payment */
            // process invoice
            if($payment->PaymentStatus == 2){
                $rs = $payment->Unapprove($payment->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Un-approve Pembayaran',$payment->PaymentNo,'Success');
                    $infos[] = sprintf("Approval Data Pembayaran No: '%s' (%s) telah berhasil di-batalkan.", $payment->PaymentNo, $payment->PaymentDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.payment','Un-approve Pembayaran',$payment->PaymentNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Pembayaran: '%s'. Message: %s", $payment->PaymentNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($payment->PaymentStatus == 1){
                    $errors[] = sprintf("Data Pembayaran No.%s masih berstatus -Posted- !",$payment->PaymentNo);
                }else{
                    $errors[] = sprintf("Data Pembayaran No.%s masih berstatus -Draf/Void- !",$payment->PaymentNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.payment");
    }

    public function updatecarabayar($id,$wti,$wbi,$ket){
        $payment = new Payment();
        if ($payment->UpdateCaraBayar($id,$wti,$wbi,$ket)){
            print 1;
        }else{
            print 0;
        }
    }
}


// End of File: estimasi_controller.php
