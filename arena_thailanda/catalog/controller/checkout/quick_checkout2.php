<?php
class ControllerCheckoutQuickCheckout2 extends Controller {
	public function index() {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->load->language('checkout/quick_checkout2');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_shopcart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/quick_checkout')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart2'),
			'href' => $this->url->link('checkout/quick_checkout2')
		);

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$shipping_code_cod = ($this->session->data['shipping_method']['code'] == 'pickup.pickup') ? true : false;

		$total = $order_info['total'];

		$data['payment_methods'] = array();

		$this->load->model('setting/extension');

		$method_data = $this->getPaymentMethod(209, $order_info['shipping_zone_id']);

		$data['payment_methods'] = $method_data;

		// Payment Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('payment');

		$recurring = $this->cart->hasRecurringProducts();

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);
				if (($shipping_code_cod && $result['code']=='cod') || (!$shipping_code_cod && $result['code'] != 'cod')) {
					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod(array('country_id'=>$order_info['shipping_country_id'],'zone_id'=>$order_info['shipping_zone_id']), $total);

					if ($method) {
						if ($recurring) {
							if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
								$method_data[$result['code']] = $method;
							}
						} else {
							$method_data[$result['code']] = $method;
						}
					}
				}
			}
		}

		// print_r($this->session->data);

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		$data['payment_methods'] = $method_data;


		$data['sub_total'] = $this->cart->getSubTotal();


		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}

		$data['products'] = array();

		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$data['products'][] = array(
				'href'            => $this->url->link('product/product', 'product_id='.$product['product_id']),
				'cart_id'         => $product['cart_id'],
				'product_id'      => $product['product_id'],
				'name'            => $product['name'],
				'model'           => $product['model'],
				'shipping'        => $product['shipping'],
				'image'           => $product['image'],
				'option'          => $product['option'],
				'download'        => $product['download'],
				'quantity'        => $product['quantity'],
				'minimum'         => $product['minimum'],
				'subtract'        => $product['subtract'],
				'stock'           => $product['stock'],
				'price'           => $this->currency->format($product['price'], $this->session->data['currency']),
				'total'           => $this->currency->format($product['total'], $this->session->data['currency']),
				'reward'          => $product['reward'],
				'points'          => $product['points'],
				'tax_class_id'    => $product['tax_class_id'],
				'weight'          => $product['weight'],
				'weight_class_id' => $product['weight_class_id'],
				'length'          => $product['length'],
				'width'           => $product['width'],
				'height'          => $product['height'],
				'length_class_id' => $product['length_class_id'],
				'recurring'       => $product['recurring']
			);
		}

		$data['isLogged'] = $this->customer->isLogged();

		$data['link_shipping'] = $this->url->link('checkout/quick_checkout');

		$data['show_shipping_address'] = '<h4 style="margin-bottom:5px;">'.$order_info['shipping_firstname'].' '.$order_info['shipping_lastname'].'</h4><p>'.$order_info['shipping_address_1'].' '.$order_info['shipping_address_1'].' '.$order_info['shipping_city'].' '.$order_info['shipping_zone'].' '.$order_info['shipping_country'].' <br>Tel: '.$order_info['telephone'].'</p>';

		$data['link_login'] = $this->url->link('account/login', '&redirect='.urlencode($this->url->link('checkout/quick_checkout')));




		// Banner
		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/owlcarousel/assets/owl.carousel.min.css');
		$this->document->addScript('catalog/view/javascript/owlcarousel/owl.carousel.min.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner(13);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => HTTP_SERVER.'image/'.$result['image']
					// 'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		// Related Product
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$bestseller = $this->model_catalog_product->getPopularProducts(12);
		foreach ($bestseller as $product) {

			$product_total = 0;

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], 400,400);
			} else {
				$image = '';
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$categories = $this->model_catalog_product->getCategories($product['product_id']);

			$path = array();

			foreach ($categories as $category) {
				$path[] = $category['category_id'];
			}

			$path = '&path='.implode('_', $path);

			$data['moreview_product'][] = array(
				'thumb'      => $image,
				'product_id' => $product['product_id'],
				'minimum'    => $product['minimum'],
				'name'       => utf8_substr($product['name'], 0, 18).'..',
				'model'      => $product['model'],
				'price'      => $price,
				'total'      => $total,
				'href'       => $this->url->link('product/product', $path . '&product_id=' . $product['product_id']),
				'href_encode' => $this->url->link('product/product').urlencode($path . '&product_id=' . $product['product_id'])
			);
		}
		// Top Product
		$this->load->model('catalog/product');
		$bestseller = $this->model_catalog_product->getBestSellerProducts(12);
		foreach ($bestseller as $product) {

			$product_total = 0;

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], 400,400);
			} else {
				$image = '';
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$categories = $this->model_catalog_product->getCategories($product['product_id']);

			$path = array();

			foreach ($categories as $category) {
				$path[] = $category['category_id'];
			}

			$path = '&path='.implode('_', $path);

			$data['bestseller_product'][] = array(
				'thumb'      => $image,
				'product_id' => $product['product_id'],
				'minimum'    => $product['minimum'],
				'name'       => utf8_substr($product['name'], 0, 18).'..',
				'model'      => $product['model'],
				'price'      => $price,
				'total'      => $total,
				'href'       => $this->url->link('product/product', $path . '&product_id=' . $product['product_id']),
				'href_encode' => $this->url->link('product/product').urlencode($path . '&product_id=' . $product['product_id'])
			);
		}

		// Custom Fields
		$this->load->model('account/custom_field');

		$data['custom_fields'] = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

		$data['is_login'] = $this->customer->isLogged() ? 1 : 0;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['order_id'] = $this->session->data['order_id'];

		$this->response->setOutput($this->load->view('checkout/quick_checkout2', $data));
	}

	public function updatePaymentMethod() {
		$json = array();
		if (isset($this->request->post['code']) && !empty($this->request->post['code'])) {
			$this->load->model('checkout/order');

			// Get Payment method
			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('payment');
			foreach ( $results as $result ) {
				if ($this->config->get('payment_' . $result['code'] . '_status')) {
					$this->load->model('extension/payment/' . $result['code']);

					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod(array('country_id'=>209,'zone_id'=>3192), $this->cart->getTotal());

					if ($method) {
						$method['information'] = '';
						$method_data[$result['code']] = $method;
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$payment_methods = $method_data;

			foreach ($payment_methods as $key => $value) {
				if ($key == $this->request->post['code']) {
					$temp = $value;
				}
			}

			$data = array();

			$order_info = $this->model_checkout_order->getOrder($this->request->post['order_id']);

			$order_product = $this->model_checkout_order->getOrderProducts($this->request->post['order_id']);
			foreach ($order_product as $key => $p) {
				$order_option = $this->model_checkout_order->getOrderOptions($this->request->post['order_id'], $p['order_product_id']);
				$order_product[$key]['option'] = $order_option;
			}
			
			$order_total = $this->model_checkout_order->getOrderTotals($this->request->post['order_id']);

			$order_voucher = $this->model_checkout_order->getOrderVouchers($this->request->post['order_id']);

			$data = $order_info;

			$data['products'] = $order_product;

			$data['totals'] = $order_total;

			$data['vouchers'] = $order_voucher;

			$data['payment_method'] = $temp['title'];
			$data['payment_code'] = $temp['code'];

			$this->model_checkout_order->editOrder($this->request->post['order_id'], $data);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function payment_view () {
		$json = array();
		$this->session->data['payment_method']['code'] = $this->request->post['code'];
		$json = $this->load->controller('extension/payment/'.$this->request->post['code']);


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getzone() {
		$json = array();
		if ($this->request->post['country_id']>0) {
			$this->load->model('localisation/zone');
			$zones = $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']);
			$text = '';
			foreach ($zones as $zone) {
				$text .= '<option value="'.$zone['zone_id'].'">'.$zone['name'].'</option>';
			}
			$json = $text;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getPaymentMethod ($country_id, $zone_id) {

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('payment');
		foreach ( $results as $result ) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				$method = $this->{'model_extension_payment_' . $result['code']}->getMethod(array('country_id'=>$country_id,'zone_id'=>$zone_id), $this->cart->getTotal());

				if ($method) {
					$method['information'] = '';
					$method_data[$result['code']] = $method;
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		return $method_data;
	}

	public function confirm() {
		$json = array();

		$this->load->model('account/address');

		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		// Shipping Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote(array('country_id'=>209,'zone_id'=>3192));

				if ($quote) {
					$method_data[$result['code']] = array(
						'title'      => $quote['title'],
						'quote'      => $quote['quote'],
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					);
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		$shipping_methods = $method_data;

		$post_shipping_method = explode('.',$this->request->post['shipping_method']);

		foreach ($shipping_methods as $key => $value) {
			if ($post_shipping_method[0] == $key) {
				$shipping_code = $value['quote'][$post_shipping_method[1]]['code'];
				$shipping_method = $post_shipping_method[0];
			}
		}


		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'value' => $total['value'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}

		// $total = end($data['totals']['value']);

		$total = end($totals);
		$total = number_format((float)$total['value'],4,'.','');

		// Shipping
		if (isset($this->request->post['shipping_address']) && $this->request->post['shipping_address']>0 && $this->request->post['shipping_address']!='newshipping') {
				$address_info         = $this->model_account_address->getAddress($this->request->post['payment_address']);
				$country_info         = $this->model_localisation_country->getCountry($address_info['country_id']);
				$zone_info            = $this->model_localisation_zone->getZone($shipping_zone_id);
				$payment_firstname    = $address_info['firstname'];
				$payment_lastname     = $address_info['lastname'];
				$payment_company      = $address_info['company'];
				$payment_address_1    = $address_info['address_1'];
				$payment_address_2    = $address_info['address_2'];
				$payment_city         = $address_info['city'];
				$payment_postcode     = $address_info['postcode'];
				$payment_country      = $country_info['name'];
				$payment_country_id   = $address_info['country_id'];
				$payment_zone         = $zone_info['name'];
				$payment_zone_id      = $zone_info['zone_id'];
				$payment_custom_field = $address_info['custom_field'];
		} else if (isset($this->request->post['shipping_address']) && $this->request->post['shipping_address']=='newshipping') {
			$country_info          = $this->model_localisation_country->getCountry($this->request->post['shipping_country']);
			$zone_info             = $this->model_localisation_zone->getZone($this->request->post['shipping_zone']);
			$shipping_firstname    = $this->request->post['shipping_firstname'];
			$shipping_lastname     = $this->request->post['shipping_lastname'];
			$shipping_company      = $this->request->post['shipping_company'];
			$shipping_address_1    = $this->request->post['shipping_address_1'];
			$shipping_address_2    = $this->request->post['shipping_address_2'];
			$shipping_city         = $this->request->post['shipping_city'];
			$shipping_postcode     = $this->request->post['shipping_postcode'];
			$shipping_country      = $country_info['name'];
			$shipping_country_id   = $this->request->post['shipping_country'];
			$shipping_zone         = $zone_info['name'];
			$shipping_zone_id      = $this->request->post['shipping_zone'];
			$shipping_custom_field = $this->request->post['shipping_custom_field'];
		} else {
			$shipping_firstname    = '';
			$shipping_lastname     = '';
			$shipping_company      = '';
			$shipping_address_1    = '';
			$shipping_address_2    = '';
			$shipping_city         = '';
			$shipping_postcode     = '';
			$shipping_country      = '';
			$shipping_country_id   = '';
			$shipping_zone         = '';
			$shipping_zone_id      = '';
			$shipping_custom_field = '';
		}

		// Payment
		if (isset($this->request->post['payment_type']) && $this->request->post['payment_type']==1) {
			if (isset($this->request->post['payment_address']) && $this->request->post['payment_address']>0 && $this->request->post['payment_address']!='newshipping') {
				$address_info         = $this->model_account_address->getAddress($this->request->post['payment_address']);
				$country_info         = $this->model_localisation_country->getCountry($address_info['country_id']);
				$zone_info            = $this->model_localisation_zone->getZone($address_info['zone_id']);
				$payment_firstname    = $address_info['firstname'];
				$payment_lastname     = $address_info['lastname'];
				$payment_company      = $address_info['company'];
				$payment_address_1    = $address_info['address_1'];
				$payment_address_2    = $address_info['address_2'];
				$payment_city         = $address_info['city'];
				$payment_postcode     = $address_info['postcode'];
				$payment_country      = $country_info['name'];
				$payment_country_id   = $address_info['country_id'];
				$payment_zone         = $zone_info['name'];
				$payment_zone_id      = $address_info['zone_id'];
				$payment_custom_field = $address_info['custom_field'];
				// $payment_taxid        = '';
			} else if (isset($this->request->post['payment_address']) && $this->request->post['payment_address']=='newshipping') {
				$country_info         = $this->model_localisation_country->getCountry($this->request->post['payment_country']);
				$zone_info            = $this->model_localisation_zone->getZone($this->request->post['payment_zone']);
				$payment_firstname    = $this->request->post['payment_firstname'];
				$payment_lastname     = $this->request->post['payment_lastname'];
				$payment_company      = $this->request->post['payment_company'];
				$payment_address_1    = $this->request->post['payment_address_1'];
				$payment_address_2    = $this->request->post['payment_address_2'];
				$payment_city         = $this->request->post['payment_city'];
				$payment_postcode     = $this->request->post['payment_postcode'];
				$payment_country      = $country_info['name'];
				$payment_country_id   = $this->request->post['payment_country'];
				$payment_zone         = $zone_info['name'];
				$payment_zone_id      = $this->request->post['payment_zone'];
				$payment_custom_field = $this->request->post['payment_custom_field'];
				// $payment_taxid        = $this->request->post['payment_taxid'];
			} else {
				$payment_firstname    = '';
				$payment_lastname     = '';
				$payment_company      = '';
				$payment_address_1    = '';
				$payment_address_2    = '';
				$payment_city         = '';
				$payment_postcode     = '';
				$payment_country      = '';
				$payment_country_id   = '';
				$payment_zone         = '';
				$payment_zone_id      = '';
				$payment_custom_field = '';
				// $payment_taxid        = '';
			}
		} else {
			$payment_firstname    = $shipping_firstname;
			$payment_lastname     = $shipping_lastname;
			$payment_company      = $shipping_company;
			$payment_address_1    = $shipping_address_1;
			$payment_address_2    = $shipping_address_2;
			$payment_city         = $shipping_city;
			$payment_postcode     = $shipping_postcode;
			$payment_country      = $shipping_country;
			$payment_country_id   = $shipping_country_id;
			$payment_zone         = $shipping_zone;
			$payment_zone_id      = $shipping_zone_id;
			$payment_custom_field = $shipping_custom_field;
			// $payment_taxid        = '';
		}

		// echo '<pre>';
		// print_r($this->request->post);
		// echo '</pre>';
		// exit();

		// Checkout with register
		if (isset($this->request->post['password']) && !empty($this->request->post['password'])) {

			$customer_add = array(
				'firstname'  => $firstname,
				'lastname'   => $lastname,
				'email'      => $email,
				'telephone'  => $telephone,
				'password'   => $this->request->post['password'],
				'newsletter' => 1
			);

			$this->load->model('account/customer');

			// Check Email is customer?
			$customer_info = $this->model_account_customer->getCustomerByEmail($email);

			if (count($customer_info) == 0) {
				// Add New Customer
				$customer_id = $this->model_account_customer->addCustomer($customer_add);

				// Add New Address this customer
				$this->load->model('account/address');

				$address = array(
					'firstname'    => $shipping_firstname,
					'lastname'     => $shipping_lastname,
					'company'      => $shipping_company,
					'address_1'    => $shipping_address_1,
					'address_2'    => $shipping_address_2,
					'postcode'     => $shipping_postcode,
					'city'         => $shipping_city,
					'zone_id'      => $shipping_zone_id,
					'country_id'   => $shipping_country_id,
					'custom_field' => $shipping_custom_field,
					'default'      => 1
				);

				$this->model_account_address->addAddress($customer_id, $address);
			}
		}

		if (isset($this->request->post['shipping_custom_field']) && !empty($this->request->post['shipping_custom_field'])) {
			$custom_field = $this->request->post['shipping_custom_field'];
		}

		if (isset($this->request->post['email']) && !empty($this->request->post['email'])) {
			$email = $this->request->post['email'];
		}

		if (isset($this->request->post['telephone']) && !empty($this->request->post['telephone'])) {
			$telephone = $this->request->post['telephone'];
		}

		if ($this->customer->isLogged()) {
			$customer_id       = $this->customer->getId();
			$customer_group_id = $this->customer->getGroupId();
			$firstname         = $this->customer->getFirstName();
			$lastname          = $this->customer->getLastName();
			$email             = $this->customer->getEmail();
			$telephone         = $this->customer->getTelephone();
			$fax               = $this->customer->getFax();
			$custom_field      = '';
		} else {
			$customer_id       = 0;
			$customer_group_id = 1;
			$firstname         = $shipping_firstname;
			$lastname          = $shipping_lastname;
			$email             = $this->request->post['email'];
			$telephone         = $this->request->post['telephone'];
			$fax               = '';
			$custom_field      = '';
		}

		$order_data['shipping_firstname']      = $shipping_firstname;
		$order_data['shipping_lastname']       = $shipping_lastname;
		$order_data['shipping_company']        = $shipping_company;
		$order_data['shipping_address_1']      = $shipping_address_1;
		$order_data['shipping_address_2']      = $shipping_address_2;
		$order_data['shipping_city']           = $shipping_city;
		$order_data['shipping_postcode']       = $shipping_postcode;
		$order_data['shipping_country']        = $shipping_country;
		$order_data['shipping_country_id']     = $shipping_country_id;
		$order_data['shipping_zone']           = $shipping_zone;
		$order_data['shipping_zone_id']        = $shipping_zone_id;
		$order_data['shipping_custom_field']   = $shipping_custom_field;
		$order_data['shipping_address_format'] = '';

		$order_data['shipping_method'] = $shipping_method;
		$order_data['shipping_code'] = $shipping_code;

		$order_data['comment'] = '';

		$order_data['total'] = $total;

		$order_data['order_status_id'] = 1;
		$order_data['affiliate_id'] = 0;
		$order_data['commission'] = 0.0000;
		$order_data['marketing_id'] = 0;
		$order_data['tracking'] = (isset($this->session->data['tracking'])&&!empty($this->session->data['tracking']))?$this->session->data['tracking']:'';
		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		$order_data['forwarded_ip'] = '';

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}

		$order_data['payment_method'] = $this->request->post['payment_method'];
		$order_data['payment_code'] = $this->request->post['payment_method'];

		$order_data['payment_firstname']      = $payment_firstname;
		$order_data['payment_lastname']       = $payment_lastname;
		$order_data['payment_company']        = $payment_company;
		$order_data['payment_address_1']      = $payment_address_1;
		$order_data['payment_address_2']      = $payment_address_2;
		$order_data['payment_city']           = $payment_city;
		$order_data['payment_postcode']       = $payment_postcode;
		$order_data['payment_country']        = $payment_country;
		$order_data['payment_country_id']     = $payment_country_id;
		$order_data['payment_zone']           = $payment_zone;
		$order_data['payment_zone_id']        = $payment_zone_id;
		$order_data['payment_custom_field']   = $payment_custom_field;
		$order_data['payment_address_format'] = '';

		$order_data['customer_id']       = $customer_id;
		$order_data['customer_group_id'] = $customer_group_id;
		$order_data['firstname']         = $firstname;
		$order_data['lastname']          = $lastname;
		$order_data['email']             = $email;
		$order_data['telephone']         = $telephone;
		$order_data['fax']               = '';
		$order_data['custom_field']      = $custom_field;

		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['invoice_no'] = 0;$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');

		if ($order_data['store_id']) {
			$order_data['store_url'] = $this->config->get('config_url');
		} else {
			if ($this->request->server['HTTPS']) {
				$order_data['store_url'] = HTTPS_SERVER;
			} else {
				$order_data['store_url'] = HTTP_SERVER;
			}
		}

		$order_data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
			}

			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => token(10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}

		$this->load->model('checkout/order');
		$order_id = $this->model_checkout_order->addOrder($order_data);

		$this->model_checkout_order->addOrderHistory($order_id, 1, $order_data['comment'], true, true);

		$this->cart->clear();

		$json = $order_id;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function shipping_method() {

		// Shipping Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote(array('country_id'=>209,'zone_id'=>3192));

				if ($quote) {
					$method_data[$result['code']] = array(
						'title'      => $quote['title'],
						'quote'      => $quote['quote'],
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					);
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		$data['shipping_methods'] = $method_data;

		$json['all'] = $data['shipping_methods'];

		$post_shipping_method = explode('.', $this->request->post['shipping_method']);

		// Check from all
		foreach ($data['shipping_methods'] as $key => $value) {
			if ($key == $post_shipping_method[0]) {
				$this->session->data['shipping_method']['code'] = $value['quote'][$post_shipping_method[1]]['code'];
				$this->session->data['shipping_method']['title'] = $value['quote'][$post_shipping_method[1]]['title'];
				$this->session->data['shipping_method']['cost'] = $value['quote'][$post_shipping_method[1]]['cost'];
				$this->session->data['shipping_method']['tax_class_id'] = $value['quote'][$post_shipping_method[1]]['tax_class_id'];
				$this->session->data['shipping_method']['text'] = $value['quote'][$post_shipping_method[1]]['text'];
			}
		}


		// $this->response->addHeader('Content-Type: application/json');
		// $this->response->setOutput(json_encode($json));
	}

}