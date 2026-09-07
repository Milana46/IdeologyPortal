<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_theme_ideology_category_slugs() {
	return array( 'symbolika', 'akty', 'pasport', 'plany', 'grafik-ipg' );
}

function portal_theme_ideology_category_labels() {
	return array(
		'symbolika'   => __( 'Государственная символика', 'portal-theme' ),
		'akty'        => __( 'Акты', 'portal-theme' ),
		'pasport'     => __( 'Социальный паспорт предприятия', 'portal-theme' ),
		'plany'       => __( 'Планы работы', 'portal-theme' ),
		'grafik-ipg'  => __( 'График работы ИПГ', 'portal-theme' ),
	);
}

function portal_theme_ideology_register_post_type() {
	register_post_type(
		'portal_ideology',
		array(
			'labels'              => array(
				'name'               => __( 'Основы идеолога', 'portal-theme' ),
				'singular_name'      => __( 'Материал', 'portal-theme' ),
				'add_new'            => __( 'Добавить материал', 'portal-theme' ),
				'add_new_item'       => __( 'Новый материал', 'portal-theme' ),
				'edit_item'          => __( 'Редактировать материал', 'portal-theme' ),
				'new_item'           => __( 'Новый материал', 'portal-theme' ),
				'view_item'          => __( 'Просмотр', 'portal-theme' ),
				'search_items'       => __( 'Поиск материалов', 'portal-theme' ),
				'not_found'          => __( 'Материалов не найдено', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
				'all_items'          => __( 'Все материалы', 'portal-theme' ),
				'menu_name'          => __( 'Основы идеолога', 'portal-theme' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-book-alt',
			'menu_position'       => 27,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'excerpt', 'thumbnail' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
		)
	);
}
add_action( 'init', 'portal_theme_ideology_register_post_type' );

function portal_theme_ideology_post_to_item_array( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'portal_ideology' !== $post->post_type ) {
		return null;
	}
	$allowed = portal_theme_ideology_category_slugs();
	$cat     = get_post_meta( $post->ID, '_portal_ideology_category', true );
	$cat     = is_string( $cat ) ? sanitize_key( $cat ) : 'akty';
	if ( ! in_array( $cat, $allowed, true ) ) {
		$cat = 'akty';
	}
	$file_id = (int) get_post_meta( $post->ID, '_portal_ideology_file', true );
	$tid     = (int) get_post_thumbnail_id( $post->ID );
	$thumb   = '';
	if ( $tid > 0 ) {
		$thumb = wp_get_attachment_image_url( $tid, 'medium' );
		$thumb = is_string( $thumb ) ? $thumb : '';
	}

	return array(
		'id'            => (string) (int) $post->ID,
		'title'         => get_the_title( $post ),
		'excerpt'       => (string) $post->post_excerpt,
		'category'      => $cat,
		'attachment_id' => $file_id > 0 ? $file_id : 0,
		'thumb_url'     => $thumb,
	);
}

function portal_theme_ideology_add_meta_box() {
	add_meta_box(
		'portal_idl_details',
		__( 'Тип и файл материала', 'portal-theme' ),
		'portal_theme_ideology_meta_box_render',
		'portal_ideology',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_ideology_add_meta_box' );

function portal_theme_ideology_meta_box_render( $post ) {
	wp_nonce_field( 'portal_idl_save_meta', 'portal_idl_meta_nonce' );
	$type     = get_post_meta( $post->ID, '_portal_ideology_category', true );
	$type     = is_string( $type ) && $type !== '' ? sanitize_key( $type ) : 'symbolika';
	$labels   = portal_theme_ideology_category_labels();
	$allowed  = portal_theme_ideology_category_slugs();
	$file_id  = (int) get_post_meta( $post->ID, '_portal_ideology_file', true );
	$file_txt = '—';
	if ( $file_id > 0 ) {
		$p = get_post( $file_id );
		if ( $p && 'attachment' === $p->post_type ) {
			$path = get_attached_file( $file_id );
			$file_txt = is_string( $path ) && $path !== '' ? basename( $path ) : $p->post_title;
		}
	}
	?>
	<p><strong><?php esc_html_e( 'Тип материала', 'portal-theme' ); ?></strong></p>
	<select name="portal_ideology_category" id="portal-idl-type" style="max-width:100%;">
		<?php foreach ( $allowed as $slug ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $type, $slug ); ?>>
				<?php echo isset( $labels[ $slug ] ) ? esc_html( $labels[ $slug ] ) : esc_html( $slug ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<p style="margin-top:16px;"><strong><?php esc_html_e( 'Файл (PDF, Word, презентация, изображение, видео)', 'portal-theme' ); ?></strong></p>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Необязательно. Откроется в окне просмотра и будет доступно скачивание.', 'portal-theme' ); ?>
	</p>
	<p>
		<input type="hidden" name="portal_ideology_file" id="portal-idl-file" value="<?php echo esc_attr( (string) $file_id ); ?>">
		<button type="button" class="button" id="portal-idl-pick-file"><?php esc_html_e( 'Выбрать из медиатеки', 'portal-theme' ); ?></button>
		<button type="button" class="button" id="portal-idl-clear-file"><?php esc_html_e( 'Сбросить', 'portal-theme' ); ?></button>
	</p>
	<p id="portal-idl-file-name"><?php echo esc_html( $file_txt ); ?></p>

	<p style="margin-top:16px;" class="description">
		<?php esc_html_e( 'Краткое описание для карточки и режима чтения — поле «Отрывок» справа. Картинка слева на карточке — «Изображение записи» (необязательно; иначе подставится стандартная иконка темы).', 'portal-theme' ); ?>
	</p>
	<?php
}

function portal_theme_ideology_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_idl_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_idl_meta_nonce'] ) ), 'portal_idl_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_ideology' ) {
		return;
	}

	$allowed = portal_theme_ideology_category_slugs();
	$cat     = isset( $_POST['portal_ideology_category'] ) ? sanitize_key( wp_unslash( $_POST['portal_ideology_category'] ) ) : 'symbolika';
	if ( ! in_array( $cat, $allowed, true ) ) {
		$cat = 'symbolika';
	}
	update_post_meta( $post_id, '_portal_ideology_category', $cat );

	$file = isset( $_POST['portal_ideology_file'] ) ? absint( $_POST['portal_ideology_file'] ) : 0;
	if ( $file > 0 ) {
		$p = get_post( $file );
		if ( $p && 'attachment' === $p->post_type ) {
			update_post_meta( $post_id, '_portal_ideology_file', $file );
		} else {
			delete_post_meta( $post_id, '_portal_ideology_file' );
		}
	} else {
		delete_post_meta( $post_id, '_portal_ideology_file' );
	}
}
add_action( 'save_post_portal_ideology', 'portal_theme_ideology_save_meta' );

function portal_theme_ideology_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'portal_ideology' ) {
		return;
	}
	wp_enqueue_media();
	$path = get_template_directory() . '/assets/js/ideology-admin.js';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'portal-ideology-admin',
		get_template_directory_uri() . '/assets/js/ideology-admin.js',
		array( 'jquery' ),
		(string) filemtime( $path ),
		true
	);
	wp_localize_script(
		'portal-ideology-admin',
		'portalIdlAdmin',
		array(
			'pickTitle' => __( 'Выберите файл', 'portal-theme' ),
			'pickBtn'   => __( 'Использовать файл', 'portal-theme' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'portal_theme_ideology_admin_assets' );

function portal_theme_ideology_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portal_idl_thumb'] = __( 'Обложка', 'portal-theme' );
			$new['portal_idl_cat']   = __( 'Тип', 'portal-theme' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_ideology_posts_columns', 'portal_theme_ideology_posts_columns' );

function portal_theme_ideology_posts_custom_column( $column, $post_id ) {
	$post_id = (int) $post_id;
	if ( 'portal_idl_thumb' === $column ) {
		$tid = (int) get_post_thumbnail_id( $post_id );
		if ( $tid > 0 ) {
			echo wp_get_attachment_image( $tid, array( 60, 60 ), false, array( 'style' => 'max-width:60px;height:auto;' ) );
		} else {
			echo '<span class="dashicons dashicons-format-image" style="color:#c3c4c7;" aria-hidden="true"></span> ';
			esc_html_e( 'Нет', 'portal-theme' );
		}
		return;
	}
	if ( 'portal_idl_cat' === $column ) {
		$cat = get_post_meta( $post_id, '_portal_ideology_category', true );
		$cat = is_string( $cat ) ? sanitize_key( $cat ) : '';
		$labels = portal_theme_ideology_category_labels();
		echo isset( $labels[ $cat ] ) ? esc_html( $labels[ $cat ] ) : esc_html( $cat );
	}
}
add_action( 'manage_portal_ideology_posts_custom_column', 'portal_theme_ideology_posts_custom_column', 10, 2 );

function portal_theme_ideology_migrate_legacy_option() {
	if ( get_option( 'portal_theme_ideology_legacy_migrated', '' ) === 'yes' ) {
		return;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'portal_ideology',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $existing ) ) {
		update_option( 'portal_theme_ideology_legacy_migrated', 'yes', false );
		return;
	}
	$list = get_option( 'portal_theme_ideology_materials', array() );
	if ( empty( $list ) || ! is_array( $list ) ) {
		update_option( 'portal_theme_ideology_legacy_migrated', 'yes', false );
		return;
	}
	$imported = 0;
	foreach ( $list as $row ) {
		if ( ! is_array( $row ) || empty( $row['title'] ) ) {
			continue;
		}
		$cat = isset( $row['category'] ) ? sanitize_key( $row['category'] ) : 'akty';
		if ( ! in_array( $cat, portal_theme_ideology_category_slugs(), true ) ) {
			$cat = 'akty';
		}
		$new_id = wp_insert_post(
			array(
				'post_type'    => 'portal_ideology',
				'post_status'  => 'publish',
				'post_title'   => sanitize_text_field( (string) $row['title'] ),
				'post_excerpt' => isset( $row['excerpt'] ) ? sanitize_textarea_field( (string) $row['excerpt'] ) : '',
			),
			true
		);
		if ( is_wp_error( $new_id ) || ! $new_id ) {
			continue;
		}
		++$imported;
		update_post_meta( (int) $new_id, '_portal_ideology_category', $cat );
		$aid = isset( $row['attachment_id'] ) ? (int) $row['attachment_id'] : 0;
		if ( $aid > 0 && get_post( $aid ) && 'attachment' === get_post_type( $aid ) ) {
			update_post_meta( (int) $new_id, '_portal_ideology_file', $aid );
		}
	}
	if ( $imported > 0 ) {
		delete_option( 'portal_theme_ideology_materials' );
	}
	update_option( 'portal_theme_ideology_legacy_migrated', 'yes', false );
}
add_action( 'init', 'portal_theme_ideology_migrate_legacy_option', 30 );

function portal_theme_ideology_register_link_post_type() {
	register_post_type(
		'portal_idl_link',
		array(
			'labels'             => array(
				'name'               => __( 'Полезные ссылки', 'portal-theme' ),
				'singular_name'      => __( 'Ссылка', 'portal-theme' ),
				'add_new'            => __( 'Добавить ссылку', 'portal-theme' ),
				'add_new_item'       => __( 'Новая ссылка', 'portal-theme' ),
				'edit_item'          => __( 'Редактировать ссылку', 'portal-theme' ),
				'new_item'           => __( 'Новая ссылка', 'portal-theme' ),
				'search_items'       => __( 'Поиск ссылок', 'portal-theme' ),
				'not_found'          => __( 'Ссылок не найдено', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
				'all_items'          => __( 'Полезные ссылки', 'portal-theme' ),
				'menu_name'          => __( 'Полезные ссылки', 'portal-theme' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=portal_ideology',
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => false,
		)
	);
}
add_action( 'init', 'portal_theme_ideology_register_link_post_type' );

function portal_theme_ideology_link_enter_title( $title, $post ) {
	if ( $post instanceof WP_Post && 'portal_idl_link' === $post->post_type ) {
		return __( 'Название ссылки', 'portal-theme' );
	}
	return $title;
}
add_filter( 'enter_title_here', 'portal_theme_ideology_link_enter_title', 10, 2 );

function portal_theme_ideology_link_add_meta_boxes() {
	add_meta_box(
		'portal_idl_link_details',
		__( 'Адрес ссылки', 'portal-theme' ),
		'portal_theme_ideology_link_metabox_render',
		'portal_idl_link',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_ideology_link_add_meta_boxes' );

function portal_theme_ideology_link_metabox_render( WP_Post $post ) {
	wp_nonce_field( 'portal_idl_link_save', 'portal_idl_link_nonce' );
	$url = (string) get_post_meta( $post->ID, '_portal_idl_link_url', true );
	?>
	<p>
		<label for="portal_idl_link_url"><strong><?php esc_html_e( 'URL', 'portal-theme' ); ?></strong></label><br>
		<input type="url" class="large-text code" name="portal_idl_link_url" id="portal_idl_link_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
	</p>
	<p class="description">
		<?php esc_html_e( 'Заголовок записи — текст в блоке «Полезные ссылки». Иконка — «Изображение записи» справа. Порядок на странице — поле «Порядок».', 'portal-theme' ); ?>
	</p>
	<?php
}

function portal_theme_ideology_link_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_idl_link_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_idl_link_nonce'] ) ), 'portal_idl_link_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_idl_link' ) {
		return;
	}

	$url = isset( $_POST['portal_idl_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['portal_idl_link_url'] ) ) : '';
	if ( $url !== '' ) {
		update_post_meta( $post_id, '_portal_idl_link_url', $url );
	} else {
		delete_post_meta( $post_id, '_portal_idl_link_url' );
	}
}
add_action( 'save_post_portal_idl_link', 'portal_theme_ideology_link_save_meta' );

function portal_theme_ideology_link_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new[ $key ] = __( 'Название', 'portal-theme' );
			$new['portal_idl_link_url'] = __( 'URL', 'portal-theme' );
			continue;
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_idl_link_posts_columns', 'portal_theme_ideology_link_columns' );

function portal_theme_ideology_link_custom_column( $column, $post_id ) {
	if ( 'portal_idl_link_url' !== $column ) {
		return;
	}
	$url = trim( (string) get_post_meta( (int) $post_id, '_portal_idl_link_url', true ) );
	echo $url !== '' ? esc_html( $url ) : '—';
}
add_action( 'manage_portal_idl_link_posts_custom_column', 'portal_theme_ideology_link_custom_column', 10, 2 );

function portal_theme_ideology_link_icon_url( $post_id, $link_url ) {
	$post_id = (int) $post_id;
	if ( $post_id > 0 && has_post_thumbnail( $post_id ) ) {
		$thumb = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
		if ( is_string( $thumb ) && $thumb !== '' ) {
			return $thumb;
		}
	}

	$theme_icon = (string) get_post_meta( $post_id, '_portal_idl_theme_icon', true );
	$theme_icon = $theme_icon !== '' ? basename( $theme_icon ) : '';
	if ( $theme_icon !== '' ) {
		$file = get_template_directory() . '/assets/img/' . $theme_icon;
		if ( is_readable( $file ) ) {
			return get_template_directory_uri() . '/assets/img/' . $theme_icon;
		}
	}

	$host = wp_parse_url( $link_url, PHP_URL_HOST );
	if ( is_string( $host ) && $host !== '' ) {
		return 'https://www.google.com/s2/favicons?domain=' . rawurlencode( $host ) . '&sz=64';
	}

	return '';
}

function portal_theme_ideology_link_posts() {
	$posts = get_posts(
		array(
			'post_type'   => 'portal_idl_link',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'menu_order title',
			'order'       => 'ASC',
		)
	);
	return is_array( $posts ) ? $posts : array();
}

function portal_theme_ideology_render_useful_links() {
	$posts = portal_theme_ideology_link_posts();

	if ( empty( $posts ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			echo '<p class="ideology-widget__placeholder">' . esc_html__( 'Добавьте ссылки в меню «Основы идеолога» → «Полезные ссылки».', 'portal-theme' ) . '</p>';
		}
		return;
	}

	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$pid   = (int) $post->ID;
		$url   = trim( (string) get_post_meta( $pid, '_portal_idl_link_url', true ) );
		$title = get_the_title( $pid );
		if ( $url === '' || $title === '' ) {
			continue;
		}
		$icon = portal_theme_ideology_link_icon_url( $pid, $url );
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="ideology-link-item" target="_blank" rel="noopener noreferrer">
			<span class="ideology-link-item__left">
				<?php if ( $icon !== '' ) : ?>
					<img src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $title ); ?>">
				<?php endif; ?>
				<span><?php echo esc_html( $title ); ?></span>
			</span>
			<span class="ideology-link-item__arrow">&gt;</span>
		</a>
		<?php
	}
}

function portal_theme_ideology_seed_useful_links() {
	if ( get_option( 'portal_theme_idl_links_seeded', '' ) === 'yes' ) {
		return;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'portal_idl_link',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $existing ) ) {
		update_option( 'portal_theme_idl_links_seeded', 'yes', false );
		return;
	}

	$defaults = array(
		array(
			'title' => 'Право.by',
			'url'   => 'https://pravo.by/',
			'icon'  => 'pravo_by.png',
			'order' => 1,
		),
		array(
			'title' => 'Пул Первого',
			'url'   => 'https://t.me/pul_1',
			'icon'  => 'pul.png',
			'order' => 2,
		),
		array(
			'title' => 'Belta.by',
			'url'   => 'https://belta.by/',
			'icon'  => 'belta.png',
			'order' => 3,
		),
	);

	foreach ( $defaults as $row ) {
		$new_id = wp_insert_post(
			array(
				'post_type'   => 'portal_idl_link',
				'post_status' => 'publish',
				'post_title'  => $row['title'],
				'menu_order'  => (int) $row['order'],
			),
			true
		);
		if ( is_wp_error( $new_id ) || ! $new_id ) {
			continue;
		}
		update_post_meta( (int) $new_id, '_portal_idl_link_url', esc_url_raw( $row['url'] ) );
		update_post_meta( (int) $new_id, '_portal_idl_theme_icon', sanitize_file_name( $row['icon'] ) );
	}

	update_option( 'portal_theme_idl_links_seeded', 'yes', false );
}
add_action( 'init', 'portal_theme_ideology_seed_useful_links', 40 );
