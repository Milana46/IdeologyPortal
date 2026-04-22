<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_theme_bp_category_slugs() {
	return array( 'social', 'smi', 'events', 'templates' );
}

function portal_theme_bp_category_labels() {
	return array(
		'social'    => __( 'Работа в соцсетях', 'portal-theme' ),
		'smi'       => __( 'СМИ', 'portal-theme' ),
		'events'    => __( 'Мероприятия', 'portal-theme' ),
		'templates' => __( 'Шаблоны', 'portal-theme' ),
	);
}

function portal_theme_bp_register_post_type() {
	register_post_type(
		'portal_bp_material',
		array(
			'labels'              => array(
				'name'               => __( 'Библиотека практик', 'portal-theme' ),
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
				'menu_name'          => __( 'Библиотека практик', 'portal-theme' ),
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
			'supports'            => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
		)
	);
}
add_action( 'init', 'portal_theme_bp_register_post_type' );

function portal_theme_bp_add_meta_boxes() {
	add_meta_box(
		'portal_bp_file',
		__( 'Файл материала', 'portal-theme' ),
		'portal_theme_bp_metabox_file_render',
		'portal_bp_material',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_bp_add_meta_boxes' );

function portal_theme_bp_metabox_file_render( $post ) {
	wp_nonce_field( 'portal_bp_save_meta', 'portal_bp_meta_nonce' );
	$file_id = (int) get_post_meta( $post->ID, '_portal_bp_file_id', true );
	$cat     = get_post_meta( $post->ID, '_portal_bp_category', true );
	$cat     = is_string( $cat ) ? sanitize_key( $cat ) : 'templates';
	if ( ! in_array( $cat, portal_theme_bp_category_slugs(), true ) ) {
		$cat = 'templates';
	}
	$popular = get_post_meta( $post->ID, '_portal_bp_in_popular', true ) === '1';

	$file_name = '';
	if ( $file_id > 0 ) {
		$p = get_post( $file_id );
		if ( $p && 'attachment' === $p->post_type ) {
			$file_name = $p->post_title ? $p->post_title : basename( (string) get_attached_file( $file_id ) );
		}
	}
	$labels = portal_theme_bp_category_labels();
	?>
	<p>
		<label for="portal-bp-file-id"><strong><?php esc_html_e( 'Вложение (файл для открытия и скачивания)', 'portal-theme' ); ?></strong></label><br>
		<input type="hidden" name="portal_bp_file_id" id="portal-bp-file-id" value="<?php echo esc_attr( (string) $file_id ); ?>">
		<button type="button" class="button" id="portal-bp-pick-file"><?php esc_html_e( 'Выбрать файл', 'portal-theme' ); ?></button>
		<button type="button" class="button" id="portal-bp-clear-file" style="margin-left:6px;"><?php esc_html_e( 'Сбросить', 'portal-theme' ); ?></button>
	</p>
	<p class="description" id="portal-bp-file-label"><?php echo $file_id ? esc_html( $file_name ) : esc_html__( 'Файл не выбран.', 'portal-theme' ); ?></p>
	<p style="margin-top:16px;">
		<label for="portal-bp-category"><strong><?php esc_html_e( 'Тип материала (цвет на сайте)', 'portal-theme' ); ?></strong></label><br>
		<select name="portal_bp_category" id="portal-bp-category" style="max-width:360px;">
			<?php foreach ( portal_theme_bp_category_slugs() as $slug ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $cat, $slug ); ?>>
					<?php echo isset( $labels[ $slug ] ) ? esc_html( $labels[ $slug ] ) : esc_html( $slug ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label>
			<input type="checkbox" name="portal_bp_in_popular" value="1" <?php checked( $popular ); ?>>
			<?php esc_html_e( 'Показывать в боковом блоке «Популярные материалы»', 'portal-theme' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Заголовок — название карточки. Поле «Цитата» (отрывок) — краткое описание на сайте. Миниатюра записи — картинка на карточке (необязательно).', 'portal-theme' ); ?>
	</p>
	<?php
}

function portal_theme_bp_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'portal_bp_material' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	$js_path = get_template_directory() . '/assets/js/bp-admin.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'portal-bp-admin',
			get_template_directory_uri() . '/assets/js/bp-admin.js',
			array( 'jquery' ),
			(string) filemtime( $js_path ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'portal_theme_bp_admin_scripts' );

function portal_theme_bp_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_bp_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_bp_meta_nonce'] ) ), 'portal_bp_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_bp_material' ) {
		return;
	}

	$fid = isset( $_POST['portal_bp_file_id'] ) ? absint( $_POST['portal_bp_file_id'] ) : 0;
	if ( $fid > 0 && get_post_type( $fid ) === 'attachment' ) {
		update_post_meta( $post_id, '_portal_bp_file_id', $fid );
	} else {
		delete_post_meta( $post_id, '_portal_bp_file_id' );
	}

	$cat = isset( $_POST['portal_bp_category'] ) ? sanitize_key( wp_unslash( $_POST['portal_bp_category'] ) ) : 'templates';
	if ( ! in_array( $cat, portal_theme_bp_category_slugs(), true ) ) {
		$cat = 'templates';
	}
	update_post_meta( $post_id, '_portal_bp_category', $cat );

	$pop = ! empty( $_POST['portal_bp_in_popular'] ) ? '1' : '0';
	update_post_meta( $post_id, '_portal_bp_in_popular', $pop );
}
add_action( 'save_post_portal_bp_material', 'portal_theme_bp_save_post' );

function portal_theme_bp_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portal_bp_type'] = __( 'Тип', 'portal-theme' );
			$new['portal_bp_file'] = __( 'Файл', 'portal-theme' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_bp_material_posts_columns', 'portal_theme_bp_posts_columns' );

function portal_theme_bp_posts_custom_column( $column, $post_id ) {
	$post_id = (int) $post_id;
	if ( 'portal_bp_type' === $column ) {
		$c = get_post_meta( $post_id, '_portal_bp_category', true );
		$c = is_string( $c ) ? sanitize_key( $c ) : '';
		$labels = portal_theme_bp_category_labels();
		echo isset( $labels[ $c ] ) ? esc_html( $labels[ $c ] ) : esc_html( $c );
		return;
	}
	if ( 'portal_bp_file' === $column ) {
		$fid = (int) get_post_meta( $post_id, '_portal_bp_file_id', true );
		if ( $fid <= 0 ) {
			echo '—';
			return;
		}
		$url = wp_get_attachment_url( $fid );
		if ( $url ) {
			echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Скачать', 'portal-theme' ) . '</a>';
		} else {
			echo '—';
		}
	}
}
add_action( 'manage_portal_bp_material_posts_custom_column', 'portal_theme_bp_posts_custom_column', 10, 2 );

function portal_theme_bp_resolve_doc( $filename, $is_local ) {
	$out = array(
		'doc'          => '',
		'pdf'          => '',
		'office_embed' => '',
		'has_preview'  => false,
	);
	if ( ! is_string( $filename ) || $filename === '' ) {
		return $out;
	}
	$doc_path = get_template_directory() . '/assets/documents/' . $filename;
	if ( file_exists( $doc_path ) ) {
		$out['doc'] = get_template_directory_uri() . '/assets/documents/' . rawurlencode( $filename );
	}
	$pdf_name = preg_replace( '/\.docx?$/iu', '.pdf', $filename );
	$pdf_path = get_template_directory() . '/assets/documents/' . $pdf_name;
	if ( file_exists( $pdf_path ) ) {
		$out['pdf'] = get_template_directory_uri() . '/assets/documents/' . rawurlencode( $pdf_name );
	}
	if ( ! $out['pdf'] && $out['doc'] && ! $is_local ) {
		$out['office_embed'] = 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode( $out['doc'] );
	}
	$out['has_preview'] = (bool) ( $out['pdf'] || $out['office_embed'] );
	return $out;
}
