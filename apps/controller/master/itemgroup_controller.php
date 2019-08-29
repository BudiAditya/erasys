<?php

class ItemGroupController extends AppController {
	private $userUid;

	protected function Initialize() {
		require_once(MODEL . "master/itemgroup.php");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.bgid", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.bgkode", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.bgnama", "display" => "Kelompok", "width" => 300);

		$settings["filters"][] = array("name" => "a.bgkode", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.bgnama", "display" => "Kelompok");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Kelompok Barang";

			if ($acl->CheckUserAccess("master.itemgroup", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.itemgroup/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.itemgroup", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.itemgroup/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemgroup terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemgroup.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.itemgroup", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.itemgroup/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemgroup terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemgroup.",
					"Confirm" => "Apakah anda mau menghapus data itemgroup yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_barang_group AS a";
            $settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemGroup $itemgroup) {

		return true;
	}

	public function add() {
        $itemgroup = new ItemGroup();
        if (count($this->postData) > 0) {
            $itemgroup->BgKode = $this->GetPostValue("BgKode");
            $itemgroup->BgNama = $this->GetPostValue("BgNama");
            if ($this->ValidateData($itemgroup)) {
                $itemgroup->CreatebyId = $this->userUid;
                $rs = $itemgroup->Insert();
                if ($rs == 1) {
                    $this->persistence->SaveState("info", sprintf("Data Kelompok: %s (%s) sudah berhasil disimpan", $itemgroup->BgNama, $itemgroup->BgKode));
                    redirect_url("master.itemgroup");
                } else {
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("itemgroup", $itemgroup);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.itemgroup");
        }
        $itemgroup = new ItemGroup();
        if (count($this->postData) > 0) {
            $itemgroup->BgId = $id;
            $itemgroup->BgKode = $this->GetPostValue("BgKode");
            $itemgroup->BgNama = $this->GetPostValue("BgNama");
            if ($this->ValidateData($itemgroup)) {
                $itemgroup->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemgroup->Update($id);
                if ($rs == 1) {
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $itemgroup->BgNama, $itemgroup->BgKode));
                    redirect_url("master.itemgroup");
                } else {
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemgroup = $itemgroup->LoadById($id);
            if ($itemgroup == null || $itemgroup->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.itemgroup");
            }
        }
        $this->Set("itemgroup", $itemgroup);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.itemgroup");
        }
        $itemgroup = new ItemGroup();
        $itemgroup = $itemgroup->LoadById($id);
        if ($itemgroup == null || $itemgroup->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.itemgroup");
        }
        $rs = $itemgroup->Delete($id);
        if ($rs == 1) {
            $this->persistence->SaveState("info", sprintf("Kelompok Barang: %s (%s) sudah dihapus", $itemgroup->BgNama, $itemgroup->BgKode));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemgroup->BgNama, $itemgroup->BgKode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.itemgroup");
	}
}

// End of file: itemgroup_controller.php
