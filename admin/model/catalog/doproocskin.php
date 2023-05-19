<?php
class ModelCatalogDoproocskin extends Model {
 
    public function check_db() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "toc_doproskin (
		  `skin_id` int(11) NOT NULL AUTO_INCREMENT,
		  `skinname` varchar(64) NOT NULL,
		  `inbox` int(11) NOT NULL,
		  `status` int(3) NOT NULL,
		  `custom_css` text NOT NULL,
		   PRIMARY KEY (`skin_id`)
		 )  ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		");
        
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "toc_doproskin   ");
        if (empty($query->row)) { 
				$this->db->query(" 
					INSERT INTO " . DB_PREFIX . "toc_doproskin ( skin_id, skinname, inbox, status) VALUES
					(1, 'luxury'   , 1, 1),
					(2, 'red-black', 0, 0),
					(3, 'deepblue' , 0, 0),
					(4, 'wood'     , 0, 0),
					(5, 'black'    , 0, 0);

				");
   		
	    }
		$query = $this->db->query("SELECT count(*) as total,max(skin_id) as max_skin FROM " . DB_PREFIX . "toc_doproskin   ");
		 if ($query->row['total']=='5' && $query->row['max_skin']=='5' ) { 
			   $this->db->query(" 
					INSERT INTO " . DB_PREFIX . "toc_doproskin ( skin_id, skinname, inbox, status,custom_css) VALUES
					(6, 'line'   , 0, 0,''),
					(7, 'farbic', 0, 0 ,''),
					(8, 'clean-blue' , 0, 0,' .caption > h4 + p {display:none;}'),
					(9, 'starry-night'  , 1, 0,' .caption > h4 + p {display:none;}'),
					(10, 'creamy'   , 1, 0,'');
			   ");
		}

	}

    public function load_skin() {
        $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "toc_doproskin order by skin_id  ");
        return $query->rows; 
    } 

   public function checkstatus($skin_id) {
        $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "toc_doproskin  where skin_id = '" . $skin_id  . "' ");
        return $query->row; 
   } 

   public function updateskin($data,$skin_id) {
         $this->db->query("UPDATE  " . DB_PREFIX . "toc_doproskin set status ='0'  ");
		 $this->db->query("UPDATE  " . DB_PREFIX . "toc_doproskin set 
		 status =" . $data['skin_status'] ."  ,
		 custom_css =   '" . $this->db->escape(html_entity_decode($data['custom_css'], ENT_QUOTES, 'UTF-8')) . "'
		 where   skin_id = '" . $this->db->escape($skin_id)   . "' ");
        
   } 



}