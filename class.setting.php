<?php

class RAYMAND_SETTING {
public function get_setting( $request = null ) {
    global $wpdb;
    $mySetting = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wp_raymand_setting LIMIT 1") );
    return array(
        'code' => 1000,
        'data' => $mySetting,
        'message' => 'Get Setting Successfully'
    ) ;

}

public function set_setting($request = null) {
    global $wpdb;
    $message = '';
    if(isset($request['id']) && $request['id'] != 0)
    {
        $sql = "UPDATE wp_raymand_setting 
                SET 
                    created=NOW(),
                    server_url='{$request['server_url']}',
                    image_url='{$request['image_url']}',
                    token='{$request['token']}',
                    default_customer_code='{$request['default_customer_code']}',
                    default_subledger_code='{$request['default_subledger_code']}',
                    price_calc_kind='{$request['price_calc_kind']}',
                    stock_update_period='{$request['stock_update_period']}',
                    price_update_period='{$request['price_update_period']}',
                    good_update_period='{$request['good_update_period']}',
                    allow_zero_stock='{$request['allow_zero_stock']}'
                ";
        $message = 'Update Setting Successfully';

    } else {
        $sql = "INSERT INTO wp_raymand_setting (created,server_url,image_url,token,default_customer_code,
                                                default_subledger_code,price_calc_kind,stock_update_period,
                                                price_update_period,good_update_period,allow_zero_stock) 
                VALUES (
                            NOW(),
                            '{$request['server_url']}',
                            '{$request['image_url']}',
                            '{$request['token']}',
                            '{$request['default_customer_code']}',
                            '{$request['default_subledger_code']}',
                            '{$request['price_calc_kind']}',
                            '{$request['stock_update_period']}',
                            '{$request['price_update_period']}',
                            '{$request['good_update_period']}',
                            '{$request['allow_zero_stock']}'
                        )";
        $message = 'Insert Setting Successfully';

    }
    // var_dump($sql); // debug
    // $sql = $wpdb->prepare($sql,$request['server_url'],$request['token'],$request['default_customer_code']);
    // var_dump($sql); // debug
    $wpdb->query($sql);
    $mySetting = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wp_raymand_setting LIMIT 1") );
    return array(
        'code' => 1000,
        'data' => $mySetting,
        'message' => $message
    ) ; //prints "10"
}

public function login($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            // 'headers' => array(
            //     'Authorization' => 'Bearer ' . $request['token']
            //     )
            'body' => 
                array(
                    'phone' => $request['phone'],
                    'password' => $request['password']
                ),
            );
    

        $response = wp_remote_post( $request['base_url'] . '/BehtaShopWebService/api/Login',$args );
        if(wp_remote_retrieve_response_code( $response ) == 200)
        { 
            $dataArr = json_decode(wp_remote_retrieve_body( $response));            
            $message= "Login Successfully";
        }
        else 
        {
            $code = 1001;
            $message= $response;
        }
        return array(
            'code' => $code,
            'data' => $dataArr,
            'message' => $message
        ) ;
    }

    public function con_test($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        // $args = array(
        //     'method' => 'GET',
        //     'timeout' => 30,
        //     );        
        // $response = wp_remote_get( $request['base_url'] . '/BehtaShopWebService/api/ConTest',$args );
        // if(wp_remote_retrieve_response_code( $response ) == 200)
        // { 
        //     $dataArr = json_decode(wp_remote_retrieve_body( $response));            
        //     $message= "Communication Is Established.";
        // }
        // else 
        // {
        //     $code = 1001;
        //     $message= $response;
        // }
        // $product = new WC_Product_Variable( 11404 );


        //َAdd variation Programically
        // $variation = new WC_Product_Variation();
        // $variation->set_parent_id( 11404 );
        // $variation->set_attributes( array( 'attribute_magical' => 'Yes' ) );
        // $variation->set_regular_price( 50 );
        // $variation->save();

        $this->myplugin_create_variations(11404);
        $product = new WC_Product_Variable( 11404 );
        // $product    = wc_get_product( 11404 );
        $vars1 = $product->get_children();
        foreach ( $vars1 as $variation_id ) {
            $variation = wc_get_product($variation_id);
            $variation->set_regular_price(100);
            $variation->set_sale_price(99);
            $variation->set_manage_stock( true ); // true/false
            $variation->set_stock_quantity( 100 );
            $variation->save();
        }
        $vars = $product->get_available_variations();
        // var_dump ($vars);
        // $vars1 = $product->get_variation_attributes();
        return array(
            'code' => $code,
            'data' => $product,
            'data1' => $vars,
            'data2' => $vars1,
            // 'message' => $message
        ) ;
    }

    function myplugin_create_variations($product_id){

        wc_maybe_define_constant( 'WC_MAX_LINKED_VARIATIONS', 50 );
        wc_set_time_limit( 0 );
        if ( ! $product_id ) {
        	wp_die();
        }
        $product    = wc_get_product( $product_id );
        $data_store = $product->get_data_store();
        if ( ! is_callable( array( $data_store, 'create_all_product_variations' ) ) ) {
        	wp_die();
        }
        echo esc_html( $data_store->create_all_product_variations( $product, WC_MAX_LINKED_VARIATIONS ) );
        $data_store->sort_all_product_variations( $product->get_id() );  

    }

}