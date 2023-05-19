<?php
class ModelExtensionShippingTocthaipostreg extends Model {
	function getQuote($address) {
		 $this->language->load('extension/shipping/thaipostreg');
		
		$quote_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		foreach ($query->rows as $result) {
			if ($this->config->get('shipping_tocthaipostreg_' . $result['geo_zone_id'] . '_status')) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}
	
			if ($status) {

           	$cost = 0;
					$cart_subtotal = $this->cart->getTotal();
					
					$rates = explode(',', $this->config->get('shipping_tocthaipostreg_' . $result['geo_zone_id'] . '_rate' ));
					// print_r($rates);
					foreach ($rates as $rate) {
  						$data = explode(':', $rate);

  					//	 print_r($data);
						if ($cart_subtotal <= $data[0]) {
							if (isset($data[1])) {
    							$cost = $data[1];
							}
					
   							break;
  						}
					}
		}	  else { 
		    	$cost = 0;
		}			
            $method_data = array();
			$quote_data = array();
			
      		$quote_data['tocthaipostreg'] = array(
        		'code'         => 'tocthaipostreg.tocthaipostreg',
        		'title'        => '<img src= "' . $this->config->get('config_url') . 'image/data/shipping/regicon.png" />' . $this->language->get('text_description'),
        	     'cost'         =>  $cost ,
        		'tax_class_id' => $this->config->get('shipping_tocthaipostreg_tax_class_id'),
				'text'         => $this->currency->format($cost, $this->session->data['currency'])
 
      		);

          

      		$method_data = array(
        		'code'       => 'tocthaipostreg',
        		'title'      => '',
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_tocthaipostreg_sort_order'),
        		'error'      => false
      		);
	 
	
		return $method_data;

 




	}
}



		
	 


} 