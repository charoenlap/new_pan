<?php
class ModelExtensionShippingJandt extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/jandt');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_jandt_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_jandt_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		$total = $this->cart->getTotal();
		$cost = 0;

		if ($total>0 && $total<500) {
			$cost = 49.00;
		}

		if ($address['country_id']!=209) { $status = false; }

		if ($status) {
			$quote_data = array();

			$quote_data['jandt'] = array(
				'code'         => 'jandt.jandt',
				'title'        => $this->language->get('text_description'),
				'cost'         => $cost,
				'tax_class_id' => 0,
				'text'         => $this->currency->format($cost, $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'jandt',
				'title'      => $this->language->get('text_title').$total,
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_jandt_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}