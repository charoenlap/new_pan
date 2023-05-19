<?php
class ControllerExtensionModuleHomeCategory extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/home_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('home_category', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/home_category', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/home_category', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/home_category', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/home_category', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
 
        if (!empty($module_info['category1'])) {
            $data['category1'] = $module_info['category1'];
        } else {
            $data['category1'] = '';
        }
        if (!empty($module_info['category2'])) {
            $data['category2'] = $module_info['category2'];
        } else {
            $data['category2'] = '';
        }
        if (!empty($module_info['category3'])) {
            $data['category3'] = $module_info['category3'];
        } else {
            $data['category3'] = '';
        }
        if (!empty($module_info['category4'])) {
            $data['category4'] = $module_info['category4'];
        } else {
            $data['category4'] = '';
        }
        if (!empty($module_info['category5'])) {
            $data['category5'] = $module_info['category5'];
        } else {
            $data['category5'] = '';
        }
        if (!empty($module_info['category6'])) {
            $data['category6'] = $module_info['category6'];
        } else {
            $data['category6'] = '';
        }
        if (!empty($module_info['category7'])) {
            $data['category7'] = $module_info['category7'];
        } else {
            $data['category7'] = '';
        }
        
        // Category
        $this->load->model('catalog/category');
        $data['categories'] = array();
		$categories = $this->model_catalog_category->getCategories(array('filter_category_id'=>0));
		foreach ($categories as $category) {
			$subcategory = $this->model_catalog_category->getCategories(array('filter_category_id'=>$category['category_id'])); 
			if (count($subcategory)>0) {
				$data['categories'][] = array(
					'name' => $category['name'],
					'category_id' => $category['category_id']
				);
				foreach ($subcategory as $sub) {
					$data['categories'][] = array(
						'name' => $sub['name'],
						'category_id' => $sub['category_id']
					);
				}
			} else {
				$data['categories'][] = array(
					'name' => $category['name'],
					'category_id' => $category['category_id']
				);
			}
		}
		
		// Image
		$this->load->model('tool/image');
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($module_info)) {
			$data['image'] = $module_info['image'];
		} else {
			$data['image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		if (isset($this->request->post['image2'])) {
			$data['image2'] = $this->request->post['image2'];
		} elseif (!empty($module_info)) {
			$data['image2'] = $module_info['image2'];
		} else {
			$data['image2'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		if (isset($this->request->post['image3'])) {
			$data['image3'] = $this->request->post['image3'];
		} elseif (!empty($module_info)) {
			$data['image3'] = $module_info['image3'];
		} else {
			$data['image3'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($module_info) && is_file(DIR_IMAGE . $module_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($module_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }
        
        if (isset($this->request->post['image2']) && is_file(DIR_IMAGE . $this->request->post['image2'])) {
            $data['thumb2'] = $this->model_tool_image->resize($this->request->post['image2'], 100, 100);
        } elseif (!empty($module_info) && is_file(DIR_IMAGE . $module_info['image2'])) {
            $data['thumb2'] = $this->model_tool_image->resize($module_info['image2'], 100, 100);
        } else {
            $data['thumb2'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }
        
        if (isset($this->request->post['image3']) && is_file(DIR_IMAGE . $this->request->post['image3'])) {
            $data['thumb3'] = $this->model_tool_image->resize($this->request->post['image3'], 100, 100);
        } elseif (!empty($module_info) && is_file(DIR_IMAGE . $module_info['image3'])) {
            $data['thumb3'] = $this->model_tool_image->resize($module_info['image3'], 100, 100);
        } else {
            $data['thumb3'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }




		// if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
		// 	$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        //     $data['thumb2'] = $this->model_tool_image->resize($this->request->post['image2'], 100, 100);
        //     $data['thumb3'] = $this->model_tool_image->resize($this->request->post['image3'], 100, 100);
		// } elseif (!empty($product_info) && is_file(DIR_IMAGE . $product_info['image'])) {
		// 	$data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
        //     $data['thumb2'] = $this->model_tool_image->resize($product_info['image2'], 100, 100);
        //     $data['thumb3'] = $this->model_tool_image->resize($product_info['image3'], 100, 100);
		// } else {
		// 	$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		// 	$data['thumb2'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		// 	$data['thumb3'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		// }

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder2'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder3'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/home_category', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/home_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}