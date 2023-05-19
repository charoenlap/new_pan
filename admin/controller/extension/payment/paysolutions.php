 <?php
class ControllerExtensionPaymentPaySolutions extends Controller {

    private $error = array();
    
    public function index() {

        //Load the model and language settings.
        $this->load->model('setting/setting');
        $this->load->model('extension/payment/paysolutions');
        $this->load->language('extension/payment/paysolutions');

        $this->document->setTitle('Paysolutions Payment Method Configuration');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('payment_paysolutions', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['entry_status']          = $this->language->get('entry_status');
        $data['merchentid']            = $this->language->get('merchentid');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['payable'])) {
            $data['error_payable'] = $this->error['payable'];
        } else {
            $data['error_payable'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/paysolutions', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/paysolutions', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        //Get setting value from OC_setting table.
        if (isset($this->request->post['payment_paysolutions_merchant'])) {
            $data['payment_paysolutions_merchant'] = $this->request->post['payment_paysolutions_merchant'];
        } else {
            $data['payment_paysolutions_merchant'] = $this->config->get('payment_paysolutions_merchant');
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        /* Validate the Merchant configuration field */
        if (isset($this->error['merchantid'])) {
            $data['error_merchantid'] = $this->error['merchantid'];
        } else {
            $data['error_merchantid'] = '';
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        try {
        $this->response->setOutput($this->load->view('extension/payment/paysolutions', $data));
        } catch (Exception $e) {
            echo "Paysolutions Admin Template is not available. Please try to contact your administrator!"; die;
        }
    }

    public function install() {
        $this->load->model('extension/payment/paysolutions');
        $this->model_extension_payment_paysolutions->addOrderStatus("Awaiting Payment");
        $this->model_extension_payment_paysolutions->install();
    }

    public function uninstall() {
        $this->load->model('extension/payment/paysolutions');
        $this->model_extension_payment_paysolutions->uninstall();
    }

    //Validate merchant field settings
    protected function validate(){

        if (!$this->user->hasPermission('modify', 'extension/payment/paysolutions')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['payment_paysolutions_merchant']) {
            $this->error['merchantid'] = $this->language->get('error_merchantid');
        }
        return !$this->error;
    }
    
    public function callback() {        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($this->request->get));
    }
}