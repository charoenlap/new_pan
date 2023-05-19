<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$results = $this->model_design_banner->getBanner(20);

		$data['banners_home'] = array();

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners_home'][] = array(
					'title'        => $result['title'],
					'introduction' => $result['introduction'],
					'topic'        => $result['topic'],
					'link'         => $result['link'],
					'image' => '../image/'.$result['image'],
					// 'image'        => $this->model_tool_image->resize($result['image'], 1920, 900),
				);
			}
		}



		$results = $this->model_design_banner->getBanner(19);

		$data['banner2'] = array();

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banner2'][] = array(
					'title'        => $result['title'],
					'introduction' => $result['introduction'],
					'topic'        => $result['topic'],
					'link'         => $result['link'],
					'image' => '../image/'.$result['image'],
					// 'image'        => $this->model_tool_image->resize($result['image'], 1920, 900),
				);
			}
		}



		$results = $this->model_design_banner->getBanner(23);
		$data['banner_under']['image'] = '../image/'.$results[0]['image'];
		$data['banner_under']['topic'] = $results[0]['topic'];
		$data['banner_under']['title'] = $results[0]['title'];
		$data['banner_under']['introduction'] = $results[0]['introduction'];


		$results = $this->model_design_banner->getBanner(6);

		$data['brands'] = array();

		foreach ($results as $result) {
			$image = HTTP_SERVER_MAIN.'/image/'.$result['image'];
			$data['brands'][] = array(
				'title'        => $result['title'],
				'introduction' => $result['introduction'],
				'topic'        => $result['topic'],
				'link'         => $result['link'],
				'image'        => $image
			);
		}

		$this->load->model('catalog/category');
		$results = $this->model_catalog_category->getCategories(95);
		foreach ($results as $result) {
			$data['categories'][] = array(
				'name' => $result['name'],
				'image' => $this->model_tool_image->resize($result['image'], 640, 960),
				'href' => $this->url->link('product/category', '&path='.$result['category_id'])
			);
		}

		$this->load->model('catalog/product');
		$results = $this->model_catalog_product->getBestSellerProducts(10);
		foreach ($results as $value) {
			$temp_product_id[] = $value['product_id'];
		}
		if (count($results) < 10 && isset($temp_product_id)) {
			$filter = array(
				'filter_not_product_id'=>$temp_product_id,
				'start' => 0,
				'limit' => 10 - count($results)
			);
			$tmp = $this->model_catalog_product->getProducts($filter);

			// array_merge($results, $tmp);

			foreach ($tmp as $value) {
				$results[] = $value;
			}
		}

		$data['product_bestseller'] = array();


		$product_lasts = $this->model_catalog_product->getLatestProducts(10);
		$i=0;
		$limit = 8;
		foreach ($results as $result) {
			$i++;

			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], 400, 400);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', 400, 400);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$price_int = $result['price'];
			} else {
				$price = false;
				$price_int = 0;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$special_int = $result['special'];
			} else {
				$special = false;
				$special_int = 0;
			}

			$percent = 0;
			if ($price_int > 0 && $special_int > 0) {
				$percent = round((($price_int-$special_int) / $price_int) * 100, 2);
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}

			if ($this->config->get('config_review_status')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}

			$new = in_array($result['product_id'], $product_lasts) ? true : false;


			if ($i <= $limit ) {
				$data['product_bestseller'][] = array(
					'manufacturer_id' => $result['manufacturer_id'],
					'product_id'  => $result['product_id'],
					'image'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, 25) . '..',
					'price'       => $price,
					'special'     => $special,
					'percent' => $percent,
					'minimum'     => $result['minimum'],
					'tax'         => $tax,
					'new'         => $new,
					'rating'      => ($rating * 20),
					'reviews'     => $result['reviews'],
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}
		}


		$this->load->model('catalog/information');
		$information = $this->model_catalog_information->getInformation(9);
		$data['vdo'] = isset($information['description']) ? html_entity_decode($information['description'], ENT_QUOTES, 'UTF-8') : '';
		

		$data['firsthalf'] = array_slice($data['product_bestseller'], 0, (count($data['product_bestseller']) / 2));
		$data['secondhalf'] = array_slice($data['product_bestseller'], (count($data['product_bestseller']) / 2)-1);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');


		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
