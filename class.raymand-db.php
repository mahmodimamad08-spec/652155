<?php

global $wpdb;
$sql = array();
$plugin_name_db_version = '1.0';
$charset_collate = $wpdb->get_charset_collate();

// Setting
$table_setting = $wpdb->prefix . "raymand_setting"; 
if($wpdb->get_var("show tables like '". $table_setting . "'") != $table_setting)
{ 
  $sql[] = "CREATE TABLE $table_setting (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            server_url varchar(500) NULL,
            image_url varchar(500) NULL,
            token varchar(255) DEFAULT '' NOT NULL,
            default_customer_code varchar(255) DEFAULT '' NOT NULL,
            default_subledger_code varchar(255) DEFAULT '' NOT NULL,
            price_calc_kind int DEFAULT 1 NOT NULL,
            stock_update_period int DEFAULT 1 NOT NULL,
            price_update_period int DEFAULT 1 NOT NULL,
            good_update_period int DEFAULT 1 NOT NULL,
            allow_zero_stock bit DEFAULT '' NOT NULL,
            UNIQUE KEY id (id)
          ) $charset_collate;";
}

//GoodsGroups
$table_goods_group = $wpdb->prefix . "raymand_goods_groups"; 
if($wpdb->get_var("show tables like '". $table_goods_group . "'") != $table_goods_group)
{ 
  $sql[] = "CREATE TABLE $table_goods_group (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            parent_id int NULL,
            code int DEFAULT 0 NOT NULL,
            title varchar(500) DEFAULT '' NOT NULL,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            raymand_group_id int NULL,
            store_group_id int NULL,
            level int DEFAULT 0 NOT NULL,
            parent_store_id int NULL,
            UNIQUE KEY id (id)
          ) $charset_collate;";
}

//Goods
$table_goods = $wpdb->prefix . "raymand_goods"; 
if($wpdb->get_var("show tables like '". $table_goods . "'") != $table_goods)
{ 
  $sql[] = "CREATE TABLE $table_goods (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            code int DEFAULT 0 NOT NULL,
            title varchar(500) DEFAULT '' NOT NULL,
            nick_name varchar(500) DEFAULT '' NOT NULL,
            is_active bit DEFAULT 1 NOT NULL,
            brand varchar(500) DEFAULT '' NOT NULL,
            store_brand_id int NULL,
            stock float DEFAULT 0 NOT NULL,
            minimum_order_quantity decimal(18,3) NULL,
            maximum_order_quantity decimal(18,3) NULL,
            unit_price decimal(18,3) DEFAULT 0 NOT NULL,
            taxable bit DEFAULT 1 NOT NULL,
            weight float DEFAULT 0 NOT NULL,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            raymand_group_id int NULL,
            raymand_goods_id int NULL,
            store_goods_id int NULL,
            online_update bit DEFAULT 0 NOT NULL,
            description text NULL,
            images json DEFAULT '' NOT NULL,
            properties json DEFAULT '' NOT NULL,
            attributes json DEFAULT '' NOT NULL,
            UNIQUE KEY id (id)
          ) $charset_collate;";
}

if ( !empty($sql) ) 
{
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
  add_option( 'plugin_name_db_version', $plugin_name_db_version );
}
