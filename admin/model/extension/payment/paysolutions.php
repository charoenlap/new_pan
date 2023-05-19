<?php
class ModelExtensionPaymentPaysolutions extends Model {

    //Install the custom setting or create database table.
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paysolutions_order` (
            `paysolutions_order_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `order_id` VARCHAR(20) NOT NULL,
            `merchant_id` VARCHAR(25) NULL,
            `refno` VARCHAR(10) NULL,
            `customeremail` VARCHAR(100) NULL,
            `status` VARCHAR(2) NULL,
            `productdetail` VARCHAR(1000) NULL,
            `total` FLOAT NULL,
            `lang` VARCHAR(2) NULL,
            `cc` VARCHAR(2) NULL
           
            -- PRIMARY KEY (`paysolutions_order_id`)
            ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

    }
    
    /* Uninstall the custom plugin setting from database. */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paysolutions_order`;");
    }

    public function addOrderStatus($order_status_name) {

        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE  name = '" . $this->db->escape($order_status_name) . "'");

        if(!$result->num_rows) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET language_id = 1 ,name = '" . $this->db->escape($order_status_name) . "'");
        }    
    }
}