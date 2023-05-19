<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		$data['facebook'] = $this->config->get('config_social_facebook_arenathailand');
		$data['google'] = $this->config->get('config_social_google_arenathailand');

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}




		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$categories = $this->model_catalog_category->getCategories(95);

		$data['category'] = array();

		$i=1;

		foreach ($categories as $category) {

			if ($category['top']) {
				$products = array();

				$filter = array(
					'filter_category_id'     => $category['category_id'],
					'filter_sub_category'    => false,
					'start'                  => 0,
					'limit'                  => 4
				);

				$products = $this->model_catalog_product->getProducts($filter);

				$temp = array();

				foreach ($products as $product) {
					$temp[] = array(
						'name' => $product['name'],
						'href' => $this->url->link('product/product','&path=95_'.$category['category_id'].'&product_id='.$product['product_id'])
					);
				}

				if ( $i <= 2 ) {
					$data['category'][] = array(
						'name'    => $category['name'],
						'href'    => $this->url->link('product/category','path=95_'.$category['category_id']),
						'product' => $temp
					);
				}
				$i++;
			}

			

		}



		$data['scripts'] = $this->document->getScripts('footer');
		
		return $this->load->view('common/footer', $data);
	}
}
