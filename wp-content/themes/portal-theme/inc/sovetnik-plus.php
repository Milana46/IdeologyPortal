<?php
/**
 * Советник+: тип записей и метаполя (wp-admin).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Категории (вкладки на странице).
 *
 * @return string[]
 */
function portal_theme_sovetnik_category_slugs() {
	return array( 'social', 'media', 'events', 'templates' );
}

/**
 * Подписи категорий.
 *
 * @return array<string,string>
 */
function portal_theme_sovetnik_category_labels() {
	return array(
		'social'    => __( 'Работа в соцсетях', 'portal-theme' ),
		'media'     => __( 'СМИ', 'portal-theme' ),
		'events'    => __( 'Мероприятия', 'portal-theme' ),
		'templates' => __( 'Шаблоны', 'portal-theme' ),
	);
}

/**
 * Цвет полосы превью по умолчанию по категории.
 *
 * @param string $cat Slug.
 * @return string orange|teal|amber
 */
function portal_theme_sovetnik_default_tint( $cat ) {
	$map = array(
		'social'    => 'orange',
		'media'     => 'teal',
		'events'    => 'amber',
		'templates' => 'orange',
	);
	return isset( $map[ $cat ] ) ? $map[ $cat ] : 'orange';
}

/**
 * @return string[]
 */
function portal_theme_sovetnik_thumb_tints() {
	return array( 'orange', 'teal', 'amber' );
}

/**
 * Регистрация типа записей.
 */
function portal_theme_sovetnik_register_post_type() {
	register_post_type(
		'portal_sovetnik',
		array(
			'labels'              => array(
				'name'               => __( 'Советник+', 'portal-theme' ),
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
				'menu_name'          => __( 'Советник+', 'portal-theme' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'menu_position'       => 28,
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
add_action( 'init', 'portal_theme_sovetnik_register_post_type' );

/**
 * Данные карточки из записи.
 *
 * @param int|WP_Post $post Post.
 * @return array<string,mixed>|null
 */
function portal_theme_sovetnik_post_to_item_array( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'portal_sovetnik' !== $post->post_type ) {
		return null;
	}
	$allowed = portal_theme_sovetnik_category_slugs();
	$cat     = get_post_meta( $post->ID, '_portal_sov_category', true );
	$cat     = is_string( $cat ) ? sanitize_key( $cat ) : 'social';
	if ( ! in_array( $cat, $allowed, true ) ) {
		$cat = 'social';
	}
	$tint = get_post_meta( $post->ID, '_portal_sov_thumb_tint', true );
	$tint = is_string( $tint ) ? sanitize_key( $tint ) : '';
	if ( ! in_array( $tint, portal_theme_sovetnik_thumb_tints(), true ) ) {
		$tint = portal_theme_sovetnik_default_tint( $cat );
	}
	$file_id = (int) get_post_meta( $post->ID, '_portal_sov_file', true );
	$tid       = (int) get_post_thumbnail_id( $post->ID );
	$thumb_url = '';
	if ( $tid > 0 ) {
		$u = wp_get_attachment_image_url( $tid, 'thumbnail' );
		$thumb_url = is_string( $u ) ? $u : '';
	}

	return array(
		'id'            => (string) (int) $post->ID,
		'title'         => get_the_title( $post ),
		'excerpt'       => (string) $post->post_excerpt,
		'category'      => $cat,
		'thumb_tint'    => $tint,
		'thumb_url'     => $thumb_url,
		'attachment_id' => $file_id > 0 ? $file_id : 0,
		'sort_ts'       => (int) get_post_time( 'U', true, $post ),
	);
}

/**
 * HTML карточки в основном списке.
 *
 * @param array<string,mixed> $item Из portal_theme_sovetnik_post_to_item_array().
 */
function portal_theme_sovetnik_render_card( array $item ) {
	$cat_labels = portal_theme_sovetnik_category_labels();
	$cat        = isset( $item['category'] ) ? sanitize_key( $item['category'] ) : 'social';
	$cat_label  = isset( $cat_labels[ $cat ] ) ? $cat_labels[ $cat ] : $cat;
	$tint       = isset( $item['thumb_tint'] ) ? sanitize_key( $item['thumb_tint'] ) : 'orange';
	if ( ! in_array( $tint, portal_theme_sovetnik_thumb_tints(), true ) ) {
		$tint = 'orange';
	}

	$title_t   = isset( $item['title'] ) ? (string) $item['title'] : '';
	$excerpt_t = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';
	$raw_s      = wp_strip_all_tags( $title_t . ' ' . $excerpt_t );
	$search_idx = function_exists( 'mb_strtolower' ) ? mb_strtolower( $raw_s, 'UTF-8' ) : strtolower( $raw_s );
	$sort_ts    = isset( $item['sort_ts'] ) ? (int) $item['sort_ts'] : 0;
	$plain_title = wp_strip_all_tags( $title_t );

	$viewer_item = array(
		'title'         => $title_t,
		'excerpt'       => $excerpt_t,
		'attachment_id' => isset( $item['attachment_id'] ) ? (int) $item['attachment_id'] : 0,
	);
	$open_data = function_exists( 'portal_theme_ideology_build_open_data' )
		? portal_theme_ideology_build_open_data( $viewer_item )
		: array();
	$open_json = wp_json_encode( $open_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	$thumb_url = isset( $item['thumb_url'] ) ? (string) $item['thumb_url'] : '';

	ob_start();
	?>
	<article
		class="sov-card"
		data-sov-category="<?php echo esc_attr( $cat ); ?>"
		data-sov-search="<?php echo esc_attr( $search_idx ); ?>"
		data-sov-title="<?php echo esc_attr( $plain_title ); ?>"
		data-sov-sort="<?php echo esc_attr( (string) $sort_ts ); ?>"
	>
		<?php if ( $thumb_url !== '' ) : ?>
			<div class="sov-card__thumb sov-card__thumb--has-img" aria-hidden="true">
				<img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
			</div>
		<?php else : ?>
			<div class="sov-card__thumb sov-card__thumb--<?php echo esc_attr( $tint ); ?>" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="sov-card__main">
			<h3 class="sov-card__title"><?php echo esc_html( $title_t ); ?></h3>
			<p class="sov-card__excerpt"><?php echo esc_html( $excerpt_t ); ?></p>
		</div>
		<div class="sov-card__tags">
			<span class="sov-card__tag"><?php echo esc_html( $cat_label ); ?></span>
		</div>
		<div class="sov-card__action">
			<button type="button" class="sov-btn sov-btn--green sov-card__open" data-open="<?php echo esc_attr( $open_json ); ?>">
				<?php esc_html_e( 'Открыть', 'portal-theme' ); ?>
			</button>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Кнопка сайдбара «Популярные».
 *
 * @param array<string,mixed> $item Item.
 */
function portal_theme_sovetnik_render_popular_button( array $item ) {
	$title_t   = isset( $item['title'] ) ? (string) $item['title'] : '';
	$excerpt_t = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';
	$sub       = wp_trim_words( $excerpt_t, 14, '…' );

	$viewer_item = array(
		'title'         => $title_t,
		'excerpt'       => $excerpt_t,
		'attachment_id' => isset( $item['attachment_id'] ) ? (int) $item['attachment_id'] : 0,
	);
	$open_data = function_exists( 'portal_theme_ideology_build_open_data' )
		? portal_theme_ideology_build_open_data( $viewer_item )
		: array();
	$open_json = wp_json_encode( $open_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	ob_start();
	?>
	<button type="button" class="sov-popular sov-sidebar-open" data-open="<?php echo esc_attr( $open_json ); ?>">
		<span class="sov-popular__icon" aria-hidden="true"></span>
		<span class="sov-popular__text">
			<strong><?php echo esc_html( $title_t ); ?></strong>
			<?php if ( $sub !== '' ) : ?>
				<span class="sov-popular__sub"><?php echo esc_html( $sub ); ?></span>
			<?php endif; ?>
		</span>
	</button>
	<?php
	return (string) ob_get_clean();
}

/**
 * Кнопка сайдбара «Новые поступления».
 *
 * @param array<string,mixed> $item Item.
 */
function portal_theme_sovetnik_render_new_button( array $item ) {
	$title_t   = isset( $item['title'] ) ? (string) $item['title'] : '';
	$excerpt_t = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';
	$sub       = wp_trim_words( $excerpt_t, 12, '…' );

	$viewer_item = array(
		'title'         => $title_t,
		'excerpt'       => $excerpt_t,
		'attachment_id' => isset( $item['attachment_id'] ) ? (int) $item['attachment_id'] : 0,
	);
	$open_data = function_exists( 'portal_theme_ideology_build_open_data' )
		? portal_theme_ideology_build_open_data( $viewer_item )
		: array();
	$open_json = wp_json_encode( $open_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	ob_start();
	?>
	<button type="button" class="sov-new-item sov-sidebar-open" data-open="<?php echo esc_attr( $open_json ); ?>">
		<span class="sov-new-item__doc" aria-hidden="true"></span>
		<span class="sov-new-item__text">
			<strong><?php echo esc_html( $title_t ); ?></strong>
			<?php if ( $sub !== '' ) : ?>
				<span class="sov-new-item__sub"><?php echo esc_html( $sub ); ?></span>
			<?php endif; ?>
		</span>
		<span class="sov-new-item__dot" aria-hidden="true"></span>
	</button>
	<?php
	return (string) ob_get_clean();
}

/**
 * Метабокс.
 */
function portal_theme_sovetnik_add_meta_box() {
	add_meta_box(
		'portal_sv_details',
		__( 'Категория и файл', 'portal-theme' ),
		'portal_theme_sovetnik_meta_box_render',
		'portal_sovetnik',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_sovetnik_add_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function portal_theme_sovetnik_meta_box_render( $post ) {
	wp_nonce_field( 'portal_sv_save_meta', 'portal_sv_meta_nonce' );
	$cat     = get_post_meta( $post->ID, '_portal_sov_category', true );
	$cat     = is_string( $cat ) && $cat !== '' ? sanitize_key( $cat ) : 'social';
	$labels  = portal_theme_sovetnik_category_labels();
	$allowed = portal_theme_sovetnik_category_slugs();

	$tint = get_post_meta( $post->ID, '_portal_sov_thumb_tint', true );
	$tint = is_string( $tint ) ? sanitize_key( $tint ) : '';
	if ( ! in_array( $tint, portal_theme_sovetnik_thumb_tints(), true ) ) {
		$tint = portal_theme_sovetnik_default_tint( $cat );
	}

	$file_id  = (int) get_post_meta( $post->ID, '_portal_sov_file', true );
	$file_txt = '—';
	if ( $file_id > 0 ) {
		$p = get_post( $file_id );
		if ( $p && 'attachment' === $p->post_type ) {
			$path = get_attached_file( $file_id );
			$file_txt = is_string( $path ) && $path !== '' ? basename( $path ) : $p->post_title;
		}
	}

	$highlight = (int) get_post_meta( $post->ID, '_portal_sov_highlight', true ) === 1;
	?>
	<p><strong><?php esc_html_e( 'Категория', 'portal-theme' ); ?></strong></p>
	<select name="portal_sov_category" id="portal-sv-cat" style="max-width:100%;">
		<?php foreach ( $allowed as $slug ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $cat, $slug ); ?>>
				<?php echo isset( $labels[ $slug ] ) ? esc_html( $labels[ $slug ] ) : esc_html( $slug ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<p style="margin-top:16px;"><strong><?php esc_html_e( 'Цвет полосы превью', 'portal-theme' ); ?></strong></p>
	<select name="portal_sov_thumb_tint" style="max-width:100%;">
		<option value="orange" <?php selected( $tint, 'orange' ); ?>><?php esc_html_e( 'Оранжевый', 'portal-theme' ); ?></option>
		<option value="teal" <?php selected( $tint, 'teal' ); ?>><?php esc_html_e( 'Бирюзовый', 'portal-theme' ); ?></option>
		<option value="amber" <?php selected( $tint, 'amber' ); ?>><?php esc_html_e( 'Янтарный', 'portal-theme' ); ?></option>
	</select>

	<p style="margin-top:16px;"><strong><?php esc_html_e( 'Файл для просмотра и скачивания', 'portal-theme' ); ?></strong></p>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Необязательно: PDF, документ, изображение или видео.', 'portal-theme' ); ?>
	</p>
	<p>
		<input type="hidden" name="portal_sov_file" id="portal-sv-file" value="<?php echo esc_attr( (string) $file_id ); ?>">
		<button type="button" class="button" id="portal-sv-pick-file"><?php esc_html_e( 'Выбрать из медиатеки', 'portal-theme' ); ?></button>
		<button type="button" class="button" id="portal-sv-clear-file"><?php esc_html_e( 'Сбросить', 'portal-theme' ); ?></button>
	</p>
	<p id="portal-sv-file-name"><?php echo esc_html( $file_txt ); ?></p>

	<p style="margin-top:16px;">
		<label>
			<input type="checkbox" name="portal_sov_highlight" value="1" <?php checked( $highlight ); ?>>
			<?php esc_html_e( 'Показывать в блоке «Популярные материалы» на странице', 'portal-theme' ); ?>
		</label>
	</p>

	<p class="description" style="margin-top:12px;">
		<?php esc_html_e( 'Краткий текст для карточки и режима чтения — поле «Отрывок». Миниатюра слева — «Изображение записи» (иначе цветная полоса).', 'portal-theme' ); ?>
	</p>
	<?php
}

/**
 * Сохранение метаполей.
 *
 * @param int $post_id ID записи.
 */
function portal_theme_sovetnik_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_sv_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_sv_meta_nonce'] ) ), 'portal_sv_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_sovetnik' ) {
		return;
	}

	$allowed = portal_theme_sovetnik_category_slugs();
	$cat     = isset( $_POST['portal_sov_category'] ) ? sanitize_key( wp_unslash( $_POST['portal_sov_category'] ) ) : 'social';
	if ( ! in_array( $cat, $allowed, true ) ) {
		$cat = 'social';
	}
	update_post_meta( $post_id, '_portal_sov_category', $cat );

	$tint = isset( $_POST['portal_sov_thumb_tint'] ) ? sanitize_key( wp_unslash( $_POST['portal_sov_thumb_tint'] ) ) : 'orange';
	if ( ! in_array( $tint, portal_theme_sovetnik_thumb_tints(), true ) ) {
		$tint = 'orange';
	}
	update_post_meta( $post_id, '_portal_sov_thumb_tint', $tint );

	$file = isset( $_POST['portal_sov_file'] ) ? absint( $_POST['portal_sov_file'] ) : 0;
	if ( $file > 0 ) {
		$p = get_post( $file );
		if ( $p && 'attachment' === $p->post_type ) {
			update_post_meta( $post_id, '_portal_sov_file', $file );
		} else {
			delete_post_meta( $post_id, '_portal_sov_file' );
		}
	} else {
		delete_post_meta( $post_id, '_portal_sov_file' );
	}

	if ( ! empty( $_POST['portal_sov_highlight'] ) ) {
		update_post_meta( $post_id, '_portal_sov_highlight', 1 );
	} else {
		delete_post_meta( $post_id, '_portal_sov_highlight' );
	}
}
add_action( 'save_post_portal_sovetnik', 'portal_theme_sovetnik_save_meta' );

/**
 * Админские скрипты.
 *
 * @param string $hook_suffix Hook.
 */
function portal_theme_sovetnik_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'portal_sovetnik' ) {
		return;
	}
	wp_enqueue_media();
	$path = get_template_directory() . '/assets/js/sovetnik-admin.js';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'portal-sovetnik-admin',
		get_template_directory_uri() . '/assets/js/sovetnik-admin.js',
		array( 'jquery' ),
		(string) filemtime( $path ),
		true
	);
	wp_localize_script(
		'portal-sovetnik-admin',
		'portalSvAdmin',
		array(
			'pickTitle' => __( 'Выберите файл', 'portal-theme' ),
			'pickBtn'   => __( 'Использовать файл', 'portal-theme' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'portal_theme_sovetnik_admin_assets' );

/**
 * Колонки списка.
 *
 * @param string[] $columns Колонки.
 * @return string[]
 */
function portal_theme_sovetnik_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portal_sv_thumb'] = __( 'Превью', 'portal-theme' );
			$new['portal_sv_cat']   = __( 'Категория', 'portal-theme' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_sovetnik_posts_columns', 'portal_theme_sovetnik_posts_columns' );

/**
 * @param string $column Колонка.
 * @param int    $post_id ID.
 */
function portal_theme_sovetnik_posts_custom_column( $column, $post_id ) {
	$post_id = (int) $post_id;
	if ( 'portal_sv_thumb' === $column ) {
		$tid = (int) get_post_thumbnail_id( $post_id );
		if ( $tid > 0 ) {
			echo wp_get_attachment_image( $tid, array( 50, 50 ), false, array( 'style' => 'max-width:50px;height:auto;border-radius:6px;' ) );
		} else {
			$tint = get_post_meta( $post_id, '_portal_sov_thumb_tint', true );
			$tint = is_string( $tint ) && in_array( $tint, portal_theme_sovetnik_thumb_tints(), true ) ? $tint : 'orange';
			$grad = array(
				'orange' => 'linear-gradient(145deg,#ffb347,#f5a623)',
				'teal'   => 'linear-gradient(145deg,#4ecdc4,#2a9d8f)',
				'amber'  => 'linear-gradient(145deg,#ffe066,#f4d35e)',
			);
			$bg   = isset( $grad[ $tint ] ) ? $grad[ $tint ] : $grad['orange'];
			echo '<span style="display:inline-block;width:40px;height:40px;border-radius:8px;background:' . esc_attr( $bg ) . ';"></span>';
		}
		return;
	}
	if ( 'portal_sv_cat' === $column ) {
		$cat = get_post_meta( $post_id, '_portal_sov_category', true );
		$cat = is_string( $cat ) ? sanitize_key( $cat ) : '';
		$labels = portal_theme_sovetnik_category_labels();
		echo isset( $labels[ $cat ] ) ? esc_html( $labels[ $cat ] ) : esc_html( $cat );
	}
}
add_action( 'manage_portal_sovetnik_posts_custom_column', 'portal_theme_sovetnik_posts_custom_column', 10, 2 );
