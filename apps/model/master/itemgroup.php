<?php
class ItemGroup extends EntityBase {
	public $BgId;
	public $IsDeleted = false;
	public $BgKode;
	public $BgNama;
    public $CreatebyId;
    public $UpdatebyId;

	public function __construct($bgid = null) {
		parent::__construct();
		if (is_numeric($bgid)) {
			$this->FindById($bgid);
		}
	}

	public function FillProperties(array $row) {
		$this->BgId = $row["bgid"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->BgKode = $row["bgkode"];
		$this->BgNama = $row["bgnama"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.bgkode", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.* FROM m_barang_group AS a ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.* FROM m_barang_group AS a WHERE a.is_deleted = 0 ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemGroup();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $bgid
	 * @return Location
	 */
	public function FindById($bgid) {
		$this->connector->CommandText = "SELECT a.* FROM m_barang_group AS a WHERE a.bgid = ?bgid";
		$this->connector->AddParameter("?bgid", $bgid);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	/**
	 * @param int $bgid
	 * @return Location
	 */
	public function LoadById($bgid) {
		return $this->FindById($bgid);
	}

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO m_barang_group(bgkode,bgnama,createby_id,create_time) VALUES(?bgkode,?bgnama,?createby_id,now())';
		$this->connector->AddParameter("?bgkode", $this->BgKode);
        $this->connector->AddParameter("?bgnama", $this->BgNama);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($bgid) {
		$this->connector->CommandText = 'UPDATE m_barang_group SET bgkode = ?bgkode, bgnama = ?bgnama, updateby_id = ?updateby_id, update_time = now() WHERE bgid = ?bgid';
		$this->connector->AddParameter("?bgkode", $this->BgKode);
        $this->connector->AddParameter("?bgnama", $this->BgNama);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?bgid", $bgid);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($bgid) {
		$this->connector->CommandText = 'UPDATE m_barang_group SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE bgid = ?bgid';
		$this->connector->AddParameter("?bgid", $bgid);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

}
