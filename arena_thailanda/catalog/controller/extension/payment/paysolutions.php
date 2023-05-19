 <?php
class ControllerExtensionPaymentPaysolutions extends Controller {

    public function index() {

        //Load model and languages
        $this->load->language('extension/payment/paysolutions');
        $this->load->model('extension/payment/paysolutions');
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_total = $this->currency->format($order_info['total'], $order_info['currency_code'],$order_info['currency_value'], false);

        $currency_code = $this->getCurrencies($order_info['currency_code']);

        $description = "Payment Order #".$order_info['order_id'];
        $data['merchantid'] = $this->config->get('payment_paysolutions_merchant');
        $data['description']= $description;
        $data['total']      = $order_total;
        $data['refno']      = $order_info['order_id'];
        $data['cc']         = $currency_code;
        $data['postbackurl']= $this->url->link('extension/payment/paysolutions/callback&orderid='.$order_info['order_id']);
        $data['returnurl']  = $this->url->link('checkout/paysolutions&orderid='.$order_info['order_id']);
        $data['customeremail'] = $order_info['email'];
        $data['url'] = $this->language->get("url");
        $this->session->data['order_id'] = $order_info['order_id'];
        $this->session->data['amount']   = $order_info['total'];
        return $this->load->view('extension/payment/paysolutions',$data);

    }


    public function callback()
    {
        $order_id = (int) $_GET['orderid'];
        if($order_id)
        {
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/paysolutions');
            $complete_payment_order_status_id = $this->model_extension_payment_paysolutions->getCustomeOrderStatus('Complete');
            $this->model_checkout_order->addOrderHistory($order_id, $complete_payment_order_status_id);
            print_r($_REQUEST);
        }

    }

    public function get_paysolutions_currency_code() {

        $setting_curreny_code = $this->config->get('config_currency');
        echo $setting_curreny_code;
        $currencyCodeArray    = $this->model_extension_payment_paysolutions->getCurrencies();

        foreach ($currencyCodeArray as $key => $value) {
            if (strcasecmp($key, $setting_curreny_code) == 0 || strcasecmp($value['Num'], $setting_curreny_code) == 0) {
                return $value['Num'];
            }
        }

        return "";
    }

    public function confirm() {

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/paysolutions');
        $awaiting_payment_order_status_id = $this->model_extension_payment_paysolutions->getCustomeOrderStatus('Awaiting Payment');
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $awaiting_payment_order_status_id);
        $json['redirect'] = $this->url->link('checkout/paysolutions');

    }

    public function getCurrencies($currency) {
        $currencies =
            [
            "AUD" => array(
                "Num" => "036",
                "code" => "07"
            ),
            "CHF" => array(
                "Num" => "756",
                "code" => "08"
            ),
            "EUR" => array(
                "Num" => "978",
                "code" => "05"
            ),
            "GBP" => array(
                "Num" => "826",
                "code" => "06"
            ),
            "HKD" => array(
                "Num" => "344",
                "code" => "04"
            ),
            "JPY" => array(
                "Num" => "392",
                "code" => "02"
            ),
            "SGD" => array(
                "Num" => "702",
                "code" => "03"
            ),
            "THB" => array(
                "Num" => "764",
                "code" => "00"
            ),
            "USD" => array(
                "Num" => "840",
                "code" => "01"
            ),
        ];
        $code = ($currencies[$currency]["code"])? $currencies[$currency]["code"] : '00';
        return $code;
    }



}