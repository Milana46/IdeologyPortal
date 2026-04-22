<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PORTAL_THEME_ANALYTICS_PAGE_REPUBLISH_VER' ) ) {
	
	define( 'PORTAL_THEME_ANALYTICS_PAGE_REPUBLISH_VER', 1 );
}

if ( ! function_exists( 'portal_theme_is_analytics_page' ) ) {
	
	function portal_theme_is_analytics_page() {
		if ( is_admin() ) {
			return false;
		}
		if ( is_page_template( 'page-analytics.php' ) ) {
			return true;
		}
		if ( function_exists( 'portal_theme_analytics_get_page_id' ) ) {
			$aid = portal_theme_analytics_get_page_id();
			if ( $aid && is_singular( 'page' ) && (int) get_queried_object_id() === $aid ) {
				return true;
			}
		}
		$page_id = 0;
		if ( is_singular( 'page' ) ) {
			$page_id = (int) get_queried_object_id();
		}
		if ( $page_id <= 0 ) {
			global $post;
			if ( isset( $post ) && $post instanceof WP_Post && 'page' === $post->post_type ) {
				$page_id = (int) $post->ID;
			}
		}
		if ( $page_id <= 0 ) {
			return false;
		}
		$meta = get_post_meta( $page_id, '_wp_page_template', true );
		if ( is_string( $meta ) && $meta !== '' && 'default' !== $meta ) {
			if ( 'page-analytics.php' === $meta || 'templates/page-analytics.php' === $meta || 'page-analytics.php' === basename( $meta ) ) {
				return true;
			}
		}
		$p = get_post( $page_id );
		if ( $p instanceof WP_Post && 'analytics' === $p->post_name && locate_template( 'page-analytics.php', false, false ) ) {
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'portal_theme_enqueue_analytics_page_assets' ) ) {
	
	function portal_theme_enqueue_analytics_page_assets() {
		if ( wp_style_is( 'portal-analytics-page', 'enqueued' ) ) {
			return;
		}
		$an_css = get_template_directory() . '/assets/css/analytics-page.css';
		$an_js  = get_template_directory() . '/assets/js/analytics-page.js';
		if ( file_exists( $an_css ) ) {
			wp_enqueue_style(
				'portal-analytics-page',
				get_template_directory_uri() . '/assets/css/analytics-page.css',
				array( 'portal-layout' ),
				(string) filemtime( $an_css )
			);
		}
		if ( file_exists( $an_js ) ) {
			wp_enqueue_script(
				'portal-analytics-page',
				get_template_directory_uri() . '/assets/js/analytics-page.js',
				array(),
				(string) filemtime( $an_js ),
				true
			);
		}
	}
}

function portal_theme_analytics_get_page_id() {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-analytics.php',
			'number'     => 1,
		)
	);
	if ( ! empty( $pages ) ) {
		return (int) $pages[0]->ID;
	}
	$p = get_page_by_path( 'analytics', OBJECT, 'page' );
	return $p instanceof WP_Post ? (int) $p->ID : 0;
}

function portal_theme_analytics_is_section_page( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return false;
	}
	$aid = portal_theme_analytics_get_page_id();
	if ( $aid && $post_id === $aid ) {
		return true;
	}
	$tpl = get_post_meta( $post_id, '_wp_page_template', true );
	return is_string( $tpl ) && 'page-analytics.php' === basename( $tpl );
}

function portal_theme_analytics_tasks_meta_query_for_section( $section_page_id ) {
	$section_page_id = (int) $section_page_id;
	if ( $section_page_id <= 0 ) {
		return array();
	}
	return array(
		'relation' => 'OR',
		array(
			'key'     => '_portal_at_section_page_id',
			'value'   => (string) $section_page_id,
			'compare' => '=',
		),
		array(
			'key'     => '_portal_at_section_page_id',
			'compare' => 'NOT EXISTS',
		),
	);
}

function portal_theme_analytics_task_section_hidden_field() {
	global $post;
	$screen = get_current_screen();
	if ( ! $screen || 'portal_at_task' !== $screen->post_type ) {
		return;
	}
	$sid = 0;
	if ( $post && $post->ID ) {
		$sid = (int) get_post_meta( $post->ID, '_portal_at_section_page_id', true );
	}
	if ( $sid <= 0 && isset( $_GET['portal_from_section'] ) ) {
		$sid = absint( wp_unslash( $_GET['portal_from_section'] ) );
	}
	if ( $sid <= 0 ) {
		$sid = portal_theme_analytics_get_page_id();
	}
	if ( $sid <= 0 || ! portal_theme_analytics_is_section_page( $sid ) ) {
		return;
	}
	echo '<input type="hidden" name="portal_at_task_section_page_id" value="' . esc_attr( (string) $sid ) . '" />';
}
add_action( 'edit_form_after_title', 'portal_theme_analytics_task_section_hidden_field', 1 );

function portal_theme_analytics_admin_new_task_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'portal_at_task' !== $screen->post_type || 'add' !== $screen->action ) {
		return;
	}
	if ( empty( $_GET['portal_from_section'] ) ) {
		return;
	}
	$pid = absint( wp_unslash( $_GET['portal_from_section'] ) );
	if ( ! $pid || ! portal_theme_analytics_is_section_page( $pid ) ) {
		return;
	}
	$page = get_post( $pid );
	if ( ! $page ) {
		return;
	}
	echo '<div class="notice notice-info is-dismissible"><p>';
	printf( esc_html__( 'Эта задача будет показана в блоке «Текущие задачи» на странице «%s» после публикации.', 'portal-theme' ), esc_html( get_the_title( $page ) ) );
	echo '</p></div>';
}
add_action( 'admin_notices', 'portal_theme_analytics_admin_new_task_notice' );

function portal_theme_analytics_page_tasks_metabox_render( $post ) {
	if ( ! $post instanceof WP_Post || ! portal_theme_analytics_is_section_page( $post->ID ) ) {
		return;
	}
	$add_url = add_query_arg(
		array(
			'post_type'            => 'portal_at_task',
			'portal_from_section' => $post->ID,
		),
		admin_url( 'post-new.php' )
	);
	$list_url = add_query_arg(
		array( 'post_type' => 'portal_at_task' ),
		admin_url( 'edit.php' )
	);
	$q = new WP_Query(
		array(
			'post_type'              => 'portal_at_task',
			'post_status'            => 'any',
			'posts_per_page'         => 80,
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => portal_theme_analytics_tasks_meta_query_for_section( $post->ID ),
		)
	);
	?>
	<p class="description"><?php esc_html_e( 'Карточки в блоке «Текущие задачи» на сайте. Основная работа — в отдельном пункте меню «Аналитика» (как «Медиабанк»): «Все задачи» и «Новая задача».', 'portal-theme' ); ?></p>
	<p>
		<a href="<?php echo esc_url( $add_url ); ?>" class="button button-primary"><?php esc_html_e( 'Новая задача (с привязкой к странице)', 'portal-theme' ); ?></a>
		<a href="<?php echo esc_url( $list_url ); ?>" class="button"><?php esc_html_e( 'Все задачи', 'portal-theme' ); ?></a>
	</p>
	<?php if ( $q->have_posts() ) : ?>
		<table class="widefat striped" style="margin-top:10px;">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Задача', 'portal-theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Статус', 'portal-theme' ); ?></th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				$tid  = get_the_ID();
				$edit = get_edit_post_link( $tid, 'raw' );
				$st   = get_post_status_object( get_post_status() );
				?>
				<tr>
					<td><?php echo esc_html( get_the_title() ? get_the_title() : __( '(без названия)', 'portal-theme' ) ); ?></td>
					<td><?php echo $st ? esc_html( $st->label ) : ''; ?></td>
					<td>
						<?php if ( $edit ) : ?>
							<a href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'Изменить', 'portal-theme' ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
		<?php
		wp_reset_postdata();
	else :
		?>
		<p><?php esc_html_e( 'Задач ещё нет — нажмите «Добавить задачу для этой страницы».', 'portal-theme' ); ?></p>
	<?php endif; ?>
	<?php
}

function portal_theme_analytics_register_page_section_metabox( $post ) {
	if ( ! $post instanceof WP_Post || ! portal_theme_analytics_is_section_page( $post->ID ) ) {
		return;
	}
	add_meta_box(
		'portal_analytics_page_tasks',
		__( 'Материалы страницы: текущие задачи', 'portal-theme' ),
		'portal_theme_analytics_page_tasks_metabox_render',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_page', 'portal_theme_analytics_register_page_section_metabox', 10, 1 );

function portal_theme_analytics_section_page_author_id() {
	$by_login = get_user_by( 'login', 'admin' );
	if ( $by_login instanceof WP_User ) {
		return (int) $by_login->ID;
	}
	$users = get_users(
		array(
			'role'    => 'administrator',
			'number'  => 1,
			'orderby' => 'ID',
			'order'   => 'ASC',
			'fields'  => 'ID',
		)
	);
	if ( ! empty( $users ) ) {
		return (int) $users[0];
	}
	return 1;
}

function portal_theme_analytics_maybe_republish_section_page() {
	if ( wp_installing() ) {
		return;
	}
	$done = (int) get_option( 'portal_theme_analytics_page_republish_ver', 0 );
	if ( $done >= PORTAL_THEME_ANALYTICS_PAGE_REPUBLISH_VER ) {
		return;
	}
	$page_id = portal_theme_analytics_get_page_id();
	if ( $page_id <= 0 ) {
		return;
	}
	$author_id = portal_theme_analytics_section_page_author_id();
	if ( $author_id <= 0 ) {
		$author_id = 1;
	}
	$now_local = current_time( 'mysql' );
	$now_gmt   = current_time( 'mysql', 1 );
	wp_update_post(
		array(
			'ID'                => $page_id,
			'post_author'       => $author_id,
			'post_date'         => $now_local,
			'post_date_gmt'     => $now_gmt,
			'post_modified'     => $now_local,
			'post_modified_gmt' => $now_gmt,
			'post_status'       => 'publish',
		)
	);
	update_option( 'portal_theme_analytics_page_republish_ver', PORTAL_THEME_ANALYTICS_PAGE_REPUBLISH_VER, true );
}
add_action( 'init', 'portal_theme_analytics_maybe_republish_section_page', 99 );

function portal_theme_ensure_analytics_page() {
	if ( wp_installing() ) {
		return;
	}
	if ( ! locate_template( 'page-analytics.php', false, false ) ) {
		return;
	}
	$slug = 'analytics';
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page instanceof WP_Post ) {
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
				)
			);
		}
		update_post_meta( $page->ID, '_wp_page_template', 'page-analytics.php' );
		return;
	}
	$post_id = wp_insert_post(
		array(
			'post_title'   => __( 'Аналитика и эффективность', 'portal-theme' ),
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
			'post_content' => '',
			'post_excerpt' => __( 'Центр управления текущими задачами, решениями и актуальной информацией', 'portal-theme' ),
		),
		true
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}
	update_post_meta( (int) $post_id, '_wp_page_template', 'page-analytics.php' );
}
add_action( 'init', 'portal_theme_ensure_analytics_page', 98 );

function portal_theme_analytics_default_popular_rows() {
	$sub = __( 'Краткая информация', 'portal-theme' );
	return array(
		array(
			'title' => __( 'Кейсы', 'portal-theme' ),
			'sub'   => $sub,
			'url'   => '',
			'icon'  => 'yellow',
		),
		array(
			'title' => __( 'Практика', 'portal-theme' ),
			'sub'   => $sub,
			'url'   => '',
			'icon'  => 'green',
		),
		array(
			'title' => __( 'Гайдлайны', 'portal-theme' ),
			'sub'   => $sub,
			'url'   => '',
			'icon'  => 'yellow',
		),
	);
}

function portal_theme_analytics_get_popular_rows() {
	$raw = get_option( 'portal_theme_analytics_popular', null );
	if ( ! is_array( $raw ) || array() === $raw ) {
		return portal_theme_analytics_default_popular_rows();
	}
	$out = array();
	foreach ( array_slice( $raw, 0, 6 ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$icon = ( isset( $row['icon'] ) && 'green' === $row['icon'] ) ? 'green' : 'yellow';
		$t    = isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '';
		$s    = isset( $row['sub'] ) ? sanitize_text_field( (string) $row['sub'] ) : '';
		$u    = isset( $row['url'] ) ? esc_url_raw( (string) $row['url'] ) : '';
		if ( $t === '' && $s === '' && $u === '' ) {
			continue;
		}
		$out[] = array(
			'title' => $t,
			'sub'   => $s,
			'url'   => $u,
			'icon'  => $icon,
		);
	}
	return $out !== array() ? $out : portal_theme_analytics_default_popular_rows();
}

function portal_theme_analytics_redirect_to_messages_admin() {
	wp_safe_redirect( admin_url( 'edit.php?post_type=portal_at_question' ) );
	exit;
}

function portal_theme_analytics_task_menu_parent_slug() {
	global $submenu;
	$preferred = 'edit.php?post_type=portal_at_task';
	if ( isset( $submenu[ $preferred ] ) ) {
		return $preferred;
	}
	foreach ( array_keys( (array) $submenu ) as $key ) {
		if ( ! is_string( $key ) ) {
			continue;
		}
		if ( false !== strpos( $key, 'post_type=portal_at_task' ) || false !== strpos( $key, 'portal_at_task' ) ) {
			return $key;
		}
	}
	return $preferred;
}

function portal_theme_analytics_register_analytics_extra_submenus() {
	static $done = false;
	if ( $done ) {
		return;
	}
	if ( ! post_type_exists( 'portal_at_task' ) ) {
		return;
	}
	$parent = portal_theme_analytics_task_menu_parent_slug();

	$task_obj = get_post_type_object( 'portal_at_task' );
	$task_cap = ( $task_obj && isset( $task_obj->cap->edit_posts ) ) ? $task_obj->cap->edit_posts : 'edit_posts';

	if ( post_type_exists( 'portal_at_question' ) ) {
		add_submenu_page(
			$parent,
			__( 'Сообщения с портала', 'portal-theme' ),
			__( 'Сообщения с портала', 'portal-theme' ),
			$task_cap,
			'portal-at-messages',
			'portal_theme_analytics_redirect_to_messages_admin'
		);
	}

	add_submenu_page(
		$parent,
		__( 'Настройки раздела «Аналитика»', 'portal-theme' ),
		__( 'Настройки блоков', 'portal-theme' ),
		'manage_options',
		'portal-analytics-section-settings',
		'portal_theme_analytics_settings_page_render'
	);

	$done = true;
}
add_action( 'admin_menu', 'portal_theme_analytics_register_analytics_extra_submenus', 20 );
add_action( 'admin_menu', 'portal_theme_analytics_register_analytics_extra_submenus', 2000 );

function portal_theme_analytics_settings_page_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'portal-theme' ) );
	}
	if ( isset( $_POST['portal_analytics_popular_save'] ) && isset( $_POST['portal_analytics_popular_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_analytics_popular_nonce'] ) ), 'portal_analytics_popular_save' ) ) {
		$rows = array();
		$n    = isset( $_POST['pop_title'] ) && is_array( $_POST['pop_title'] ) ? count( $_POST['pop_title'] ) : 0;
		for ( $i = 0; $i < $n; $i++ ) {
			$icon = isset( $_POST['pop_icon'][ $i ] ) && 'green' === $_POST['pop_icon'][ $i ] ? 'green' : 'yellow';
			$rows[] = array(
				'title' => isset( $_POST['pop_title'][ $i ] ) ? sanitize_text_field( wp_unslash( $_POST['pop_title'][ $i ] ) ) : '',
				'sub'   => isset( $_POST['pop_sub'][ $i ] ) ? sanitize_text_field( wp_unslash( $_POST['pop_sub'][ $i ] ) ) : '',
				'url'   => isset( $_POST['pop_url'][ $i ] ) ? esc_url_raw( wp_unslash( $_POST['pop_url'][ $i ] ) ) : '',
				'icon'  => $icon,
			);
		}
		update_option( 'portal_theme_analytics_popular', $rows );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Сохранено.', 'portal-theme' ) . '</p></div>';
	}
	$raw  = get_option( 'portal_theme_analytics_popular', null );
	$rows = array();
	if ( is_array( $raw ) ) {
		foreach ( array_slice( $raw, 0, 6 ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$rows[] = array(
				'title' => isset( $row['title'] ) ? (string) $row['title'] : '',
				'sub'   => isset( $row['sub'] ) ? (string) $row['sub'] : '',
				'url'   => isset( $row['url'] ) ? (string) $row['url'] : '',
				'icon'  => ( isset( $row['icon'] ) && 'green' === $row['icon'] ) ? 'green' : 'yellow',
			);
		}
	} else {
		$rows = portal_theme_analytics_default_popular_rows();
	}
	while ( count( $rows ) < 6 ) {
		$rows[] = array( 'title' => '', 'sub' => '', 'url' => '', 'icon' => 'yellow' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Настройки раздела «Аналитика и эффективность»', 'portal-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Блок «Популярные материалы» на странице раздела. Заголовок и подпись страницы (герой) задаются при редактировании самой страницы «Аналитика и эффективность» в разделе «Страницы».', 'portal-theme' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'portal_analytics_popular_save', 'portal_analytics_popular_nonce' ); ?>
			<table class="widefat striped" style="max-width:920px;margin-top:12px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Заголовок', 'portal-theme' ); ?></th>
						<th><?php esc_html_e( 'Подпись', 'portal-theme' ); ?></th>
						<th><?php esc_html_e( 'Ссылка', 'portal-theme' ); ?></th>
						<th><?php esc_html_e( 'Иконка', 'portal-theme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $i => $r ) : ?>
						<tr>
							<td><input type="text" class="large-text" name="pop_title[]" value="<?php echo esc_attr( $r['title'] ); ?>"></td>
							<td><input type="text" class="large-text" name="pop_sub[]" value="<?php echo esc_attr( $r['sub'] ); ?>"></td>
							<td><input type="url" class="large-text" name="pop_url[]" value="<?php echo esc_attr( $r['url'] ); ?>" placeholder="https://"></td>
							<td>
								<select name="pop_icon[]">
									<option value="yellow" <?php selected( $r['icon'], 'yellow' ); ?>><?php esc_html_e( 'Жёлтая', 'portal-theme' ); ?></option>
									<option value="green" <?php selected( $r['icon'], 'green' ); ?>><?php esc_html_e( 'Зелёная', 'portal-theme' ); ?></option>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="submit" name="portal_analytics_popular_save" class="button button-primary"><?php esc_html_e( 'Сохранить', 'portal-theme' ); ?></button></p>
		</form>
	</div>
	<?php
}

function portal_theme_analytics_priority_slugs() {
	return array( 'red', 'yellow', 'green' );
}

function portal_theme_analytics_get_task_document_ids( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return array();
	}
	$raw = get_post_meta( $post_id, '_portal_at_documents', true );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return array();
	}
	$ids = array_map( 'absint', explode( ',', $raw ) );
	return array_values( array_filter( array_unique( $ids ) ) );
}

function portal_theme_analytics_edit_form_after_title() {
	$screen = get_current_screen();
	if ( ! $screen || 'portal_at_task' !== $screen->post_type ) {
		return;
	}
	echo '<p class="description" style="margin:-6px 0 14px;">';
	echo esc_html__( 'Название — заголовок карточки. «Цитата» (отрывок) — краткая информация под заголовком. Большое поле ниже — полный текст; на сайте оно открывается по кнопке «Показать». При необходимости прикрепите файлы в метабоксе «Документы к задаче».', 'portal-theme' );
	echo '</p>';
}
add_action( 'edit_form_after_title', 'portal_theme_analytics_edit_form_after_title' );

function portal_theme_analytics_admin_enqueue( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'portal_at_task' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	$path = get_template_directory() . '/assets/js/analytics-task-admin.js';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : '1';
	wp_enqueue_script(
		'portal-analytics-task-admin',
		get_template_directory_uri() . '/assets/js/analytics-task-admin.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		$ver,
		true
	);
	wp_localize_script(
		'portal-analytics-task-admin',
		'portalAtAdmin',
		array(
			'pickTitle' => __( 'Выберите файлы из медиатеки', 'portal-theme' ),
			'pickBtn'   => __( 'Добавить в задачу', 'portal-theme' ),
			'removeBtn' => __( 'Убрать', 'portal-theme' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'portal_theme_analytics_admin_enqueue' );

function portal_theme_analytics_migrate_task_post_type_slug() {
	if ( wp_installing() ) {
		return;
	}
	if ( '1' === get_option( 'portal_theme_portal_at_task_migrated', '' ) ) {
		return;
	}
	global $wpdb;
	$legacy = 'portal_' . 'analytics_task';
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
			'portal_at_task',
			$legacy
		)
	);
	update_option( 'portal_theme_portal_at_task_migrated', '1', true );
	flush_rewrite_rules( false );
}
add_action( 'init', 'portal_theme_analytics_migrate_task_post_type_slug', 5 );

function portal_theme_analytics_redirect_legacy_task_admin_urls() {
	if ( ! is_admin() || ! is_user_logged_in() ) {
		return;
	}
	global $pagenow;
	$legacy = 'portal_' . 'analytics_task';
	if ( empty( $_GET['post_type'] ) ) {
		return;
	}
	$pt = sanitize_key( wp_unslash( $_GET['post_type'] ) );
	if ( $pt !== $legacy ) {
		return;
	}
	if ( 'post-new.php' !== $pagenow && 'edit.php' !== $pagenow ) {
		return;
	}
	$query               = wp_unslash( $_GET );
	$query['post_type'] = 'portal_at_task';
	wp_safe_redirect( add_query_arg( $query, admin_url( $pagenow ) ) );
	exit;
}
add_action( 'admin_init', 'portal_theme_analytics_redirect_legacy_task_admin_urls', 0 );

function portal_theme_analytics_register_cpt() {
	register_post_type(
		'portal_at_task',
		array(
			'labels'             => array(
				'name'               => __( 'Аналитика', 'portal-theme' ),
				'singular_name'      => __( 'Задача', 'portal-theme' ),
				'menu_name'          => __( 'Аналитика', 'portal-theme' ),
				'all_items'          => __( 'Все задачи', 'portal-theme' ),
				'add_new'            => __( 'Добавить задачу', 'portal-theme' ),
				'add_new_item'       => __( 'Новая задача', 'portal-theme' ),
				'new_item'           => __( 'Новая задача', 'portal-theme' ),
				'edit_item'          => __( 'Редактировать задачу', 'portal-theme' ),
				'view_item'          => __( 'Просмотр', 'portal-theme' ),
				'search_items'       => __( 'Поиск задач', 'portal-theme' ),
				'not_found'          => __( 'Задач не найдено', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-chart-area',
			'menu_position'      => 27,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'supports'           => array( 'title', 'editor', 'excerpt' ),
			'has_archive'        => false,
			'rewrite'            => false,
		)
	);
}
add_action( 'init', 'portal_theme_analytics_register_cpt' );

function portal_theme_analytics_register_question_cpt() {
	register_post_type(
		'portal_at_question',
		array(
			'labels'             => array(
				'name'               => __( 'Сообщения с портала', 'portal-theme' ),
				'singular_name'      => __( 'Сообщение', 'portal-theme' ),
				'menu_name'          => __( 'Сообщения с портала', 'portal-theme' ),
				'all_items'          => __( 'Все сообщения', 'portal-theme' ),
				'add_new_item'       => __( 'Добавить вручную', 'portal-theme' ),
				'edit_item'          => __( 'Сообщение', 'portal-theme' ),
				'search_items'       => __( 'Поиск сообщений', 'portal-theme' ),
				'not_found'          => __( 'Сообщений пока нет', 'portal-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-theme' ),
			),
			'description'        => __( 'Сообщения из блока «Задайте дополнительные вопросы» на странице аналитики.', 'portal-theme' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'supports'           => array( 'title', 'editor' ),
			'has_archive'        => false,
			'rewrite'            => false,
		)
	);
}
add_action( 'init', 'portal_theme_analytics_register_question_cpt', 11 );

function portal_theme_analytics_question_meta_boxes() {
	add_meta_box(
		'portal_at_question_meta',
		__( 'Данные отправки', 'portal-theme' ),
		static function ( $post ) {
			if ( ! $post instanceof WP_Post || 'portal_at_question' !== $post->post_type ) {
				return;
			}
			$ip = get_post_meta( $post->ID, '_portal_at_q_ip', true );
			if ( is_string( $ip ) && $ip !== '' ) {
				echo '<p><strong>' . esc_html__( 'IP-адрес', 'portal-theme' ) . '</strong><br>' . esc_html( $ip ) . '</p>';
			} else {
				echo '<p class="description">' . esc_html__( 'IP не зафиксирован.', 'portal-theme' ) . '</p>';
			}
		},
		'portal_at_question',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_portal_at_question', 'portal_theme_analytics_question_meta_boxes' );

function portal_theme_analytics_store_visitor_question( $text ) {
	$text = trim( (string) $text );
	if ( $text === '' ) {
		return 0;
	}
	if ( ! post_type_exists( 'portal_at_question' ) ) {
		return 0;
	}
	$title = wp_html_excerpt( $text, 78, '…' );
	if ( $title === '' ) {
		$title = __( 'Вопрос с формы «Аналитика»', 'portal-theme' );
	}
	$author_id = function_exists( 'portal_theme_analytics_section_page_author_id' )
		? portal_theme_analytics_section_page_author_id()
		: 1;
	if ( $author_id <= 0 || ! get_userdata( $author_id ) ) {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'order'   => 'ASC',
				'fields'  => 'ID',
			)
		);
		$author_id = ! empty( $admins ) ? (int) $admins[0] : 1;
	}
	$prev_uid = get_current_user_id();
	wp_set_current_user( $author_id );

	$GLOBALS['portal_at_question_inserting'] = true;
	$elevate                                 = static function ( $allcaps ) {
		if ( empty( $GLOBALS['portal_at_question_inserting'] ) ) {
			return $allcaps;
		}
		$allcaps['edit_posts']          = true;
		$allcaps['edit_others_posts']   = true;
		$allcaps['publish_posts']       = true;
		$allcaps['delete_posts']        = true;
		$allcaps['read_private_posts']  = true;
		$allcaps['edit_private_posts']  = true;
		$allcaps['delete_private_posts'] = true;
		return $allcaps;
	};
	add_filter( 'user_has_cap', $elevate, 999, 1 );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'portal_at_question',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $text,
			'post_author'  => $author_id,
		),
		true
	);

	remove_filter( 'user_has_cap', $elevate, 999 );
	unset( $GLOBALS['portal_at_question_inserting'] );
	wp_set_current_user( $prev_uid );

	if ( is_wp_error( $post_id ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'portal_at_question insert: ' . $post_id->get_error_message() );
		}
		$post_id = 0;
	}
	if ( ! $post_id ) {
		global $wpdb;
		$now_local = current_time( 'mysql' );
		$now_gmt   = current_time( 'mysql', 1 );
		$slug      = sanitize_title( $title );
		if ( $slug === '' ) {
			$slug = 'portal-at-question-' . wp_generate_password( 6, false, false );
		}
		$inserted = $wpdb->insert(
			$wpdb->posts,
			array(
				'post_author'           => $author_id,
				'post_date'             => $now_local,
				'post_date_gmt'         => $now_gmt,
				'post_content'          => $text,
				'post_title'            => $title,
				'post_excerpt'          => '',
				'post_status'           => 'publish',
				'comment_status'        => 'closed',
				'ping_status'           => 'closed',
				'post_password'         => '',
				'post_name'             => $slug,
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => $now_local,
				'post_modified_gmt'     => $now_gmt,
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => '',
				'menu_order'            => 0,
				'post_type'             => 'portal_at_question',
				'post_mime_type'        => '',
				'comment_count'         => 0,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
			)
		);
		if ( false !== $inserted ) {
			$post_id = (int) $wpdb->insert_id;
			clean_post_cache( $post_id );
		}
	}
	if ( ! $post_id ) {
		return 0;
	}
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		if ( $ip !== '' ) {
			update_post_meta( (int) $post_id, '_portal_at_q_ip', $ip );
		}
	}
	return (int) $post_id;
}

function portal_theme_analytics_meta_boxes() {
	add_meta_box(
		'portal_at_task_meta',
		__( 'Параметры карточки', 'portal-theme' ),
		'portal_theme_analytics_metabox_render',
		'portal_at_task',
		'side',
		'default'
	);
	add_meta_box(
		'portal_at_task_docs',
		__( 'Документы к задаче', 'portal-theme' ),
		'portal_theme_analytics_documents_metabox_render',
		'portal_at_task',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'portal_theme_analytics_meta_boxes' );

function portal_theme_analytics_documents_metabox_render( $post ) {
	$ids = portal_theme_analytics_get_task_document_ids( $post->ID );
	$value = $ids !== array() ? implode( ',', $ids ) : '';
	?>
	<p class="description"><?php esc_html_e( 'Добавьте один или несколько файлов. На сайте ссылки появятся в окне с полным текстом задачи.', 'portal-theme' ); ?></p>
	<style>
		.portal-at-docs-list { list-style: none; margin: 0 0 12px; padding: 0; max-width: 640px; }
		.portal-at-docs-item { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid #dcdcde; }
		.portal-at-docs-item__handle { cursor: grab; color: #787c82; user-select: none; font-size: 12px; }
		.portal-at-docs-item__name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	</style>
	<ul id="portal-at-docs-list" class="portal-at-docs-list">
		<?php
		foreach ( $ids as $aid ) :
			$att = get_post( $aid );
			if ( ! $att || 'attachment' !== $att->post_type ) {
				continue;
			}
			$fname = get_the_title( $att );
			if ( $fname === '' ) {
				$fname = basename( (string) get_attached_file( $aid ) );
			}
			?>
			<li class="portal-at-docs-item" data-id="<?php echo esc_attr( (string) $aid ); ?>">
				<span class="portal-at-docs-item__handle" aria-hidden="true">⋮⋮</span>
				<span class="portal-at-docs-item__name"><?php echo esc_html( $fname ); ?></span>
				<button type="button" class="button-link portal-at-docs-remove"><?php esc_html_e( 'Убрать', 'portal-theme' ); ?></button>
			</li>
		<?php endforeach; ?>
	</ul>
	<input type="hidden" id="portal-at-docs-ids" name="portal_at_documents" value="<?php echo esc_attr( $value ); ?>">
	<p>
		<button type="button" class="button" id="portal-at-docs-add"><?php esc_html_e( 'Добавить файлы из медиатеки', 'portal-theme' ); ?></button>
	</p>
	<?php
}

function portal_theme_analytics_metabox_render( $post ) {
	wp_nonce_field( 'portal_at_task_save', 'portal_at_task_nonce' );
	$deadline = get_post_meta( $post->ID, '_portal_at_deadline', true );
	$deadline = is_string( $deadline ) ? $deadline : '';
	$priority = get_post_meta( $post->ID, '_portal_at_priority', true );
	$priority = is_string( $priority ) ? sanitize_key( $priority ) : 'red';
	if ( ! in_array( $priority, portal_theme_analytics_priority_slugs(), true ) ) {
		$priority = 'red';
	}
	?>
	<p>
		<label for="portal_at_deadline"><strong><?php esc_html_e( 'Дедлайн', 'portal-theme' ); ?></strong></label><br>
		<input type="text" class="widefat" name="portal_at_deadline" id="portal_at_deadline" value="<?php echo esc_attr( $deadline ); ?>" placeholder="<?php esc_attr_e( 'например 15.03.2026', 'portal-theme' ); ?>">
	</p>
	<p>
		<label for="portal_at_priority"><strong><?php esc_html_e( 'Индикатор на карточке', 'portal-theme' ); ?></strong></label><br>
		<select name="portal_at_priority" id="portal_at_priority" class="widefat">
			<option value="red" <?php selected( $priority, 'red' ); ?>><?php esc_html_e( 'Красный', 'portal-theme' ); ?></option>
			<option value="yellow" <?php selected( $priority, 'yellow' ); ?>><?php esc_html_e( 'Жёлтый', 'portal-theme' ); ?></option>
			<option value="green" <?php selected( $priority, 'green' ); ?>><?php esc_html_e( 'Зелёный', 'portal-theme' ); ?></option>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Краткий текст под заголовком — поле «Цитата» (отрывок) на экране редактирования.', 'portal-theme' ); ?></p>
	<?php
}

function portal_theme_analytics_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_at_task_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_at_task_nonce'] ) ), 'portal_at_task_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_at_task' ) {
		return;
	}
	$deadline = isset( $_POST['portal_at_deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_at_deadline'] ) ) : '';
	update_post_meta( $post_id, '_portal_at_deadline', $deadline );
	$pr = isset( $_POST['portal_at_priority'] ) ? sanitize_key( wp_unslash( $_POST['portal_at_priority'] ) ) : 'red';
	if ( ! in_array( $pr, portal_theme_analytics_priority_slugs(), true ) ) {
		$pr = 'red';
	}
	update_post_meta( $post_id, '_portal_at_priority', $pr );

	if ( isset( $_POST['portal_at_documents'] ) ) {
		$doc_raw = sanitize_text_field( wp_unslash( $_POST['portal_at_documents'] ) );
		$doc_ids = array_map( 'absint', array_filter( explode( ',', $doc_raw ) ) );
		$valid   = array();
		foreach ( array_unique( $doc_ids ) as $aid ) {
			if ( $aid <= 0 ) {
				continue;
			}
			if ( ! get_post( $aid ) || 'attachment' !== get_post_type( $aid ) ) {
				continue;
			}
			if ( ! current_user_can( 'read_post', $aid ) ) {
				continue;
			}
			$valid[] = $aid;
		}
		update_post_meta( $post_id, '_portal_at_documents', $valid !== array() ? implode( ',', $valid ) : '' );
	}

	$section_pid = 0;
	if ( isset( $_POST['portal_at_task_section_page_id'] ) ) {
		$section_pid = absint( wp_unslash( $_POST['portal_at_task_section_page_id'] ) );
	}
	if ( $section_pid && portal_theme_analytics_is_section_page( $section_pid ) ) {
		update_post_meta( $post_id, '_portal_at_section_page_id', $section_pid );
	} else {
		$aid = portal_theme_analytics_get_page_id();
		if ( $aid ) {
			update_post_meta( $post_id, '_portal_at_section_page_id', $aid );
		}
	}
}
add_action( 'save_post_portal_at_task', 'portal_theme_analytics_save_meta' );

function portal_theme_analytics_priority_mark( $slug ) {
	switch ( $slug ) {
		case 'yellow':
			return '1';
		case 'green':
			return '✓';
		case 'red':
		default:
			return 'i';
	}
}

function portal_theme_analytics_task_card_html( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'portal_at_task' !== $post->post_type ) {
		return '';
	}
	$priority = get_post_meta( $post_id, '_portal_at_priority', true );
	$priority = is_string( $priority ) ? sanitize_key( $priority ) : 'red';
	if ( ! in_array( $priority, portal_theme_analytics_priority_slugs(), true ) ) {
		$priority = 'red';
	}
	$deadline = get_post_meta( $post_id, '_portal_at_deadline', true );
	$deadline = is_string( $deadline ) ? $deadline : '';
	$excerpt  = $post->post_excerpt;
	if ( ! is_string( $excerpt ) || $excerpt === '' ) {
		$excerpt = __( 'Краткая/необходимая информация', 'portal-theme' );
	}
	$pub_label = __( 'Дата публикации/размещения', 'portal-theme' );
	$pub_date  = get_the_date( 'd.m.Y', $post );
	$mark      = portal_theme_analytics_priority_mark( $priority );

	ob_start();
	?>
	<article id="<?php echo esc_attr( 'analytics-task-' . (string) $post_id ); ?>" class="analytics-task-card" data-task-id="<?php echo esc_attr( (string) $post_id ); ?>">
		<span class="analytics-task-card__badge analytics-task-card__badge--<?php echo esc_attr( $priority ); ?>" title="<?php esc_attr_e( 'Приоритет', 'portal-theme' ); ?>" aria-hidden="true"><?php echo esc_html( $mark ); ?></span>
		<div class="analytics-task-card__body">
			<h2 class="analytics-task-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
			<p class="analytics-task-card__excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
			<p class="analytics-task-card__deadline">
				<?php
				if ( $deadline !== '' ) {
					echo esc_html__( 'Дедлайн:', 'portal-theme' ) . ' ' . esc_html( $deadline );
				} else {
					esc_html_e( 'Дедлайн не указан', 'portal-theme' );
				}
				?>
			</p>
			<p class="analytics-task-card__pub"><?php echo esc_html( $pub_label . ': ' . $pub_date ); ?></p>
		</div>
		<button type="button" class="portal-btn portal-btn--green analytics-task-card__show"><?php esc_html_e( 'Показать', 'portal-theme' ); ?></button>
	</article>
	<?php
	return (string) ob_get_clean();
}

function portal_theme_analytics_task_modal_template_html( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'portal_at_task' !== $post->post_type ) {
		return '';
	}
	$deadline = get_post_meta( $post_id, '_portal_at_deadline', true );
	$deadline = is_string( $deadline ) ? $deadline : '';
	$content  = apply_filters( 'the_content', $post->post_content );
	$content  = is_string( $content ) ? $content : '';

	$doc_ids = portal_theme_analytics_get_task_document_ids( $post_id );
	$doc_rows = array();
	foreach ( $doc_ids as $aid ) {
		$att = get_post( $aid );
		if ( ! $att || 'attachment' !== $att->post_type ) {
			continue;
		}
		$url = wp_get_attachment_url( $aid );
		if ( ! is_string( $url ) || $url === '' ) {
			continue;
		}
		$label = get_the_title( $att );
		if ( $label === '' ) {
			$label = basename( (string) get_attached_file( $aid ) );
		}
		$doc_rows[] = array(
			'url'   => $url,
			'label' => $label,
		);
	}

	ob_start();
	?>
	<template id="analytics-task-detail-<?php echo esc_attr( (string) $post_id ); ?>">
		<div class="analytics-modal__inner">
			<div class="analytics-modal__head">
				<h2 class="analytics-modal__title" id="analytics-modal-title-live"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
				<button type="button" class="analytics-modal__close" aria-label="<?php esc_attr_e( 'Закрыть', 'portal-theme' ); ?>">×</button>
			</div>
			<div class="analytics-modal__body">
				<p class="analytics-modal__deadline"><strong><?php esc_html_e( 'Дедлайн:', 'portal-theme' ); ?></strong> <?php echo $deadline !== '' ? esc_html( $deadline ) : esc_html__( 'Не указан', 'portal-theme' ); ?></p>
				<div class="analytics-modal__content"><?php echo wp_kses_post( $content ); ?></div>
				<?php if ( $doc_rows !== array() ) : ?>
					<div class="analytics-modal__documents">
						<h3 class="analytics-modal__documents-title"><?php esc_html_e( 'Документы', 'portal-theme' ); ?></h3>
						<ul class="analytics-modal__doc-list">
							<?php foreach ( $doc_rows as $row ) : ?>
								<li>
									<a href="<?php echo esc_url( $row['url'] ); ?>" class="analytics-modal__doc-link" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $row['label'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</template>
	<?php
	return (string) ob_get_clean();
}

function portal_theme_analytics_handle_question() {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'portal_analytics_ask' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$text = isset( $_POST['analytics_question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['analytics_question'] ) ) : '';
	$ref  = wp_get_referer();
	$base = $ref ? remove_query_arg( 'analytics_ask', $ref ) : home_url( '/' );

	if ( $text === '' ) {
		wp_safe_redirect( add_query_arg( 'analytics_ask', 'empty', $base ) );
		exit;
	}

	$saved_id = portal_theme_analytics_store_visitor_question( $text );
	if ( $saved_id > 0 ) {
		$subject = sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), __( 'Вопрос со страницы «Аналитика и эффективность»', 'portal-theme' ) );
		wp_mail( get_option( 'admin_email' ), $subject, $text );
		wp_safe_redirect( add_query_arg( 'analytics_ask', '1', $base ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'analytics_ask', 'fail', $base ) );
	exit;
}
add_action( 'admin_post_portal_analytics_ask', 'portal_theme_analytics_handle_question' );
add_action( 'admin_post_nopriv_portal_analytics_ask', 'portal_theme_analytics_handle_question' );
