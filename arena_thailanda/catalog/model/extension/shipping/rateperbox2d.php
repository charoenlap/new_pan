<?php
class ModelExtensionShippingRateperbox2d extends Model {
	function getQuote($address) {
		 $this->language->load('extension/shipping/rateperbox2d'); 
		 $quote_data = array();
		 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");
	 
		 foreach ($query->rows as $result) {
				if ($this->config->get('shipping_rateperbox2d_' . $result['geo_zone_id'] . '_status')) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

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
				$cart_size = $this->cart->getSize(); 
				$rates = explode(',', $this->config->get('shipping_rateperbox2d_' . $result['geo_zone_id'] . '_rate_2d')); 
				// print_r($rates);
				foreach ($rates as $rate) {
					$data = explode(':', $rate);
				 	 // print_r($data);
					if ($cart_size <= $data[0]) {
						if (isset($data[1])) {
							$cost = $data[1];
						}
						break;
					}
				}
			} else { 
				$cost = 0;
			}

		    $method_data = array();
			$quote_data = array();
	 
	  		$quote_data['rateperbox2d'] = array(
				'code'         => 'rateperbox2d.rateperbox2d',
				'title'        => $this->language->get('text_description'),
				'cost'         => $cost,
				'tax_class_id' => $this->config->get('shipping_rateperbox2d_tax_class_id'),
				'text'         => $this->currency->format($cost, $this->session->data['currency']) 
	  		);

	  		$method_data = array(
	    		'code'       => 'rateperbox2d',
	    		'title'      => '',
	    		'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_rateperbox2d_sort_order'),
	    		'error'      => false
	  		);
	 
			return $method_data;
		}
	}
} 