<?php
class OrderController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ar/order.php");
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
        $settings["columns"][] = array("name" => "a.so_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.so_no", "display" => "No. Order", "width" => 80);
        $settings["columns"][] = array("name" => "a.customer_name", "display" => "Nama Customer", "width" => 200);
        $settings["columns"][] = array("name" => "a.so_descs", "display" => "Keterangan", "width" => 160);
        $settings["columns"][] = array("name" => "a.sales_name", "display" => "Salesman", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Cara Bayar", "width" => 80);
        $settings["columns"][] = array("name" => "format(a.total_amount,0)", "display" => "Nilai Order", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.so_status = 1,'Open','Closed')", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");
        $settings["filters"][] = array("name" => "a.so_no", "display" => "No. Order");
        $settings["filters"][] = array("name" => "a.so_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.customer_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "if(a.so_status = 1,'Open','Closed')", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = true;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Sales Order (SO)";

            if ($acl->CheckUserAccess("ar.order", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ar.order/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.order", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ar.order/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Order terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ar.order", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "ar.order/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ar.order", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ar.order/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Order terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            /*
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.order", "approve")) {
                $settings["actions"][] = array("Text" => "Proses PO", "Url" => "ar.order/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Order terlebih dahulu sebelum proses approval.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda menyetujui data po yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
            if ($acl->CheckUserAccess("ar.order", "approve")) {
                $settings["actions"][] = array("Text" => "Batal Proses PO", "Url" => "ar.order/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Order terlebih dahulu sebelum proses pembatalan.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda mau membatalkan approval data po yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
            */
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.order", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ar.order/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_ar_so_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.so_date) = ".$this->trxYear." And month(a.so_date) = ".$this->trxMonth;
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
        $loader = null;
		$order = new Order();
        $order->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
			$order->CabangId = $this->GetPostValue("CabangId");
			$order->SoDate = $this->GetPostValue("SoDate");
            $order->RequestDate = $this->GetPostValue("RequestDate");
            if ($order->RequestDate == '' || $order->RequestDate == null){
                $order->RequestDate = $order->SoDate;
            }
            $order->SoNo = $this->GetPostValue("SoNo");
            $order->SoDescs = $this->GetPostValue("SoDescs");
            $order->CustomerId = $this->GetPostValue("CustomerId");
            $order->SalesName = $this->GetPostValue("SalesName");
            if ($this->GetPostValue("SoStatus") == null || $this->GetPostValue("SoStatus") == 0){
                $order->SoStatus = 1;
            }else{
                $order->SoStatus = $this->GetPostValue("SoStatus");
            }
            $order->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->GetPostValue("PaymentType") == null){
                $order->PaymentType = 0;
            }else{
                $order->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $order->CreditTerms = 0;
            }else{
                $order->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            $order->BaseAmount = 0;
            $order->Disc1Pct = 0;
            $order->Disc1Amount = 0;
            $order->Disc2Pct = 0;
            $order->Disc2Amount = 0;
            $order->TaxPct = 0;
            $order->TaxAmount = 0;
            $order->OtherCosts = '-';
            $order->OtherCostsAmount = 0;
            $order->PaidAmount = 0;
            if ($this->ValidateMaster($order)) {
                if ($order->SoNo == null || $order->SoNo == "-" || $order->SoNo == ""){
                    $order->SoNo = $order->GetSoDocNo();
                }
                $rs = $order->Insert();
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                }else{
                    redirect_url("ar.order/edit/".$order->Id);
                }
			}
		}
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
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
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
	}

	private function ValidateMaster(Order $order) {
        if ($order->CustomerId == 0 || $order->CustomerId == null || $order->CustomerId == ''){
            $this->Set("error", "Customer tidak boleh kosong!");
            return false;
        }
        return true;
	}

    public function edit($orderId = null) {
        require_once(MODEL . "master/cabang.php");
        $loader = null;
        $order = new Order();
        if (count($this->postData) > 0) {
            $order->Id = $orderId;
            $order->CabangId = $this->GetPostValue("CabangId");
            $order->SoDate = $this->GetPostValue("SoDate");
            $order->RequestDate = $this->GetPostValue("RequestDate");
            if ($order->RequestDate == '' || $order->RequestDate == null){
                $order->RequestDate = $order->SoDate;
            }
            $order->SoNo = $this->GetPostValue("SoNo");
            $order->SoDescs = $this->GetPostValue("SoDescs");
            $order->CustomerId = $this->GetPostValue("CustomerId");
            $order->SalesName = $this->GetPostValue("SalesName");
            if ($this->GetPostValue("SoStatus") == null || $this->GetPostValue("SoStatus") == 0){
                $order->SoStatus = 1;
            }else{
                $order->SoStatus = $this->GetPostValue("SoStatus");
            }
            $order->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->GetPostValue("PaymentType") == null){
                $order->PaymentType = 0;
            }else{
                $order->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $order->CreditTerms = 0;
            }else{
                $order->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            $order->Disc1Pct = $this->GetPostValue("Disc1Pct");
            $order->TaxPct = $this->GetPostValue("TaxPct");
            $order->OtherCosts = $this->GetPostValue("OtherCosts");
            $order->OtherCostsAmount = str_replace(",","",$this->GetPostValue("OtherCostsAmount"));
            if ($this->ValidateMaster($order)) {
                $rs = $order->Update($order->Id);
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->persistence->SaveState("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                }else{
                    $this->persistence->SaveState("info", sprintf("Data Order/Nota No.: '%s' Tanggal: %s telah berhasil diubah..", $order->SoNo, $order->SoDate));
                    redirect_url("ar.order/edit/".$order->Id);
                }
            }
        }else{
            $order = $order->LoadById($orderId);
            if($order == null){
               $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ar.order");
            }
            if($order->SoStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Order No. %s sudah berstatus -CLOSED-",$order->SoNo));
                redirect_url("ar.order");
            }
            // load details
            $order->LoadDetails();
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
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
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
    }

	public function view($orderId = null) {
        require_once(MODEL . "master/cabang.php");
        $loader = null;
        $order = new Order();
        $order = $order->LoadById($orderId);
        if($order == null){
            $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.order");
        }
        // load details
        $order->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
	}

    public function delete($orderId) {
        // Cek datanya
        $order = new Order();
        $order = $order->FindById($orderId);
        if($order == null){
            $this->Set("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.order");
        }
        // periksa status po
        if($order->SoStatus < 2){
            $order->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($order->Delete($orderId) == 1) {
                $this->persistence->SaveState("info", sprintf("Data Order No: %s sudah berhasil dihapus", $order->SoNo));
            }else{
                $this->persistence->SaveState("error", sprintf("Maaf, Data Order No: %s gagal dihapus", $order->SoNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Order No: %s sudah berstatus -CLOSED-", $order->SoNo));
        }
        redirect_url("ar.order");
    }

	public function add_detail($orderId = null) {
        require_once(MODEL . "master/items.php");
        $order = new Order($orderId);
        $orderdetail = new OrderDetail();
        $orderdetail->SoId = $orderId;
        $orderdetail->SoNo = $order->SoNo;
        $orderdetail->CabangId = $order->CabangId;
        $items = null;
        if (count($this->postData) > 0) {
            $orderdetail->ItemId = $this->GetPostValue("aItemId");
            $orderdetail->OrderQty = $this->GetPostValue("aQty");
            $orderdetail->SendQty = 0;
            $orderdetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $orderdetail->DiscFormula = 0;
            }else{
                $orderdetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $orderdetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $orderdetail->SubTotal = $this->GetPostValue("aSubTotal");
            $items = new Items($orderdetail->ItemId);
            if ($items != null){
                $orderdetail->ItemCode = $items->Bkode;
                $orderdetail->ItemDescs = $items->Bnama;
                $orderdetail->Lqty = 0;
                $orderdetail->Sqty = 0;
                // insert ke table
                $rs = $orderdetail->Insert()== 1;
                if ($rs > 0) {
                    echo json_encode(array());
                } else {
                    echo json_encode(array('errorMsg'=>'Some errors occured.'));
                }
            }else{
                echo json_encode(array('errorMsg'=>'Data barang tidak ditemukan!'));
            }
        }
	}

    public function edit_detail($orderId = null) {
        require_once(MODEL . "master/items.php");
        $order = new Order($orderId);
        $orderdetail = new OrderDetail();
        $orderdetail->SoId = $orderId;
        $orderdetail->SoNo = $order->SoNo;
        $orderdetail->CabangId = $order->CabangId;
        $items = null;
        if (count($this->postData) > 0) {
            $orderdetail->Id = $this->GetPostValue("aId");
            $orderdetail->ItemId = $this->GetPostValue("aItemId");
            $orderdetail->OrderQty = $this->GetPostValue("aQty");
            $orderdetail->SendQty = $this->GetPostValue("rQty");
            $orderdetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $orderdetail->DiscFormula = 0;
            }else{
                $orderdetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $orderdetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $orderdetail->SubTotal = $this->GetPostValue("aSubTotal");
            $items = new Items($orderdetail->ItemId);
            if ($items != null){
                $orderdetail->ItemCode = $items->Bkode;
                $orderdetail->ItemDescs = $items->Bnama;
                // insert ke table
                $rs = $orderdetail->Update($orderdetail->Id);
                if ($rs > 0) {
                    echo json_encode(array());
                } else {
                    echo json_encode(array('errorMsg'=>'Some errors occured.'));
                }
            }else{
                echo json_encode(array('errorMsg'=>'Data barang tidak ditemukan!'));
            }
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $orderdetail = new OrderDetail();
        $orderdetail = $orderdetail->FindById($id);
        if ($orderdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($orderdetail->Delete($id) == 1) {
            printf("Data Detail Order ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail Order ID: %d gagal dihapus!",$id);
        }
    }

    public function print_pdf($orderId = null) {
        require_once(MODEL . "trx/rekonsil.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/asuransi.php");
        require_once(MODEL . "master/plservice.php");
        require_once(MODEL . "master/sparepart.php");
        require_once(MODEL . "master/contacts.php");

        // Cek datanya
        $order = new Order();
        $order = $order->LoadById($orderId);
        if ($order == null) {
            redirect_url("trx.estimasi");
            return;
        }
        $loader = null;
        $rekonsil = new Order($order->EntityId);
        // Untuk Detail yang lainnya kita dynamic loading saja....
        $order->LoadDetails();
        $services = array();
        $parts = array();
        foreach ($order->Details as $detail) {
            if (!array_key_exists($detail->ServiceId, $services)) {
                $services[$detail->ServiceId] = new PlService($detail->ServiceId);
            }
            if (!array_key_exists($detail->PartId, $parts)) {
                $parts[$detail->PartId] = new SparePart($detail->PartId);
            }
        }
        $loader = new PlService();
        $plservice = $loader->LoadAll();
        $loader = new SparePart();
        if($rekonsil->Merk == null){
            $sparepart = $loader->LoadAll();
        }else{
            $sparepart = $loader->LoadByMerk($rekonsil->Merk);
        }
        $asuransi = new Asuransi($rekonsil->OrderTypeId);
        $customer = new Contacts($rekonsil->ContactsId);
        $loader = new Company($rekonsil->EntityId);
        $this->Set("company_name", $loader->CompanyName);
        $this->Set("rekonsil",$rekonsil);
        $this->Set("estimasi", $order);
        $this->Set("plservices", $plservice);
        $this->Set("spareparts", $sparepart);
        $this->Set("services",$services);
        $this->Set("parts",$parts);
        $this->Set("asuransi",$asuransi);
        $this->Set("customer",$customer);
        $loader = new OrderDetail();
        $qcrepair = $loader->GetSumByType(1,$orderId);
        $qcpart = $loader->GetSumByType(2,$orderId);
        $this->Set("qcrepair",$qcrepair);
        $this->Set("qcpart",$qcpart);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.order");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $order = new Order();
            $order = $order->FindById($id);
            /** @var $order Order */
            // process po
            if($order->SoStatus == 0){
                $rs = $order->Approve($order->Id,$uid);
                if ($rs) {
                    $infos[] = sprintf("Data Order No.: '%s' (%s) telah berhasil di-approve.", $order->SoNo, $order->SoDescs);
                } else {
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Order: '%s'. Message: %s", $order->SoNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Order No.%s sudah berstatus -Posted- !",$order->SoNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.order");
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
            $sSoStatus = $this->GetPostValue("SoStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $order = new Order();
            if ($sJnsLaporan == 1){
                $reports = $order->Load4Reports($this->userCompanyId,$sCabangId,$sContactsId,$sSoStatus,$sStartDate,$sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $order->Load4ReportsDetail($this->userCompanyId,$sCabangId,$sContactsId,$sSoStatus,$sStartDate,$sEndDate);
            }else{
                $reports = $order->Load4ReportsRekapItem($this->userCompanyId,$sCabangId,$sContactsId,$sSoStatus,$sStartDate,$sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sSoStatus = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $sJnsLaporan = 1;
            $reports = null;
        }
        $customer = new Contacts();
        $customer = $customer->LoadByEntity($this->userCompanyId);
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
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
        $this->Set("customers",$customer);
        $this->Set("CabangId",$sCabangId);
        $this->Set("ContactsId",$sContactsId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Status",$sSoStatus);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("JnsLaporan",$sJnsLaporan);
    }

    public function getitemprices_json($order="a.bnama"){
        require_once(MODEL . "master/setprice.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $setprice = new SetPrice();
        $itemlists = $setprice->GetJSonItemPrice($this->userCompanyId,$this->userCabangId,$filter,$order);
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

    public function getjson_solists($cabangId,$customerId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new Order();
        $solists = $order->GetActiveSoList($cabangId,$customerId,$filter);
        echo json_encode($solists);
    }
}


// End of File: estimasi_controller.php
