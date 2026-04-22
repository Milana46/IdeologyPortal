<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_theme_mediabank_type_slugs() {
	return array( 'photo', 'video', 'infographic', 'logo' );
}

function portal_theme_mediabank_type_labels() {
	return array(
		'photo'        => __( 'Фото', 'portal-theme' ),
		'video'        => __( 'Видео', 'portal-theme' ),
		'infographic'  => __( 'Инфографика', 'portal-theme' ),
		'logo'         => __( 'Логотип', 'portal-theme' ),
	);
}

function portal_theme_mediabank_register_post_type() {
	register_post_type(
		'portal_mediabank',
		array(
			'labels'              => array(
				'name'               => __( 'Медиабанк', 'portal-theme' ),
				'singular_name'      => __( 'Материал медиабанка', 'portal-theme' ),
				'add_new'            => __( 'Добавить материал', 'portal-theme' ),
				'add_new_item'       => __( 'Новый материал', 'portal-theme' ),
				'edit_item'          => __( 'Редактировать материал', 'portal-theme' ),
				'new_item'           => __( 'Новый материал', 'portal-theme' ),
				'view_item'          => __( 'Просмотр', 'portal-theme' ),
				'search_items'       => __( 'Поиск материалов', 'portal-theme' ),
				'not_found'          => __( 'Материалов не найдено', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
				'all_items'          => __( 'Все материалы', 'portal-theme' ),
				'menu_name'          => __( 'Медиабанк', 'portal-theme' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-format-gallery',
			'menu_position'       => 26,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'thumbnail' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
		)
	);
}
add_action( 'init', 'portal_theme_mediabank_register_post_type' );

function portal_theme_mediabank_resolve_thumbnail_id( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return 0;
	}
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id > 0 ) {
		return $thumb_id;
	}
	foreach ( portal_theme_mediabank_get_gallery_ids( $post_id ) as $gid ) {
		if ( wp_attachment_is_image( $gid ) ) {
			return (int) $gid;
		}
	}
	$file_id = (int) get_post_meta( $post_id, '_portal_mb_file', true );
	if ( $file_id > 0 && wp_attachment_is_image( $file_id ) ) {
		return $file_id;
	}
	return 0;
}

function portal_theme_mediabank_get_gallery_ids( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return array();
	}
	$raw = get_post_meta( $post_id, '_portal_mb_gallery', true );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return array();
	}
	$parts = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	$out   = array();
	foreach ( $parts as $id ) {
		if ( $id <= 0 ) {
			continue;
		}
		$p = get_post( $id );
		if ( $p && 'attachment' === $p->post_type ) {
			$out[] = $id;
		}
	}
	return array_values( array_unique( $out ) );
}

function portal_theme_mediabank_get_slide_items( $post_id ) {
	$items = array();
	foreach ( portal_theme_mediabank_get_gallery_ids( $post_id ) as $id ) {
		$mime = get_post_mime_type( $id );
		$mime = is_string( $mime ) ? $mime : '';
		$url  = wp_get_attachment_url( $id );
		if ( ! $url ) {
			continue;
		}
		$is_video = ( strpos( $mime, 'video/' ) === 0 );
		$is_image = wp_attachment_is_image( $id );
		if ( ! $is_video && ! $is_image ) {
			continue;
		}
		$src = $is_image ? (string) wp_get_attachment_image_url( $id, 'large' ) : $url;
		if ( $is_image && $src === '' ) {
			$src = $url;
		}
		$items[] = array(
			'id'       => (int) $id,
			'is_video' => $is_video,
			'src'      => $src,
			'full'     => $url,
			'mime'     => $mime,
		);
	}
	return $items;
}

function portal_theme_mediabank_sanitize_gallery_ids( array $ids ) {
	$out = array();
	foreach ( $ids as $id ) {
		$id = absint( $id );
		if ( $id <= 0 ) {
			continue;
		}
		$p = get_post( $id );
		if ( ! $p || 'attachment' !== $p->post_type ) {
			continue;
		}
		$m = get_post_mime_type( $id );
		if ( ! is_string( $m ) ) {
			continue;
		}
		if ( strpos( $m, 'image/' ) === 0 || strpos( $m, 'video/' ) === 0 ) {
			$out[] = $id;
		}
	}
	return array_values( array_unique( $out ) );
}

function portal_theme_mediabank_card_link_url( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return '';
	}
	$slides = portal_theme_mediabank_get_slide_items( $post_id );
	if ( ! empty( $slides ) ) {
		return $slides[0]['full'];
	}
	$file_id = (int) get_post_meta( $post_id, '_portal_mb_file', true );
	if ( $file_id > 0 ) {
		$f = get_post( $file_id );
		if ( $f && 'attachment' === $f->post_type ) {
			$url = wp_get_attachment_url( $file_id );
			if ( $url ) {
				return $url;
			}
		}
	}
	$thumb_res = portal_theme_mediabank_resolve_thumbnail_id( $post_id );
	if ( $thumb_res > 0 ) {
		$full = wp_get_attachment_image_url( $thumb_res, 'full' );
		if ( $full ) {
			return $full;
		}
	}
	return '';
}

function portal_theme_mediabank_add_meta_box() {
	add_meta_box(
		'portal_mb_details',
		__( 'Тип и файл материала', 'portal-theme' ),
		'portal_theme_mediabank_meta_box_render',
		'portal_mediabank',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_mediabank_add_meta_box' );

function portal_theme_mediabank_meta_box_render( $post ) {
	wp_nonce_field( 'portal_mb_save_meta', 'portal_mb_meta_nonce' );
	$type     = get_post_meta( $post->ID, '_portal_mb_type', true );
	$type     = is_string( $type ) && $type !== '' ? $type : 'photo';
	$labels   = portal_theme_mediabank_type_labels();
	$allowed  = portal_theme_mediabank_type_slugs();
	$file_id  = (int) get_post_meta( $post->ID, '_portal_mb_file', true );
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
	<select name="portal_mb_type" id="portal-mb-type" style="max-width:100%;">
		<?php foreach ( $allowed as $slug ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $type, $slug ); ?>>
				<?php echo isset( $labels[ $slug ] ) ? esc_html( $labels[ $slug ] ) : esc_html( $slug ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<p style="margin-top:16px;"><strong><?php esc_html_e( 'Файл (видео, PDF, документ)', 'portal-theme' ); ?></strong></p>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Необязательно. Для фото и логотипов обычно достаточно обложки ниже. Для видео или документа выберите файл — по клику на карточке откроется он.', 'portal-theme' ); ?>
	</p>
	<p>
		<input type="hidden" name="portal_mb_file" id="portal-mb-file" value="<?php echo esc_attr( (string) $file_id ); ?>">
		<button type="button" class="button" id="portal-mb-pick-file"><?php esc_html_e( 'Выбрать из медиатеки', 'portal-theme' ); ?></button>
		<button type="button" class="button" id="portal-mb-clear-file"><?php esc_html_e( 'Сбросить', 'portal-theme' ); ?></button>
	</p>
	<p id="portal-mb-file-name"><?php echo esc_html( $file_txt ); ?></p>

	<p style="margin-top:16px;" class="description">
		<?php esc_html_e( 'Обложка на сайте: лучше задать «Изображение записи» справа. Если обложки нет, но в поле выше выбран файл-картинка, он будет использован как превью.', 'portal-theme' ); ?>
	</p>

	<hr style="margin:20px 0;border:none;border-top:1px solid #c3c4c7;">

	<p><strong><?php esc_html_e( 'Галерея публикации (карусель)', 'portal-theme' ); ?></strong></p>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Добавьте несколько фото и/или видео — на сайте они отобразятся одной карточкой с пролистыванием, как в Instagram. Порядок слайдов можно менять перетаскиванием.', 'portal-theme' ); ?>
	</p>
	<input type="hidden" name="portal_mb_gallery" id="portal-mb-gallery-ids" value="<?php echo esc_attr( implode( ',', portal_theme_mediabank_get_gallery_ids( $post->ID ) ) ); ?>">
	<ul id="portal-mb-gallery-list" class="portal-mb-gallery-list">
		<?php
		foreach ( portal_theme_mediabank_get_gallery_ids( $post->ID ) as $gid ) :
			$gmime = get_post_mime_type( $gid );
			$gmime = is_string( $gmime ) ? $gmime : '';
			$is_vid = ( strpos( $gmime, 'video/' ) === 0 );
			$glabel = $is_vid ? __( 'Видео', 'portal-theme' ) : __( 'Фото', 'portal-theme' );
			?>
			<li class="portal-mb-gallery-item" data-id="<?php echo esc_attr( (string) $gid ); ?>">
				<span class="portal-mb-gallery-item__handle" aria-hidden="true" title="<?php esc_attr_e( 'Перетащить', 'portal-theme' ); ?>">⋮⋮</span>
				<span class="portal-mb-gallery-item__preview">
					<?php
					if ( wp_attachment_is_image( $gid ) ) {
						echo wp_get_attachment_image( $gid, array( 48, 48 ), false, array( 'style' => 'width:48px;height:48px;object-fit:cover;' ) );
					} else {
						echo '<span class="portal-mb-gallery-item__vid-icon dashicons dashicons-video-alt3" aria-hidden="true"></span>';
					}
					?>
				</span>
				<span class="portal-mb-gallery-item__meta">
					<span class="portal-mb-gallery-item__type"><?php echo esc_html( $glabel ); ?></span>
					<span class="portal-mb-gallery-item__name"><?php
					$apath = get_attached_file( $gid );
					$gname = is_string( $apath ) && $apath !== '' ? wp_basename( $apath ) : get_the_title( $gid );
					echo esc_html( $gname );
					?></span>
				</span>
				<button type="button" class="button-link portal-mb-gallery-remove" aria-label="<?php esc_attr_e( 'Убрать из галереи', 'portal-theme' ); ?>"><?php esc_html_e( 'Удалить', 'portal-theme' ); ?></button>
			</li>
			<?php
		endforeach;
		?>
	</ul>
	<p>
		<button type="button" class="button" id="portal-mb-add-gallery"><?php esc_html_e( 'Добавить в галерею из медиатеки', 'portal-theme' ); ?></button>
	</p>
	<?php
}

function portal_theme_mediabank_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_mb_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_mb_meta_nonce'] ) ), 'portal_mb_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_mediabank' ) {
		return;
	}

	$allowed = portal_theme_mediabank_type_slugs();
	$type    = isset( $_POST['portal_mb_type'] ) ? sanitize_key( wp_unslash( $_POST['portal_mb_type'] ) ) : 'photo';
	if ( ! in_array( $type, $allowed, true ) ) {
		$type = 'photo';
	}
	update_post_meta( $post_id, '_portal_mb_type', $type );

	$file = isset( $_POST['portal_mb_file'] ) ? absint( $_POST['portal_mb_file'] ) : 0;
	if ( $file > 0 ) {
		$p = get_post( $file );
		if ( $p && 'attachment' === $p->post_type ) {
			update_post_meta( $post_id, '_portal_mb_file', $file );
		} else {
			delete_post_meta( $post_id, '_portal_mb_file' );
		}
	} else {
		delete_post_meta( $post_id, '_portal_mb_file' );
	}

	$gal_raw = isset( $_POST['portal_mb_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_mb_gallery'] ) ) : '';
	$gal_ids = array();
	if ( $gal_raw !== '' ) {
		$gal_ids = array_map( 'absint', explode( ',', $gal_raw ) );
	}
	$gal_ids = portal_theme_mediabank_sanitize_gallery_ids( $gal_ids );
	if ( ! empty( $gal_ids ) ) {
		update_post_meta( $post_id, '_portal_mb_gallery', implode( ',', $gal_ids ) );
	} else {
		delete_post_meta( $post_id, '_portal_mb_gallery' );
	}
}
add_action( 'save_post_portal_mediabank', 'portal_theme_mediabank_save_meta' );

function portal_theme_mediabank_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'portal_mediabank' ) {
		return;
	}
	wp_enqueue_media();
	$path = get_template_directory() . '/assets/js/mediabank-admin.js';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script( 'jquery-ui-sortable' );
	$mb_admin_css = get_template_directory() . '/assets/css/mediabank-admin.css';
	if ( file_exists( $mb_admin_css ) ) {
		wp_enqueue_style(
			'portal-mediabank-admin',
			get_template_directory_uri() . '/assets/css/mediabank-admin.css',
			array(),
			(string) filemtime( $mb_admin_css )
		);
	}
	wp_enqueue_script(
		'portal-mediabank-admin',
		get_template_directory_uri() . '/assets/js/mediabank-admin.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		(string) filemtime( $path ),
		true
	);
	wp_localize_script(
		'portal-mediabank-admin',
		'portalMbAdmin',
		array(
			'pickTitle'   => __( 'Выберите файл', 'portal-theme' ),
			'pickBtn'     => __( 'Использовать файл', 'portal-theme' ),
			'galTitle'    => __( 'Выберите изображения и видео', 'portal-theme' ),
			'galBtn'      => __( 'Добавить в галерею', 'portal-theme' ),
			'photoLabel'  => __( 'Фото', 'portal-theme' ),
			'videoLabel'  => __( 'Видео', 'portal-theme' ),
			'removeBtn'   => __( 'Удалить', 'portal-theme' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'portal_theme_mediabank_admin_assets' );

function portal_theme_mediabank_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portal_mb_thumb'] = __( 'Обложка', 'portal-theme' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_mediabank_posts_columns', 'portal_theme_mediabank_posts_columns' );

function portal_theme_mediabank_posts_custom_column( $column, $post_id ) {
	if ( 'portal_mb_thumb' !== $column ) {
		return;
	}
	$post_id = (int) $post_id;
	$tid     = portal_theme_mediabank_resolve_thumbnail_id( $post_id );
	$gcount  = count( portal_theme_mediabank_get_gallery_ids( $post_id ) );
	if ( $tid > 0 ) {
		echo '<span class="portal-mb-thumb-wrap">';
		echo wp_get_attachment_image( $tid, array( 60, 60 ), false, array( 'style' => 'max-width:60px;height:auto;' ) );
		if ( $gcount > 1 ) {
			echo '<span class="portal-mb-gallery-badge">' . esc_html( (string) $gcount ) . '</span>';
		}
		echo '</span>';
	} else {
		echo '<span class="dashicons dashicons-format-image" style="color:#c3c4c7;" aria-hidden="true"></span> ';
		esc_html_e( 'Нет изображения', 'portal-theme' );
	}
}
add_action( 'manage_portal_mediabank_posts_custom_column', 'portal_theme_mediabank_posts_custom_column', 10, 2 );
