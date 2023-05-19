<?php
class ModelCatalogBankaccount extends Model {
 
	public function dbinstall() { 
		 $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "bankaccount(
		  `bank_id` int(11) NOT NULL AUTO_INCREMENT,
		  `bankname` varchar(64) NOT NULL,
		  `banklist` varchar(64) NOT NULL,
		  `acc_name` varchar(64) NOT NULL,
		  `acc_no` varchar(64) NOT NULL,
		  `image` varchar(255) DEFAULT NULL,
		  `status` int(3) NOT NULL,
		  `sort_order` int(3) NOT NULL,
		  PRIMARY KEY (`bank_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;
		"); 

      }
	   
	  
	public function addBankaccount($data) {
 

      	$this->db->query("INSERT INTO " . DB_PREFIX . "bankaccount SET 
		 banklist = '" . $this->db->escape($data['banklist']) . "'
		 ,bankname = '" . $this->db->escape($data['bankname']) . "'
		 ,acc_name = '" . $this->db->escape($data['acc_name']) . "'
		,acc_no = '" . $this->db->escape($data['acc_no']) . "'
		, sort_order = '" . (int)$data['sort_order'] . "'
		, status = '" . (int)$data['status'] . "'
		
		");
		

		$bank_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "bankaccount SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE bank_id = '" . (int)$bank_id . "'");
		}
		
		 
		
		$this->cache->delete('Bankaccount');
	}
	



	public function editBankaccount($bank_id, $data) {

      	$this->db->query("UPDATE " . DB_PREFIX . "bankaccount SET 
		 bankname = '" . $this->db->escape($data['bankname']) . "'
		,banklist = '" . $this->db->escape($data['banklist']) . "'
		,acc_name = '" . $this->db->escape($data['acc_name']) . "'
		,acc_no = '" . $this->db->escape($data['acc_no']) . "'
		, sort_order = '" . (int)$data['sort_order'] . "'
		, status = '" . (int)$data['status'] . "'
		WHERE bank_id = '" . (int)$bank_id . "'");



		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "bankaccount SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE bank_id = '" . (int)$bank_id . "'");
		}
		 
		
		$this->cache->delete('Bankaccount');
	}
	



	public function deleteBankaccount($bank_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "bankaccount WHERE bank_id = '" . (int)$bank_id . "'");
 
		$this->cache->delete('Bankaccount');
	}	


	
	public function getBankaccount($bank_id) {
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "bankaccount WHERE bank_id = '" . (int)$bank_id . "'");
 
		return $query->row;
	}

	

	public function getBankaccounts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "bankaccount";
 
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
 
} 