<?php 
class ControllerCatalogTocPayment extends Controller { 
	private $error = array();

	public function index() {
	 
	 
		$this->language->load('catalog/toc_payment');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/toc_payment');
		$this->model_catalog_toc_payment->dbinstall();  
	
		$this->getList();
	}

	public function hide() {
		if (isset($this->request->get['id']) && $this->request->get['id'] > 0) {
			$this->load->model('catalog/toc_payment');
			$this->model_catalog_toc_payment->setHide($this->request->get['id']);
		}

		$this->response->redirect($this->url->link('catalog/toc_payment', 'user_token=' . $this->session->data['user_token']));
	}
 
	protected function getList() {
		
        $data['heading_title'] = $this->language->get('heading_title'); 
		$data['text_date_added'] = $this->language->get('text_date_added'); 
		$data['text_date_transfer'] = $this->language->get('text_date_transfer'); 
		$data['text_name'] = $this->language->get('text_name'); 
		$data['text_tel'] = $this->language->get('text_tel'); 
		$data['text_amount'] = $this->language->get('text_amount'); 
		$data['text_orderno'] = $this->language->get('text_orderno'); 
		$data['text_comment'] = $this->language->get('text_comment'); 
		$data['text_slip'] = $this->language->get('text_slip'); 
		$data['text_bank'] = $this->language->get('text_bank'); 
		$data['text_bank'] = $this->language->get('text_bank'); 
		$data['text_email'] = $this->language->get('text_email'); 

		$data['showhide'] = $this->url->link('catalog/toc_payment', 'user_token=' . $this->session->data['user_token'] . '&show=true');
		$data['notshowhide'] = $this->url->link('catalog/toc_payment', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->request->get['show']) && $this->request->get['show'] == 'true') {
			$data['show'] = true;
		} else {
			$data['show'] = false;
		}
             
          $url ='';
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$url = '';
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

	     
	    $filter_data = array(
			'start' => ($page - 1) * 30,
			'limit' =>30
		);

	 
	    $this->load->model('tool/image');
		$data['orders'] = array();
		$results =   $this->model_catalog_toc_payment->getNotification($filter_data); 
		$notice_total =    $this->model_catalog_toc_payment->getTotal(); 
		foreach ($results as $result) {
			
	    	$file = DIR_UPLOAD . $result['filename']; 
	    	$file2 = DIR_UPLOAD2 . $result['filename']; 
			$new_name = str_replace(  $result['original_name'] . '.'  , "" , $result['filename'] ) .'-' .  $result['original_name'];
			$new_file = DIR_IMAGE . 'catalog/slip/' . $new_name;
			$thumb_file =  'catalog/slip/' . $new_name;
			// $reallink = '../../storage/upload/' . $result['filename'];
			if (is_file($new_file)) {   
				$data['thumb'] = $this->model_tool_image->resize($thumb_file  , 100, 100); 
				$data['slip']  = true;
			} else {  
				 if (is_file( $file)) { 
				     rename($file  ,  $new_file ); 
				    $data['thumb'] = $this->model_tool_image->resize($thumb_file , 100, 100);
				    $data['slip']  = true;
				 } else if (is_file( $file2)) { 
				     rename($file2  ,  $new_file ); 
				    $data['thumb'] = $this->model_tool_image->resize($thumb_file , 100, 100);
				    $data['slip']  = true;
				 } else {  
				     $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
				     $data['slip']  = false;
				 }  
			} 
			$data['orders'][] = array(
				'payment_id'    => $result['payment_id'], 
				'name'          => $result['name'],
				'tel'           => $result['tel'],
				'order_ref'     => $result['order_ref'],
				'amount_paid'   => $result['amount_paid'] ,
				'comment'       => $result['comment'] ,
				'bank_acc'      => strip_tags(html_entity_decode($result['bank_acc'], ENT_QUOTES, 'UTF-8'))     ,
				'date_transfer' => $result['date_transfer'] ,
				'slip_img'      => $data['thumb'] ,
				// 'reallink' => $reallink,
				'slip'          => $data['slip'] ,
				'is_hide' => $result['is_hide'],
				'email'         => $result['email'] ,
				'date_added'    => $result['date_added'] ,
				'href'          => HTTP_CATALOG . 'image/' . $thumb_file ,
				'href_order'    =>  $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_ref'] , 'SSL') ,
				'hidelink'      => $this->url->link('catalog/toc_payment/hide', 'user_token=' . $this->session->data['user_token'] . '&id='.$result['payment_id'])
			);
		}
       	$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		 
		$url = '';
		$pagination = new Pagination();
		$pagination->total = $notice_total;
		$pagination->page =  $page;
		$pagination->limit = 30;
		$pagination->url = $this->url->link('catalog/toc_payment', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($notice_total) ? (($page - 1) * 30) + 1 : 0, ((($page - 1) * 30) > ($notice_total - 30)) ? $notice_total : ((($page - 1) * 30) + 30), $notice_total, ceil($notice_total / 30));
 
     
	  	$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('catalog/toc_payment', $data));
		
	}
 
}