<?php

class ItemDeptController extends AppController {
	private $userUid;

	protected function Initialize() {
		require_once(MODEL . "master/itemdept.php");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.did", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.dkode", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.dnama", "display" => "Divisi", "width" => 300);

		$settings["filters"][] = array("name" => "a.dkode", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.dnama", "display" => "Divisi");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Divisi Barang";

			if ($acl->CheckUserAccess("master.itemdept", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.itemdept/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.itemdept", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.itemdept/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemdept terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemdept.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.itemdept", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.itemdept/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemdept terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemdept.",
					"Confirm" => "Apakah anda mau menghapus data itemdept yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_barang_dept AS a";
            $settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemDept $itemdept) {

		return true;
	}

	public function add() {
        $itemdept = new ItemDept();
        if (count($this->postData) > 0) {
            $itemdept->Dkode = $this->GetPostValue("Dkode");
            $itemdept->Dnama = $this->GetPostValue("Dnama");
            if ($this->ValidateData($itemdept)) {
                $itemdept->CreatebyId = $this->userUid;
                $rs = $itemdept->Insert();
                if ($rs == 1) {
                    $this->persistence->SaveState("info", sprintf("Data Divisi: %s (%s) sudah berhasil disimpan", $itemdept->Dnama, $itemdept->Dkode));
                    redirect_url("master.itemdept");
                } else {
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("itemdept", $itemdept);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.itemdept");
        }
        $itemdept = new ItemDept();
        if (count($this->postData) > 0) {
            $itemdept->Did = $id;
            $itemdept->Dkode = $this->GetPostValue("Dkode");
            $itemdept->Dnama = $this->GetPostValue("Dnama");
            if ($this->ValidateData($itemdept)) {
                $itemdept->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemdept->Update($id);
                if ($rs == 1) {
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $itemdept->Dnama, $itemdept->Dkode));
                    redirect_url("master.itemdept");
                } else {
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemdept = $itemdept->LoadById($id);
            if ($itemdept == null || $itemdept->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.itemdept");
            }
        }
        $this->Set("itemdept", $itemdept);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.itemdept");
        }
        $itemdept = new ItemDept();
        $itemdept = $itemdept->LoadById($id);
        if ($itemdept == null || $itemdept->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.itemdept");
        }
        $rs = $itemdept->Delete($id);
        if ($rs == 1) {
            $this->persistence->SaveState("info", sprintf("Divisi Barang: %s (%s) sudah dihapus", $itemdept->Dnama, $itemdept->Dkode));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemdept->Dnama, $itemdept->Dkode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.itemdept");
	}
}

// End of file: itemdept_controller.php
