<?php
require_once( RAYMAND__PLUGIN_DIR . 'web_service/class.setting.php' );
require_once( RAYMAND__PLUGIN_DIR . 'web_service/class.goods_group.php' );
require_once( RAYMAND__PLUGIN_DIR . 'web_service/class.goods.php' );
require_once( RAYMAND__PLUGIN_DIR . 'web_service/class.orders.php' );
class RAYMAND_REST_API {
	// global $wpdb 
    /**
	 * Register the REST API routes.
	 */
	public static function init() {
		if ( ! function_exists( 'register_rest_route' ) ) {
			// The REST API wasn't integrated into core until 4.4, and we support 4.0+ (for now).
			return false;
		}

		register_rest_route( 'raymand/v1', '/settings', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				// 'permission_callback' => array( 'Raymand_REST_API', 'privileged_permission_callback' ),
				'callback' => array( 'Raymand_REST_API', 'get_setting' ),
			),
			array (
				'methods' => "POST",
				// 'permission_callback' => array( 'Raymand_REST_API', 'privileged_permission_callback' ),
				'callback' => array( 'Raymand_REST_API', 'set_setting' ),
				'args' => array(
					'id' => array(
						'required' => false,
						'type' => 'int'
					),
					'server_url' => array(
						'required' => true,
						'type' => 'string',
						'description' => __( 'آدرس سرور نرم افزار رایمند برای دریافت و ارسال اطلاعات', 'raymand' ),
					),
					'token' => array(
						'required' => true,
						'type' => 'string',
						'description' => __( 'شناسه توکن دریافتی از رایمند', 'raymand' ),
					),
					'default_customer_code' => array(
						'required' => false,
						'type' => 'string',
						'description' => __( 'کد مشتری پیش فرض برای زمانی که کاربر سفارشات ارسالی مهم نباشد ', 'raymand' ),
					),
					'price_calc_kind' => array(
						'required' => false,
						'type' => 'int',
						'description' => __( 'نمایش و نوع محاسبه قیمت در وبسایت', 'raymand' ),
					),
					'stock_update_period' => array(
						'required' => false,
						'type' => 'int',
						'description' => __( 'موجودی کالاهای وب سایت براساس انتخاب به صورت خودکار بروزرسانی انجام می شود', 'raymand' ),
					),
					'price_update_period' => array(
						'required' => false,
						'type' => 'int',
						'description' => __( 'قیمت کالاهای وب سایت براساس انتخاب به صورت خودکار بروزرسانی انجام می شود', 'raymand' ),
					),
					'good_update_period' => array(
						'required' => false,
						'type' => 'int',
						'description' => __( 'اطلاعات کالاهای وب سایت براساس انتخاب به صورت خودکار بروزرسانی انجام می شود', 'raymand' ),
					),
					'allow_zero_stock' => array(
						'required' => false,
						'type' => 'bit',
						'description' => __( 'مجوز فروش کالاهای ناموجود', 'raymand' ),
					),
				),
			),
		) );

		register_rest_route( 'raymand/v1', '/goods_groups',  array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( 'Raymand_REST_API', 'get_goods_groups' ),
				'permission_callback' => function () {
					return is_user_logged_in(); // بررسی توکن JWT
				},
				// 'permission_callback' => 'is_user_logged_in'
			) ,
			array(
				'methods' => "POST",
				'callback' => array( 'Raymand_REST_API', 'set_goods_groups' ),
				'args' => array(
					// 'base_url' => array(
					// 	'required' => true,
					// 	'type' => 'string'
					// ),
					// 'token' => array(
					// 	'required' => true,
					// 	'type' => 'string'
					// ),
					'goods_groups' => array(
						'required' => true,
						'type' => 'string'
					),
				),
			),
		) );

		register_rest_route( 'raymand/v1', '/goods',  array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( 'Raymand_REST_API', 'get_goods' ),
				'permission_callback' => function () {
					return is_user_logged_in(); // بررسی توکن JWT
				},
			) ,
			array(
				'methods' => "POST",
				'callback' => array( 'Raymand_REST_API', 'set_goods' ),
				'args' => array(
					// 'base_url' => array(
					// 	'required' => true,
					// 	'type' => 'string'
					// ),
					// 'token' => array(
					// 	'required' => true,
					// 	'type' => 'string'
					// ),
					'goods' => array(
						'required' => true,
						'type' => 'string'
					),
					'goods_id' => array(
						'required' => false,
						'type' => 'string'
					),
				),
			),
			array(
				'methods' => "PUT",
				'callback' => array( 'Raymand_REST_API', 'update_goods' ),
				'args' => array(
					// 'base_url' => array(
					// 	'required' => true,
					// 	'type' => 'string'
					// ),
					// 'token' => array(
					// 	'required' => true,
					// 	'type' => 'string'
					// ),
					'goods' => array(
						'required' => true,
						'type' => 'string'
					),
					'raymand_goods_id' => array(
						'required' => false,
						'type' => 'string'
					),
					'store_goods_id' => array(
						'required' => false,
						'type' => 'string'
					),
				),
			),
		) );

		register_rest_route('raymand/v1', '/goods_send_to_store_batch',  array(
			array(
				'methods' => "POST",
				'callback' => array( 'Raymand_REST_API', 'goods_send_to_store_batch' ),
				'args' => array(
					'create' => array(
						'required' => false,
						'type' => 'array'
					),
				),
			),
		));

		register_rest_route('raymand/v1', '/goods_group_send_to_store_batch',  array(
			array(
				'methods' => "POST",
				'callback' => array( 'Raymand_REST_API', 'goods_group_send_to_store_batch' ),
				'args' => array(
					'create' => array(
						'required' => false,
						'type' => 'array'
					)
				),
			),
		));

		register_rest_route( 'raymand/v1', '/order',  array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( 'Raymand_REST_API', 'get_orders' ),
				'permission_callback' => function () {
					return is_user_logged_in(); // بررسی توکن JWT
				},
			) ,
			// array(
			// 	'methods' => "POST",
			// 	'callback' => array( 'Raymand_REST_API', 'send_orders_to_raymands' ),
			// 	'args' => array(
			// 		'base_url' => array(
			// 			'required' => true,
			// 			'type' => 'string'
			// 		),
			// 		'token' => array(
			// 			'required' => true,
			// 			'type' => 'string'
			// 		),
			// 	),
			// ),
		) );

		register_rest_route( 'raymand/v1', '/login',  array(
			array(
				'methods' => "POST",
				'callback' => array( 'Raymand_REST_API', 'login' ),
				'args' => array(
					'base_url' => array(
						'required' => true,
						'type' => 'string'
					),
					'phone' => array(
						'required' => true,
						'type' => 'string'
					),
					'password' => array(
						'required' => true,
						'type' => 'string'
					),
				),
			) ,
			
			// array(
			// 	'methods' => "POST",
			// 	'callback' => array( 'Raymand_REST_API', 'send_orders_to_raymands' ),
			// 	'args' => array(
			// 		'base_url' => array(
			// 			'required' => true,
			// 			'type' => 'string'
			// 		),
			// 		'token' => array(
			// 			'required' => true,
			// 			'type' => 'string'
			// 		),
			// 	),
			// ),
		) );

		register_rest_route( 'raymand/v1', '/con_test',  array(
			array(
				'methods' => "POST",
				'callback' => array( 'Raymand_REST_API', 'con_test' ),
				'args' => array(
					'base_url' => array(
						'required' => true,
						'type' => 'string'
					),
				),
			) ,
		) );

		
	}

    public static function get_setting( $request = null ) {
		$proxy = new RAYMAND_SETTING;
		return $proxy->get_setting();
	}

	public static function set_setting($request = null) {
		$proxy = new RAYMAND_SETTING;
		return $proxy->set_setting($request);
	}

	public static function login($request = null) {
		$proxy = new RAYMAND_SETTING;
		return $proxy->login($request);
	}

	public static function con_test($request = null) {
		$proxy = new RAYMAND_SETTING;
		return $proxy->con_test($request);
	}

	public static function get_goods_groups( $request = null ) {
		$proxy = new RAYMAND_GOODSGROUP;
		return $proxy->get_goods_groups();
	}

	public static function set_goods_groups( $request = null ) {
		$proxy = new RAYMAND_GOODSGROUP;
		return $proxy->set_goods_groups($request);
	}

	public static function goods_group_send_to_store_batch( $request = null ) {
		$proxy = new RAYMAND_GOODSGROUP;
		return $proxy->goods_group_send_to_store_batch($request);
	}

	public static function get_goods( $request = null ) {
		$proxy = new RAYMAND_GOODS;
		return $proxy->get_goods();
	}

	public static function set_goods( $request = null ) {
		$proxy = new RAYMAND_GOODS;
		return $proxy->set_goods($request);
	}
	
	public static function update_goods( $request = null ) {
		$proxy = new RAYMAND_GOODS;
		return $proxy->update_goods($request);
	}
	
	public static function goods_send_to_store_batch( $request = null ) {
		$proxy = new RAYMAND_GOODS;
		return $proxy->goods_send_to_store_batch($request);
	}

	public static function get_orders( $request = null ) {
		$proxy = new RAYMAND_ORDERS;
		return $proxy->get_orders($request);
	}

	
    
}