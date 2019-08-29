<?php

require_once("order_detail.php");

class Order extends EntityBase {
    private $editableDocId = array(1, 2, 3, 4);

    public static $SoStatusCodes = array(
        0 => "DRAFT",
        1 => "POSTED",
        2 => "CLOSED",
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
    public $SoNo;
    public $SoDate;
    public $CustomerId;
    public $CustomerCode;
    public $CustomerName;
    public $SalesName;
    public $SoDescs;
    public $ExReffNo;
    public $RequestDate;
    public $BaseAmount;
    public $Disc1Pct;
    public $Disc1Amount;
    public $Disc2Pct;
    public $Disc2Amount;
    public $TaxPct;
    public $TaxAmount;
    public $OtherCosts;
    public $OtherCostsAmount;
    public $TotalAmount;
    public $PaidAmount;
    public $CreditTerms;
    public $SoStatus;
    public $CreatebyId;
    public $CreateTime;
    public $UpdatebyId;
    public $UpdateTime;
    public $PaymentType;

    /** @var SoDetail[] */
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
        $this->CompanyName = $row["company_name"];
        $this->CabangId = $row["cabang_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->SoNo = $row["so_no"];
        $this->SoDate = strtotime($row["so_date"]);
        $this->CustomerId = $row["customer_id"];
        $this->CustomerCode = $row["customer_code"];
        $this->CustomerName = $row["customer_name"];
        $this->SalesName = $row["sales_name"];
        $this->SoDescs = $row["so_descs"];
        $this->ExReffNo = $row["ex_reff_no"];
        $this->BaseAmount = $row["base_amount"];
        $this->Disc1Pct = $row["disc1_pct"];
        $this->Disc1Amount = $row["disc1_amount"];
        $this->Disc2Pct = $row["disc2_pct"];
        $this->Disc2Amount = $row["disc2_amount"];
        $this->TaxPct = $row["tax_pct"];
        $this->TaxAmount = $row["tax_amount"];
        $this->OtherCosts = $row["other_costs"];
        $this->OtherCostsAmount = $row["other_costs_amount"];
        $this->TotalAmount = $row["total_amount"];
        $this->PaidAmount = $row["paid_amount"];
        $this->CreditTerms = $row["credit_terms"];
        $this->RequestDate = strtotime($row["request_date"]);
        $this->SoStatus = $row["so_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
        $this->PaymentType = $row["payment_type"];
    }

    public function FormatSoDate($format = HUMAN_DATE) {
        return is_int($this->SoDate) ? date($format, $this->SoDate) : date($format, strtotime(date('Y-m-d')));
    }

    public function FormatRequestDate($format = HUMAN_DATE) {
        return is_int($this->RequestDate) ? date($format, $this->RequestDate) : null;
    }

    /**
     * @return SoDetail[]
     */
    public function LoadDetails() {
        if ($this->Id == null) {
            return $this->Details;
        }
        $detail = new OrderDetail();
        $this->Details = $detail->LoadBySoId($this->Id);
        return $this->Details;
    }

    /**
     * @param int $id
     * @return So
     */
    public function LoadById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_so_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_so_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadBySoNo($invNo,$cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_so_master AS a WHERE a.so_no = ?invNo And a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?invNo", $invNo);
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadByEntityId($entityId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_so_master AS a WHERE a.entity_id = ?entityId";
        $this->connector->AddParameter("?entityId", $entityId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Order();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_so_master AS a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Order();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function Insert() {
        $sql = "INSERT INTO t_ar_so_master (cabang_id, so_no, so_date, request_date, customer_id, sales_name, so_descs, ex_reff_no, base_amount, disc1_pct, disc1_amount, disc2_pct, disc2_amount, tax_pct, tax_amount, other_costs, other_costs_amount, paid_amount, payment_type, credit_terms, so_status, createby_id, create_time)";
        $sql.= "VALUES(?cabang_id, ?so_no, ?so_date, ?request_date, ?customer_id, ?sales_name, ?so_descs, ?ex_reff_no, ?base_amount, ?disc1_pct, ?disc1_amount, ?disc2_pct, ?disc2_amount, ?tax_pct, ?tax_amount, ?other_costs, ?other_costs_amount, ?paid_amount, ?payment_type, ?credit_terms, ?so_status, ?createby_id, now())";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?so_no", $this->SoNo, "char");
        $this->connector->AddParameter("?so_date", $this->SoDate);
        $this->connector->AddParameter("?request_date", $this->RequestDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?sales_name", $this->SalesName);
        $this->connector->AddParameter("?so_descs", $this->SoDescs);
        $this->connector->AddParameter("?ex_reff_no", $this->ExReffNo);
        $this->connector->AddParameter("?base_amount", $this->BaseAmount);
        $this->connector->AddParameter("?disc1_pct", $this->Disc1Pct);
        $this->connector->AddParameter("?disc1_amount", $this->Disc1Amount);
        $this->connector->AddParameter("?disc2_pct", $this->Disc2Pct);
        $this->connector->AddParameter("?disc2_amount", $this->Disc2Amount);
        $this->connector->AddParameter("?tax_pct", $this->TaxPct);
        $this->connector->AddParameter("?tax_amount", $this->TaxAmount);
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?so_status", $this->SoStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $this->connector->AddParameter("?other_costs", $this->OtherCosts);
        $this->connector->AddParameter("?other_costs_amount", $this->OtherCostsAmount);
        $this->connector->AddParameter("?paid_amount", $this->PaidAmount);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
        }
        return $rs;
    }

    public function Update($id) {
        $this->connector->CommandText =
            "UPDATE t_ar_so_master SET
	cabang_id = ?cabang_id
	, so_no = ?so_no
	, so_date = ?so_date
	, request_date = ?request_date
	, customer_id = ?customer_id
	, sales_name = ?sales_name
	, so_descs = ?so_descs
	, ex_reff_no = ?ex_reff_no
	, base_amount = ?base_amount
	, disc1_pct = ?disc1_pct
	, disc1_amount = ?disc1_amount
	, disc2_pct = ?disc2_pct
	, disc2_amount = ?disc2_amount
	, tax_pct = ?tax_pct
	, tax_amount = ?tax_amount
	, other_costs = ?other_costs
	, other_costs_amount = ?other_costs_amount
	, paid_amount = ?paid_amount
	, payment_type = ?payment_type
	, credit_terms = ?credit_terms
	, so_status = ?so_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?so_no", $this->SoNo, "char");
        $this->connector->AddParameter("?so_date", $this->SoDate);
        $this->connector->AddParameter("?request_date", $this->RequestDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?sales_name", $this->SalesName);
        $this->connector->AddParameter("?so_descs", $this->SoDescs);
        $this->connector->AddParameter("?ex_reff_no", $this->ExReffNo);
        $this->connector->AddParameter("?base_amount", $this->BaseAmount);
        $this->connector->AddParameter("?disc1_pct", $this->Disc1Pct);
        $this->connector->AddParameter("?disc1_amount", $this->Disc1Amount);
        $this->connector->AddParameter("?disc2_pct", $this->Disc2Pct);
        $this->connector->AddParameter("?disc2_amount", $this->Disc2Amount);
        $this->connector->AddParameter("?tax_pct", $this->TaxPct);
        $this->connector->AddParameter("?tax_amount", $this->TaxAmount);
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?so_status", $this->SoStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?other_costs", $this->OtherCosts);
        $this->connector->AddParameter("?other_costs_amount", $this->OtherCostsAmount);
        $this->connector->AddParameter("?paid_amount", $this->PaidAmount);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1){
            $this->RecalculateSoMaster($id);
        }
        return $rs;
    }

    public function Delete($id) {
        //hapus data po
        $this->connector->CommandText = "Delete From t_ar_so_master WHERE id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function Void($id) {
        //hapus data po
        $this->connector->CommandText = "Update t_ar_so_master a Set a.so_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetSoDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'SOR';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->SoDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function RecalculateSoMaster($soId){
        $sql = 'Update t_ar_so_master a Set a.base_amount = 0, a.tax_amount = 0, a.disc1_amount = 0 Where a.id = ?soId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?soId", $soId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_so_master a
Join (Select c.so_id, sum(c.sub_total) As sumPrice From t_ar_so_detail c Group By c.so_id) b
On a.id = b.so_id Set a.base_amount = b.sumPrice, a.disc1_amount = if(a.disc1_pct > 0,round(b.sumPrice * (a.disc1_pct/100),0),0) Where a.id = ?soId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?soId", $soId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_so_master a Set a.tax_amount = if(a.tax_pct > 0 And (a.base_amount - a.disc1_amount) > 0,round((a.base_amount - a.disc1_amount)  * (a.tax_pct/100),0),0) Where a.id = ?soId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?soId", $soId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetActiveSoList($cabangId,$customerId) {
        $sql = "SELECT a.id,a.so_no,a.so_date,format(a.total_amount,0) as nilai,a.so_descs FROM vw_ar_so_master as a Where a.so_status < 2 And a.is_deleted = 0 And a.cabang_id = ".$cabangId." And a.customer_id = ".$customerId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.so_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetItemSoItems($soNo,$gdId) {
        //$sql = "SELECT a.item_id,a.item_code,b.bnama as item_name,b.bsatkecil as satuan,a.qty_stock,b.is_stock,b.is_sale,b.is_purchase FROM t_ic_stockcenter AS a INNER JOIN m_barang AS b ON a.item_code = b.bkode";
        $sql = "SELECT a.item_id,a.item_code,a.item_descs as item_name,b.bsatkecil as satuan,a.order_qty-a.send_qty as qty_order,c.qty_stock,b.is_stock,b.is_sale,b.is_purchase,a.price";
        $sql.= " From t_ar_so_detail AS a Join m_barang AS b On a.item_code = b.bkode Join t_ic_stockcenter AS c On a.item_code = c.item_code";
        $sql.= " Where  c.cabang_id = ".$gdId." And a.so_no = '".$soNo."' And a.order_qty - a.send_qty > 0";
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.item_code Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function Load4Reports($entityId, $cabangId = 0, $customerId = 0, $soStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ar_so_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.so_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($soStatus > -1){
            $sql.= " and a.so_status = ".$soStatus;
        }else{
            $sql.= " and a.so_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Order By a.so_date,a.so_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($entityId, $cabangId = 0, $customerId = 0, $soStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*, b.item_code,b.item_descs,b.order_qty,b.send_qty,b.price,b.sub_total FROM vw_ar_so_master AS a Join t_ar_so_detail AS b On a.so_no = b.so_no";
        $sql.= " WHERE a.is_deleted = 0 and a.so_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($soStatus > -1){
            $sql.= " and a.so_status = ".$soStatus;
        }else{
            $sql.= " and a.so_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Order By a.so_date,a.so_no,b.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($entityId, $cabangId = 0, $customerId = 0, $soStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.customer_code,a.customer_name,b.item_code,b.item_descs,c.bsatkecil as satuan,coalesce(sum(b.order_qty),0) as sum_orderqty,coalesce(sum(b.send_qty),0) as sum_sendqty,coalesce(sum(b.order_qty - b.send_qty),0) as sum_outstandqty";
        $sql.= " FROM vw_ar_so_master AS a Join t_ar_so_detail AS b On a.so_no = b.so_no Left Join m_barang AS c On b.item_code = c.bkode";
        $sql.= " WHERE a.is_deleted = 0 and a.so_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($soStatus > -1){
            $sql.= " and a.so_status = ".$soStatus;
        }else{
            $sql.= " and a.so_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Group By a.customer_code,a.customer_name,b.item_code,b.item_descs,c.bsatkecil Order By a.customer_code,a.customer_name,b.item_descs,b.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
