<?php
class ControllerExtensionPaymentBblGateway extends Controller {
	public function index() {
		$this->load->language('extension/payment/bbl_gateway');

		$this->load->model('checkout/order');

		$order_info          = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$invoice_no          = date('Ymd', time()).sprintf("%04d", $order_info['order_id']);
		// $data['action']   = 'https://ipay.bangkokbank.com/b2c/eng/payment/payForm.jsp';
		$data['action']      = 'https://ipay.bangkokbank.com/b2c/eng/payment/payForm.jsp';
		$data['merchant_id'] = '2673'; // 1
		$data['code']        = '764';  // 344
		$data['cancelURL']   = $this->url->link('extension/payment/bbl_gateway/checkResponse', '&type='.urlencode(base64_encode('cancel')), true);
		$data['returnURL']   = $this->url->link('extension/payment/bbl_gateway/checkResponse', '&type='.urlencode(base64_encode('rejected')), true);
		$data['successURL']  = $this->url->link('extension/payment/bbl_gateway/checkResponse', '&type='.urlencode(base64_encode('success')), true);
		$data['amount']      = sprintf('%0.1f', $order_info['total']);
		$data['remark']      = 'Payment Shopping on PAN';
		$data['invoice']     = $invoice_no;
		$data['language']    = ($this->session->data['language']=='th')?'T':'E';
		$data['deviceMode']  = ($this->isMobileDevice()) ? 'mobile' : 'pc';

		return $this->load->view('extension/payment/bbl_gateway', $data);
	}

	public function checkResponse() {

		$returnString = array(
			'00' => 'Approved ชำระเงินสำเร็จ',
			'01' => 'Refer to Card Issuer โปรดติดต่อธนาคารผู้ออกบัตร',
			'03' => 'Invalid Merchant ID ไม่อนุญาติให้รับบัตรประเภทนี ้',
			'05' => 'Do Not Honour โปรดติดต่อธนาคารผู้ออกบัตร',
			'12' => 'Invalid Transaction รายการผิดพลาดเนื่องจากระบบ ธนาคารไม่พร้อมให้บริการ',
			'13' => 'Invalid Amount จำนวนเงินไม่ถูกต้อง',
			'14' => 'Invalid Card Number หมายเลขบัตรเครดิตไม่ถูกต้อง',
			'17' => 'Customer Cancellation  ลูกค้ายกเลิกการทำรายการ ',
			'19' => 'Re-enter Transaction รายการช าระเงินซ ้า ',
			'30' => 'Format Error รายการผิดพลาดเนื่องจากระบบ ธนาคารไม่พร้อมให้บริการ' ,
			'41' => 'Lost Card -Pick Up บัตรได้รับการแจ้งสูญหาย',
			'43' => 'Stolen Card -Pick Up บัตรถูกขโมย',
			'50' => 'Invalid Payment Condition เงื่อนไขการช าระเงินไม่ถูกต้อง',
			'51' => 'Insufficient Funds วงเงินบัตรเครดิตไม่พอ ',
			'54' => 'Expired Card ใส่วันที่บัตรหมดอายุผิด ',
			'58' => 'Transaction not Permitted to Terminal ธนาคารผู้ออกบัตรไม่อนุญาตให้ใช้ บัตรนี ้มาช าระเงิน',
			'91' => 'Issuer or Switch is Inoperative ระบบของธนาคารผู้ออกบัตรไม่ พร้อมให้บริการ',
			'94' => 'Duplicate Transmission รายการชำระเงินซ ้า',
			'96' => 'System Malfunction รายการผิดพลาดเนื่องจากระบบ ธนาคารไม่พร้อมให้บริการ',
			'xx' => 'Transaction Timeout รายการผิดพลาดเนื่องจากระบบ ธนาคารไม่พร้อมให้บริการ'
		);

		$comment = 'ไม่พบข้อความส่งกลับ';
		$orderid = 0;

		$this->load->model('checkout/order');


		$txt = "post\r\n<br>";
		foreach ($this->request->post as $key => $p) {
			$txt .= $key . '=' .$p . "\r\n<br>";
		}
		$txt .= "\r\n<br>get\r\n<br>";
		foreach ($this->request->get as $key => $p) {
			$txt .= $key . '=' .$p . "\r\n<br>";
		}
		$txt .= "\r\n<br>request\r\n<br>";
		foreach ($this->request->request as $key => $p) {
			$txt .= $key . '=' .$p . "\r\n<br>";
		}

		// Log
					// create folder year
	        if (!is_dir('../logs/'.date('Y',time()))) {
	            mkdir('../logs/'.date('Y',time()), 0777);
	        }
	        // folder month
	        if (!is_dir('../logs/'.date('Y',time()).'/'.date('M',time()))) {
	            mkdir('../logs/'.date('Y',time()).'/'.date('M',time()), 0777);
	        }
	        // file day
	        $path = '../logs/'.date('Y',time()).'/'.date('M',time()).'/'.date('d', time()).'.txt';

			$file = fopen($path, 'a+');
			$data = $txt;
	        fwrite($file, $data);
	        fclose($file);
	    // Log

				
		if (isset($this->request->get['type']) && !empty($this->request->get['type'])) {

			$type = urldecode(base64_decode($this->request->get['type']));
		
			$day = substr($this->request->get['Ref'], 0, 8);
			$orderid = substr($this->request->get['Ref'], 8, strlen($this->request->get['Ref']));
    	$order_id = (int)$orderid;
    	$order_info = $this->model_checkout_order->getOrder($order_id);

	    if ($type == 'success') {
		    if ($day == date('Ymd', time())) {
				$order_status_id = 15;
				$comment = 'Successful, this message is response by bangkokbank, Please wait admin checking order.';
		    	$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true, true);
		    	$this->response->redirect($this->url->link('checkout/success'));
		    } else {
				$order_status_id = 2;
				$comment = 'Fail, Response Date is not match, Please wait admin check order.';
		    	$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true, true);
		    	$this->response->redirect($this->url->link('checkout/cart', '&fail=responsedate'));
		    }
	    } else if ($type == 'rejected') {
				$order_status_id = 10;
				$comment = 'Fail, This order is rejected by bangkokbank.';
					$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true, true);
				$this->response->redirect($this->url->link('checkout/cart', '&fail=rejected'));
	    } else {
				$order_status_id = 8;
				$comment = 'Fail, This order is cancel by bangkokbank. type is cancel';
					$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true, true);
					$this->response->redirect($this->url->link('checkout/cart', '&fail=cancel'));
	    }
		} else {
			$order_status_id = 8;
			$comment = 'Fail, This order is cancel by bangkokbank. not found type';
				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true, true);
				$this->response->redirect($this->url->link('checkout/cart', '&fail=cancel'));
		}


	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'bbl_gateway') {
			$this->load->language('extension/payment/bbl_gateway');

			$this->load->model('checkout/order');

			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= $this->config->get('payment_bbl_gateway_bank' . $this->config->get('config_language_id')) . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_bbl_gateway_order_status_id'), $comment, true);
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}




	public function isMobileDevice(){
	    $aMobileUA = array(
	        '/iphone/i' => 'iPhone', 
	        '/ipod/i' => 'iPod', 
	        '/ipad/i' => 'iPad', 
	        '/android/i' => 'Android', 
	        '/blackberry/i' => 'BlackBerry', 
	        '/webos/i' => 'Mobile'
	    );

	    //Return true if Mobile User Agent is detected
	    foreach($aMobileUA as $sMobileKey => $sMobileOS){
	        if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
	            return true;
	        }
	    }
	    //Otherwise return false..  
	    return false;
	}
}