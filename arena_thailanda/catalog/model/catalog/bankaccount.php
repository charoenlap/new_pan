<?php
class ModelCatalogBankaccount extends Model
  {
 
	public function getbank($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "bankaccount where status = '1' " ;
 
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
 
} 