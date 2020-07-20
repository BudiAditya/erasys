<?php

class DeliveryDetail extends EntityBase {
	public $Id;
    public $CabangId;
	public $DoId;
    public $DoNo;
	public $ItemDescs;
    public $ItemCode;
    public $ItemId;
    public $ExInvoiceId;
    public $ExInvoiceNo;
    public $ExInvDetailId;
    public $QtyOrder = 0;
	public $QtyDelivered = 0;
    public $SatBesar;
    public $SatKecil;
    public $IsiKecil = 1;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->DoId = $row["do_id"];
        $this->CabangId = $row["cabang_id"];
        $this->DoNo = $row["do_no"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_descs"];                
        $this->ExInvoiceId = $row["ex_invoice_id"];
        $this->ExInvoiceNo = $row["ex_invoice_no"];
        $this->ExInvDetailId = $row["ex_invdetail_id"];
        $this->QtyOrder = $row["qty_order"];
		$this->QtyDelivered = $row["qty_delivered"];
        $this->SatBesar = $row["bsatbesar"];
        $this->SatKecil = $row["bsatkecil"];
        $this->IsiKecil = $row["bisisatkecil"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.bsatbesar,b.bsatkecil,b.bisisatkecil FROM t_ic_delivery_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.bsatbesar,b.bsatkecil,b.bisisatkecil FROM t_ic_delivery_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByDoId($DoId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,b.bsatbesar,b.bsatkecil,b.bisisatkecil FROM t_ic_delivery_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.do_id = ?DoId ORDER BY $orderBy";
		$this->connector->AddParameter("?DoId", $DoId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new DeliveryDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByDoNo($invoiceNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.bsatbesar,b.bsatkecil FROM t_ic_delivery_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.do_no = ?invoiceNo ORDER BY $orderBy";
        $this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new DeliveryDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ic_delivery_detail(do_id, cabang_id, do_no, item_id, item_code, item_descs, ex_invoice_id, ex_invoice_no, qty_order, qty_delivered, ex_invdetail_id)
VALUES(?do_id, ?cabang_id, ?do_no, ?item_id, ?item_code, ?item_descs, ?ex_invoice_id, ?ex_invoice_no, ?qty_order, ?qty_delivered, ?ex_invdetail_id)";
		$this->connector->AddParameter("?do_id", $this->DoId);
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?do_no", $this->DoNo);
        $this->connector->AddParameter("?item_id", $this->ItemId);
		$this->connector->AddParameter("?item_code", $this->ItemCode, "char");
        $this->connector->AddParameter("?item_descs", $this->ItemDescs);
        $this->connector->AddParameter("?ex_invoice_id", $this->ExInvoiceId);
        $this->connector->AddParameter("?ex_invoice_no", $this->ExInvoiceNo);
        $this->connector->AddParameter("?qty_order", $this->QtyOrder);
		$this->connector->AddParameter("?qty_delivered", $this->QtyDelivered);
        $this->connector->AddParameter("?ex_invdetail_id", $this->ExInvDetailId);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        $did = 0;
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
			$sql = "Update t_ar_invoice_detail a 
Left Join (Select c.ex_invoice_id,c.ex_invdetail_id,sum(c.qty_delivered) as qty_del From t_ic_delivery_detail c Group By c.ex_invoice_id,c.ex_invdetail_id) b
ON a.invoice_id = b.ex_invoice_id And a.id = b.ex_invdetail_id
Set a.qty_delivered = coalesce(b.qty_del,0)
Where a.invoice_id = ".$this->ExInvoiceId;
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();
		}
		return $rs;
	}

	public function Delete($id,$ivi) {
        //baru hapus detail
		$this->connector->CommandText = "DELETE FROM t_ic_delivery_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs){
            $sql = "Update t_ar_invoice_detail a 
Left Join (Select c.ex_invoice_id,c.ex_invdetail_id,sum(c.qty_delivered) as qty_del From t_ic_delivery_detail c Group By c.ex_invoice_id,c.ex_invdetail_id) b
ON a.invoice_id = b.ex_invoice_id And a.id = b.ex_invdetail_id
Set a.qty_delivered = coalesce(b.qty_del,0)
Where a.invoice_id = ".$ivi;
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();
        }
        return $rs;
	}
}
// End of File: estimasi_detail.php
