<?php

	function matematica_theme_setup() {

		add_theme_support( 'title-tag' );

		add_theme_support( 'post-formats', array( 'aside', 'gallery' ) );

		add_theme_support( 'post-thumbnails' );

		add_theme_support( 'custom-header', $defaults );

		add_theme_support( 'custom-logo', array(
			'height'      => 70,
			'width'       => 75,
			'flex-width'  => true,
			'header-text' => array( 'site-title' ),
		) );

		load_theme_textdomain( 'matematica_theme', get_template_directory() . '/languages' );

		register_nav_menus( array(
			'primary' => __( 'Primary Navigation', 'matematica_theme' ),
			'top' => __( 'Top Navigation', 'matematica_theme' ),
		) );

		add_custom_background();
	}

	function matematica_theme_widgets_init() {
		register_sidebar( array(
			'name'          => __( 'Widget Area', 'matematica_theme' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your sidebar.', 'matematica_theme' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}

	function matematica_theme_enqueue_style() {
		wp_enqueue_style( 'matematica_theme_style', get_stylesheet_uri() ); 
		wp_enqueue_style( 'matematica_theme_bootstrap_style', get_template_directory_uri().'/css/bootstrap.css' );
		wp_enqueue_script( 'matematica_theme_bootstrap_script', get_template_directory_uri() . '/js/bootstrap.js', array( 'jquery' ), '3.3.7', true );
		wp_enqueue_script( 'matematica_theme_script', get_template_directory_uri()
			. '/js/myscript.js', '', '1.0', true );
	}

	function js_variables(){
	    $variables = array (
	        'ajaxurl' => admin_url('admin-ajax.php')
	    );
	    echo(
	        '<script type="text/javascript">window.wp_data = '.
	        json_encode($variables).
	        ';</script>'
	    );
	}
	add_action('wp_head','js_variables');

	function my_action_callback() {
		
	 	$errors = false;

		if( !is_email( $_POST[ 'ajax_email' ] )) $errors= true;
		if (empty( $_POST[ 'ajax_buyer_name' ] )) $errors[] = true;

		if ($errors) {
			echo 'Ошибка при отправке заказа';
			wp_die();
		}

		$post_data = array(
			'post_title'    => '',
			'post_content'  => $_POST['ajax_productname'],
			'post_status'   => 'publish',
			'post_type' => 'orders'
		);

		$post_id = wp_insert_post( $post_data );

		$my_post = array();
		$my_post['ID'] = $post_id; 
		$my_post['post_title'] = 'Заказ №'.$post_id;
		wp_update_post( $my_post );

		wp_set_post_terms( $post_id, $_POST[ 'ajax_delivery_method' ], 'delivery_method', False );
		update_post_meta( $post_id, 'email', $_POST[ 'ajax_email' ] );
		update_post_meta( $post_id, 'buyer_name', $_POST[ 'ajax_buyer_name' ] );
        update_post_meta( $post_id, 'delivery_method', $_POST[ 'ajax_delivery_method' ] );
        echo 'Заказ успешно отправлен';
        wp_die();
	}
	add_action( 'wp_ajax_my_action', 'my_action_callback' );
	add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );

	add_action( 'after_setup_theme', 'matematica_theme_setup');
	add_action( 'widgets_init', 'matematica_theme_widgets_init');
	add_action( 'wp_enqueue_scripts', 'matematica_theme_enqueue_style' );

	//******************************Фильтры*******************************************

	add_action( 'restrict_manage_posts','admin_posts_filter_restrict_manage_posts');
	function admin_posts_filter_restrict_manage_posts() {
		$arr_taxonomy = array("delivery_method", "status");
		
		global $wpdb, $pagenow;
		if ($pagenow != 'edit.php') return; //если страница не список постов, то выходим из функции
		$out = ''; //переменная в которой будут храниться html код наших фильтров
		switch ($_GET['post_type']) { //выбираем нужный тип поста (post_type)
		case 'orders': //тип поста orders
		foreach ($arr_taxonomy as $cat) {
			$rows = $wpdb->get_results("SELECT DISTINCT {$wpdb->terms}.term_id, {$wpdb->terms}.name FROM {$wpdb->terms}, {$wpdb->term_taxonomy} 
			WHERE {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_taxonomy_id AND {$wpdb->term_taxonomy}.taxonomy = '$cat'", ARRAY_A);

			$out .= '<select name="'.$cat.'2">';
			$out .='<option value="">--'.$cat.'--</option>';
			foreach($rows as $row) {
				if (isset($_GET[$cat.'2']) && $_GET[$cat.'2'] == $row['term_id'])
					$out .= sprintf ('<option value="%s" selected>%s</option>',
					$row['term_id'], $row['name']);
				else
					$out .= sprintf ('<option value="%s">%s</option>',
					$row['term_id'], $row['name']);
				}
				$out .= '</select>';

				//echo $out;
			}
			echo $out;
		}
	}

	add_filter( 'posts_where' , 'my_posts_where');
	function my_posts_where($where){
		$arr_taxonomy = array("delivery_method", "status");
		global $wp_query;
		if(!is_admin()) return $where;
		if($wp_query->get('post_type') !='orders' ) return $where; 

		global $wpdb;
		foreach ($arr_taxonomy as $cat) {
			if($_GET[$cat.'2']){
				$term_id = intval($_GET[$cat.'2']); 
				$add_where =" AND $wpdb->posts.ID in (SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = {$term_id})";
				$where.=$add_where;
			}
		}
		return $where;
	}

	//**************Колонки****************

	add_filter( 'manage_orders_posts_columns', 'matematica_theme_add_post_columns', 10, 1 );
	function matematica_theme_add_post_columns($columns){
		$num = 2; 
		$new_columns = array(
			'delivery_method' => __('Delivery method'),
			'status' => __('Status'),
			'date' => __('Date')
		);
		return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
	}

	add_action( 'manage_posts_custom_column', 'matematica_theme_fill_post_columns', 10, 1 );
	function matematica_theme_fill_post_columns( $column ) {
		global $post;
		switch ( $column ) {
			case 'delivery_method':
				//echo get_post_meta($post->ID, 'delivery_method', true);
				$post_terms = get_the_terms( $post->ID, 'delivery_method' );
				if ($post_terms) {
					$term = array_shift( $post_terms );
					echo $term->name;
				}
				break;
			case 'status':
				//echo get_post_meta($post->ID, 'status', true);
				//$post_terms = wp_get_object_terms( $post->ID,  'status' );
				$post_terms = get_the_terms( $post->ID, 'status' );
				if ($post_terms) {
					$term = array_shift( $post_terms );
					echo $term->name;
				}
				break;
		}
	}
?>