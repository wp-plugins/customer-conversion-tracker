<?php
/*
Plugin Name: Customer Conversion Tracker Lite
Plugin URI: http://yourdomain.com/
Description: Customer-Conversion-Tracker-Plugin
Version: 1.0
Author: TBI
Author URI: http://yourdomain.com
License: GPL
*/
/**
 * Check if WooCommerce is active
 **/
// @ini_set('display_errors', 0 );
session_start();

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { 
    
require_once(plugin_dir_path(__FILE__) . '/../woocommerce/woocommerce.php');
require_once(plugin_dir_path(__FILE__) . '/../woocommerce/includes/admin/reports/class-wc-admin-report.php');
require_once(plugin_dir_path(__FILE__) . '/../woocommerce/includes/class-wc-cart.php');
require_once(plugin_dir_path(__FILE__) . '/../woocommerce/includes/class-wc-post-types.php');
require_once(plugin_dir_path(__FILE__) . '/includes/custom-widgets.php');  
load_plugin_textdomain('Wptuts_Dashboard_Widgets', false, dirname( plugin_basename( __FILE__ ) ) . '/');   

                           
/*************************Post Type Registeration************************************/
class WC_custom_Post_types extends WC_Post_types {
        public static function register_post_status(){            
        register_post_status( 'wc-on-cart', array(  
                        'label'                     => _x( 'on cart', 'Order status', 'woocommerce' ),
                        'public'                    => false,
                        'exclude_from_search'       => false,
                        'show_in_admin_all_list'    => true,
                        'show_in_admin_status_list' => true,
                        'label_count'               => _n_noop( 'On Cart <span class="count">(%s)</span>', 'On Cart <span class="count">(%s)</span>', 'woocommerce' )
                ) );
                register_post_status( 'wc-on-checkout', array(
                        'label'                     => _x( 'on checkout', 'Order status', 'woocommerce' ),
                        'public'                    => false,
                        'exclude_from_search'       => false,
                        'show_in_admin_all_list'    => true,
                        'show_in_admin_status_list' => true,
                        'label_count'               => _n_noop( 'On Checkout <span class="count">(%s)</span>', 'On Checkout <span class="count">(%s)</span>', 'woocommerce' )
                ) );
    }
}
/*************************Post Type Registeration************************************/


                    /*************************Customer Conversion tracker************************************/
Class Wptuts_Dashboard_Widgets extends WC_Admin_Report
    {
                public $chart_colours = array();  
                function __construct()
                             {  
                                // Initialize settings
                                register_activation_hook( __FILE__, array(&$this, 'woocommerce_ac_activate'));
                               // Define constants
                                $this->define_constants();
                              
                                // Actions to be done on cart update     
                                add_action('woocommerce_add_to_cart', array(&$this, 'woocommerce_ac_store_cart_timestamp')); 
                                // delete added temp fields after order is placed 
                                add_filter('woocommerce_order_details_after_order_table', array(&$this, 'action_after_delivery_session'));
                                if(!is_admin()){
                                function checkout_order()
                                {
                                    
                                   $postid = get_the_ID();
                                  
                                global $wpdb;
                                $curr_page_id=isset($_REQUEST['page_id'])?$_REQUEST['page_id']:''; 
                                
                                $requesturl=$_SERVER['REDIRECT_URL'];
                                 if (strpos($requesturl,'checkout') !== false){$checkout="checkout";}
                                 
                                $checkout_page_id = wc_get_page_id( 'checkout' );
                                $add_to_cart = wc_get_page_id( 'cart' );
                                $shop_page_id = wc_get_page_id('shop');
                                $checkout_url     = '';
                                $user_id=get_current_user_id();
                                global $woocommerce;
                                $_SESSION['pro_quantity']=$product_quantity= count($woocommerce->cart->get_cart());
                                $meta_key='_woocommerce_persistent_checkout';
                                $tablename=$wpdb->prefix."ct_abandoned_cart_history";
                                $select_query= $wpdb->prepare( "SELECT * FROM $tablename WHERE user_id = %d AND cart_ignored= %s", $user_id,'0','0' );  
                                $results = $wpdb->get_results( $select_query ); 
                                 if ($checkout_page_id ==$curr_page_id || (isset($checkout) && $checkout=="checkout")) {
                                     foreach($results as $result){
                                         $post_id= $result->post_id;
                                         $update_post= array(
                                                    'ID'   => $post_id,
                                                    'post_name'=> 'order-'.date("M-d-Y").'-'.date("h:i-a"),
                                                    'post_status'    => 'wc-on-checkout',
                                                    'post_date'      =>date("Y-m-d h:i:sa"),
                                                    'post_date_gmt'  =>date("Y-m-d h:i:sa")
                                                  );
                                    wp_update_post( $update_post );
                                    $option="last_updated_tracker";
                                    $value=date('l jS M g:ia');
                                    update_option( $option, $value);
                                         $i=0;
                                         if($product_quantity>1){
                                        for($i=0;$i<$product_quantity;$i++){
                                         $post_id= $result->post_id-$i;
                                          $update_post= array(
                                                    'ID'   => $post_id,
                                                    'post_name'=> 'order-'.date("M-d-Y").'-'.date("h:i-a"),
                                                    'post_status'    => 'wc-on-checkout',
                                                    'post_date'      =>date("Y-m-d h:i:sa"),
                                                    'post_date_gmt'  =>date("Y-m-d h:i:sa")
                                                  );
                                    wp_update_post( $update_post );
                                        $option="last_updated_tracker";
                                        $value=date('l jS M g:ia');
                                    update_option( $option, $value);
                                            }}}
                                }      
                            }
                            add_action('init','checkout_order');     
                            }
                            add_action( 'admin_enqueue_scripts', array(&$this, 'my_enqueue_scripts_css' ));
                           // add_action('admin_init', array(&$this,'my_enqueue_scripts_js'));
                            add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
                            add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );     
                             
                            }     
                       
                
                           
function define_constants(){
       define('WPTL_BASIC_PLUGIN_DIR',plugin_dir_path(__FILE__));
       define('WPTL_BASIC_PLUGIN_URL',plugins_url('', __FILE__));       
}   
                         
        function my_enqueue_scripts_css( $hook ) {                				
           wp_enqueue_style( 'woocommerce_admin_styles', plugins_url() . '/woocommerce/assets/css/admin.css' );
           wp_enqueue_style( 'dashboard_style', WPTL_BASIC_PLUGIN_URL . '/assets/css/dashboard.css' );                         
           wp_enqueue_style( 'jquery-ui-style', WPTL_BASIC_PLUGIN_URL . '/assets/css/jquery-ui.css' );				

           }     
                       
    public function calculate_current_ranges( $current_range ) {
                switch ( $current_range ) {

            case 'month' :
                    $this->start_date    = strtotime( date( 'Y-m-01', current_time('timestamp') ) );
                    $this->end_date      = strtotime( 'midnight', current_time( 'timestamp' ) );
                    $this->chart_groupby = 'day';
            break;


          }

		// Group by
               // echo $this->chart_groupby; die("------asdasd");
		switch ( $this->chart_groupby ) {

			case 'day' :
				$this->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
				$this->chart_interval = ceil( max( 0, ( $this->end_date - $this->start_date ) / ( 60 * 60 * 24 ) ) );
				$this->barwidth       = 60 * 60 * 24 * 1000;
			break;

			case 'month' :
				$this->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date)';
				$this->chart_interval = 0;
				$min_date             = $this->start_date;

				while ( ( $min_date   = strtotime( "+1 MONTH", $min_date ) ) <= $this->end_date ) {
					$this->chart_interval ++;
				}

				$this->barwidth = 60 * 60 * 24 * 7 * 4 * 1000;
			break;
		}
                
                
                        
         }
        /**
	 * Get the legend for the main chart sidebar
	 * @return array
	 */
	public function get_chart_legend() {
		$legend   = array();
                $total_cart_orders = $this->get_order_report_tracker_data( array(
			'data' => array(                   
				'ID' => array(
					'type'     => 'post_data',
					'function' => 'COUNT',
					'name'     => 'total_abandoned_cart'
				),
				'post_date' => array(
					'type'     => 'post_data',
					'function' => '',
					'name'     => 'post_date'
				),
			),
			'query_type'   => 'get_var',
                        'order_types'  => wc_get_order_types( 'order-count' ),
			'filter_range' => true,
			'order_status' => array('on-cart'),
		) );
             $total_shop_orders= $this->get_order_report_tracker_data( array(
			'data' => array(                   
				'ID' => array(
					'type'     => 'post_data',
					'function' => 'COUNT',
					'name'     => 'total_orders'
				),
				'post_date' => array(
					'type'     => 'post_data',
					'function' => '',
					'name'     => 'post_date'
				),
			),
			'query_type'   => 'get_var',
			'order_types'  => wc_get_order_types( 'order-count' ),
			'filter_range' => true,
			'order_status' => array('completed', 'processing', 'on-hold', 'refunded','pending'),                 
		) );
                $total_checkout_orders = $this->get_order_report_tracker_data( array(
			'data' => array(				                             
				'ID' => array(
					'type'     => 'post_data',
					'function' => 'COUNT',
					'name'     => 'total_abandoned_checkout'
				),
				'post_date' => array(
					'type'     => 'post_data',
					'function' => '',
					'name'     => 'post_date'
				),
			),
			'query_type'   => 'get_var',
			'order_types'  => wc_get_order_types( 'order-count' ),
			'filter_range' => true,
			'order_status' => array( 'on-checkout'),
		) );
                    
        $total=$total_cart_orders+$total_checkout_orders+$total_shop_orders;
      /*  echo "total_shop_orders".$total_shop_orders; echo '<br>';
         echo "total_checkout_orders".$total_checkout_orders; echo '<br>';
          echo "total_cart_orders".$total_cart_orders; echo '<br>';*/
          if($total_cart_orders!=0 || $total!==0 || $total_checkout_orders!=0 || $total_shop_orders!=0){              
                $_SESSION['opacity_checkout']=$checkout_percent=round( (($total_checkout_orders / $total)*100), 2 ).'%';
                $_SESSION['opacity_purchased']=$purchased_percent=round( (($total_shop_orders / $total)*100), 2 ).'%';
                $_SESSION['opacity_cart']=$cart_percent=round( (($total_cart_orders / $total)*100), 2 ).'%';
          
          }
                $legend[] = array(
			'title' => sprintf( __( '%sAdded to Cart', 'woocommerce' ), '<div class="cart range"><img src="'.plugins_url().'/Customer-Conversion-Tracker-Lite/assets/images/cart.png" alt="cart" class="cart_icn icon"/><div class="line_connector add-cart-line"></div></div><strong><span>' .$cart_percent.'</span>&nbsp;('. $total_cart_orders  . ')</strong>' ),
			'color' => $this->chart_colours['order_add_to_cart_count'],
			'highlight_series' => 0 
		);
                $legend[] = array(
			'title' => sprintf( __( '%sReached  Checkout', 'woocommerce' ), '<div class="checkout range"><img src="'.plugins_url().'/Customer-Conversion-Tracker-Lite/assets/images/rarrow.png" alt="checkout" class="checkout_icn icon"/><div class="line_connector"></div></div><strong><span>' .$checkout_percent.'</span>&nbsp;('. $total_checkout_orders . ')</strong>' ),
			'color' => $this->chart_colours['order_reached_to_checkout_count'],
			'highlight_series' => 1
		);
                $legend[] = array(
			'title' => sprintf( __( '%sPurchased', 'woocommerce' ), '<div class="purchased range"><img src="'.plugins_url().'/Customer-Conversion-Tracker-Lite/assets/images/barrow.png" alt="purchased" class="purchased_icn icon"/></div><strong><span>' .$purchased_percent.'</span>&nbsp;('. $total_shop_orders . ')</strong>' ),
			'color' => $this->chart_colours['order_count'],
			'highlight_series' => 3
		);
		return $legend;
	}

	/**
	 * Output the report
	 */
	public function output_report() { 
		
		$this->chart_colours = array(
			'order_count'  => ' #009900',
                        'order_reached_to_checkout_count' => '#e67e00',
			'order_add_to_cart_count' => '#FFFF00 ',
                        'sales_amount' => '#3498db',
			'average'      => '#75b9e7',
			'order_count'  => '#b8c0c5',
			'item_count'   => '#d4d9dc',
			'coupon_amount' => '#e67e22',
			'shipping_amount' => '#1abc9c',
			'refund_amount' => '#CC3366'
		);
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : 'month';		
		$this->calculate_current_ranges( $current_range );               
                include (plugin_dir_path( __FILE__ ).'includes/html-report-by-date.php');
		
	}
	/**
	 * Output an export link
	 */
	
             
        /**
	 * Get report totals such as order totals and discount amounts.
	 *
	 * Data example:
	 *
	 * '_order_total' => array(
	 *     'type'     => 'meta',
	 *     'function' => 'SUM',
	 *     'name'     => 'total_sales'
	 * )
	 *
	 * @param  array $args
	 * @return array|string depending on query_type
	 */
	public function get_order_report_tracker_data( $args = array() ) {
		global $wpdb;

		$default_args = array(
			'data'                => array(),
			'where'               => array(),
			'where_meta'          => array(),
			'query_type'          => 'get_row',
			'group_by'            => '',
			'order_by'            => '',
			'limit'               => '',
			'filter_range'        => false,
			'nocache'             => false,
			'debug'               => false,
			'order_types'         => wc_get_order_types( 'reports' ),
			'order_status'        => array( 'completed', 'processing', 'on-hold' ),
			'parent_order_status' => false
		);
		$args = apply_filters( 'woocommerce_reports_get_order_report_data_args', wp_parse_args( $args, $default_args ) );                
		extract( $args );
		if ( empty( $data ) ) {
			return false;
		}

		$order_status = apply_filters( 'woocommerce_reports_order_statuses', $order_status );

		$query  = array();
		$select = array();
		foreach ( $data as $key => $value ) {
			$distinct = '';

			if ( isset( $value['distinct'] ) )
				$distinct = 'DISTINCT';

			if ( $value['type'] == 'meta' ) {
				$get_key = "meta_{$key}.meta_value";
			} elseif( $value['type'] == 'post_data' ) {
				$get_key = "posts.{$key}";
			} elseif( $value['type'] == 'order_item_meta' ) {
				$get_key = "order_item_meta_{$key}.meta_value";
			} elseif( $value['type'] == 'order_item' ) {
				$get_key = "order_items.{$key}";
			}

			if ( $value['function'] ) {
				$get = "{$value['function']}({$distinct} {$get_key})";
			} else {
				$get = "{$distinct} {$get_key}";
			}

			$select[] = "{$get} as {$value['name']}";
		}

		$query['select'] = "SELECT " . implode( ',', $select );
		$query['from']   = "FROM {$wpdb->posts} AS posts";

		// Joins
		$joins = array();

		foreach ( $data as $key => $value ) {

			if ( $value['type'] == 'meta' ) {

				$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";

			} elseif ( $value['type'] == 'order_item_meta' ) {

				$joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
				$joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

			} elseif ( $value['type'] == 'order_item' ) {

				$joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";

			}
		}

		if ( ! empty( $where_meta ) ) {

			foreach ( $where_meta as $value ) {

				if ( ! is_array( $value ) ) {
					continue;
				}

				$key = is_array( $value['meta_key'] ) ? $value['meta_key'][0] . '_array' : $value['meta_key'];

				if ( isset( $value['type'] ) && $value['type'] == 'order_item_meta' ) {

					$joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
					$joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

				} else {
					// If we have a where clause for meta, join the postmeta table
					$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
				}
			}
		}

		if ( ! empty( $parent_order_status ) ) {
			$joins["parent"] = "LEFT JOIN {$wpdb->posts} AS parent ON posts.post_parent = parent.ID";
		}

		$query['join'] = implode( ' ', $joins );

		
                $query['where']  = "
			WHERE 	posts.post_type 	IN ( '" . implode( "','", $order_types ) . "' )
			";

		if ( ! empty( $order_status ) ) {
			$query['where'] .= "
				AND 	posts.post_status 	IN ( 'wc-" . implode( "','wc-", $order_status ) . "')
			";
		}

		if ( ! empty( $parent_order_status ) ) {
			$query['where'] .= "
				AND 	parent.post_status 	IN ( 'wc-" . implode( "','wc-", $parent_order_status ) . "')
			";
		}

		if ( $filter_range ) {

			$query['where'] .= "
				AND 	posts.post_date >= '" . date('Y-m-d', $this->start_date ) . "'
				AND 	posts.post_date < '" . date('Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) . "'
			";
		}

		foreach ( $data as $key => $value ) {

			if ( $value['type'] == 'meta' ) {

				$query['where'] .= " AND meta_{$key}.meta_key = '{$key}'";

			} elseif ( $value['type'] == 'order_item_meta' ) {

				$query['where'] .= " AND order_items.order_item_type = '{$value['order_item_type']}'";
				$query['where'] .= " AND order_item_meta_{$key}.meta_key = '{$key}'";

			}
		}

		if ( ! empty( $where_meta ) ) {

			$relation = isset( $where_meta['relation'] ) ? $where_meta['relation'] : 'AND';

			$query['where'] .= " AND (";

			foreach ( $where_meta as $index => $value ) {

				if ( ! is_array( $value ) ) {
					continue;
				}

				$key = is_array( $value['meta_key'] ) ? $value['meta_key'][0] . '_array' : $value['meta_key'];

				if ( strtolower( $value['operator'] ) == 'in' ) {

					if ( is_array( $value['meta_value'] ) ) {
						$value['meta_value'] = implode( "','", $value['meta_value'] );
					}

					if ( ! empty( $value['meta_value'] ) ) {
						$where_value = "IN ('{$value['meta_value']}')";
					}
				} else {
					$where_value = "{$value['operator']} '{$value['meta_value']}'";
				}
				if ( ! empty( $where_value ) ) {
					if ( $index > 0 ) {
						$query['where'] .= ' ' . $relation;
					}

					if ( isset( $value['type'] ) && $value['type'] == 'order_item_meta' ) {

						if ( is_array( $value['meta_key'] ) ) {
							$query['where'] .= " ( order_item_meta_{$key}.meta_key   IN ('" . implode( "','", $value['meta_key'] ) . "')";
						} else {
							$query['where'] .= " ( order_item_meta_{$key}.meta_key   = '{$value['meta_key']}'";
						}

						$query['where'] .= " AND order_item_meta_{$key}.meta_value {$where_value} )";
					} else {

						if ( is_array( $value['meta_key'] ) ) {
							$query['where'] .= " ( meta_{$key}.meta_key   IN ('" . implode( "','", $value['meta_key'] ) . "')";
						} else {
							$query['where'] .= " ( meta_{$key}.meta_key   = '{$value['meta_key']}'";
						}

						$query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
					}
				}
			}
			$query['where'] .= ")";
		}
		if ( ! empty( $where ) ) {

			foreach ( $where as $value ) {

				if ( strtolower( $value['operator'] ) == 'in' ) {

					if ( is_array( $value['value'] ) ) {
						$value['value'] = implode( "','", $value['value'] );
					}

					if ( ! empty( $value['value'] ) ) {
						$where_value = "IN ('{$value['value']}')";
					}
				} else {
					$where_value = "{$value['operator']} '{$value['value']}'";
				}

				if ( ! empty( $where_value ) )
					$query['where'] .= " AND {$value['key']} {$where_value}";
			}
		}
		if ( $group_by ) {
			$query['group_by'] = "GROUP BY {$group_by}";
		}
		if ( $order_by ) {
			$query['order_by'] = "ORDER BY {$order_by}";
		}
		if ( $limit ) {
			$query['limit'] = "LIMIT {$limit}";
		}
		$query          = apply_filters( 'woocommerce_reports_get_order_report_query', $query );
		$query          = implode( ' ', $query );
                $results=$wpdb->$query_type($query);
		return $results;
	}
        /**
	 * To remove dashboard Widget	
	 * 
	 */     
        
    function remove_dashboard_widgets()
	{
	    global $remove_defaults_widgets;
	 
	    foreach ($remove_defaults_widgets as $wigdet_id => $options)
	    {
	        remove_meta_box($wigdet_id, $options['page'], $options['context']);
	    }
	}
 /**
	 * To add dashboard Widget	
	 * 
	 */ 
    function add_dashboard_widgets()
	{
	    global $custom_dashboard_widgets;
	 
	    foreach ($custom_dashboard_widgets as $widget_id => $options)
	    {
	        wp_add_dashboard_widget(
	            $widget_id,
	            $options['title'],
	            $options['callback']
	        );
	    }
	}
     
            /**
              * To Create  database table	
              * 
              */ 
                function woocommerce_ac_activate() {
                    global $wpdb;
				$ac_history_table_name = $wpdb->prefix . "ct_abandoned_cart_history";
				 
				$history_query = "CREATE TABLE IF NOT EXISTS $ac_history_table_name (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`abandoned_cart_info` text COLLATE utf8_unicode_ci NOT NULL,
				`abandoned_cart_time` int(11) NOT NULL,
                                `abandoned_cart_date` datetime NOT NULL,
				`cart_ignored` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
				`recovered_cart` int(11) NOT NULL,
                                `post_id` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
						 
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($history_query);			
			}		
                        /**
                            * store cart timestamp	
                            * 
                            */ 
			function woocommerce_ac_store_cart_timestamp() {
                            $history_query = "CREATE TABLE IF NOT EXISTS $ac_history_table_name (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`abandoned_cart_info` text COLLATE utf8_unicode_ci NOT NULL,
				`abandoned_cart_time` int(11) NOT NULL,
                                `abandoned_cart_date` datetime NOT NULL,
				`cart_ignored` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
				`recovered_cart` int(11) NOT NULL,
                                `post_id` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";						 
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($history_query);
                                $option="last_updated_tracker";
                                $value=date('l jS M g:ia');
                                if(get_option($option)){update_option( $option, $value);}else{add_option( $option, $value);}
                                error_reporting(0);
				$user_id = get_current_user_id();
                                if ( $user_id )
				{
                                    $user_id = get_current_user_id();
                                }
                                else{
                                   $user_id='0'; 
                                }
				global $wpdb;
                                $date=date("Y-m-d");
                                $date_post=date("M-d-Y");
                                $time=date("h:i:sa");
                                $time_post=date("h:i");                                
				
				$current_time = current_time('timestamp');
				$cut_off_time = json_decode(get_option('woocommerce_ac_settings'));
				$cart_cut_off_time = $cut_off_time[0]->cart_time * 60;
				$compare_time = $current_time - $cart_cut_off_time;
                                
                                $tablename=$wpdb->prefix."ct_abandoned_cart_history";
                                
                                $newquery= $wpdb->prepare( "SELECT * FROM $tablename WHERE user_id = %d AND cart_ignored= %s AND recovered_cart=%s ", $user_id,'0','0' );                                
				$results = $wpdb->get_results( $newquery );  
                                
                               
                                
                                $option="last_updated_tracker";
                                $value=date('l jS M g:ia');
                                if(get_option($option)!=""){update_option( $option, $value);}else{add_option( $option, $value);} 
                            
				if ( count($results) == 0 )
				{                                    
                                    	$cart_info = json_encode(get_user_meta($user_id, '_woocommerce_persistent_cart', true));
                                        $checkout_info=json_encode(get_user_meta($user_id, '_woocommerce_persistent_checkout', true));
                                        $post = array(
                                            'post_content'   => $cart_info,
                                            'post_name'      => 'order-'.$date_post.'-'.date("h:i-a"),
                                            'post_title'     => 'order-'.$date_post.'-'.$time_post,
                                            'post_status'    => 'wc-on-cart',
                                            'post_type'      => 'shop_order',
                                            'post_author'    => $user_id,
                                            'ping_status'    =>'closed',
                                            'post_date'      =>date("Y-m-d h:i:sa"),
                                            'post_date_gmt'  =>date("Y-m-d h:i:sa")                                            
                                          ); 
                                         $post_id =  wp_insert_post($post);  
                                        
                                         
                                         $query=$wpdb->prepare( 
                                                "
                                                INSERT INTO $tablename
                                                ( user_id, abandoned_cart_info,abandoned_cart_time,abandoned_cart_date,cart_ignored,recovered_cart,post_id )
                                                VALUES (%d,%s,%s,%s,%s,%s,%d)
                                                ", 
                                                array(
                                                            $user_id, 
                                                            $cart_info, 
                                                            $current_time,
                                                            $date,
                                                            '0',
                                                            '0',
                                                            $post_id
                                                    ) 
                                        );
                                        $wpdb->query($query);
                                        
                                        $option="last_updated_tracker";
                                        $value=date('l jS M g:ia');
                                        if(get_option($option)){update_option( $option, $value);}else{add_option( $option, $value);}
				}
				elseif ( $compare_time > $results[0]->abandoned_cart_time )
				{
                                       
                                    $updated_cart_info = json_encode(get_user_meta($user_id, '_woocommerce_persistent_cart', true));
                                    $updated_checkout_info=json_encode(get_user_meta($user_id, '_woocommerce_persistent_checkout', true));
                                    if (! $this->compare_carts( $user_id, $results[0]->abandoned_cart_info) )
                                    {  
                                        $wpdb->update(
                                                $tablename,
                                                array( 'cart_ignored' => '1'),
                                                array( 'user_id' => $user_id ),
                                                array( '%s'),                                                        
                                                array( '%d') 
                                               );
                                        $post = array(
                                        'post_content'   => $updated_cart_info,
                                        'post_name'      => 'order-'.$date_post.'-'.date("h:i-a"),
                                        'post_title'     => 'order-'.$date_post.'-'.$time_post,
                                        'post_status'    => 'wc-on-cart',
                                        'post_type'      => 'shop_order',
                                        'post_author'    => $user_id,
                                        'ping_status'    =>'closed',
                                        'post_date'      =>date("Y-m-d h:i:sa"),
                                        'post_date_gmt'  =>date("Y-m-d h:i:sa")
                                      ); 
                                    $option="last_updated_tracker";
                                    $value=date('l jS M g:ia');
                                    if(get_option($option)){update_option( $option, $value);}else{add_option( $option, $value);}
                                    $post_id =wp_insert_post($post);                                     
                                     $query_update=$wpdb->prepare( 
                                                "
                                                INSERT INTO $tablename
                                                ( user_id, abandoned_cart_info,abandoned_cart_time,abandoned_cart_date,cart_ignored,recovered_cart,post_id )
                                                VALUES (%d,%s,%s,%s,%s,%s,%d)
                                                ", 
                                                array(
                                                            $user_id, 
                                                            $updated_cart_info, 
                                                            $current_time,
                                                            $date,
                                                            '0',
                                                            '0',
                                                            $post_id
                                                    ) 
                                        );
                                        $wpdb->query($query_update);
                                        update_user_meta($user_id, '_woocommerce_ac_modified_cart', md5("yes"));
                                    }
                                    else
                                    {
                                        update_user_meta($user_id, '_woocommerce_ac_modified_cart', md5("no"));
                                    }
				}
				else
				{                                   
                                        $option="last_updated_tracker";
                                        $value=date('l jS M g:ia');
                                        update_option( $option, $value);
					$updated_cart_info = json_encode(get_user_meta($user_id, '_woocommerce_persistent_cart', true));
                                        $updated_checkout_info=json_encode(get_user_meta($user_id, '_woocommerce_persistent_checkout', true));                                        
                                        $wpdb->update( 
                                            $tablename, 
                                            array( 
                                                    'abandoned_cart_info' => $updated_cart_info,	
                                                    'abandoned_cart_time' => $current_time	
                                                 ), 
                                            array( 'user_id' => 1,'cart_ignored'=>0 ), 
                                            array( 
                                                    '%s',	
                                                    '%s'	
                                                ), 
                                            array( '%d','%s' ) 
                                        );                                        
				}                                
				return 0;
			}	
                        /**
                            * compare cart Items	
                            * 
                            */ 
			function compare_carts($user_id, $last_abandoned_cart)
			{
				$current_woo_cart = get_user_meta($user_id, '_woocommerce_persistent_cart', true);
				$abandoned_cart_arr = json_decode($last_abandoned_cart,true);			
				$temp_variable = "";
				if ( count($current_woo_cart['cart']) >= count($abandoned_cart_arr['cart']) )
				{
					//do nothing
				}
				else
				{
					$temp_variable = $current_woo_cart;
					$current_woo_cart = $abandoned_cart_arr;
					$abandoned_cart_arr = $temp_variable;
				}
				foreach ($current_woo_cart as $key => $value)
				{
					foreach ($value as $item_key => $item_value)
					{
						$current_cart_product_id = $item_value['product_id'];
						$current_cart_variation_id = $item_value['variation_id'];
				$current_cart_quantity = $item_value['quantity'];
			$abandoned_cart_checkout = get_option( 'abandoned_cart_checkout' );	
                        if($abandoned_cart_checkout){
						$abandoned_cart_product_id = $abandoned_cart_arr[$key][$item_key]['product_id'];
						$abandoned_cart_variation_id = $abandoned_cart_arr[$key][$item_key]['variation_id'];
						$abandoned_cart_quantity = $abandoned_cart_arr[$key][$item_key]['quantity'];
                        }else{                        
                        add_option( 'abandoned_cart_checkout', '255', '', 'yes' ); 
                        }		if (($current_cart_product_id != $abandoned_cart_product_id) ||
								($current_cart_variation_id != $abandoned_cart_variation_id) ||
								($current_cart_quantity != $abandoned_cart_quantity) )
						{
							return false;
						}
					}
				}
				return true;
			}			
			function action_after_delivery_session( $order ) {				
				global $wpdb;
				$user_id = get_current_user_id();
				delete_user_meta($user_id, '_woocommerce_ac_persistent_cart_time');
				delete_user_meta($user_id, '_woocommerce_ac_persistent_cart_temp_time');
			
				// get all latest abandoned carts that were modified				
                                $tablename=$wpdb->prefix."ct_abandoned_cart_history";
                                $query= $wpdb->prepare( "SELECT * FROM $tablename WHERE user_id = %d AND cart_ignored= %s AND recovered_cart=%s ORDER BY id DESC LIMIT 1", $user_id,'0','0' );  
				$results = $wpdb->get_results( $query );
                                global $woocommerce;
                                echo $quantity=$_SESSION['pro_quantity'];
                                foreach($results as $result){
                                    $post_id= $result->post_id; 
                                    $i=0;
                                   }
                                
				if ( get_user_meta($user_id, '_woocommerce_ac_modified_cart', true) == md5("yes") || 
						get_user_meta($user_id, '_woocommerce_ac_modified_cart', true) == md5("no") )
				{
					
					$order_id = $order->id;
                                        
                                        
                                        $wpdb->update( 
                                            $tablename, 
                                            array( 
                                                    'recovered_cart' => $order_id,	
                                                    'cart_ignored' => '1'	
                                            ), 
                                            array( 'id' => $results[0]->id), 
                                            array( 
                                                    '%s',	
                                                    '%s'	
                                            ), 
                                            array( '%d' ) 
                                        ); 
                                        
                                        
					delete_user_meta($user_id, '_woocommerce_ac_modified_cart');
				}
				else
				{
                                  
                                    $select_query= $wpdb->prepare( "SELECT * FROM $tablename WHERE id = %d",$results[0]->id);  
                                    $results = $wpdb->get_results( $select_query );
                                        foreach($results as $result){
                                                   $post_id= $result->post_id;
                                                   wp_delete_post($post_id, true);
                                        }
                                        $delete_query= $wpdb->prepare("DELETE FROM $tablename WHERE id = %d",$results[0]->id); 
                                        $wpdb->query($delete_query);
                               }
                    }					
} 
 $wdw = new Wptuts_Dashboard_Widgets();

}
