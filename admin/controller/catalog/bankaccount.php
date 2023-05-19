<?php    
class ControllerCatalogBankaccount extends Controller { 
	private $error = array();
  
  	public function index() {
		 $this->language->load('catalog/bankaccount');
		 $this->document->setTitle('Bank Account');
		 $this->load->model('catalog/bankaccount');
		 $this->model_catalog_bankaccount->dbinstall();  
    	 $this->getList();
  	}
  
  	public function insert() {
		 $this->language->load('catalog/bankaccount');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/bankaccount');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST')/* && $this->validateForm()*/) {
			$this->model_catalog_bankaccount->addBankaccount($this->request->post); 
			$this->session->data['success'] = $this->language->get('text_success'); 
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			$this->response->redirect($this->url->link('catalog/bankaccount', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}
    
    	$this->getForm();
  	} 
   
  	public function update() {
		 $this->language->load('catalog/bankaccount');
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/bankaccount');
		 
		
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_bankaccount->editBankaccount($this->request->get['bank_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			 
			
		  $this->response->redirect($this->url->link('catalog/bankaccount', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL')); 
		}
         
    	$this->getForm();
  	}   

  	public function delete() {
		 $this->language->load('catalog/bankaccount');
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/bankaccount');
		
			
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $bank_id) {
				$this->model_catalog_bankaccount->deleteBankaccount($bank_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			  $this->response->redirect($this->url->link('catalog/bankaccount', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
    	}
	
    	$this->getList();
  	}  
    
  	public function getList() {
		 $this->language->load('catalog/bankaccount');
		$this->document->setTitle('Bank Account');
		$data['heading_title'] = $this->language->get('heading_title_bank_list');
		
		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');		
		$data['column_bank_account'] = $this->language->get('column_bank_account');		
		
		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_delete'] = $this->language->get('button_delete');
		$url = '';
  		$data['breadcrumbs'] = array();
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => 'Bank Account',
			'href'      => $this->url->link('catalog/bankaccount', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		 $data['text_confirm'] ='Do you want to delete data?';					
		$data['add'] = $this->url->link('catalog/bankaccount/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/bankaccount/delete', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');	

		$data['bankaccounts'] = array(); 
		$results = $this->model_catalog_bankaccount->getBankaccounts();
 
    	foreach ($results as $result) {
	  
			$data['bankaccounts'][] = array(
				'bank_id' => $result['bank_id'],
				'name'            => $result['banklist'],
				'sort_order'      => $result['sort_order'],
				'selected'        => isset($this->request->post['selected']) && in_array($result['bank_id'], $this->request->post['selected']),
				'action_edit'     => $this->url->link('catalog/bankaccount/update', 'user_token=' . $this->session->data['user_token'] . '&bank_id=' . $result['bank_id'] . $url, 'SSL'),
				'text_edit'          => $this->language->get('text_edit')
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

		$url = ''; 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/bankaccount_list', $data));
		
	}
  


  	protected function getForm() {
        
		$this->document->setTitle('Edit Bank account'); 
    	$data['heading_title'] = $this->language->get('heading_title_bank');
    	$data['text_enabled'] = $this->language->get('text_enabled');
    	$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
    	$data['text_image_manager'] = $this->language->get('text_image_manager');
		$data['text_browse'] = $this->language->get('text_browse');
		$data['text_clear'] = $this->language->get('text_clear');			
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['entry_status'] = $this->language->get('entry_status');		
		$data['entry_name'] = $this->language->get('entry_name'); 
		$data['entry_bankname'] = $this->language->get('entry_bankname'); 
		$data['entry_bankno'] = $this->language->get('entry_bankno'); 
		$data['entry_accname'] = $this->language->get('entry_accname'); 
		$data['entry_bankinfo'] = $this->language->get('entry_bankinfo'); 
    	$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
	
		$data['entry_customer_group'] = $this->language->get('entry_customer_group');
		  
    	$data['button_save'] = $this->language->get('button_save');
    	$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['tab_general'] = $this->language->get('tab_general');
			  
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
 		if (isset($this->error['bankname'])) {
			$data['error_name'] = $this->error['bankname'];
		} else {
			$data['error_name'] = '';
		}
		    
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => 'Bank account setup',
			'href'      => $this->url->link('catalog/bankaccount', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		if (!isset($this->request->get['bank_id'])) {
			$data['action'] = $this->url->link('catalog/bankaccount/insert', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('catalog/bankaccount/update', 'user_token=' . $this->session->data['user_token'] . '&bank_id=' . $this->request->get['bank_id'] . $url, 'SSL');
		}
		
		$data['cancel'] = $this->url->link('catalog/bankaccount', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

    	if (isset($this->request->get['bank_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$Bankaccount_info = $this->model_catalog_bankaccount->getBankaccount($this->request->get['bank_id']);
    	}


		$data['user_token'] = $this->session->data['user_token'];

    	if (isset($this->request->post['bankname'])) {
      		$data['bankname'] = $this->request->post['bankname'];
			 
    	} elseif (!empty($Bankaccount_info)) {
			$data['bankname'] = $Bankaccount_info['bankname'];
			 
		} else {	
      		$data['bankname'] = '';
    	}
		

		
    	if (isset($this->request->post['banklist'])) {
      		$data['banklist'] = $this->request->post['banklist'];
			 
    	} elseif (!empty($Bankaccount_info)) {
			$data['banklist'] = $Bankaccount_info['banklist'];
			 
		} else {	
      		$data['banklist'] = '';
    	}



        
        if (isset($this->request->post['acc_name'])) {
      		$data['acc_name'] = $this->request->post['acc_name'];
			 
    	} elseif (!empty($Bankaccount_info)) {
			$data['acc_name'] = $Bankaccount_info['acc_name'];
			 
		} else {	
      		$data['acc_name'] = '';
    	}
  

	   if (isset($this->request->post['acc_no'])) {
      		$data['acc_no'] = $this->request->post['acc_no'];
			 
    	} elseif (!empty($Bankaccount_info)) {
			$data['acc_no'] = $Bankaccount_info['acc_no'];
			 
		} else {	
      		$data['acc_no'] = '';
    	}


		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($Bankaccount_info)) {
			$data['status'] =  $Bankaccount_info['status'];
		} else {
			$data['status'] = 1;
		}

		 
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($Bankaccount_info)) {
			$data['image'] = $Bankaccount_info['image'];
		} else {
			$data['image'] = '';
		}
		
		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && file_exists(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($Bankaccount_info) && $Bankaccount_info['image'] && file_exists(DIR_IMAGE . $Bankaccount_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($Bankaccount_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
 
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset($this->request->post['sort_order'])) {
      		$data['sort_order'] = $this->request->post['sort_order'];
    	} elseif (!empty($Bankaccount_info)) {
			$data['sort_order'] = $Bankaccount_info['sort_order'];
		} else {
      		$data['sort_order'] = '';
    	}
		
 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/bankaccount_form', $data));
	}  
	 
  	protected function validateForm() {
    	if (!$this->user->hasPermission('modify', 'catalog/bankaccount')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['bankname']) < 3) || (utf8_strlen($this->request->post['bankname']) > 64)) {
      		$this->error['bankname'] = $this->language->get('error_name');
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/bankaccount')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}	

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	}
	

 
} 