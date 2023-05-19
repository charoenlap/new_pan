<?php
class ModelCatalogTocPayment extends Model {
	
	public function dbinstall() { 
		 $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "toc_payment (
				  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(100) NOT NULL,
				  `tel` varchar(100) NOT NULL,
				  `order_ref` int(11) NOT NULL,
				  `amount_paid` decimal(10,2) NOT NULL,
				  `bank_acc` varchar(100) NOT NULL,
				  `date_transfer` varchar(100) NOT NULL,
				  `email` varchar(100) NOT NULL,
				  `slip_ref` varchar(100) NOT NULL,
				  `comment` text NOT NULL,
				  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `session_id` varchar(100) NOT NULL,
				  PRIMARY KEY (`payment_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
		"); 

      }
	  
	public function setHide($id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "toc_payment WHERE payment_id = '" . (int)$id . "'");
		if ($query->num_rows == 1) {
			if ($query->row['is_hide'] == 1) {
				$hide = 0;
			} else {
				$hide = 1;
			}
			$this->db->query("UPDATE " . DB_PREFIX . "toc_payment SET is_hide = '" . (int)$hide . "' WHERE payment_id = '" . (int)$id . "';");
		}
	}
	  
	public function getNotification($data) {
		$sql = "SELECT *,b.name as original_name FROM " . DB_PREFIX . "toc_payment  a LEFT JOIN   " . DB_PREFIX . "upload b ON a.slip_ref = b.code 
		
		order by a.date_added desc  ";
		 
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 30;
			}	
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		 return $query->rows;
	}
	
	public function getTotal() {
		 $query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "toc_payment  ");
		 return $query->row['total'];
	}
	 
}