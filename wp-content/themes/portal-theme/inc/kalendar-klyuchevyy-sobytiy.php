<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_theme_kse_type_slugs() {
	return array( 'video', 'docs' );
}

function portal_theme_kse_type_labels() {
	return array(
		'video' => __( 'Видеоконференция', 'portal-theme' ),
		'docs'  => __( 'Подача документов', 'portal-theme' ),
	);
}

function portal_theme_kse_register_post_type() {
	register_post_type(
		'portal_kse_event',
		array(
			'labels'              => array(
				'name'               => __( 'Календарь ключевых событий', 'portal-theme' ),
				'singular_name'      => __( 'Событие', 'portal-theme' ),
				'add_new'            => __( 'Добавить событие', 'portal-theme' ),
				'add_new_item'       => __( 'Новое событие', 'portal-theme' ),
				'edit_item'          => __( 'Редактировать событие', 'portal-theme' ),
				'new_item'           => __( 'Новое событие', 'portal-theme' ),
				'view_item'          => __( 'Просмотр', 'portal-theme' ),
				'search_items'       => __( 'Поиск событий', 'portal-theme' ),
				'not_found'          => __( 'Событий не найдено', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
				'all_items'          => __( 'Все события', 'portal-theme' ),
				'menu_name'          => __( 'Календарь событий', 'portal-theme' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-calendar-alt',
			'menu_position'       => 29,
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
add_action( 'init', 'portal_theme_kse_register_post_type' );

function portal_theme_kse_collect_events_for_js() {
	$q = new WP_Query(
		array(
			'post_type'      => 'portal_kse_event',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => -1,
			'meta_key'       => '_portal_kse_date',
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
		$date = get_post_meta( $pid, '_portal_kse_date', true );
		$date = is_string( $date ) ? $date : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			continue;
		}
		$type = get_post_meta( $pid, '_portal_kse_type', true );
		$type = is_string( $type ) ? sanitize_key( $type ) : 'video';
		if ( ! in_array( $type, portal_theme_kse_type_slugs(), true ) ) {
			$type = 'video';
		}
		$color = ( 'docs' === $type ) ? 'blue' : 'green';
		$out[] = array(
			'id'          => $pid,
			'date'        => $date,
			'title'       => get_the_title(),
			'description' => (string) get_post_field( 'post_excerpt', $pid ),
			'color'       => $color,
			'conferenceUrl' => (string) get_post_meta( $pid, '_portal_kse_conference_url', true ),
		);
	}
	wp_reset_postdata();
	return $out;
}

function portal_theme_kse_add_meta_box() {
	add_meta_box(
		'portal_kse_details',
		__( 'Дата и тип события', 'portal-theme' ),
		'portal_theme_kse_meta_box_render',
		'portal_kse_event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_kse_add_meta_box' );

function portal_theme_kse_meta_box_render( $post ) {
	wp_nonce_field( 'portal_kse_save_meta', 'portal_kse_meta_nonce' );
	$date = get_post_meta( $post->ID, '_portal_kse_date', true );
	$date = is_string( $date ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';

	$type = get_post_meta( $post->ID, '_portal_kse_type', true );
	$type = is_string( $type ) ? sanitize_key( $type ) : 'video';
	if ( ! in_array( $type, portal_theme_kse_type_slugs(), true ) ) {
		$type = 'video';
	}
	$conference_url = get_post_meta( $post->ID, '_portal_kse_conference_url', true );
	$conference_url = is_string( $conference_url ) ? $conference_url : '';
	$labels = portal_theme_kse_type_labels();
	?>
	<p>
		<label for="portal-kse-date"><strong><?php esc_html_e( 'Дата события', 'portal-theme' ); ?></strong></label><br>
		<input type="date" name="portal_kse_date" id="portal-kse-date" value="<?php echo esc_attr( $date ); ?>" required style="max-width:220px;">
	</p>
	<p class="description"><?php esc_html_e( 'День, в котором событие отображается в календаре на сайте.', 'portal-theme' ); ?></p>
	<p style="margin-top:16px;">
		<label for="portal-kse-type"><strong><?php esc_html_e( 'Тип', 'portal-theme' ); ?></strong></label><br>
		<select name="portal_kse_type" id="portal-kse-type" style="max-width:320px;">
			<?php foreach ( portal_theme_kse_type_slugs() as $slug ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $type, $slug ); ?>>
					<?php echo isset( $labels[ $slug ] ) ? esc_html( $labels[ $slug ] ) : esc_html( $slug ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description">
		<?php esc_html_e( 'Краткое описание в календаре — поле «Отрывок» справа. Заголовок — название события.', 'portal-theme' ); ?>
	</p>
	<p style="margin-top:16px;">
		<label for="portal-kse-conference-url"><strong><?php esc_html_e( 'Ссылка на видеоконференцию', 'portal-theme' ); ?></strong></label><br>
		<input type="url" name="portal_kse_conference_url" id="portal-kse-conference-url" value="<?php echo esc_attr( $conference_url ); ?>" placeholder="https://" style="max-width:100%;">
	</p>
	<p class="description"><?php esc_html_e( 'Необязательно. Если ссылка заполнена, на сайте появится кнопка перехода к видеоконференции.', 'portal-theme' ); ?></p>
	<?php
}

function portal_theme_kse_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_kse_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_kse_meta_nonce'] ) ), 'portal_kse_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_kse_event' ) {
		return;
	}

	$raw = isset( $_POST['portal_kse_date'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_kse_date'] ) ) : '';
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		update_post_meta( $post_id, '_portal_kse_date', $raw );
	} else {
		delete_post_meta( $post_id, '_portal_kse_date' );
	}

	$type = isset( $_POST['portal_kse_type'] ) ? sanitize_key( wp_unslash( $_POST['portal_kse_type'] ) ) : 'video';
	if ( ! in_array( $type, portal_theme_kse_type_slugs(), true ) ) {
		$type = 'video';
	}
	update_post_meta( $post_id, '_portal_kse_type', $type );

	$conference_url = isset( $_POST['portal_kse_conference_url'] ) ? esc_url_raw( wp_unslash( $_POST['portal_kse_conference_url'] ) ) : '';
	if ( is_string( $conference_url ) && $conference_url !== '' ) {
		update_post_meta( $post_id, '_portal_kse_conference_url', $conference_url );
	} else {
		delete_post_meta( $post_id, '_portal_kse_conference_url' );
	}
}
add_action( 'save_post_portal_kse_event', 'portal_theme_kse_save_meta' );

function portal_theme_kse_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portal_kse_date'] = __( 'Дата', 'portal-theme' );
			$new['portal_kse_type'] = __( 'Тип', 'portal-theme' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_kse_event_posts_columns', 'portal_theme_kse_posts_columns' );

function portal_theme_kse_posts_custom_column( $column, $post_id ) {
	$post_id = (int) $post_id;
	if ( 'portal_kse_date' === $column ) {
		$d = get_post_meta( $post_id, '_portal_kse_date', true );
		echo esc_html( is_string( $d ) ? $d : '—' );
		return;
	}
	if ( 'portal_kse_type' === $column ) {
		$t = get_post_meta( $post_id, '_portal_kse_type', true );
		$t = is_string( $t ) ? sanitize_key( $t ) : '';
		$labels = portal_theme_kse_type_labels();
		echo isset( $labels[ $t ] ) ? esc_html( $labels[ $t ] ) : esc_html( $t );
	}
}
add_action( 'manage_portal_kse_event_posts_custom_column', 'portal_theme_kse_posts_custom_column', 10, 2 );
