<?php
/**
 * «Основы идеолога»: тип записей и метаполя (wp-admin).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Категории материала (вкладки на сайте).
 *
 * @return string[]
 */
function portal_theme_ideology_category_slugs() {
	return array( 'symbolika', 'akty', 'pasport' );
}

/**
 * Подписи категорий.
 *
 * @return array<string,string>
 */
function portal_theme_ideology_category_labels() {
	return array(
		'symbolika' => __( 'Государственная символика', 'portal-theme' ),
		'akty'      => __( 'Акты', 'portal-theme' ),
		'pasport'   => __( 'Социальный паспорт предприятия', 'portal-theme' ),
	);
}

/**
 * Регистрация типа записей.
 */
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

/**
 * Массив полей для карточки и модального окна из записи WP.
 *
 * @param int|WP_Post $post ID или объект записи.
 * @return array<string,mixed>|null
 */
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

/**
 * Метабокс: категория и файл.
 */
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

/**
 * @param WP_Post $post Post.
 */
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

/**
 * Сохранение метаполей.
 *
 * @param int $post_id ID записи.
 */
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

/**
 * Админские скрипты выбора файла.
 *
 * @param string $hook_suffix Hook.
 */
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

/**
 * Колонки в списке материалов.
 *
 * @param string[] $columns Колонки.
 * @return string[]
 */
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

/**
 * Вывод доп. колонок.
 *
 * @param string $column Имя колонки.
 * @param int    $post_id ID записи.
 */
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

/**
 * Однократный импорт из старой опции portal_theme_ideology_materials.
 */
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
		// Уже есть материалы в новом формате — не импортируем старую опцию повторно.
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
