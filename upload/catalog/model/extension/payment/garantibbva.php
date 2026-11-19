<?php
class ModelExtensionPaymentGarantibbva extends Model {
    public function getMethod() {
        $this->load->language('extension/payment/garantibbva');

        $method_data = array(
            'code'       => 'garantibbva',
            'title'      => $this->language->get('text_title'),
            'terms'      => '',
            'sort_order' => 1
        );
        return $method_data;
    }
}