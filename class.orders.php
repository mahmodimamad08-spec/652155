<?php
class RAYMAND_ORDERS {

    public function get_orders( $request = null ) 
    {
        $orders = [];
        $args = array(
            'method' => 'get',
            'timeout' => 30,
            'headers' => array(
                'Authorization' => $request->get_header('authorization')
            )
        );
        $response = wp_remote_get( site_url() . '/wp-json/wc/v3/orders',$args );
        if(wp_remote_retrieve_response_code($response) == 200)
        {
            $orders = json_decode($response['body']);
        }
		return array(
			'code' => 1000,
			'data' => $orders,
			'message' => 'Get Orders Successfully'
		) ;

	}



}