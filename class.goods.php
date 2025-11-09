<?php
class RAYMAND_GOODS {

    
    public function get_goods( $request = null ) 
    {
		global $wpdb;
        $sql = "update wp_raymand_goods AS g inner JOIN
                (SELECT p.id,g.store_goods_id FROM wp_raymand_goods g 
                LEFT JOIN (SELECT * from wp_posts
                              where post_type = 'product' AND 
                                        post_status in ('publish','pending', 'draft')
                             ) p ON g.store_goods_id = p.ID
                WHERE g.store_goods_id IS NOT NULL) AS w ON g.store_goods_id = w.store_goods_id
                SET g.store_goods_id = w.id";
        $wpdb->query($sql);
        
        global $wpdb;
        $sql = "UPDATE wp_raymand_goods g
                inner JOIN
                (SELECT g.id,g.store_brand_id,t.term_taxonomy_id,g.brand,t.name FROM wp_raymand_goods g
                    LEFT JOIN (SELECT t.term_id,tx.term_taxonomy_id,t.name FROM wp_terms t
                                    inner JOIN wp_term_taxonomy tx ON tx.term_id = t.term_id
                                    WHERE tx.taxonomy = 'product_brand') t 
                            ON REPLACE(REPLACE(t.name,'ي','ی'),'ك','ک') = REPLACE(REPLACE(g.brand,'ي','ی'),'ك','ک')
                ) AS b on g.id = b.id
                SET g.store_brand_id = b.term_taxonomy_id";
        $wpdb->query($sql);
        
		global $wpdb;
		$goods_groups = $wpdb->get_results( 
                        $wpdb->prepare( "SELECT g.id, g.code, REPLACE(REPLACE(g.title,'ي','ی'),'ك','ک') as title, g.nick_name, 
                                                g.is_active, g.brand,g.store_brand_id, g.stock, g.unit_price, g.minimum_order_quantity,g.maximum_order_quantity,
                                                g.taxable, g.weight, g.created, g.raymand_group_id,
                                                gp.title AS raymand_group_title, g.raymand_goods_id, 
                                                g.store_goods_id, g.online_update, g.description, 
                                                g.images, g.properties,g.attributes,gp.store_group_id
                                         FROM wp_raymand_goods g
                                         LEFT JOIN  wp_raymand_goods_groups gp 
                                                    ON gp.raymand_group_id = g.raymand_group_id
                                                    ORDER BY g.raymand_goods_id") 
                        );
		return array(
			'code' => 1000,
			'data' => $goods_groups,
			'message' => 'Get Goods Successfully'
		) ;
	}

    public function set_goods_old($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        $args = array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $request['token']
                )
            );
        $response = wp_remote_get( $request['base_url'] . '/BehtaShopWebService/api/GetGoodsList',$args );
        if(wp_remote_retrieve_response_code( $response ) == 200)
        { 
            global $wpdb;
            $sql = "update wp_raymand_goods set is_active = 0 ;"; 
            $wpdb->query($sql);

            global $wpdb;
            $dataArr = json_decode(wp_remote_retrieve_body( $response));
            $insert_query = "INSERT INTO wp_raymand_goods (code, title, nick_name, is_active, brand, stock, unit_price, taxable, weight, created, raymand_group_id, raymand_goods_id, store_goods_id, online_update, description, images, properties, attributes) VALUES ";
            $query = Substr($dataArr->goods,0,-1);
            $sql = $insert_query . $query;
            $wpdb->query($sql);   

            global $wpdb;
            $sql = "delete from wp_raymand_goods where is_active = 0 ;"; 
            $wpdb->query($sql);
            
            $message= "Set Goods Successfully";
        }
        else 
        {
            $code = 1001;
            $message= $response;
        }
        return array(
            'code' => $code,
            'data' => $sql,
            'message' => $message
        ) ;
    }

    public function set_goods($request = null) 
    {
        
        $message = '';
        $sql = '';
        $sqlMain = '';
        $data = null;
        $code = 1000;
        // $args = array(
        //     'timeout' => 30,
        //     'headers' => array(
        //         'Authorization' => 'Bearer ' . $request['token']
        //         )
        //     );
        global $wpdb;
        $sql = "update wp_raymand_goods set is_active = 0 ;"; 
        $wpdb->query($sql);
        try {
            // global $wpdb;
            $dataArr = json_decode($request['goods']);
            // $sqlMain = $dataArr;
            foreach ($dataArr as $arr) {
                
                $sqlMain = Substr($arr,0,-1);
                global $wpdb;
                $insert_query = "INSERT INTO wp_raymand_goods (code, title, nick_name, is_active, brand, store_brand_id, stock, unit_price, minimum_order_quantity, maximum_order_quantity, taxable, weight, created, raymand_group_id, raymand_goods_id, store_goods_id, online_update, description, images, properties, attributes) VALUES ";
                $query = Substr($arr,0,-1);
                $sqlMain = $insert_query . $query;
                $wpdb->query($sqlMain);  
            } 
          }
          catch(Error $e){
            $message = 'Error writing to database: '.  $e->getMessage();
          }
        

        global $wpdb;
        $sql = "delete from wp_raymand_goods where is_active = 0 ;"; 
        $wpdb->query($sql);
        
        $message= "Set Goods Successfully";
        return array(
            'code' => $code,
            'data' => $sqlMain,
            // 'data1' => $dataArr,
            'message' => $message
        ) ;
    }

    public function update_goods_old($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $request['token']
            ),
            'body'        => array(
                'id' => $request['raymand_good_id']
            ),
            );
        $response = wp_remote_post( $request['base_url'] . '/BehtaShopWebService/api/GetGoodsByID',$args );
        if(wp_remote_retrieve_response_code( $response ) == 200)
        { 
            $dataArr = json_decode(wp_remote_retrieve_body( $response));

            foreach ($dataArr->goods as $query)
            {
                global $wpdb;
                $wpdb->query($query);
            }

            global $wpdb;
            $data = $wpdb->get_results( 
                $wpdb->prepare( "select * from wp_raymand_goods where raymand_goods_id in (" . $request['raymand_good_id'] . ')') 
            );
            $message= "Update Goods Successfully";
            if($request['store_good_id'] != '0')
            {
                $result = $this->update_product($data,$request);
                if($result != 1000)
                {
                    $message = $message . ' - But Woocommerce Update Faild';
                }
            }
        }
        else 
        {
            $code = 1001;
            $message= $response;
        }
        return array(
            'code' => $code,
            'data' => $data,
            'message' => $message
        ) ;
    }

    public function update_goods($request = null) 
    {
        
        $message = '';
        $sql = '';
        $data = null;
        $code = 1000;
        // $args = array(
        //     'method' => 'POST',
        //     'timeout' => 30,
        //     'headers' => array(
        //         'Authorization' => 'Bearer ' . $request['token']
        //     ),
        //     'body'        => array(
        //         'id' => $request['raymand_good_id']
        //     ),
        //     );
        //$response = wp_remote_post( $request['base_url'] . '/BehtaShopWebService/api/GetGoodsByID',$args );
        //if(wp_remote_retrieve_response_code( $response ) == 200)
        //{ 
        // $dataArr = json_decode(wp_remote_retrieve_body( $response));
        $dataArr = json_decode($request['goods']);
        foreach ($dataArr as $query)
        {
            global $wpdb;
            $wpdb->query($query);
        }
        

        global $wpdb;
        $data = $wpdb->get_results( 
            $wpdb->prepare( "select * from wp_raymand_goods where raymand_goods_id in (" . $request['raymand_good_id'] . ") order by id") 
        );
        $message= "Update Goods Successfully";
        if($request['store_good_id'] != '0')
        {
            $result = $this->update_product($data,$request);
            if($result != 1000)
            {
                $message = $message . ' - But Woocommerce Update Faild';
            }
        }
        // }
        // else 
        // {
        //     $code = 1001;
        //     $message= $response;
        // }
        return array(
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ) ;
    }


    function update_product($products, $request = null)
    {   
        $goods_update_list = [];
        $body = [];
        $code = 1000;
        foreach ($products as $product)
        {
            if($product->store_goods_id != null)
            {
                $goods_update_list[] = (array)$product;
            }
        }
        if(count($goods_update_list) > 0)
        {   
            $res = $this->update_batch($goods_update_list);
            $body = array('update' => $res['body']);
            
        }
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Authorization' => $request->get_header('authorization')
            ),
            'body' => $body,
        );
        $response = wp_remote_post( site_url() . '/wp-json/wc/v3/products/batch',$args );
        if(wp_remote_retrieve_response_code($response ) == 201 || wp_remote_retrieve_response_code($response ) == 200)
        {            
            $products = json_decode($response['body']);
            foreach ($products->create as $product)
            {
                global $wpdb;
                $update_query = "update wp_raymand_goods set store_goods_id = " . $product->id;
                $sql = $update_query . " where title = '". $product->name ."'";
                $wpdb->query($sql); 
            }
         }
        else 
        {
            $code = 1001;
         }
         return $code;
    }

    public function goods_send_to_store_batch1($request = null) 
    {
        $data = [
            'type' => 'variable',
            'description' => 'Trying it out for real',
            'short_description' => 'Pellentesque habitant.',
            'categories' => [
                [
                    'id' => 37
                ],
                [
                    'id' => 38
                ]
            ],
            'images' => [
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_4_front.jpg',
                    'position' => 0
                ]
            ],
            'attributes' => [
                [
                    'name' => ' طعم',
                    'position' => 0,
                    'visible' => true,
                    'variation' => true,
                    'options' => [
                        'هلو',
                        'گندم'
                    ]
                ]
            ],
            'default_attributes' => [
                [
                    'name' => 'طعم',
                    'option' => 'هلو'
                ]
            ],
            'variations' => [ 
                [
                    'regular_price' => '29.98', 
                    'attributes' => [ 
                        [
                            'slug'=>'color',
                            'name'=>'طعم',
                            'option'=>'هلو'
                        ]
                    ]   
                ]
            ]
        ];
    
        $wp_rest_request = new WP_REST_Request( 'POST' );
        $wp_rest_request->set_body_params( $data );
        $products_controller = new WC_REST_Products_Controller;
        $res_c = $products_controller->create_item( $wp_rest_request );
        $res = $res_c->data;
        
        $a = null;
        $b = null;
        $c = null;
    
        // The created product must have variations
        // If it doesn't, it's the new WC3+ API which forces us to build those manually
        if ( !isset( $res['variations'] ) ){
            $res['variations'] = array();
        }
        if ( count( $res['variations'] ) == 0 && count( $data['variations'] ) > 0 ) {
            if ( ! isset( $variations_controler ) ) {
                $variations_controler = new WC_REST_Product_Variations_Controller();
            }
            foreach ( $data['variations'] as $variation ) {
                $a = $variations_controler;
                $wp_rest_request = new WP_REST_Request( 'POST' );
                $variation_rest = array(
                    'product_id' => $res['id'],
                    'regular_price' => $variation['regular_price'],
                    'attributes' => $variation['attributes'],
                );
                $b = $wp_rest_request;
                $wp_rest_request->set_body_params( $variation_rest );
                $new_variation = $variations_controler->create_item( $wp_rest_request );
                $c = $new_variation;
                $res['variations'][] = $new_variation->data;
            }
        }

        return array(
            'code' => 1000,
            'data' =>  $res_c,
            'data1' =>  $a,
            'data2' =>  $b,
            'data3' =>  $c,
            
        ) ;
    }

    function get_iss_from_jwt($token) {
        // حذف "Bearer " از ابتدای توکن
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        // توکن JWT سه بخش دارد که با نقطه جدا شده‌اند: header.payload.signature
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return 'Invalid JWT format';
        }

        // بخش payload که شامل iss هست
        $payload = base64_decode(strtr($parts[1], '-_', '+/'));
        $data = json_decode($payload, true);

        return isset($data['iss']) ? $data['iss'] : 'iss not found in token';
    }
    
    public function goods_send_to_store_batch($request = null) 
    {
        $message = 'Send Product Successfully';
        $sql = '';
        $data = null;
        $code = 1000;
        $body = [];
        $goods_id = [];
        $variation_a = null;
        if($request['create'] != null)
        {
            $res = $this->create_batch($request['create']);
            $body = array('create' => $res['body']);
            $goods_id = $res['goods_id'];
        }
        else if($request['update'] != null)
        {
            $res = $this->update_batch($request['update']);
            $body = array('update' => $res['body']);
            $goods_id = $res['goods_id'];
        }
        // print(site_url());
        // print($request->get_header('AuthWT'));
        // $resultType = $this->change_product_type(4561, $request->get_header('authorization'));
        // return array(
        //     // 'code' => $code,
        //     // 'data' =>   $body['update'][0]['id'],
        //     // 'request' => $request['update'],
        //     'res' => $res,
        //     'body' => $body,
        // ) ;

        // return array(
        //     'code' => $code,
        //     'data' =>  json_encode([
        //                 array('id'=> 0,'name'=> "طعم 1",'options'=> "ليمو")
        //             ]),
        //     'message' => $body,
        //     // 'message1' => $body['create'][0]['variations'] == null ? 0 : count( $body['create'][0]['variations'] )
        // ) ;
        
        // $authHeader = $request->get_header('AuthWT');
        // $client_iss = $this->get_iss_from_jwt($authHeader);
        // $server_iss = site_url();
        // return array(
        //     'code' => $authHeader,
        //     'data' =>  $client_iss,
        //     'message' => $server_iss,
        //     // 'message1' => $body['create'][0]['variations'] == null ? 0 : count( $body['create'][0]['variations'] )
        // ) ;
        
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Authorization' => $request->get_header('authorization')
                //'Authorization' => $request->get_header('AuthWT')
            ),
            'body' => $body,
        );
        $response = wp_remote_post( site_url() . '/wp-json/wc/v3/products/batch',$args );
        // return array('args' => json_decode($response['body'], true));
        // var_dump(json_decode($response['body'], true));
        if(wp_remote_retrieve_response_code($response ) == 201 || wp_remote_retrieve_response_code($response ) == 200)
        {            
            $products = json_decode($response['body'], true);
            // return array(
            //     // 'code' => $code,
            //     // 'data' =>   $body['update'][0]['id'],
            //     // 'request' => $request['update'],
            //     'res' => $products,
            //     'body' => $body,
            // ) ;
            if ($products['create'] != null)
            {
                foreach ($products['create'] as $key=>$res)
                {                
                    global $wpdb;
                    $update_query = "update wp_raymand_goods set store_goods_id = " . $res['id'];
                    $sql = $update_query . " where title = '". $res['name'] ."'";
                    $wpdb->query($sql);
                    if ( !isset( $res['variations'] ) ){
                        $res['variations'] = array();
                    }
                    if ( count( $res['variations'] ) == 0 && 
                                $body['create'][$key]['variations']  != null && 
                                count( $body['create'][$key]['variations'] ) > 0 ) 
                    {
                        // return array(
                        //         // 'code' => $code,
                        //         // 'data' =>   $body['update'][0]['id'],
                        //         // 'request' => $request['update'],
                        //         'res' => $products,
                        //         'body' => ,
                        // ) ;
                        try
                        {
                            //براساس موجودی به صورت صعودی مرتب میشه تا آخرین سطر به عنوان پیشفرض قرار میگیره دارای موجودی باشه
                            usort($body['create'][$key]['variations'], function($a, $b) {
                                    return $a['stock_quantity'] <=> $b['stock_quantity'];
                            });
                            
                            foreach ( $body['create'][$key]['variations'] as $i=>$variation ) 
                            {                 
                                $variations_controler = new WC_REST_Product_Variations_Controller();  
                                $wp_rest_request = new WP_REST_Request( 'POST' );
                                $variation_rest = array(
                                    'product_id' => $res['id'],
                                    'manage_stock' => $variation['manage_stock'],
                                    'sku' => $variation['sku'],
                                    'stock_quantity' => $variation['stock_quantity'],
                                    'weight' => $variation['weight'],
                                    'dimensions' => $variation['dimensions'],
                                    'regular_price' => $variation['regular_price'],
                                    'sales_price' => $variation['regular_price'],
                                    'attributes' => $variation['attributes']
                                );
                                $wp_rest_request->set_body_params( $variation_rest );
                                $new_variation = $variations_controler->create_item( $wp_rest_request );
                                
                                if($i == 0)
                                {
                                    $variation_a = $new_variation;
                                }
                            }
                        }
                        catch(Error $e){
                            $message = 'Error writing to database: '.  $e->getMessage();
                        }
                    }
                    global $wpdb;
                    $brand_query = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) 
                                        VALUES (" . $res['id'] . "," . $body['create'][0]['store_brand_id'].",0)";
                    $wpdb->query($brand_query); 
                    
                    if($body['create'][$key]['variations']  != null && 
                                count( $body['create'][$key]['variations'] ) > 0)
                    {
                        $this->change_product_type($res['id'], $request->get_header('authorization'));
                        $variation = wc_get_product($variation_a->data['id']);
                        $variation_data = $variation->get_attributes();
                        update_post_meta( $res['id'] , '_default_attributes', $variation_data);
                    }
                    update_post_meta( $res['id'], '_minimum_order_quantity', $body['create'][$key]['minimum_order_quantity'] );
                    update_post_meta( $res['id'], '_maximum_order_quantity', $body['create'][$key]['maximum_order_quantity'] );
                    update_post_meta( $res['id'], '_step_order_quantity', $body['create'][$key]['step_order_quantity'] );
                    update_post_meta( $res['id'], '_unit_price', $body['create'][$key]['unit_price'] );

                }
            } 
            else if($products['update'] != null)
            {
                foreach ($products['update'] as $key=>$res)
                {                
                    $a = $this->delete_all_variations_by_product_id($body['update'][$key]['id']);
                    global $wpdb;
                    $update_query = "update wp_raymand_goods set store_goods_id = " . $res['id'];
                    $sql = $update_query . " where title = '". $res['name'] ."'";
                    $wpdb->query($sql); 
                    
                    if ( !isset( $res['variations'] ) ){
                        $res['variations'] = array();
                    }

                    if ( //count( $res['variations'] ) == 0 && 
                                $body['update'][$key]['variations']  != null && 
                                count( $body['update'][$key]['variations'] ) > 0 ) 
                    {
                        try
                        {
                            //براساس موجودی به صورت صعودی مرتب میشه تا آخرین سطر به عنوان پیشفرض قرار میگیره دارای موجودی باشه
                            usort($body['update'][$key]['variations'], function($a, $b) {
                                    return $a['stock_quantity'] <=> $b['stock_quantity'];
                            });
                            
                            foreach ( $body['update'][$key]['variations'] as $variation ) 
                            {                 
                                $variations_controler = new WC_REST_Product_Variations_Controller();  
                                $wp_rest_request = new WP_REST_Request( 'POST' );
                                $variation_rest = array(
                                    'product_id' => $res['id'],
                                    'manage_stock' => $variation['manage_stock'],
                                    'sku' => $variation['sku'],
                                    'stock_quantity' => $variation['stock_quantity'],
                                    'weight' => $variation['weight'],
                                    'dimensions' => $variation['dimensions'],
                                    'regular_price' => $variation['regular_price'],
                                    'sales_price' => $variation['regular_price'],
                                    // 'custom_price' => 987,
                                    // 'simple_price' => 987,
                                    'attributes' => $variation['attributes']
                                );
                                // var_dump($variation_rest);
                                $wp_rest_request->set_body_params( $variation_rest );
                                $new_variation = $variations_controler->create_item( $wp_rest_request );
                                if($i == 0)
                                {
                                    $variation_a = $new_variation;
                                }
                            }
                        }
                        catch(Error $e){
                            $message = 'Error writing to database: '.  $e->getMessage();
                        }
                    }
                    global $wpdb;
                    $brand_query = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) 
                                        VALUES (" . $res['id'] . "," . $body['update'][0]['store_brand_id'].",0)";
                    $wpdb->query($brand_query); 
                    if($body['create'][$key]['variations']  != null && 
                                count( $body['create'][$key]['variations'] ) > 0)
                    {
                        $variation = wc_get_product($variation_a->data['id']);
                        $variation_data = $variation->get_attributes();
                        update_post_meta( $res['id'] , '_default_attributes', $variation_data);
                        $this->change_product_type($res['id'], $request->get_header('authorization'));
                    }
                    update_post_meta( $res['id'], '_minimum_order_quantity', $body['update'][$key]['minimum_order_quantity'] );
                    update_post_meta( $res['id'], '_maximum_order_quantity', $body['update'][$key]['maximum_order_quantity'] );
                    update_post_meta( $res['id'], '_step_order_quantity', $body['update'][$key]['step_order_quantity'] );
                    update_post_meta( $res['id'], '_unit_price', $body['update'][$key]['unit_price'] );
                    
                    // global $wpdb;
                    // $brand_query = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) 
                    //                     VALUES (" . $res['id'] . "," . $body['update'][0]['store_brand_id'].",0)";
                    // $wpdb->query($brand_query); 
                
                }
            }
            global $wpdb;
            $data = $wpdb->get_results( 
                $wpdb->prepare( "select * from wp_raymand_goods where raymand_goods_id in (" . implode (", ", $goods_id) . ') order by id') 
            );
        }
        else 
        {
            $code = 1001;
            $data = json_decode($response['body']);
            $message = 'Send Product UnSuccessfully';
        }
        return array(
            'code' => $code,
            'data' =>  $data,
            'message' => $message,
            // 'message1' => $a,
            // 'message2' => $result99,
            // 'message3' => $variation_a->data['id'],
        ) ;
    }
    

    function create_batch($request)
    {
        $body = [];
        $goods_id = [];
        foreach($request as $item)
        {   
            if($item['store_goods_id'] == 0)
            {
                $propCount = json_decode($item['properties']) == null ? 0 : count(json_decode($item['properties'],true));
                $var = false;
                if($propCount > 0)
                {
                    foreach (json_decode($item['properties'],true) as $key => $value) {
                        if($value['variation'] == true && $item['attributes'] != null)
                        {
                            $var = true;
                            break;
                        }
                    }
                }
                
                $body[] =  array(
                                    "name" => str_replace('ك','ک', str_replace('ي','ی',$item['title'])),
                                    "type" => !$var ? "simple" : "variable",
                                    "minimum_order_quantity"=> $item['minimum_order_quantity'],
                                    "maximum_order_quantity"=> $item['maximum_order_quantity'],
                                    // "step_order_quantity"=> 9,
                                    "unit_price"=> $item['unit_price'],
                                    // 'description' => 'Arash',
                                    // 'short_description' => $item['description'],
                                    'regular_price' => $item['unit_price'],
                                    'manage_stock' => true,
                                    'stock_quantity' => $item['stock'],
                                    'store_brand_id' => $item['store_brand_id'],
                                    'categories' => json_decode( '[{"id": '.  $item['store_group_id'] .'}]' ),
                                    'images' => json_decode($item['images']),
                                    'attributes' => json_decode($item['properties']),
                                    'variations' => !$var ? null : json_decode($item['attributes'],true),
                                ); 
                $goods_id[] = $item['raymand_goods_id'];
            }
        }   
        $result = [];
        if($request != null)
        {
            $result = array(
                'body'=>$body,
                'goods_id' => $goods_id 
            );
        }
        // var_dump($body);
        return $result;
    }

    function update_batch($request)
    {
        
        $body = [];
        $goods_id = [];
        foreach($request as $item)
        {
            if($item['store_goods_id'] != 0)
            {
                $propCount = json_decode($item['properties']) == null ? 0 : count(json_decode($item['properties'],true));
                $var = false;
                if($propCount > 0)
                {
                    foreach (json_decode($item['properties'],true) as $key => $value) {
                        if($value['variation'] == true && $item['attributes'] != null)
                        {
                            $var = true;
                            break;
                        }
                    }
                }
                
                $body[] =  array(
                                "id" => $item['store_goods_id'],
                                "name" => str_replace('ك','ک', str_replace('ي','ی',$item['title'])),
                                'type' => !$var ? 'simple' : 'variable',
                                "minimum_order_quantity"=> $item['minimum_order_quantity'],
                                "maximum_order_quantity"=> $item['maximum_order_quantity'],
                                // "step_order_quantity"=> 9,
                                "unit_price"=> $item['unit_price'],
                                // 'description' => $item['description'],
                                // 'short_description' => $item['description'],
                                'regular_price' => $item['unit_price'],
                                'manage_stock' => true,
                                'stock_quantity' => $item['stock'],
                                'store_brand_id' => $item['store_brand_id'],
                                'categories' => json_decode( '[{"id": '.  $item['store_group_id'] .'}]' ),
                                'images' => json_decode($item['images']),
                                'attributes' => json_decode($item['properties']),
                                'variations' => !$var ? null : json_decode($item['attributes'],true),
                            );  
                // print(json_decode($item['attributes'],true));
                $goods_id[] = $item['raymand_goods_id'];
            }
        }   
        $result = [];
        if($request != null)
        {
            $result = array(
                'body'=>$body,
                'goods_id' => $goods_id 
            );
        }
        return $result;
    }

    function delete_all_variations_by_product_id($product_id) {
        global $wpdb;
        $variable_product_id = $product_id;
        $product_variation_ids = get_posts(array(
            'post_parent' => $variable_product_id,
            'post_type' => 'product_variation',
            'numberposts' => -1,
            'fields' => 'ids',
        ));
        if (!empty($product_variation_ids)) {
            foreach ($product_variation_ids as $product_variation_id) {
                wp_delete_post($product_variation_id, true);
            }
        }
        return $product_variation_ids;
    }
    
    function change_product_type($product_id,$token){
        
        $data = array(
            'type' => 'variable'
        );
        $json_data = json_encode($data);
        $args = array(
            'method'    => 'PUT',
            'timeout' => 30,
            'headers' => array(
                'Authorization' => $token,
                'Content-Type'  => 'application/json'
            ),
            'body'      => $json_data,
        );
        $response = wp_remote_request(site_url() . "/wp-json/wc/v3/products/" . $product_id, $args);
        // $response_code = wp_remote_retrieve_response_code($response);
        // // $response_body = wp_remote_retrieve_body($response);
        // return array(
        //         'code' => $response_code,
        //         'Body' => $response,
        //     );
        return $response;
    }
}