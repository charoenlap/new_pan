<?php 
class ControllerInformationPayment extends Controller {
    private $error = array(); 
  	public function index() {
        $this->language->load('information/payment');
    	$this->document->setTitle($this->language->get('heading_title'));  
  
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			 
			  $this->load->model('catalog/toc_payment');
			 // database recorded
			 $this->model_catalog_toc_payment->PaymentNotification($this->request->post); 

            unset($this->session->data['captcha']);
			$str_massage = "";
			$str_massage .= '<div style="font-size:15px;
			               color:#FF6363; 
						   margin:0 auto; 
						   width:100%;
						   line-height:30px;
						   padding:80px 0 0 80px;
					    " >' ;	 
			$str_massage .=  '<span style="color:#000; padding-right:20px;" >' . $this->language->get('entry_name')  .'</span>'.  $this->request->post['name']  . '<br/> ' ;
			$str_massage .=  '<span style="color:#000; padding-right:20px;" >' .$this->language->get('text_orderno')  . '</span>'.  $this->request->post['orderno'] . '<br/> ' ;
			$str_massage .=  '<span style="color:#000; padding-right:20px;" >' . $this->language->get('text_bank_account') . '</span>'.  html_entity_decode($this->request->post['bank'], ENT_QUOTES, 'UTF-8')  . '<br/> ' ;  
			$str_massage .= '<span style="color:#000; padding-right:20px;" >' . $this->language->get('entry_email') . '</span>' . $this->request->post['email']  . '<br/> ' ;
			$str_massage .= '<span style="color:#000; padding-right:20px;" >' . $this->language->get('entry_tel') . '</span>' . $this->request->post['tel'] . '<br/> ' ;
			$str_massage .=  '<span style="color:#000; padding-right:20px;" >' .$this->language->get('text_transfer_date') . '</span>'.  $this->request->post['transfer_date']  . '<br/> ' ;
			$str_massage .=  '<span style="color:#000; padding-right:20px;" >' .$this->language->get('text_amount_paid')   . '</span>'.  $this->request->post['amount_paid'] .$this->language->get('text_curr_unit')   . '<br/> ' ;
			$str_massage .= '<span style="color:#000; padding-right:20px;" >' . $this->language->get('entry_enquiry') . '</span>' . $this->request->post['enquiry']  . '<br/> ' ;
	        $str_massage .=  '</div>' ;
 
 
	        $str_subject= $this->language->get('email_subject') . '&nbsp;&nbsp;#&nbsp;'.  $this->request->post['orderno'];
 
		    $mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($str_subject), ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($str_massage, ENT_QUOTES, 'UTF-8'); 
            $mail->send(); 
 
            $this->response->redirect($this->url->link('information/payment/success'));   
	 

    	}

 
  	   // get bank account // 
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
 
         $this->load->model('tool/image'); 
         $this->load->model('catalog/bankaccount');
		 $data['bankaccounts'] = array();
		 $bankaccounts   = $this->model_catalog_bankaccount->getbank();
     
         foreach ($bankaccounts as $bankaccount) {
		  $data['bankaccounts'][] = array(
					 'bankname' => $bankaccount['bankname'].' </br>  '.$this->language->get('text_accname'). '   '. $bankaccount['acc_name'] . '  </br>  '. $this->language->get('text_accno'). '   '. $bankaccount['acc_no'],
				     'thumb' =>  $this->model_tool_image->resize($bankaccount['image'],100,100) ,
					  'bank_id' => $bankaccount['bank_id'] 
				);
        }

      	$data['breadcrumbs'] = array();
      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('information/payment'),
        	'separator' => $this->language->get('text_separator')
      	);	
			
    	$data['heading_title'] = $this->language->get('heading_title');
		$data['text_bank_account'] = $this->language->get('text_bank_account');
        $data['text_transfer_date'] = $this->language->get('text_transfer_date');
        $data['text_amount_paid'] = $this->language->get('text_amount_paid');
		$data['text_orderno'] = $this->language->get('text_orderno');
		$data['text_curr_unit'] = $this->language->get('text_curr_unit');
        $data['text_listbank_account'] = $this->language->get('text_listbank_account');
    	$data['entry_name'] = $this->language->get('entry_name');
    	$data['entry_email'] = $this->language->get('entry_email');
    	$data['entry_enquiry'] = $this->language->get('entry_enquiry');
		$data['entry_captcha'] = $this->language->get('entry_captcha');
		$data['entry_slip'] = $this->language->get('entry_slip');
        $data['text_openslip'] = $this->language->get('text_openslip');
		$data['entry_tel'] = $this->language->get('entry_tel');
	    $data['upload_slip'] = $this->language->get('upload_slip');
		
		
    	
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}
		

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		} 
		
		
        if (isset($this->request->post['slip'])) {
			$data['slip'] =   $this->request->post['slip'];
		} else {
			$data['slip'] = '-';
		}
		
		 if (isset($this->request->post['bank_id'])) {
			$data['bank_id'] =   $this->request->post['bank_id'];
		} else {
			$data['bank_id'] = '0';
		}
		
		 
		
		
         if (isset($this->request->post['file_uploaded']) &&  $this->request->post['file_uploaded'] !=='' ) {
			$data['file_uploaded'] = '<span>Slip ' . $this->request->post['file_uploaded'] .  ' has been uploaded</span>'   ;
		} else {
			$data['file_uploaded'] = '';
		}
            

        if (isset($this->request->post['tel'])) {
			$data['tel'] = $this->request->post['tel'];
		 } else {
			$data['tel'] = '';
		}  
		 

		if (isset($this->request->post['orderno'])) {
			$data['orderno'] = $this->request->post['orderno'];
		} else {
			$data['orderno'] = '';
		}
		

	   if (isset($this->request->post['amount_paid'])) {
			$data['amount_paid'] = $this->request->post['amount_paid'];
		} else {
			$data['amount_paid'] = '';
		}



       if (isset($this->request->post['transfer_date'])) {
			$data['transfer_date'] = $this->request->post['transfer_date'];
		} else {
			$data['transfer_date'] = '';
		}

 
		if (isset($this->request->post['file_uploaded']) && $this->request->post['file_uploaded'] !=='' ) { 
     		$data['file_uploaded'] = '<span>Slip '  .  $this->request->post['file_uploaded'] . ' has been uploaded</span>'; 
		} else { 
			$data['file_uploaded'] = ''; 
		}
		
		
     	if (isset($this->request->post['bank'])) {
			$data['bank'] = $this->request->post['bank'];
		} else {
			$data['bank'] = '';
			
		}
		
		if (isset($this->request->post['file_code'])) {
			$data['file_code'] = $this->request->post['file_code'];
		} else {
			$data['file_code'] = '';
			
		}
		
       //*** check error ****** /
 
		if (isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'] ;
		} else {
			$data['enquiry'] = '';
		}

		
		if (isset($this->request->post['captcha'])) {
			$data['captcha'] = $this->request->post['captcha'];
		} else {
			$data['captcha'] = '';
		}		

	 	if (isset($this->error['name'])) {
    		$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		 
 		if (isset($this->error['captcha'])) {
			$data['error_captcha'] = $this->error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}	
 
       if (isset($this->error['file_code'])) {
			$data['error_file_code'] = $this->error['file_code'];
		} else {
			$data['error_file_code'] = '';
		}	
		
	 
         if (isset($this->error['transfer_date'])) {
    		$data['error_transfer_date'] = $this->error['transfer_date'];
		} else {
			$data['error_transfer_date'] = '';
		}
		
		
        if (isset($this->error['orderno'])) {
    		$data['error_orderno'] = $this->error['orderno'];
		} else {
			$data['error_orderno'] = '';
		}
		
        if (isset($this->error['email'])) {
    		$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}
		
        if (isset($this->error['tel'])) {
    		$data['error_tel'] = $this->error['tel'];
		} else {
			$data['error_tel'] = '';
		}
		
		 if (isset($this->error['amount_paid'])) {
    		$data['error_amount_paid'] = $this->error['amount_paid'];
		} else {
			$data['error_amount_paid'] = '';
		}
		
		 if (isset($this->error['bank'])) {
    		$data['error_bank'] = $this->error['bank'];
		} else {
			$data['error_bank'] = '';
		}
		
		
		
    	$data['button_continue'] = $this->language->get('button_send');
    
		$data['action'] = $this->url->link('information/payment');
		$data['store'] = $this->config->get('config_name');
    	$data['address'] = nl2br($this->config->get('config_address'));
    	$data['telephone'] = $this->config->get('config_telephone');
    	$data['fax'] = $this->config->get('config_fax');
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/payment', $data));
		
	}

 	public function success() {
		$this->load->language('information/payment');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_message'] = $this->language->get('text_success');

		$data['button_continue'] = $this->language->get('button_home');

		$data['continue'] = false;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		
			$this->response->setOutput($this->load->view('common/success', $data));
		
	}

	
  	protected function validate() {
       
	    if ( !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) { //step 1 check email @ symbol 
				if ( !preg_match("/^[0-9]{10}$/", $this->request->post['email'])  ) { // step2 if not exit @symbol check telephone
				   $this->error['email'] = $this->language->get('error_massage');
				}
	    }


		 if( !preg_match('/^[0-9]*$/', $this->request->post['tel']) || utf8_strlen($this->request->post['tel']) < 9 ) {
			  $this->error['tel'] = $this->language->get('error_massage');
		 }
			
			
    	if (utf8_strlen($this->request->post['name']) == 0) {
      		$this->error['name'] = $this->language->get('error_massage');
    	}
		
		if (utf8_strlen($this->request->post['file_code']) == 0) {
      		$this->error['file_code'] = $this->language->get('error_file_code');
    	}
		
      
        if (utf8_strlen($this->request->post['orderno']) == 0 || !preg_match('/^[0-9]*$/', $this->request->post['orderno']) ) {
      		$this->error['orderno'] = $this->language->get('error_massage');
	    } else { // check existing orderno
			$this->load->model('catalog/toc_payment');
		    $data['order'] = $this->model_catalog_toc_payment->getOrder($this->request->post['orderno']); 
            if (empty($data['order']) ) {
				$this->error['orderno'] = $this->language->get('error_notfound_order');
			}
    	}

       if (!isset($this->request->post['bank']))  {
      		$this->error['bank'] =  $this->language->get('error_bank_acc');
    	}
        if (!is_numeric($this->request->post['amount_paid'])  ||  utf8_strlen($this->request->post['amount_paid']) == 0 )   {
      		$this->error['amount_paid'] =  $this->language->get('error_massage');
    	}

         if (utf8_strlen($this->request->post['transfer_date']) == 0)  {
      		$this->error['transfer_date']  =  $this->language->get('error_massage');
    	}

     
     
		if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
			$this->error['captcha'] = $this->language->get('error_captcha');
		}
  
		return !$this->error;
  	}
 
}