<?php
class ControllerExtensionTotalTocdiscountqty extends Controller {
	private $error = array(); 
	
	public function index() {   

        $this->language->load('extension/total/tocdiscount_qty');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_tocdiscount_qty', $this->request->post);			
			$this->session->data['success'] = $this->language->get('text_success');		
			$this->response->redirect($this->url->link('extension/extension/toctotal', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}			
		$data['heading_title'] = $this->language->get('heading_title');
        $data['entry_rate'] = $this->language->get('entry_rate');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');
	 
		$data['text_percent_discount'] = $this->language->get('text_percent_discount');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		 
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_reference_detail'] = $this->language->get('text_reference_detail');
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_total'),
			'href'      => $this->url->link('extension/extension/toctotal', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/total/tocdiscount_qty', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$data['action'] = $this->url->link('extension/total/tocdiscount_qty', 'user_token=' . $this->session->data['user_token'], 'SSL');
		
		$data['cancel'] = $this->url->link('extension/extension/toctotal', 'user_token=' . $this->session->data['user_token'], 'SSL');
		

		

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
 
		$this->load->model('localisation/geo_zone');
		
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		
		if (isset($this->request->post['total_tocdiscount_qty_percent'])) {
			$data['total_tocdiscount_qty_percent'] = $this->request->post['total_tocdiscount_qty_percent'];
		} else {
			$data['total_tocdiscount_qty_percent'] = $this->config->get('total_tocdiscount_qty_percent');
		}
		
 
		if (isset($this->request->post['total_tocdiscount_qty_status'])) {
			$data['total_tocdiscount_qty_status'] = $this->request->post['total_tocdiscount_qty_status'];
		} else {
			$data['total_tocdiscount_qty_status'] = $this->config->get('total_tocdiscount_qty_status');
		}
		
		if (isset($this->request->post['total_tocdiscount_qty_sort_order'])) {
			$data['total_tocdiscount_qty_sort_order'] = $this->request->post['total_tocdiscount_qty_sort_order'];
		} else {
			$data['total_tocdiscount_qty_sort_order'] = $this->config->get('total_tocdiscount_qty_sort_order');
		}				
 
	    $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/total/tocdiscount_qty', $data));
	}
	

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/tocdiscount_qty')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}