<?php
class ModelCatalogTocPayment extends Model {
	public function getOrder($order_id) {
		 $query = $this->db->query("SELECT
		  order_id FROM `" . DB_PREFIX . "order`   
		 WHERE  order_id = '" . (int)$order_id . "'
		");

		 return $query->rows;
	}
	
	public function PaymentNotification($data) {
	 
	 $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "toc_payment  
	 WHERE session_id = '" . $this->db->escape($this->session->getId()) . "'
	       and order_ref ='" . (int)($data['orderno']) . "'
	 ");
	  if ($query->rows) {
		 
		    $this->db->query("UPDATE " . DB_PREFIX . "toc_payment  SET 
			    name = '" . $this->db->escape($data['name']) . "', 
				tel = '" . $this->db->escape($data['tel']) . "',
				order_ref  = '" . (int)($data['orderno']) . "',
				amount_paid = '" . (double)($data['amount_paid']) . "',
				bank_acc =  '" . $this->db->escape($data['bank']) . "',
				date_transfer = '" . $this->db->escape($data['transfer_date']) . "',
				email = '" . $this->db->escape($data['email']) . "',
				slip_ref = '" . $this->db->escape($data['file_code']) . "',
				comment  = '" . $this->db->escape($data['enquiry']) . "',
				session_id = '" . $this->db->escape($this->session->getId()) . "',
				date_added = NOW()
				
				WHERE session_id = '" . $this->db->escape($this->session->getId()) . "' and order_ref ='" . (int)($data['orderno']) . "'
		    ");
			
	  } else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "toc_payment  SET 
			     name = '" . $this->db->escape($data['name']) . "', 
				tel = '" . $this->db->escape($data['tel']) . "',
				order_ref  = '" . (int)($data['orderno']) . "',
				amount_paid = '" . (double)($data['amount_paid']) . "',
				bank_acc =  '" . $this->db->escape($data['bank']) . "',
				date_transfer = '" . $this->db->escape($data['transfer_date']) . "',
				email = '" . $this->db->escape($data['email']) . "',
				slip_ref = '" . $this->db->escape($data['file_code']) . "',
				comment  = '" . $this->db->escape($data['enquiry']) . "',
				session_id = '" . $this->db->escape($this->session->getId()) . "',
				date_added = NOW()
			");
  
	  }
	}
}