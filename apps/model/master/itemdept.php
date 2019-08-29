<?php
class ItemDept extends EntityBase {
	public $Did;
	public $IsDeleted = false;
	public $Dkode;
	public $Dnama;
    public $CreatebyId;
    public $UpdatebyId;

	public function __construct($did = null) {
		parent::__construct();
		if (is_numeric($did)) {
			$this->FindById($did);
		}
	}

	public function FillProperties(array $row) {
		$this->Did = $row["did"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->Dkode = $row["dkode"];
		$this->Dnama = $row["dnama"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.dkode", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.* FROM m_barang_dept AS a ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.* FROM m_barang_dept AS a WHERE a.is_deleted = 0 ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemDept();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $did
	 * @return Location
	 */
	public function FindById($did) {
		$this->connector->CommandText = "SELECT a.* FROM m_barang_dept AS a WHERE a.did = ?did";
		$this->connector->AddParameter("?did", $did);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	/**
	 * @param int $did
	 * @return Location
	 */
	public function LoadById($did) {
		return $this->FindById($did);
	}

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO m_barang_dept(dkode,dnama,createby_id,create_time) VALUES(?dkode,?dnama,?createby_id,now())';
		$this->connector->AddParameter("?dkode", $this->Dkode);
        $this->connector->AddParameter("?dnama", $this->Dnama);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($did) {
		$this->connector->CommandText = 'UPDATE m_barang_dept SET dkode = ?dkode, dnama = ?dnama, updateby_id = ?updateby_id, update_time = now() WHERE did = ?did';
		$this->connector->AddParameter("?dkode", $this->Dkode);
        $this->connector->AddParameter("?dnama", $this->Dnama);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?did", $did);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($did) {
		$this->connector->CommandText = 'UPDATE m_barang_dept SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE did = ?did';
		$this->connector->AddParameter("?did", $did);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

}
