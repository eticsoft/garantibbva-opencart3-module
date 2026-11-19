<?php

require_once DIR_APPLICATION . 'controller/extension/payment/garantibbva/vendor/inc.php';

use Eticsoft\Sanalpospro\InternalApi;

class ControllerExtensionPaymentGarantibbva extends Controller
{
    public function index()
    {
        $this->load->language('extension/garantibbva/payment/garantibbva');

        $data['text_instruction'] = $this->language->get('text_instruction');
        $data['text_description'] = $this->language->get('text_description');
        $data['text_payment'] = $this->language->get('text_payment');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['request_url'] = $this->url->link('extension/payment/garantibbva/iapi');
        $data['xfvv'] = $this->config->get('payment_garantibbva_xfvv');
        return $this->load->view('extension/payment/garantibbva', $data);
    }

    public function iapi ()
    {
        if (!isset($_SERVER['HTTP_REFERER']) || parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
            header('Content-Type: application/json');
            header('HTTP/1.0 404 Not Found');
            die(json_encode(['status' => 'error', 'message' => 'Not Found']));
        }
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting;
        $api = InternalApi::getInstance()->setController($this)->setSettings($settings)->run();
        header('Content-Type: application/json');
        die(json_encode($api->response));
    }


    public function addProductTab(&$route, &$data, &$output)
    {
        if ($this->config->get('payment_garantibbva_showInstallmentsTabs') != 'yes') {
            return;
        }

        $this->load->model('setting/setting');
        $this->load->language('extension/payment/garantibbva');

        $title = $this->language->get('installments_title');
        $installments = json_decode($this->model_setting_setting->getSetting('payment_garantibbva')['payment_garantibbva_installments'], true);
        if (empty($installments)) {
            $installments = [];
        }

        foreach ($installments as $key => $installment) {
            // Her bir installment içerisindeki tüm gateway'lerin "off" olup olmadığını kontrol et
            $allGatewaysOff = true;

            foreach ($installment as $monthData) {
                if ($monthData['gateway'] != 'off') {
                    $allGatewaysOff = false;
                    break; // Bir tane bile "off" olmayan gateway varsa, kontrolü bırak
                }
            }

            // Eğer tüm gateway'ler "off" ise, bu installment'ı kaldır
            if ($allGatewaysOff) {
                unset($installments[$key]);
            }
            if ($key == 'default') {
                unset($installments[$key]);
            }
        }

        $data['text_monthly_payment'] = $this->language->get('monthly_payment');
        $data['text_total'] = $this->language->get('total');
        $data['text_installment_count'] = $this->language->get('installment_count');
        $data['text_installment'] = $this->language->get('installment');
        $data['text_note'] = $this->language->get('text_note');

        $product = $this->model_catalog_product->getProduct($this->request->get['product_id']);
        $currency = $this->session->data['currency'];
        $prc = $product['special'] ? $this->tax->calculate($product['special'], $product['tax_class_id'], true) : $this->tax->calculate($product['price'], $product['tax_class_id'], true);
        $price = $this->currency->format($prc, $currency, false, false);

        $data['installments'] = $installments;

        $currencies = array(
            'TRY' => '₺',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        );

        $data['currency'] = isset($currencies[$currency]) ? $currencies[$currency] : $currency;
        $data['garantibbva_price'] = (float)$price;

        // Yeni tab HTML içeriği
        $theme = $this->config->get('payment_garantibbva_paymentPageTheme');
        if ($theme == 'modern') {
            $custom_tab = $this->load->view('extension/payment/garantibbva/installments/modern', $data);
        } else {
            $custom_tab = $this->load->view('extension/payment/garantibbva/installments/classic', $data);
        }

        // OpenCart 3 için tab başlığı
        $custom_tab_header = '
        <li>
            <a href="#tab-garantibbva-installments" data-toggle="tab">' . $title . '</a>
        </li>';

        // Tab başlığı ekleme
        $output = preg_replace(
            '/<ul class="nav nav-tabs">(.*?)<\/ul>/s',
            '<ul class="nav nav-tabs">$1' . $custom_tab_header . '</ul>',
            $output
        );

        // Tab içeriği ekleme
        $output = str_replace(
            '<div class="tab-content">',
            '<div class="tab-content">' . $custom_tab,
            $output
        );
    }

}
