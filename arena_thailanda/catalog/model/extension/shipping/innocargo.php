<?php
class ModelExtensionShippingInnocargo extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/innocargo');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_innocargo_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_innocargo_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows == 0) {
			$status = true;
		} else {
			$status = false;
		}

		if ($this->cart->getSubTotal() < $this->config->get('shipping_innocargo_total')) {
			$status = false;
		}

		$method_data = array();
		$output['supliers_exp'] = array();

		if ($status) {
			$quote_data = array();

			$country_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$address['country_id'] . "'");
			// print_r($country_info->row['innocargo_id']);
			
			$temp = array(1=>'DHL', 2=>'FedEx', 3=>'TNT');


			for ($i=1; $i<=3; $i++) {
				// $get_url = "https://www.innocargo.com/view/ajax/ajax_cal_test.php?type=1&country=80&supplier=1&weight=1";
				// $cURL = curl_init();
				// curl_setopt($cURL, CURLOPT_URL, $get_url);
				// curl_setopt($cURL, CURLOPT_HTTPGET, true);
				// curl_setopt($cURL, CURLOPT_RETURNTRANSFER,TRUE);
				// curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
				// 'Content-Type: application/json',
				// 'Accept: application/json'
				// ));
				// // $ch = curl_init();
				// // $url = "https://www.innocargo.com/view/ajax/ajax_cal_test.php?type=1&country=".$country_info->row['innocargo_id']."&supplier=".$i."&weight=1";
				// // curl_setopt($ch,CURLOPT_URL,$url);
				// // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				// // CURLOPT_HTTPHEADER, array(
				// //     'Content-Type: application/json',
				// //     'Accept: application/json'
				// //   ));
				// $server_output = curl_exec($cURL);
				// var_dump($server_output);
				// // $server_output = trim($server_output, "\xEF\xBB\xBF");
				// curl_close ($cURL);
				// $tt = json_decode($server_output, true);


				 // ! For ssl Innocargo
				$arrContextOptions=array(
					"ssl"=>array(
						"verify_peer"=>false,
						"verify_peer_name"=>false,
					),
				);  
				 $url = 'https://www.innocargo.com/view/ajax/ajax_cal_test.php?type=2&country='.(int)$country_info->row['innocargo_id'].'&supplier='.$i.'&weight=';
				 $response =  file_get_contents($url, false, stream_context_create($arrContextOptions));
				 $tt = json_decode($response, true);


				$output['supliers_exp'][] = array(
					'name'          => $temp[$i],
					'price'         => $tt['Express'],
				);
			}

			foreach ($output['supliers_exp'] as $supliers) {
				// if ($supliers['price']>0) {
					if (file_exists('image/shipping/'.'innocargo.'.strtolower($supliers['name']).'.png')) {
						$image = 'image/shipping/'.'innocargo.'.strtolower($supliers['name']).'.png';
					} else {
						$image = 'http://placehold.it/150x150/ffffff/333333/&text=No Image';
					}

					$quote_data[strtolower($supliers['name'])] = array(
						'code'         => 'innocargo.'.strtolower($supliers['name']),
						'image'        => $image,
						'title'        => $supliers['name'],
						'cost'         => $supliers['price'],
						'tax_class_id' => 0,
						'text'         => $this->currency->format($supliers['price'], $this->session->data['currency'])
					);
				// }
			}

			// Thaipost
			// if (ceil($this->cart->getWeight()) < 1) {
			// 	$cost = (double)$country_info->row['price_start'];
			// } else {
			// 	$cost = (double)$country_info->row['price_start'] + ( ceil($this->cart->getWeight() - 1) * $country_info->row['price_peritem'] );
			// }

			// $cost += (double)$country_info->row['price_extra'];

			// $cost += 80;

			// $image = 'image/shipping/thaipost.png';

			// $quote_data['thaipost'] = array(
			// 	'code'         => 'innocargo.thaipost',
			// 	'image'        => $image,
			// 	'title'        => 'Thaipost',
			// 	'cost'         => $cost,
			// 	'tax_class_id' => 0,
			// 	'text'         => $this->currency->format($cost, $this->session->data['currency'])
			// );

			$method_data = array(
				'code'       => 'innocargo',
				'title'      => 'Innocargo (Weight: '.$this->cart->getWeight().' kg.)',
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_innocargo_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}


	public function addDbSameInnocargo() {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://www.sportstown-online.com/temp.json");
		curl_setopt($ch, CURLOPT_POST, 0);
		// curl_setopt($ch, CURLOPT_POSTFIELDS,"postvar1=value1&postvar2=value2&postvar3=value3");
		// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('country_id' => '74', 'weight' => '1')));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);

		// print_r($server_output);
		$output = json_decode($server_output, true);
		// exit();

		foreach ($output as $value) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE name LIKE '%" . $this->db->escape(trim($value['c_name_en'])) . "%' AND innocargo_id = 0");
			if ($query->num_rows > 0) {
				echo 'Update <span style="color:green;">SUCCESS</span> #'.$query->row['country_id'].' '.$query->row['name'].' = id:'.$value['c_id'].' '.$value['c_name_en'].'<br>';
				$this->db->query("UPDATE " . DB_PREFIX . "country SET innocargo_id = '" . $value['c_id'] . "' WHERE country_id = '" . (int)$query->row['country_id'] . "'");
			} else {
				echo 'Update <span style="color:red;">FAIL</span> id:'.$value['c_id'].' '.$value['c_name_en'].' // ';
				echo "SELECT * FROM " . DB_PREFIX . "country WHERE name LIKE '%" . $this->db->escape($value['c_name_en']) . "%'";
				echo '<br>';
			}
		}

		// $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE name LIKE '%" .  . "%'");
	}
}