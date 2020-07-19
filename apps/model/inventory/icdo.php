<?php

require_once("icdo_detail.php");

class IcDo extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $DoStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
        2 => "CLOSED",
		3 => "VOID"
	);

    public static $CollectStatusCodes = array(
        0 => "ON HOLD",
        1 => "ON PROCESS",
        2 => "PAID",
        3 => "VOID"
    );

	public $Id;
    public $IsDeleted = false;
    public $EntityId;
    public $AreaId;
    public $EntityCode;
    public $CompanyName;
    public $CabangId;
    public $CabangCode;
	public $DoNo;
	public $DoDate;
    public $CustomerId;
    public $CustomerCode;
    public $CustomerName;
    public $CustomerAddress;
	public $DoDescs;
	public $ExpeditionId;
    public $DriverName;
    public $VehicleNumber;
    public $DoStatus;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;

	/** @var IcDoDetail[] */
	public $Details = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
        $this->Id = $row["id"];
        $this->IsDeleted = $row["is_deleted"] == 1;
        $this->EntityCode = $row["entity_cd"];
        $this->EntityId = $row["entity_id"];
        $this->AreaId = $row["area_id"];
        $this->CompanyName = $row["company_name"];
        $this->CabangId = $row["cabang_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->DoNo = $row["do_no"];
        $this->DoDate = strtotime($row["do_date"]);
        $this->CustomerId = $row["customer_id"];
        $this->CustomerCode = $row["customer_code"];
        $this->CustomerName = $row["customer_name"];
        $this->CustomerAddress = $row["customer_address"];
        $this->DoDescs = $row["do_descs"];
        $this->ExpeditionId = $row["expedition_id"];
        $this->DriverName = $row["driver_name"];
        $this->VehicleNumber = $row["vehicle_number"];
        $this->DoStatus = $row["do_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
	}

	public function FormatDoDate($format = HUMAN_DATE) {
		return is_int($this->DoDate) ? date($format, $this->DoDate) : date($format, strtotime(date('Y-m-d')));
	}

	/**
	 * @return IcDoDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new IcDoDetail();
		$this->Details = $detail->LoadByDoId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return IcDo
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_do_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_do_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByDoNo($DoNo,$cabangId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_do_master AS a WHERE a.do_no = ?DoNo And a.cabang_id = ?cabangId";
		$this->connector->AddParameter("?DoNo", $DoNo);
        $this->connector->AddParameter("?cabangId", $cabangId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByEntityId($entityId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_do_master AS a WHERE a.entity_id = ?entityId";
        $this->connector->AddParameter("?entityId", $entityId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new IcDo();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_do_master AS a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new IcDo();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ic_do_master (cabang_id, do_no, do_date, customer_id, do_descs, expedition_id, driver_name, vehicle_number, do_status, createby_id, create_time)";
        $sql.= "VALUES(?cabang_id, ?do_no, ?do_date, ?customer_id, ?do_descs, ?expedition_id, ?driver_name, ?vehicle_number, ?do_status, ?createby_id, now())";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?do_no", $this->DoNo, "varchar");
		$this->connector->AddParameter("?do_date", $this->DoDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
		$this->connector->AddParameter("?do_descs", $this->DoDescs);
        $this->connector->AddParameter("?expedition_id", $this->ExpeditionId == null ? 0 : $this->ExpeditionId);
        $this->connector->AddParameter("?driver_name", $this->DriverName);
        $this->connector->AddParameter("?vehicle_number", $this->VehicleNumber);
        $this->connector->AddParameter("?do_status", $this->DoStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ic_do_master SET
	cabang_id = ?cabang_id
	, do_no = ?do_no
	, do_date = ?do_date
	, customer_id = ?customer_id
	, do_descs = ?do_descs
	, expedition_id = ?expedition_id
	, driver_name = ?driver_name
	, vehicle_number = ?vehicle_number
	, do_status = ?do_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?do_no", $this->DoNo, "varchar");
        $this->connector->AddParameter("?do_date", $this->DoDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?do_descs", $this->DoDescs);
        $this->connector->AddParameter("?expedition_id", $this->ExpeditionId == null ? 0 : $this->ExpeditionId);
        $this->connector->AddParameter("?driver_name", $this->DriverName);
        $this->connector->AddParameter("?vehicle_number", $this->VehicleNumber);
        $this->connector->AddParameter("?do_status", $this->DoStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}

	public function Delete($id) {
        $this->connector->CommandText = "Delete From t_ic_do_master WHERE id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        $this->connector->CommandText = "Update t_ic_do_master a Set a.do_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetIcDoDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'DNO';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->DoDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    //$reports = $rj->Load4Reports($sCabangId,$sCustomerId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate);
    public function Load4Reports($entityId, $cabangId = 0, $customerId = 0, $DoStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ic_do_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.do_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($DoStatus > -1){
            $sql.= " and a.do_status = ".$DoStatus;
        }else{
            $sql.= " and a.do_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Order By a.do_date,a.do_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($entityId, $cabangId = 0, $customerId = 0, $DoStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT	a.*, b.item_code,b.ex_invoice_no,b.item_descs,b.qty_retur,b.price,b.sub_total FROM vw_ic_do_master AS a JOIN t_ic_do_detail b ON a.do_no = b.do_no";
        $sql.= " WHERE a.is_deleted = 0 and a.do_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($DoStatus > -1){
            $sql.= " and a.do_status = ".$DoStatus;
        }else{
            $sql.= " and a.do_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Order By a.do_date,a.do_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($entityId, $cabangId = 0, $customerId = 0, $DoStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT b.item_code,b.item_descs,c.bsatkecil as satuan,coalesce(sum(b.qty_retur),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total";
        $sql.= " FROM vw_ic_do_master AS a Join t_ic_do_detail AS b On a.do_no = b.do_no Left Join m_barang AS c On b.item_code = c.bkode";
        $sql.= " WHERE a.is_deleted = 0 and a.do_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($DoStatus > -1){
            $sql.= " and a.do_status = ".$DoStatus;
        }else{
            $sql.= " and a.do_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Group By b.item_code,b.item_descs,c.bsatkecil Order By b.item_descs,b.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetJSonIcDos($cabangId,$customerId) {
        $sql = "SELECT a.id,a.do_no,a.do_date,a.expedition_id - a.driver_name as do_balance FROM t_ic_do_master as a Where a.expedition_id > a.driver_name And a.is_deleted = 0 And a.cabang_id = ".$cabangId." And a.customer_id = ".$customerId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.do_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

}


// End of File: estimasi.php
