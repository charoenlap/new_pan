<?php
class ControllerCheckoutQuickCheckout extends Controller {
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

		$this->load->language('checkout/quick_checkout');

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

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('checkout/quick_checkout/save');

		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		$data['countries'] = $this->model_localisation_country->getCountries();
		$data['default_country'] = 209;

		// zone thailand
		$data['zones'] = $this->model_localisation_zone->getZonesByCountryId(209);
		$data['default_zone'] = 3192;

		$data['logged'] = $this->customer->isLogged();

		$address = array();

		$this->load->model('account/address');

		$data['noaddress'] = 0;

		if ($this->customer->isLogged()) {
			$address = $this->model_account_address->getAddresses();

			if (count($address) == 0) {
				$country_id        = 209;
				$zone_id           = 3192;
				$data['noaddress'] = 1;
			} else {
				foreach ( $address as $key => $value ) {
					$data['address'][] = array(
						'address_id' => $value['address_id'],
						'country_id' => $value['country_id'],
						'type'       => isset($value['custom_field'][7]) ? $value['custom_field'][7] : '',
						'zone_id'    => $value['zone_id'],
						'info'       => '<strong>'.$value['firstname'].' '.$value['lastname'].'</strong> <br>'.$value['address_1'].' '.$value['address_2'].' '.$value['city'].' '.$value['zone'].' '.$value['country'].' '.$value['postcode'].(isset($value['custom_field'][8])&&!empty($value['custom_field'][8]) ? ' <br>โทรศัพท์: '.$value['custom_field'][8] : '')
					);
				}

				$country_id = $data['address'][0]['country_id'];
				$zone_id = $data['address'][0]['zone_id'];
			}
		} else {
			$country_id        = 209;
			$zone_id           = 3192;
			$data['noaddress'] = 1;
		}

		$data['link_redirect'] = $this->url->link('checkout/quick_checkout');

		$dropship = array(6,8,9);

		$data['customer_group_dropship'] = in_array($this->customer->getGroupId(), $dropship) ? true : false ;

		$data['payment_methods'] = array();

		$this->load->model('setting/extension');

		$method_data = $this->getPaymentMethod($country_id, $zone_id);

		$data['payment_methods'] = $method_data;

		// Shipping Methods
		$method_data = array();

		// Set Default Shipping
		// $this->session->data['shipping_method']['title'] = 'Register';
		// $this->session->data['shipping_method']['cost'] = 0;

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

		$shipping_method_code = '';

		$i=0;
		foreach ( $method_data as $value) {
			if ($i==0) {
				foreach ($value['quote'] as $quote) {
					$shipping_method_code         = $quote['code'];
					$shipping_method_title        = $quote['title'];
					$shipping_method_cost         = $quote['cost'];
					$shipping_method_tax_class_id = $quote['tax_class_id'];
					$shipping_method_text         = $quote['text'];
				}
			}
			$i++;
		}


		$data['shipping_required'] = $this->cart->hasShipping();

		$data['sub_total'] = $this->cart->getSubTotal();

		if (isset($this->session->data['shipping_method']) &&
			isset($this->session->data['shipping_method']['code'])&&$this->session->data['shipping_method']['code']!=''&&
			isset($this->session->data['shipping_method']['title'])&&$this->session->data['shipping_method']['title']!=''&&
			isset($this->session->data['shipping_method']['cost'])&&$this->session->data['shipping_method']['cost']!=''&&
			isset($this->session->data['shipping_method']['tax_class_id'])&&$this->session->data['shipping_method']['tax_class_id']!=''&&
			isset($this->session->data['shipping_method']['text'])&&$this->session->data['shipping_method']['text']!=''
		) {
		// $data['shipping_method_code'] = $this->session->data['shipping_method']['code'];
			unset($this->session->data['shipping_method']);
		} else {
			$data['shipping_method_code'] = $shipping_method_code;
			$this->session->data['shipping_method'] = array(
				'code' => $shipping_method_code,
				'title' => $shipping_method_title,
				'cost' => $shipping_method_cost,
				'tax_class_id' => $shipping_method_tax_class_id,
				'text' => $shipping_method_text,
			);
		}
		unset($this->session->data['shipping_method']);


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
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
				'value' =>$total['value']
			);
		}

		$this->session->data['order_total'] = $data['totals'][count($data['totals'])-1]['value'];

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

			if ((float)$product['special']) {
				$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
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
				'special'     => $special,
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

			if ((float)$product['special']) {
				$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
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
				'special'     => $special,
				'total'      => $total,
				'href'       => $this->url->link('product/product', $path . '&product_id=' . $product['product_id']),
				'href_encode' => $this->url->link('product/product').urlencode($path . '&product_id=' . $product['product_id'])
			);
		}


		// Banner
		$this->load->model('design/banner');
		$this->load->model('tool/image');

		// $this->document->addStyle('catalog/view/javascript/owlcarousel/assets/owl.carousel.min.css');
		// $this->document->addScript('catalog/view/javascript/owlcarousel/owl.carousel.min.js');

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

		$data['code'] = 'mg_weight_kerry.weight_5';

		$data['link_login'] = $this->url->link('account/login', '&redirect='.$this->url->link('checkout/quick_checkout'));

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

		$this->response->setOutput($this->load->view('checkout/quick_checkout', $data));
	}

	public function save() {

		// echo '<pre>';
		// print_r($this->request->post);
		// echo '</pre>';
		// exit();

		// Set Variable
		$invoice_no     = 0;
		$invoice_prefix = '';
		$store_id       = 0;
		$store_name     = '';

		$customer_id       = 0;
		$customer_group_id = 0;
		$firstname         = '';
		$lastname          = '';
		$email             = '';
		$telephone         = '';
		$fax               = '';
		$custom_field      = '';

		$payment_firstname      = '';
		$payment_lastname       = '';
		$payment_company        = '';
		$payment_address_1      = '';
		$payment_address_2      = '';
		$payment_city           = '';
		$payment_postcode       = '';
		$payment_country        = 'Thailand';
		$payment_country_id     = 209;
		$payment_zone           = '';
		$payment_zone_id        = 0;
		$payment_taxid          = '';
		$payment_address_format = '';
		$payment_custom_field   = '';
		$payment_method         = '';
		$payment_code           = '';

		$shipping_firstname    = '';
		$shipping_lastname     = '';
		$shipping_company      = '';
		$shipping_address_1    = '';
		$shipping_address_2    = '';
		$shipping_city         = '';
		$shipping_postcode     = '';
		$shipping_zone         = '';
		$shipping_zone_id      = 0;
		$shipping_telephone    = '';
		$shipping_country      = 'Thailand';
		$shipping_country_id   = 209;
		$shipping_custom_field = '';
		$shipping_method       = '';
		$shipping_code         = '';

		$comment         = '';
		$total           = 0.00;
		$order_status_id = 0;
		$affiliate_id    = 0;
		$commission      = 0.0000;
		$marketing_id    = 0;
		$tracking        = '';
		$language_id     = $this->config->get('config_language_id');
		$currency_id     = $this->currency->getId($this->session->data['currency']);
		$currency_code   = $this->session->data['currency'];
		$currency_value  = $this->currency->getValue($this->session->data['currency']);
		$ip              = $this->request->server['REMOTE_ADDR'];
		$forwarded_ip    = '';
		$user_agent      = '';
		$accept_language = '';

		$this->load->model('account/address');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');


		// Login Account
		if ($this->customer->isLogged()) {

			$customer_id = $this->customer->getId();
			$customer_group_id = $this->customer->getGroupId();
			if ($this->cart->getTotal() >= 500) {
				$shipping_method = 'ฟรีค่าจัดส่ง แบบ 1-2 วัน (เมื่อสั่งซื้อครบ 500 บาท) ';
				$shipping_code = 'free.free';
			} else {
				// $shipping_method = 'จัดส่งภายใน 1 วัน (Sameday) ';
				// $shipping_code = 'rateperboxnd.rateperboxnd';
				$shipping_method = 'Kerry ';
				$shipping_code = 'kerry.kerry';
			}
			$firstname = $this->customer->getFirstName();
			$lastname = $this->customer->getLastName();
			$telephone = $this->customer->getTelephone();
			// $fax = $this->customer->getFax();

			// New Address
			if (
				!empty($this->request->post['shipping_email']) &&
				!empty($this->request->post['shipping_firstname']) &&
				!empty($this->request->post['shipping_lastname']) &&
				!empty($this->request->post['shipping_address_1']) &&
				!empty($this->request->post['shipping_postcode']) &&
				!empty($this->request->post['shipping_city']) &&
				!empty($this->request->post['shipping_zone']) &&
				!empty($this->request->post['shipping_telephone'])
			) {

				$zone_info = $this->model_localisation_zone->getZone($this->request->post['shipping_zone']);

				$email                   = $this->request->post['shipping_email'];
				$shipping_firstname      = $this->request->post['shipping_firstname'];
				$shipping_lastname       = $this->request->post['shipping_lastname'];
				$shipping_address_1      = $this->request->post['shipping_address_1'];
				$shipping_address_2      = '';
				$shipping_postcode       = $this->request->post['shipping_postcode'];
				$shipping_city           = $this->request->post['shipping_city'];
				$shipping_zone_id        = $this->request->post['shipping_zone'];
				$shipping_zone           = $zone_info['name'];
				$shipping_country        = 'Thailand';
				$shipping_country_id     = 209;
				$shipping_telephone      = $this->request->post['shipping_telephone'];
				$shipping_address_format = '';


				$payment_firstname      = $shipping_firstname;
				$payment_lastname       = $shipping_lastname;
				$payment_company        = $shipping_company;
				$payment_address_1      = $shipping_address_1;
				$payment_address_2      = $shipping_address_2;
				$payment_city           = $shipping_city;
				$payment_postcode       = $shipping_postcode;
				$payment_country        = $shipping_country;
				$payment_country_id     = $shipping_country_id;
				$payment_zone           = $shipping_zone;
				$payment_zone_id        = $shipping_zone_id;
				$payment_taxid          = '';
				$payment_address_format = $shipping_address_format;
				$payment_custom_field   = $shipping_custom_field;
				$payment_method         = '';
				$payment_code           = '';

				$data_add['firstname']    = $shipping_firstname;
				$data_add['lastname']     = $shipping_lastname;
				$data_add['company']      = $shipping_company;
				$data_add['address_1']    = $shipping_address_1;
				$data_add['address_2']    = $shipping_address_2;
				$data_add['postcode']     = $shipping_postcode;
				$data_add['city']         = $shipping_city;
				$data_add['zone_id']      = $shipping_zone_id;
				$data_add['country_id']   = $shipping_country_id;
				$data_add['custom_field']['address'][8] = $this->request->post['shipping_telephone'];
				$data_add['custom_field']['address'][1] = '';
				$data_add['default']      = 0;

				$this->model_account_address->addAddress($customer_id, $data_add);

			// Old Address
			} else if (!empty($this->request->post['shipping_address']) && $this->request->post['shipping_address'] > 0) {

				$address_info            = $this->model_account_address->getAddress($this->request->post['shipping_address']);
				$country_info            = $this->model_localisation_country->getCountry($address_info['country_id']);
				$zone_info               = $this->model_localisation_zone->getZone($address_info['zone_id']);
				$email                   = $this->customer->getEmail();
				$shipping_firstname      = $address_info['firstname'];
				$shipping_lastname       = $address_info['lastname'];
				$shipping_company        = $address_info['company'];
				$shipping_address_1      = $address_info['address_1'];
				$shipping_address_2      = $address_info['address_2'].'xx';
				$shipping_city           = $address_info['city'];
				$shipping_postcode       = $address_info['postcode'];
				$shipping_country        = $country_info['name'];
				$shipping_country_id     = $address_info['country_id'];
				$shipping_zone           = $zone_info['name'];
				$shipping_zone_id        = $address_info['zone_id'];
				$shipping_custom_field   = $address_info['custom_field'];
				$shipping_address_format = '';


				$payment_firstname      = $shipping_firstname;
				$payment_lastname       = $shipping_lastname;
				$payment_company        = $shipping_company;
				$payment_address_1      = $shipping_address_1;
				$payment_address_2      = $shipping_address_2;
				$payment_city           = $shipping_city;
				$payment_postcode       = $shipping_postcode;
				$payment_country        = $shipping_country;
				$payment_country_id     = $shipping_country_id;
				$payment_zone           = $shipping_zone;
				$payment_zone_id        = $shipping_zone_id;
				$payment_taxid          = '';
				$payment_address_format = $shipping_address_format;
				$payment_custom_field   = $shipping_custom_field;
				$payment_method         = '';
				$payment_code           = '';

			} else {}

		// Guest
		} else {
		}


		if (
			(isset($this->request->post['shipping_email']) && !empty($this->request->post['shipping_email'])) &&
			(isset($this->request->post['shipping_firstname']) && !empty($this->request->post['shipping_firstname'])) &&
			(isset($this->request->post['shipping_lastname']) && !empty($this->request->post['shipping_lastname'])) &&
			(isset($this->request->post['shipping_address_1']) && !empty($this->request->post['shipping_address_1'])) &&
			(isset($this->request->post['shipping_postcode']) && !empty($this->request->post['shipping_postcode'])) &&
			(isset($this->request->post['shipping_city']) && !empty($this->request->post['shipping_city'])) &&
			(isset($this->request->post['shipping_zone']) && !empty($this->request->post['shipping_zone'])) &&
			(isset($this->request->post['shipping_telephone']) && !empty($this->request->post['shipping_telephone']))
		) {

			$email                   = $this->request->post['shipping_email'];
			$shipping_firstname      = $this->request->post['shipping_firstname'];
			$shipping_lastname       = $this->request->post['shipping_lastname'];
			$shipping_address_1      = $this->request->post['shipping_address_1'];
			$shipping_address_2      = '';
			$shipping_postcode       = $this->request->post['shipping_postcode'];
			$shipping_city           = $this->request->post['shipping_city'];
			$shipping_zone_id        = $this->request->post['shipping_zone'];
			$zone_info               = $this->model_localisation_zone->getZone($this->request->post['shipping_zone']);
			$shipping_zone           = $zone_info['name'];
			$shipping_country        = 'Thailand';
			$shipping_country_id     = $this->request->post['shipping_country'];
			$shipping_telephone      = $this->request->post['shipping_telephone'];
			$shipping_address_format = '';

			$payment_firstname      = $shipping_firstname;
			$payment_lastname       = $shipping_lastname;
			$payment_company        = $shipping_company;
			$payment_address_1      = $shipping_address_1;
			$payment_address_2      = $shipping_address_2;
			$payment_city           = $shipping_city;
			$payment_postcode       = $shipping_postcode;
			$payment_country        = $shipping_country;
			$payment_country_id     = $shipping_country_id;
			$payment_zone           = $shipping_zone;
			$payment_zone_id        = $shipping_zone_id;
			$payment_taxid          = '';
			$payment_address_format = $shipping_address_format;
			$payment_custom_field   = $shipping_custom_field;
			$payment_method         = '';
			$payment_code           = '';
		}


		if (!$this->customer->isLogged()) {
			$firstname = $shipping_firstname;
			$lastname  = $shipping_lastname;
			$telephone = $shipping_telephone;
		}

		if (isset($this->request->post['payment_firstname']) && !empty($this->request->post['payment_firstname'])) {
			$payment_firstname = $this->request->post['payment_firstname'];
		}
		if (isset($this->request->post['payment_lastname']) && !empty($this->request->post['payment_lastname'])) {
			$payment_lastname = $this->request->post['payment_lastname'];
		}
		if (isset($this->request->post['payment_company']) && !empty($this->request->post['payment_company'])) {
			$payment_company = $this->request->post['payment_company'];
		}
		if (isset($this->request->post['payment_address_1']) && !empty($this->request->post['payment_address_1'])) {
			$payment_address_1 = $this->request->post['payment_address_1'];
		}
		if (isset($this->request->post['payment_address_2']) && !empty($this->request->post['payment_address_2'])) {
			$payment_address_2 = $this->request->post['payment_address_2'];
		}
		if (isset($this->request->post['payment_city']) && !empty($this->request->post['payment_city'])) {
			$payment_city = $this->request->post['payment_city'];
		}
		if (isset($this->request->post['payment_postcode']) && !empty($this->request->post['payment_postcode'])) {
			$payment_postcode = $this->request->post['payment_postcode'];
		}
		if (isset($this->request->post['payment_zone']) && !empty($this->request->post['payment_zone']) && !empty($this->request->post['payment_postcode'])) {
			$payment_zone_id = $this->request->post['payment_zone'];
			$zone_info = $this->model_localisation_zone->getZone($this->request->post['payment_zone']);
			$payment_zone = $zone_info['name'];
		}
		if (isset($this->request->post['payment_taxid']) && !empty($this->request->post['payment_taxid'])) {
			$payment_taxid = $this->request->post['payment_taxid'];
		}


		// $this->session->data['shipping_method']['code']         = $shipping_method_code;
		// $this->session->data['shipping_method']['title']        = $shipping_method_title;
		// $this->session->data['shipping_method']['cost']         = $shipping_method_cost;
		// $this->session->data['shipping_method']['tax_class_id'] = $shipping_method_tax_class_id;
		// $this->session->data['shipping_method']['text']         = $shipping_method_text;

		// Shipping Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote(array('country_id'=>$shipping_country_id,'zone_id'=>$shipping_zone_id));

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

		

		// Debug total shipping
		// echo $this->request->post['shipping_method'];
		// echo '<pre>';

		foreach ($method_data as $shipping_value) {
			foreach ($shipping_value['quote'] as $key => $quote_value) {
				// print_r($quote_value);	
				$ex = explode('.', $this->request->post['shipping_method']);
				if ($key == $ex[0]) {
					$this->session->data['shipping_method'] = array(
						'code' => '',
						'title' => '',
						'cost' => 0,
						'tax_class_id' => 0,
						'text' => ''
					);
					$this->session->data['shipping_method']['code']         = $quote_value['code'];
					$this->session->data['shipping_method']['title']        = $quote_value['title'];
					$this->session->data['shipping_method']['cost']         = $quote_value['cost'];
					$this->session->data['shipping_method']['tax_class_id'] = $quote_value['tax_class_id'];
					$this->session->data['shipping_method']['text']         = $quote_value['text'];
				}
			}
		}
		// echo '</pre>';
		// exit();



		$shipping_method = $this->session->data['shipping_method']['title'];
		$shipping_code = $this->session->data['shipping_method']['code'];



		$order_data['invoice_no']        = 0;
		$order_data['invoice_prefix']    = $this->config->get('config_invoice_prefix');
		$order_data['store_id']          = $this->config->get('config_store_id');
		if ($order_data['store_id']) {
			$order_data['store_url'] = $this->config->get('config_url');
		} else {
			if ($this->request->server['HTTPS']) {
				$order_data['store_url'] = HTTPS_SERVER;
			} else {
				$order_data['store_url'] = HTTP_SERVER;
			}
		}
		$order_data['store_name']        = $this->config->get('config_name');
		$order_data['customer_id']       = $customer_id;
		$order_data['customer_group_id'] = $customer_group_id;
		$order_data['firstname']         = $firstname;
		$order_data['lastname']          = $lastname;
		$order_data['email']             = $email;
		$order_data['telephone']         = $telephone;
		$order_data['fax']               = $fax;
		$order_data['custom_field']      = $custom_field;

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
		$order_data['shipping_address_format'] = $shipping_address_format;
		$order_data['shipping_custom_field']   = $shipping_custom_field;
		$order_data['shipping_method']         = $shipping_method;
		$order_data['shipping_code']           = $shipping_code;

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
		$order_data['payment_taxid']          = '';
		$order_data['payment_address_format'] = $payment_address_format;
		$order_data['payment_custom_field']   = $payment_custom_field;
		$order_data['payment_method']         = '<img src= "https://www.sportstown-online.com/pan_sportswear/image/data/payment/bank_bbl.png" /> โอนผ่านบัญชีธนาคารกรุงเทพ';
		$order_data['payment_code']           = 'bank_bbl';

		if ($order_data['shipping_code'] == 'free.free') {
			$order_data['payment_method'] = '<img src= "https://www.sportstown-online.com/pan_sportswear/image/data/payment/bank_bbl.png" /> โอนผ่านบัญชีธนาคารกรุงเทพ';
			$order_data['payment_code'] = 'bank_bbl';
		}

		$order_data['order_status_id'] = 0;
		$order_data['comment'] = '';
		// Totals

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$this->load->model('setting/extension');

		$sort_order = array();

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			// echo $result['code'];
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

		// echo '<pre>';
		// print_r($totals);
		// echo '</pre>';
		// exit();

		$order_data['totals'] = $totals;


		$total = end($totals);
		$total = number_format((float)$total['value'],4,'.','');
		$order_data['total'] = $total;
		$this->session->data['order_total'] = $total;
		$order_data['affiliate_id'] = 0;
		$order_data['commission'] = 0.0000;
		$order_data['tracking'] = (isset($this->session->data['tracking'])&&!empty($this->session->data['tracking']))?$this->session->data['tracking']:'';
		$order_data['language_id'] = $language_id;
		$order_data['marketing_id'] = $marketing_id;
		$order_data['currency_id'] = $currency_id;
		$order_data['currency_code'] = $currency_code;
		$order_data['currency_value'] = $currency_value;
		$order_data['ip'] = $ip;
		$order_data['forwarded_ip'] = $forwarded_ip;

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


		if (isset($this->request->post['shipping_address_form'])) {
			$order_data['shipping_address_form_id'] = (int)$this->request->post['shipping_address_form'];
		} else {
			$order_data['shipping_address_form_id'] = 0;
		}
		

		// Check Debug Totals
		// $pass = false;
		// foreach ($order_data['totals'] as $value) {
		// 	if ($value['code'] == 'shipping') {

		// 	}
		// }

		if (isset($this->request->post['recommend'])) {
			$order_data['recommend'] = $this->request->post['recommend'];
		} else {
			$order_data['recommend'] = '';
		}



		$this->load->model('checkout/order');
		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
		$this->log->write("Totals Order #" . $this->session->data['order_id'] . " is : " . json_encode($order_data['totals']));

		$this->response->redirect($this->url->link('checkout/quick_checkout2'));


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

	public function varlidate () {
		$error = array();
		if (isset($this->request->post['new_shipping_address']) && $this->request->post['new_shipping_address'] == 1) {
			if (isset($this->request->post['shipping_email']) && empty($this->request->post['shipping_email'])) {
				$error['error_email'] = $this->language->get('error_email');
			}
			if (isset($this->request->post['shipping_firstname']) && empty($this->request->post['shipping_firstname'])) {
				$error['error_firstname'] = $this->language->get('error_firstname');
			}
			if (isset($this->request->post['shipping_lastname']) && empty($this->request->post['shipping_lastname'])) {
				$error['error_lastname'] = $this->language->get('error_lastname');
			}
			if (isset($this->request->post['shipping_address_1']) && empty($this->request->post['shipping_address_1'])) {
				$error['error_address_1'] = $this->language->get('error_address_1');
			}
			if (isset($this->request->post['shipping_postcode']) && empty($this->request->post['shipping_postcode'])) {
				$error['error_postcode'] = $this->language->get('error_postcode');
			}
			if (isset($this->request->post['shipping_city']) && empty($this->request->post['shipping_city'])) {
				$error['error_city'] = $this->language->get('error_city');
			}
			if (isset($this->request->post['shipping_zone']) && empty($this->request->post['shipping_zone'])) {
				$error['error_zone'] = $this->language->get('error_zone');
			}
			if (isset($this->request->post['shipping_telephone']) && empty($this->request->post['shipping_telephone'])) {
				$error['error_telephone'] = $this->language->get('error_telephone');
			}
		}

		return $error;
	}


	public function usecoupon() {
		if (isset($this->request->post['code'])&&!empty($this->request->post['code'])) {
			
		}
	}


	public function update_dbinnocargo() {
		$this->load->model('extension/shipping/innocargo');
		// $this->model_extension_shipping_innocargo->addDbSameInnocargo();
	}

	public function getShippingMethod() {

		// Shipping Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$country_id = 209;
		$zone_id = 3192;

		if ($this->request->post['country_id']) {
			$country_id = $this->request->post['country_id'];
			$this->session->data['shipping_address']['country_id'] = $country_id;
		}
		if ($this->request->post['zone_id']) {
			$zone_id = $this->request->post['zone_id'];
			$this->session->data['shipping_address']['zone_id'] = $zone_id;
		}

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote(array('country_id'=>$country_id,'zone_id'=>$zone_id));

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

		$i=0;
		$html = '';
		$shipmeth = array('free.free','kerry.kerry','jandt.jandt');
		foreach ($method_data as $shipping_method) {
			// $this->session->data['shipping_method'] = $shipping_method;
			// if (!isset($shipping_method['error'])) {
				foreach ($shipping_method['quote'] as $quote) {
					$this->session->data['shipping_method'] = $quote;

					if (
						($country_id==209 && in_array($quote['code'], $shipmeth)) ||
						($country_id!=209 && !in_array($quote['code'], $shipmeth))
					) {
						$this->session->data['shipping_method'] = $quote['title'];
						$this->session->data['shipping_code'] = $quote['code'];
						$html .= '<div class"radio" style="display:block;">';
						$html .= '<label>';
							if ($i==0) {
								$code = $quote['code'];
								$html .= '<input type="radio" name="shipping_method" value="'.$quote['code'].'" checked="checked">';
							} else {
								$html .= '<input type="radio" name="shipping_method" value="'.$quote['code'].'">';
							}
							$html .= ' '.$quote['title'].' - '.$quote['text'];
						$html .= '</label>';
						$html .= '</div>';
						$i++;
					}
				}
			// }
		}

		$json['data'] = $html;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function change_address () {

		$this->load->model('account/address');
		$address_info = $this->model_account_address->getAddress($this->request->post['address_id']);
		$json = array(
			'zone_id'    => $address_info['zone_id'],
			'country_id' => $address_info['country_id']
		);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}