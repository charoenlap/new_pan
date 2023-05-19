<?php
class ControllerExtensionModuleDoproocskin extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('user/user_group');
			if (!$this->user->hasPermission('modify', 'catalog/toc_payment')) {
			 $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'catalog/toc_payment');
			 $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify',  'catalog/toc_payment');
		}	
		
		$this->load->language('extension/module/doproocskin');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/doproocskin');
		$result = $this->model_catalog_doproocskin->check_db(); 
 
		$results = $this->model_catalog_doproocskin->load_skin(); 

		 foreach ($results as $result) {
		        $data['doproocskins'][] = array(
				'skin_id' => $result['skin_id'],
				'skinname'            => $result['skinname'],
				'status'              => $result['status'],
				'image'               =>  HTTP_CATALOG . 'catalog/view/theme/doproocskin/skin/' . $result['skinname'] . '.jpg'   
			);
		}	
      
      
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

	  

		
		$data['heading_title'] = $this->language->get('heading_title');
		 
        $data['text_update'] = $this->language->get('text_update');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['column_action'] = $this->language->get('column_action');
		$data['status'] = $this->language->get('entry_status');
		$data['skinname'] = $this->language->get('text_name');
		$data['text_list'] = $this->language->get('text_list');
		$data['text_image'] = $this->language->get('text_image');


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		 
		 $data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_list'),
				'href' => $this->url->link('extension/module/doproocskin', 'user_token=' . $this->session->data['user_token'], 'SSL')
		 );
		 

		$data['action'] = $this->url->link('extension/module/doproocskin/edit', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL'); 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('catalog/doproocskin_list', $data));
	}


  public function edit() {		
	   $this->load->language('extension/module/doproocskin');
 
		$this->document->setTitle($this->language->get('heading_title'));
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_edit'] = $this->language->get('text_edit');
        $data['text_status'] = $this->language->get('text_status');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		
		$data['cancel'] = $this->url->link('extension/module/doproocskin', 'user_token=' . $this->session->data['user_token'], 'SSL'); 
		 	$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		 
		 $data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('extension/module/doproocskin', 'user_token=' . $this->session->data['user_token'], 'SSL')
		 );
		 


        $data['button_save'] = $this->url->link('extension/module/doproocskin/edit', 'user_token=' . $this->session->data['user_token'] . '&skin_id=' . $this->request->get['skin_id'] , 'SSL');
        $this->load->model('catalog/doproocskin');
		$result =$this->model_catalog_doproocskin->checkstatus($this->request->get['skin_id']);
		$data['skin_status'] = $result['status'];
		$data['custom_css'] = $result['custom_css'];

		if (($this->request->server['REQUEST_METHOD'] == 'POST')  ) {
		    $this->load->model('catalog/doproocskin');
	     	$results = $this->model_catalog_doproocskin->updateskin( $this->request->post,$this->request->get['skin_id']); 

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/doproocskin', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$this->response->setOutput($this->load->view('catalog/doproocskin_form', $data));
	}
   
}