<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_theme_cal_merop_register_post_type() {
	register_post_type(
		'portal_cal_merop',
		array(
			'labels'              => array(
				'name'               => __( 'Календарь мероприятий', 'portal-theme' ),
				'singular_name'      => __( 'Мероприятие', 'portal-theme' ),
				'add_new'            => __( 'Добавить', 'portal-theme' ),
				'add_new_item'       => __( 'Новое мероприятие', 'portal-theme' ),
				'edit_item'          => __( 'Редактировать', 'portal-theme' ),
				'new_item'           => __( 'Новое мероприятие', 'portal-theme' ),
				'view_item'          => __( 'Просмотр', 'portal-theme' ),
				'search_items'       => __( 'Поиск', 'portal-theme' ),
				'not_found'          => __( 'Записей не найдено', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
				'all_items'          => __( 'Все мероприятия', 'portal-theme' ),
				'menu_name'          => __( 'Календарь мероприятий', 'portal-theme' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-schedule',
			'menu_position'       => 30,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'excerpt' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
		)
	);
}
add_action( 'init', 'portal_theme_cal_merop_register_post_type' );

function portal_theme_cal_merop_collect_for_js() {
	$q = new WP_Query(
		array(
			'post_type'      => 'portal_cal_merop',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => -1,
			'meta_key'       => '_portal_cal_merop_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_type'      => 'DATE',
		)
	);
	$out = array();
	if ( ! $q->have_posts() ) {
		wp_reset_postdata();
		return $out;
	}
	while ( $q->have_posts() ) {
		$q->the_post();
		$pid  = get_the_ID();
		$date = get_post_meta( $pid, '_portal_cal_merop_date', true );
		$date = is_string( $date ) ? $date : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			continue;
		}
		$out[] = array(
			'id'          => $pid,
			'date'        => $date,
			'title'       => get_the_title(),
			'description' => (string) get_post_field( 'post_excerpt', $pid ),
		);
	}
	wp_reset_postdata();
	return $out;
}

function portal_theme_cal_merop_add_meta_box() {
	add_meta_box(
		'portal_cal_merop_date',
		__( 'Дата в календаре', 'portal-theme' ),
		'portal_theme_cal_merop_meta_box_render',
		'portal_cal_merop',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_cal_merop_add_meta_box' );

function portal_theme_cal_merop_meta_box_render( $post ) {
	wp_nonce_field( 'portal_cal_merop_save_meta', 'portal_cal_merop_meta_nonce' );
	$date = get_post_meta( $post->ID, '_portal_cal_merop_date', true );
	$date = is_string( $date ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';
	?>
	<p>
		<label for="portal-cal-merop-date"><strong><?php esc_html_e( 'Дата', 'portal-theme' ); ?></strong></label><br>
		<input type="date" name="portal_cal_merop_date" id="portal-cal-merop-date" value="<?php echo esc_attr( $date ); ?>" required style="max-width:100%;box-sizing:border-box;">
	</p>
	<p class="description"><?php esc_html_e( 'День отображения в сетке календаря. Подробности — в поле «Отрывок».', 'portal-theme' ); ?></p>
	<?php
}

function portal_theme_cal_merop_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_cal_merop_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_cal_merop_meta_nonce'] ) ), 'portal_cal_merop_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_cal_merop' ) {
		return;
	}

	$raw = isset( $_POST['portal_cal_merop_date'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_cal_merop_date'] ) ) : '';
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		update_post_meta( $post_id, '_portal_cal_merop_date', $raw );
	} else {
		delete_post_meta( $post_id, '_portal_cal_merop_date' );
	}
}
add_action( 'save_post_portal_cal_merop', 'portal_theme_cal_merop_save_meta' );

function portal_theme_cal_merop_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portal_cal_merop_date'] = __( 'Дата', 'portal-theme' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_cal_merop_posts_columns', 'portal_theme_cal_merop_posts_columns' );

function portal_theme_cal_merop_posts_custom_column( $column, $post_id ) {
	if ( 'portal_cal_merop_date' !== $column ) {
		return;
	}
	$d = get_post_meta( (int) $post_id, '_portal_cal_merop_date', true );
	echo esc_html( is_string( $d ) ? $d : '—' );
}
add_action( 'manage_portal_cal_merop_posts_custom_column', 'portal_theme_cal_merop_posts_custom_column', 10, 2 );
