<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['link_payment'] = $this->url->link('information/payment');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(95);

		$this->load->model('catalog/product');

		foreach ($categories as $category) {
			if ($category['top']) {

				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$products = array();

					$filter = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => false,
						'start'               => 0,
						'limit'               => 10
					);

					$product_info = $this->model_catalog_product->getProducts($filter);
					foreach ($product_info as $value) {
						$products[] = array(
							'product_id' => $value['product_id'],
							'name' => $value['name'],
							'image' => $this->model_tool_image->resize($value['image'],300,300),
							'href' => $this->url->link('product/product','&product_id='.$value['product_id'])
						);
					}

					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'     => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'products' => $products,
						'href'     => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				if (count($children_data) == 0) {
					$products = array();

					$filter = array(
						'filter_category_id'  => $category['category_id'],
						'filter_sub_category' => false,
						'start'               => 0,
						'limit'               => 10
					);

					$products = $this->model_catalog_product->getProducts($filter);
				}

				// Level 1
				$data['categories'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'],
					'children'    => $children_data,
					'products'    => $products,
					'column'      => $category['column'] ? $category['column'] : 1,
					'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		return $this->load->view('common/menu', $data);
	}
}
