<?php
class ExpeditionController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/expedition.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.exp_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.exp_name", "display" => "Nama Expedisi", "width" => 250);
        $settings["columns"][] = array("name" => "a.address", "display" => "Alamat", "width" => 250);
        $settings["columns"][] = array("name" => "a.cperson", "display" => "Contact Person", "width" => 100);
        $settings["columns"][] = array("name" => "a.phone", "display" => "Telephone", "width" => 100);
        $settings["columns"][] = array("name" => "a.fax", "display" => "Facsimile", "width" => 100);

		$settings["filters"][] = array("name" => "a.exp_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.exp_name", "display" => "Nama");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Data Expedisi (Pengangkutan Barang)";

			if ($acl->CheckUserAccess("master.expedition", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.expedition/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.expedition", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.expedition/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih expedition terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu expedition.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.expedition", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.expedition/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih expedition terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu expedition.",
					"Confirm" => "Apakah anda mau menghapus data expedition yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_expedition AS a ";
            $settings["where"] = "a.is_deleted = 0 And a.cabang_id = ".$this->userCabangId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Expedition $expedition) {

		return true;
	}

	public function add() {
	    $expedition = new Expedition();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $expedition->CabangId = $this->userCabangId;
            $expedition->ExpCode = $this->GetPostValue("ExpCode");
            $expedition->ExpName = $this->GetPostValue("ExpName");
            $expedition->Address = $this->GetPostValue("Address");
            $expedition->Phone = $this->GetPostValue("Phone");
            $expedition->Fax = $this->GetPostValue("Fax");
            $expedition->Cperson = $this->GetPostValue("Cperson");
            if ($this->ValidateData($expedition)) {
                $expedition->CreatebyId = $this->userUid;
                $rs = $expedition->Insert();
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.expedition','Add New Item Expedisi -> Expedisi: '.$expedition->ExpCode.' - '.$expedition->ExpName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Expedisi: %s (%s) sudah berhasil disimpan", $expedition->ExpName, $expedition->ExpCode));
                    redirect_url("master.expedition");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.expedition','Add New Item Expedisi -> Expedisi: '.$expedition->ExpCode.' - '.$expedition->ExpName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("expedition", $expedition);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.expedition");
        }
        $log = new UserAdmin();
        $expedition = new Expedition();
        if (count($this->postData) > 0) {
            $expedition->Id = $id;
            $expedition->CabangId = $this->userCabangId;
            $expedition->ExpCode = $this->GetPostValue("ExpCode");
            $expedition->ExpName = $this->GetPostValue("ExpName");
            $expedition->Address = $this->GetPostValue("Address");
            $expedition->Phone = $this->GetPostValue("Phone");
            $expedition->Fax = $this->GetPostValue("Fax");
            $expedition->Cperson = $this->GetPostValue("Cperson");
            if ($this->ValidateData($expedition)) {
                $expedition->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $expedition->Update($id);
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.expedition','Update Item Expedisi -> Expedisi: '.$expedition->ExpCode.' - '.$expedition->ExpName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Expedisi: %s (%s) sudah berhasil disimpan", $expedition->ExpName, $expedition->ExpCode));
                    redirect_url("master.expedition");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.expedition','Update Item Expedisi -> Expedisi: '.$expedition->ExpCode.' - '.$expedition->ExpName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Expedisi. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $expedition = $expedition->LoadById($id);
            if ($expedition == null || $expedition->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.expedition");
            }
        }
        $this->Set("expedition", $expedition);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.expedition");
        }
        $log = new UserAdmin();
        $expedition = new Expedition();
        $expedition = $expedition->LoadById($id);
        if ($expedition == null || $expedition->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.expedition");
        }
        $rs = $expedition->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.expedition','Delete Item Expedisi -> Expedisi: '.$expedition->ExpCode.' - '.$expedition->ExpName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Expedisi Barang: %s (%s) sudah dihapus", $expedition->ExpName, $expedition->ExpCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.expedition','Delete Item Expedisi -> Expedisi: '.$expedition->ExpCode.' - '.$expedition->ExpName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $expedition->ExpName, $expedition->ExpCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.expedition");
	}
}

// End of file: expedition_controller.php
