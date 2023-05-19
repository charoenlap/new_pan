<?php
class ControllerExtensionDashboardChartage extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/chartage');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dashboard_chartage', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dashboard/chartage', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/dashboard/chartage', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		if (isset($this->request->post['dashboard_chartage_width'])) {
			$data['dashboard_chartage_width'] = $this->request->post['dashboard_chartage_width'];
		} else {
			$data['dashboard_chartage_width'] = $this->config->get('dashboardchart_width');
		}
	
		$data['columns'] = array();
		
		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}
				
		if (isset($this->request->post['dashboard_chartage_status'])) {
			$data['dashboard_chartage_status'] = $this->request->post['dashboard_chartage_status'];
		} else {
			$data['dashboard_chartage_status'] = $this->config->get('dashboard_chartage_status');
		}

		if (isset($this->request->post['dashboard_chartage_sort_order'])) {
			$data['dashboard_chartage_sort_order'] = $this->request->post['dashboard_chartage_sort_order'];
		} else {
			$data['dashboard_chartage_sort_order'] = $this->config->get('dashboard_chartage_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/chartage_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/chart')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}	
	
	public function dashboard() {
		$this->load->language('extension/dashboard/chartage');

		$data['user_token'] = $this->session->data['user_token'];


		$this->load->model('extension/dashboard/chartage');

		$range = array('0-20', '21-40', '41-60', '61-80', '81-100');

		$results = $this->model_extension_dashboard_chartage->getOrderByAge();

		$data['result'] = array();

		$data_chart[] = array('Order', '0-20', '21-40', '41-60', '61-80', '81-100');

		for ($i=0; $i<=max($results); $i++) {
			$data_chart[] = array($i, $results[0], $results[1], $results[2], $results[3], $results[4]);
		}

		$data['result'] = urlencode(json_encode($data_chart));

		return $this->load->view('extension/dashboard/chartage_info', $data);
	}

	public function chart() {
		$this->load->language('extension/dashboard/chartage');

		$json = array();

		$this->load->model('extension/dashboard/chartage');

		$json['order'] = array();
		$json['customer'] = array();
		$json['xaxis'] = array();

				$json['xaxis'][0] = array("0-20", "1");
				$json['xaxis'][1] = array("21-40", "2");
				$json['xaxis'][2] = array("41-60", "3");
				$json['xaxis'][3] = array("61-80", "4");
				$json['xaxis'][4] = array("81-100", "5");

		$json['order']['label'] = $this->language->get('text_order');
		$json['customer']['label'] = $this->language->get('text_customer');
		$json['order']['data'] = array();
		$json['customer']['data'] = array();

		if (isset($this->request->get['range'])) {
			$range = $this->request->get['range'];
		} else {
			$range = 'week';
		}

		switch ($range) {
			default:
			case 'day':
				$results = $this->model_extension_dashboard_chartage->getTotalOrdersByDay();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_extension_dashboard_chartage->getTotalCustomersByDay();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				for ($i = 0; $i < 24; $i++) {
					$json['xaxis'][] = array($i, $i);
				}
				break;
			case 'week':
				$results = $this->model_extension_dashboard_chartage->getTotalOrdersByWeek();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_extension_dashboard_chartage->getTotalCustomersByWeek();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				$date_start = strtotime('-' . date('w') . ' days');

				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));

					// $json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
				}
				break;
			case 'month':
				$results = $this->model_extension_dashboard_chartage->getTotalOrdersByMonth();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_extension_dashboard_chartage->getTotalCustomersByMonth();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;

					$json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
				}
				break;
			case 'year':
				$results = $this->model_extension_dashboard_chartage->getTotalOrdersByYear();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_extension_dashboard_chartage->getTotalCustomersByYear();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				for ($i = 1; $i <= 12; $i++) {
					$json['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i)));
				}
				break;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}