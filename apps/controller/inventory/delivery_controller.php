<?php
class DeliveryController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "inventory/delivery.php");
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
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 80);
        $settings["columns"][] = array("name" => "a.do_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.do_no", "display" => "No. D/O", "width" => 85);
        $settings["columns"][] = array("name" => "a.customer_name", "display" => "Nama Customer", "width" => 150);
        $settings["columns"][] = array("name" => "a.do_descs", "display" => "Keterangan", "width" => 200);
        $settings["columns"][] = array("name" => "a.exp_name", "display" => "Expedisi", "width" => 150);
        $settings["columns"][] = array("name" => "a.driver_name", "display" => "Driver", "width" => 80);
        $settings["columns"][] = array("name" => "a.vehicle_number", "display" => "Plat", "width" => 80);
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 80);
        $settings["columns"][] = array("name" => "if(a.do_status = 0,'Draft',if(a.do_status = 3,'Void','Posted'))", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "a.do_no", "display" => "No. Bukti");
        $settings["filters"][] = array("name" => "a.do_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.customer_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "if(a.do_status = 0,'Draft',if(a.do_status = 3,'Void','Posted'))", "display" => "Status");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = true;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Delivery Order";

            if ($acl->CheckUserAccess("inventory.delivery", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "inventory.delivery/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.delivery", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.delivery/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Delivery terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("inventory.delivery", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.delivery/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("inventory.delivery", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.delivery/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Delivery terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data delivery","Confirm" => "");
            }

            if ($acl->CheckUserAccess("inventory.delivery", "print")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Print D/O", "Url" => "inventory.delivery/do_print/do","Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "Cetak D/O yang dipilih?");
                $settings["actions"][] = array("Text" => "Print Surat Jalan", "Url" => "inventory.delivery/do_print/suratjalan","Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "Cetak Surat Jalan yang dipilih?");
            }

            if ($acl->CheckUserAccess("inventory.delivery", "view")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.delivery/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_ic_delivery_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.do_date) = ".$this->trxYear." And month(a.do_date) = ".$this->trxMonth;
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
        require_once(MODEL . "master/expedition.php");
        $loader = null;
        $log = new UserAdmin();
		$delivery = new Delivery();
        $delivery->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
			$delivery->CabangId = $this->GetPostValue("CabangId");
			$delivery->DoDate = $this->GetPostValue("DoDate");
            $delivery->DoNo = $this->GetPostValue("DoNo");
            $delivery->DoDescs = $this->GetPostValue("DoDescs");
            $delivery->CustomerId = $this->GetPostValue("CustomerId");
            if ($this->GetPostValue("DoStatus") == null || $this->GetPostValue("DoStatus") == 0){
                $delivery->DoStatus = 1;
            }else{
                $delivery->DoStatus = $this->GetPostValue("DoStatus");
            }
            $delivery->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            $delivery->DriverName = $this->GetPostValue("DriverName");
            $delivery->VehicleNumber = $this->GetPostValue("VehicleNumber");
            $delivery->ExpeditionId = $this->GetPostValue("ExpeditionId");
			if ($this->ValidateMaster($delivery)) {
                if ($delivery->DoNo == null || $delivery->DoNo == "-" || $delivery->DoNo == ""){
                    $delivery->DoNo = $delivery->GetDeliveryDocNo();
                }
                $rs = $delivery->Insert();
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Add New Return',$delivery->DoNo,'Failed');
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Add New Return',$delivery->DoNo,'Success');
                    redirect_url("inventory.delivery/edit/".$delivery->Id);
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
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("delivery", $delivery);
        //load expedition
        $loader = new Expedition();
        $expeditions = $loader->LoadByCompany($this->userCompanyId);
        $this->Set("expeditions", $expeditions);
	}

	private function ValidateMaster(Delivery $delivery) {
        // validation here
        if ($delivery->CustomerId > 0){
            return true;
        }else{
            $this->Set("error", "Nama Customer masih kosong..");
            return false;
        }
	}

    public function edit($deliveryId = null) {
       require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/expedition.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $delivery = new Delivery();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $delivery->Id = $deliveryId;
            $delivery->CabangId = $this->GetPostValue("CabangId");
            $delivery->DoDate = $this->GetPostValue("DoDate");
            $delivery->DoNo = $this->GetPostValue("DoNo");
            $delivery->DoDescs = $this->GetPostValue("DoDescs");
            $delivery->CustomerId = $this->GetPostValue("CustomerId");
            $delivery->DriverName = $this->GetPostValue("DriverName");
            $delivery->VehicleNumber = $this->GetPostValue("VehicleNumber");
            $delivery->ExpeditionId = $this->GetPostValue("ExpeditionId");
            if ($this->GetPostValue("DoStatus") == null || $this->GetPostValue("DoStatus") == 0){
                $delivery->DoStatus = 1;
            }else{
                $delivery->DoStatus = $this->GetPostValue("DoStatus");
            }
            $delivery->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateMaster($delivery)) {
                $rs = $delivery->Update($delivery->Id);
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->persistence->SaveState("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Update Return',$delivery->DoNo,'Failed');
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Update Return',$delivery->DoNo,'Success');
                    $this->persistence->SaveState("info", sprintf("Data Delivery/DO No.: '%s' Tanggal: %s telah berhasil diubah..", $delivery->DoNo, $delivery->DoDate));
                    redirect_url("inventory.delivery/edit/".$delivery->Id);
                }
            }
        }else{
            $delivery = $delivery->LoadById($deliveryId);
            if($delivery == null){
               $this->persistence->SaveState("error", "Maaf Data Delivery dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("inventory.delivery");
            }
            if ($delivery->DoStatus == 3){
                $this->Set("error", "Maaf Data Delivery ini berstatus -VOID-!");
                redirect_url("inventory.delivery");
            }
        }
        // load details
        $delivery->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("delivery", $delivery);
        $this->Set("acl", $acl);
        //load expedition
        $loader = new Expedition();
        $expeditions = $loader->LoadByCompany($this->userCompanyId);
        $this->Set("expeditions", $expeditions);
    }

	public function view($deliveryId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/expedition.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $delivery = new Delivery();
        $delivery = $delivery->LoadById($deliveryId);
        if($delivery == null){
            $this->persistence->SaveState("error", "Maaf Data Delivery dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("inventory.delivery");
        }
        // load details
        $delivery->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("delivery", $delivery);
        $this->Set("acl", $acl);
        //load expedition
        $loader = new Expedition();
        $expeditions = $loader->LoadByCompany($this->userCompanyId);
        $this->Set("expeditions", $expeditions);
	}

    public function delete($deliveryId) {
        // Cek datanya
        $log = new UserAdmin();
        $delivery = new Delivery();
        $delivery = $delivery->FindById($deliveryId);
        if($delivery == null){
            $this->Set("error", "Maaf Data Delivery dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("inventory.delivery");
        }
        /** @var $delivery Delivery */
        if ($delivery->Delete($deliveryId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Delete Return',$delivery->DoNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Delivery No: %s sudah berhasil dihapus", $delivery->DoNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Delete Return',$delivery->DoNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Delivery No: %s gagal dihapus", $delivery->DoNo));
        }
        redirect_url("inventory.delivery");
    }

    public function void($deliveryId) {
        // Cek datanya
        $log = new UserAdmin();
        $delivery = new Delivery();
        $delivery = $delivery->FindById($deliveryId);
        if($delivery == null){
            $this->Set("error", "Maaf Data Delivery dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("inventory.delivery");
        }
        /** @var $delivery Delivery */
        if ($delivery->DoStatus == 3){
            $this->Set("error", "Maaf Data Delivery sudah berstatus -VOID-!");
            redirect_url("inventory.delivery");
        }
        if ($delivery->Void($deliveryId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Delete Return',$delivery->DoNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Delivery No: %s sudah berhasil dibatalkan", $delivery->DoNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Delete Return',$delivery->DoNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Delivery No: %s gagal dibatalkan", $delivery->DoNo));
        }
        redirect_url("inventory.delivery");
    }

	public function add_detail($deliveryId = null) {
        $delivery = new Delivery($deliveryId);
        $dodetail = new DeliveryDetail();
        $log = new UserAdmin();
        $dodetail->DoId = $deliveryId;
        $dodetail->DoNo = $delivery->DoNo;
        $dodetail->CabangId = $delivery->CabangId;
        $items = null;
        if (count($this->postData) > 0) {
            $dodetail->ExInvoiceId = $this->GetPostValue("aExInvoiceId");
            $dodetail->ExInvoiceNo = $this->GetPostValue("aExInvoiceNo");
            $dodetail->ItemId = $this->GetPostValue("aItemId");
            $dodetail->ExInvDetailId = $this->GetPostValue("aExInvDetailId");
            $dodetail->ItemCode = $this->GetPostValue("aItemCode");
            $dodetail->ItemDescs = $this->GetPostValue("aItemDescs");
            $dodetail->QtyOrder = $this->GetPostValue("aQtyOrder");
            $dodetail->QtyDelivered = $this->GetPostValue("aQtyDelivered");
            // insert ke table
            $rs = $dodetail->Insert()== 1;
            if ($rs > 0) {
                $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Add Return detail -> Ex.Inv No: '.$dodetail->ExInvoiceNo.' -> Item Code: '.$dodetail->ItemCode.' = '.$dodetail->QtyDelivered,$delivery->DoNo,'Success');
                echo json_encode(array());
            } else {
                $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Add Return detail -> Ex.Inv No: '.$dodetail->ExInvoiceNo.' -> Item Code: '.$dodetail->ItemCode.' = '.$dodetail->QtyDelivered,$delivery->DoNo,'Failed');
                echo json_encode(array('errorMsg'=>'Some errors occured.'));
            }
        }
	}

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $dodetail = new DeliveryDetail();
        $dodetail = $dodetail->FindById($id);
        if ($dodetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($dodetail->Delete($id,$dodetail->ExInvoiceId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Delete Return detail -> Ex.Inv No: '.$dodetail->ExInvoiceNo.' -> Item Code: '.$dodetail->ItemCode.' = '.$dodetail->QtyDelivered,$dodetail->DoNo,'Success');
            printf("Data Detail ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.delivery','Delete Return detail -> Ex.Inv No: '.$dodetail->ExInvoiceNo.' -> Item Code: '.$dodetail->ItemCode.' = '.$dodetail->QtyDelivered,$dodetail->DoNo,'Success');
            printf("Maaf, Data Detail ID: %d gagal dihapus!",$id);
        }
    }

    public function getDeliveryItemRows($id){
        $delivery = new Delivery();
        $rows = $delivery->GetDeliveryItemRow($id);
        print($rows);
    }

    public function getjson_returnlists($cabangId,$customerId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $delivery = new Delivery();
        $retlists = $delivery->GetJSonDeliverys($cabangId,$customerId,$filter);
        echo json_encode($retlists);
    }

    //proses cetak form invoice
    public function do_print($doctype = 'do') {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Harap pilih data yang akan dicetak !");
            redirect_url("inventory.delivery");
            return;
        }
        $report = array();
        foreach ($ids as $id) {
            $do = new Delivery($id);
            $do->LoadDetails();
            $report[] = $do;
        }
        $this->Set("doctype", $doctype);
        $this->Set("report", $report);
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "master/contacts.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/expedition.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sContactsId = $this->GetPostValue("ContactsId");
            $sExpeditionId = $this->GetPostValue("ExpeditionId");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $delivery = new Delivery();
            if ($sJnsLaporan == 1){
                $reports = $delivery->Load4Reports($this->userCompanyId,$sCabangId,$sContactsId,$sExpeditionId,$sStartDate,$sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $delivery->Load4ReportsDetail($this->userCompanyId,$sCabangId,$sContactsId,$sExpeditionId,$sStartDate,$sEndDate);
            }else{
                $reports = $delivery->Load4ReportsRekapItem($this->userCompanyId,$sCabangId,$sContactsId,$sExpeditionId,$sStartDate,$sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sExpeditionId = 0;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $sJnsLaporan = 1;
            $reports = null;
        }
        $customer = new Contacts();
        $customer = $customer->LoadByType(1);
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
        $this->Set("ExpeditionId",$sExpeditionId);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("JnsLaporan",$sJnsLaporan);
        //load expedition
        $loader = new Expedition();
        $expeditions = $loader->LoadByCompany($this->userCompanyId);
        $this->Set("expeditions", $expeditions);
    }
}


// End of File: estimasi_controller.php
