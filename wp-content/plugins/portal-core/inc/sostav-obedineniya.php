<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function portal_core_register_union_org_cpt() {
	register_post_type(
		'portal_union_org',
		array(
			'labels'             => array(
				'name'               => __( 'Идеологическая вертикаль', 'portal-core' ),
				'singular_name'      => __( 'Карточка', 'portal-core' ),
				'add_new'            => __( 'Добавить карточку', 'portal-core' ),
				'add_new_item'       => __( 'Новая карточка', 'portal-core' ),
				'edit_item'          => __( 'Редактировать карточку', 'portal-core' ),
				'new_item'           => __( 'Новая карточка', 'portal-core' ),
				'search_items'       => __( 'Поиск', 'portal-core' ),
				'not_found'          => __( 'Карточек не найдено', 'portal-core' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'portal-core' ),
				'all_items'          => __( 'Все карточки', 'portal-core' ),
				'menu_name'          => __( 'Идеологическая вертикаль', 'portal-core' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-groups',
			'menu_position'      => 56,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => false,
		)
	);
}
add_action( 'init', 'portal_core_register_union_org_cpt' );

function portal_core_union_org_enter_title( $title, $post ) {
	if ( $post instanceof WP_Post && 'portal_union_org' === $post->post_type ) {
		return __( 'ФИО', 'portal-core' );
	}
	return $title;
}
add_filter( 'enter_title_here', 'portal_core_union_org_enter_title', 10, 2 );

function portal_core_union_org_add_meta_boxes() {
	add_meta_box(
		'portal_union_org_details',
		__( 'Данные карточки', 'portal-core' ),
		'portal_core_union_org_metabox_render',
		'portal_union_org',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'portal_core_union_org_add_meta_boxes' );

function portal_core_union_org_metabox_render( WP_Post $post ) {
	wp_nonce_field( 'portal_union_org_save', 'portal_union_org_nonce' );
	$workplace = (string) get_post_meta( $post->ID, '_portal_iv_workplace', true );
	$position  = (string) get_post_meta( $post->ID, '_portal_iv_position', true );
	$phone     = (string) get_post_meta( $post->ID, '_portal_iv_phone', true );
	?>
	<p>
		<label for="portal_iv_workplace"><strong><?php esc_html_e( 'Место работы (учебы)', 'portal-core' ); ?></strong></label><br>
		<input type="text" class="large-text" name="portal_iv_workplace" id="portal_iv_workplace" value="<?php echo esc_attr( $workplace ); ?>">
	</p>
	<p>
		<label for="portal_iv_position"><strong><?php esc_html_e( 'Должность', 'portal-core' ); ?></strong></label><br>
		<input type="text" class="large-text" name="portal_iv_position" id="portal_iv_position" value="<?php echo esc_attr( $position ); ?>">
	</p>
	<p>
		<label for="portal_iv_phone"><strong><?php esc_html_e( 'Телефон', 'portal-core' ); ?></strong></label><br>
		<input type="text" class="regular-text" name="portal_iv_phone" id="portal_iv_phone" value="<?php echo esc_attr( $phone ); ?>">
	</p>
	<p class="description">
		<?php esc_html_e( 'Заголовок записи — ФИО. Порядок карточек на главной задаётся полем «Порядок» справа. Кнопки фильтра на главной определяются по полю «Место работы (учебы)».', 'portal-core' ); ?>
	</p>
	<?php
}

function portal_core_union_org_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['portal_union_org_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['portal_union_org_nonce'] ) ), 'portal_union_org_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'portal_union_org' ) {
		return;
	}

	$workplace = isset( $_POST['portal_iv_workplace'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_iv_workplace'] ) ) : '';
	update_post_meta( $post_id, '_portal_iv_workplace', $workplace );

	$position = isset( $_POST['portal_iv_position'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_iv_position'] ) ) : '';
	update_post_meta( $post_id, '_portal_iv_position', $position );

	$phone = isset( $_POST['portal_iv_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['portal_iv_phone'] ) ) : '';
	update_post_meta( $post_id, '_portal_iv_phone', $phone );
}
add_action( 'save_post_portal_union_org', 'portal_core_union_org_save_meta' );

function portal_core_union_org_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new[ $key ] = __( 'ФИО', 'portal-core' );
			$new['portal_iv_workplace'] = __( 'Место работы (учебы)', 'portal-core' );
			$new['portal_iv_position']  = __( 'Должность', 'portal-core' );
			$new['portal_iv_phone']     = __( 'Телефон', 'portal-core' );
			continue;
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'manage_portal_union_org_posts_columns', 'portal_core_union_org_columns' );

function portal_core_union_org_custom_column( $column, $post_id ) {
	$post_id = (int) $post_id;
	$map     = array(
		'portal_iv_workplace' => '_portal_iv_workplace',
		'portal_iv_position'  => '_portal_iv_position',
		'portal_iv_phone'     => '_portal_iv_phone',
	);
	if ( ! isset( $map[ $column ] ) ) {
		return;
	}
	$val = trim( (string) get_post_meta( $post_id, $map[ $column ], true ) );
	echo $val !== '' ? esc_html( $val ) : '—';
}
add_action( 'manage_portal_union_org_posts_custom_column', 'portal_core_union_org_custom_column', 10, 2 );

function portal_core_union_org_posts() {
	$posts = get_posts(
		array(
			'post_type'   => 'portal_union_org',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'menu_order title',
			'order'       => 'ASC',
		)
	);
	return is_array( $posts ) ? $posts : array();
}

function portal_core_iv_workplaces() {
	return array(
		'gpo-belenergo'  => 'ГПО "Белэнерго"',
		'brestenergo'    => 'РУП "Брестэнерго"',
		'vitebskenergo'  => 'РУП "Витебскэнерго"',
		'gomelenergo'    => 'РУП "Гомельэнерго"',
		'grodnoenergo'   => 'РУП "Гродноэнерго"',
		'minskenergo'    => 'РУП "Минскэнерго"',
		'mogilevenergo'  => 'РУП "Могилевэнерго"',
		'belaes'         => '"Белорусская АЭС"',
		'other'          => 'Иные организации',
		'belenergostroy' => '"Белэнергострой"',
		'metz'           => 'ОАО "Мэтз имени В.И. Козлова"',
	);
}

function portal_core_iv_normalize_workplace( $value ) {
	$value = trim( (string) $value );
	if ( $value === '' ) {
		return '';
	}
	$value = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
	$value = str_replace( array( 'ё', 'Ё' ), 'е', $value );
	$value = preg_replace( '/[«»„“”"\'′`]/u', '', $value );
	$value = preg_replace( '/\s+/u', ' ', $value );
	return is_string( $value ) ? $value : '';
}

function portal_core_iv_workplace_slug( $workplace ) {
	$normalized = portal_core_iv_normalize_workplace( $workplace );
	if ( $normalized === '' ) {
		return 'other';
	}

	foreach ( portal_core_iv_workplaces() as $slug => $label ) {
		if ( $slug === 'other' ) {
			continue;
		}
		if ( $normalized === portal_core_iv_normalize_workplace( $label ) ) {
			return $slug;
		}
	}

	if ( false !== strpos( $normalized, 'белэнергострой' ) ) {
		return 'belenergostroy';
	}
	if ( preg_match( '/мэтз|метз/u', $normalized ) ) {
		return 'metz';
	}
	if ( preg_match( '/белорусск\w*\s*аэс|белаэс|\bаэс\b/u', $normalized ) ) {
		return 'belaes';
	}
	if ( false !== strpos( $normalized, 'брестэнерго' ) ) {
		return 'brestenergo';
	}
	if ( false !== strpos( $normalized, 'витебскэнерго' ) ) {
		return 'vitebskenergo';
	}
	if ( false !== strpos( $normalized, 'гомельэнерго' ) ) {
		return 'gomelenergo';
	}
	if ( false !== strpos( $normalized, 'гродноэнерго' ) ) {
		return 'grodnoenergo';
	}
	if ( false !== strpos( $normalized, 'минскэнерго' ) ) {
		return 'minskenergo';
	}
	if ( false !== strpos( $normalized, 'могилевэнерго' ) ) {
		return 'mogilevenergo';
	}
	if ( false !== strpos( $normalized, 'белэнерго' ) || 0 === strpos( $normalized, 'гпо ' ) ) {
		return 'gpo-belenergo';
	}

	return 'other';
}

function portal_core_render_union_accordion() {
	portal_core_render_vertical_cards();
}

function portal_core_render_vertical_cards() {
	$posts = portal_core_union_org_posts();

	echo '<div class="portal-iv">';
	echo '<div class="portal-iv-filters" role="group" aria-label="' . esc_attr__( 'Место работы (учебы)', 'portal-core' ) . '">';
	echo '<button type="button" class="portal-iv-filter is-active" data-workplace="all" aria-pressed="true">' . esc_html__( 'Все', 'portal-core' ) . '</button>';
	foreach ( portal_core_iv_workplaces() as $slug => $label ) {
		echo '<button type="button" class="portal-iv-filter" data-workplace="' . esc_attr( $slug ) . '" aria-pressed="false">' . esc_html( $label ) . '</button>';
	}
	echo '</div>';

	if ( empty( $posts ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			echo '<p class="portal-widget__placeholder">' . esc_html__( 'Добавьте карточки в меню «Идеологическая вертикаль».', 'portal-core' ) . '</p>';
		}
		echo '</div>';
		return;
	}

	echo '<p class="portal-iv-empty" hidden>' . esc_html__( 'Нет карточек для выбранной организации.', 'portal-core' ) . '</p>';

	echo '<div class="portal-iv-cards">';

	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$pid       = (int) $post->ID;
		$fio       = get_the_title( $pid );
		$workplace = trim( (string) get_post_meta( $pid, '_portal_iv_workplace', true ) );
		$position  = trim( (string) get_post_meta( $pid, '_portal_iv_position', true ) );
		$phone     = trim( (string) get_post_meta( $pid, '_portal_iv_phone', true ) );
		$tel       = preg_replace( '/[^\d+]/', '', $phone );
		$slug      = portal_core_iv_workplace_slug( $workplace );
		?>
		<article class="portal-iv-card" data-workplace="<?php echo esc_attr( $slug ); ?>">
			<h3 class="portal-iv-card__name"><?php echo esc_html( $fio ); ?></h3>
			<dl class="portal-iv-card__fields">
				<div class="portal-iv-card__row">
					<dt><?php esc_html_e( 'Место работы (учебы)', 'portal-core' ); ?></dt>
					<dd><?php echo $workplace !== '' ? esc_html( $workplace ) : '—'; ?></dd>
				</div>
				<div class="portal-iv-card__row">
					<dt><?php esc_html_e( 'Должность', 'portal-core' ); ?></dt>
					<dd><?php echo $position !== '' ? esc_html( $position ) : '—'; ?></dd>
				</div>
				<div class="portal-iv-card__row">
					<dt><?php esc_html_e( 'Телефон', 'portal-core' ); ?></dt>
					<dd>
						<?php if ( $phone !== '' && $tel !== '' ) : ?>
							<a href="<?php echo esc_url( 'tel:' . $tel ); ?>"><?php echo esc_html( $phone ); ?></a>
						<?php elseif ( $phone !== '' ) : ?>
							<?php echo esc_html( $phone ); ?>
						<?php else : ?>
							—
						<?php endif; ?>
					</dd>
				</div>
			</dl>
		</article>
		<?php
	}

	echo '</div></div>';
}

function portal_core_iv_header_key( $value ) {
	$normalized = portal_core_iv_normalize_workplace( $value );
	$normalized = preg_replace( '/[.\s]+/u', '', $normalized );
	if ( ! is_string( $normalized ) || $normalized === '' ) {
		return '';
	}
	if ( preg_match( '/^(n|№|пп|пn|номер)$/u', $normalized ) ) {
		return 'num';
	}
	if ( preg_match( '/фио|фамилия|сотрудник|работник|идеолог/u', $normalized ) ) {
		return 'fio';
	}
	if ( preg_match( '/должност/u', $normalized ) ) {
		return 'position';
	}
	if ( preg_match( '/телефон|тел$|^тел|моб/u', $normalized ) ) {
		return 'phone';
	}
	if ( preg_match( '/месторобот|учебы|учеб|организац|предприят|подразделен/u', $normalized ) ) {
		return 'workplace';
	}
	return '';
}

function portal_core_iv_row_nonempty( $row ) {
	$out = array();
	if ( ! is_array( $row ) ) {
		return $out;
	}
	foreach ( $row as $cell ) {
		$cell = trim( (string) $cell );
		if ( $cell !== '' ) {
			$out[] = $cell;
		}
	}
	return $out;
}

function portal_core_iv_map_table_rows( $rows ) {
	$mapped = array();
	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return $mapped;
	}

	$index   = array(
		'fio'       => 0,
		'workplace' => 1,
		'position'  => 2,
		'phone'     => 3,
	);
	$start   = 0;
	$header  = isset( $rows[0] ) && is_array( $rows[0] ) ? $rows[0] : array();
	$found   = array();
	foreach ( $header as $col => $label ) {
		$key = portal_core_iv_header_key( $label );
		if ( $key && $key !== 'num' ) {
			$found[ $key ] = (int) $col;
		}
	}
	if ( count( $found ) >= 2 ) {
		$index = array_merge( $index, $found );
		$start = 1;
	} else {
		$first = portal_core_iv_row_nonempty( $header );
		if ( count( $first ) === 1 && count( $header ) > 1 ) {
			$index = array(
				'fio'       => 1,
				'workplace' => 2,
				'position'  => 3,
				'phone'     => 4,
			);
		}
	}

	$current_workplace = '';
	$total             = count( $rows );
	for ( $i = $start; $i < $total; $i++ ) {
		$row = $rows[ $i ];
		if ( ! is_array( $row ) ) {
			continue;
		}
		$filled = portal_core_iv_row_nonempty( $row );
		if ( empty( $filled ) ) {
			continue;
		}

		$cell = static function ( $key ) use ( $row, $index ) {
			$col = isset( $index[ $key ] ) ? (int) $index[ $key ] : -1;
			if ( $col < 0 || ! isset( $row[ $col ] ) ) {
				return '';
			}
			return trim( (string) $row[ $col ] );
		};

		if ( count( $filled ) === 1 ) {
			$only = $filled[0];
			$slug = portal_core_iv_workplace_slug( $only );
			$len = function_exists( 'mb_strlen' ) ? mb_strlen( $only, 'UTF-8' ) : strlen( $only );
			if ( $slug !== 'other' || $len > 12 ) {
				$current_workplace = $only;
			}
			continue;
		}

		$fio       = $cell( 'fio' );
		$workplace = $cell( 'workplace' );
		$position  = $cell( 'position' );
		$phone     = $cell( 'phone' );

		if ( $fio === '' && isset( $filled[0] ) ) {
			$fio = $filled[0];
		}
		if ( $fio === '' ) {
			continue;
		}
		if ( $workplace === '' ) {
			$workplace = $current_workplace;
		} else {
			$current_workplace = $workplace;
		}

		$mapped[] = array(
			'fio'       => $fio,
			'workplace' => $workplace,
			'position'  => $position,
			'phone'     => $phone,
		);
	}

	return $mapped;
}

function portal_core_iv_docx_xml_text( $xml ) {
	$xml = (string) $xml;
	$xml = preg_replace( '/<w:tab\b[^>]*\/>/i', "\t", $xml );
	$xml = preg_replace( '/<w:br\b[^>]*\/?>/i', ' ', $xml );
	$xml = preg_replace( '/<w:p [^>]*>/i', ' ', $xml );
	$text = '';
	if ( preg_match_all( '/<w:t\b[^>]*>([^<]*)<\/w:t>/u', $xml, $m ) ) {
		$text = implode( '', $m[1] );
	}
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_XML1, 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );
	return trim( (string) $text );
}

function portal_core_iv_parse_docx_tables( $path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'no_zip', __( 'На сервере недоступно чтение .docx (ZipArchive). Вставьте таблицу текстом.', 'portal-core' ) );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $path ) ) {
		return new WP_Error( 'bad_docx', __( 'Не удалось открыть Word-файл.', 'portal-core' ) );
	}
	$xml = $zip->getFromName( 'word/document.xml' );
	$zip->close();
	if ( ! is_string( $xml ) || $xml === '' ) {
		return new WP_Error( 'empty_docx', __( 'В файле нет текста.', 'portal-core' ) );
	}

	$tables = array();
	if ( ! preg_match_all( '/<w:tbl\b[\s\S]*?<\/w:tbl>/u', $xml, $tbls ) ) {
		return array();
	}
	foreach ( $tbls[0] as $tbl_xml ) {
		$rows = array();
		if ( ! preg_match_all( '/<w:tr\b[\s\S]*?<\/w:tr>/u', $tbl_xml, $trs ) ) {
			continue;
		}
		foreach ( $trs[0] as $tr_xml ) {
			$cells = array();
			if ( preg_match_all( '/<w:tc\b[\s\S]*?<\/w:tc>/u', $tr_xml, $tcs ) ) {
				foreach ( $tcs[0] as $tc_xml ) {
					$cells[] = portal_core_iv_docx_xml_text( $tc_xml );
				}
			}
			if ( ! empty( portal_core_iv_row_nonempty( $cells ) ) ) {
				$rows[] = $cells;
			}
		}
		if ( count( $rows ) > count( $tables ) ) {
			$tables = $rows;
		}
	}

	return $tables;
}

function portal_core_iv_existing_titles() {
	$titles = array();
	$posts  = get_posts(
		array(
			'post_type'   => 'portal_union_org',
			'post_status' => array( 'publish', 'draft', 'pending' ),
			'numberposts' => -1,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);
	foreach ( $posts as $post ) {
		if ( $post instanceof WP_Post ) {
			$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( $post->post_title ), 'UTF-8' ) : strtolower( trim( $post->post_title ) );
			if ( $key !== '' ) {
				$titles[ $key ] = (int) $post->ID;
			}
		}
	}
	return $titles;
}

function portal_core_iv_import_can() {
	return current_user_can( 'publish_posts' );
}

function portal_core_iv_import_enqueue() {
	if ( ! portal_core_iv_import_can() ) {
		return;
	}
	$on_front = ! is_admin() && is_front_page();
	$on_admin = false;
	if ( is_admin() ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$on_admin = $screen && isset( $screen->post_type ) && 'portal_union_org' === $screen->post_type;
	}
	if ( ! $on_front && ! $on_admin ) {
		return;
	}

	$path = PORTAL_CORE_PATH . 'assets/js/iv-import.js';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'portal-iv-import',
		PORTAL_CORE_URL . 'assets/js/iv-import.js',
		array(),
		(string) filemtime( $path ),
		true
	);
	wp_localize_script(
		'portal-iv-import',
		'portalIvImport',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'portal_iv_import' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'portal_core_iv_import_enqueue', 30 );
add_action( 'admin_enqueue_scripts', 'portal_core_iv_import_enqueue' );

function portal_core_iv_ajax_parse() {
	if ( ! portal_core_iv_import_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'portal-core' ) ), 403 );
	}
	check_ajax_referer( 'portal_iv_import', 'nonce' );

	$rows = array();
	if ( ! empty( $_FILES['file']['tmp_name'] ) && is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
		$name = isset( $_FILES['file']['name'] ) ? (string) wp_unslash( $_FILES['file']['name'] ) : '';
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( 'docx' !== $ext ) {
			wp_send_json_error( array( 'message' => __( 'Нужен файл .docx.', 'portal-core' ) ) );
		}
		$parsed = portal_core_iv_parse_docx_tables( $_FILES['file']['tmp_name'] );
		if ( is_wp_error( $parsed ) ) {
			wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
		}
		$rows = $parsed;
	} elseif ( isset( $_POST['rows'] ) ) {
		$raw = json_decode( wp_unslash( (string) $_POST['rows'] ), true );
		if ( is_array( $raw ) ) {
			$rows = $raw;
		}
	}

	$cards = portal_core_iv_map_table_rows( $rows );
	if ( empty( $cards ) ) {
		wp_send_json_error( array( 'message' => __( 'В таблице не найдены строки с ФИО.', 'portal-core' ) ) );
	}

	wp_send_json_success(
		array(
			'count' => count( $cards ),
			'cards' => $cards,
		)
	);
}
add_action( 'wp_ajax_portal_iv_parse_table', 'portal_core_iv_ajax_parse' );

function portal_core_iv_ajax_import() {
	if ( ! portal_core_iv_import_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'portal-core' ) ), 403 );
	}
	check_ajax_referer( 'portal_iv_import', 'nonce' );

	$raw = isset( $_POST['cards'] ) ? json_decode( wp_unslash( (string) $_POST['cards'] ), true ) : array();
	if ( ! is_array( $raw ) || empty( $raw ) ) {
		wp_send_json_error( array( 'message' => __( 'Нет данных для импорта.', 'portal-core' ) ) );
	}
	if ( count( $raw ) > 400 ) {
		wp_send_json_error( array( 'message' => __( 'Слишком много строк (максимум 400).', 'portal-core' ) ) );
	}

	global $wpdb;
	$max_order = 0;
	if ( $wpdb instanceof wpdb ) {
		$max_order = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_type = %s",
				'portal_union_org'
			)
		);
	}

	$existing = portal_core_iv_existing_titles();
	$created  = 0;
	$skipped  = 0;
	$errors   = 0;
	$order    = $max_order;

	foreach ( $raw as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$fio       = isset( $item['fio'] ) ? sanitize_text_field( (string) $item['fio'] ) : '';
		$workplace = isset( $item['workplace'] ) ? sanitize_text_field( (string) $item['workplace'] ) : '';
		$position  = isset( $item['position'] ) ? sanitize_text_field( (string) $item['position'] ) : '';
		$phone     = isset( $item['phone'] ) ? sanitize_text_field( (string) $item['phone'] ) : '';
		if ( $fio === '' ) {
			continue;
		}
		$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $fio, 'UTF-8' ) : strtolower( $fio );
		if ( isset( $existing[ $key ] ) ) {
			++$skipped;
			continue;
		}

		++$order;
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'portal_union_org',
				'post_status' => 'publish',
				'post_title'  => $fio,
				'menu_order'  => $order,
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			++$errors;
			continue;
		}

		update_post_meta( (int) $post_id, '_portal_iv_workplace', $workplace );
		update_post_meta( (int) $post_id, '_portal_iv_position', $position );
		update_post_meta( (int) $post_id, '_portal_iv_phone', $phone );
		$existing[ $key ] = (int) $post_id;
		++$created;
	}

	wp_send_json_success(
		array(
			'created' => $created,
			'skipped' => $skipped,
			'errors'  => $errors,
		)
	);
}
add_action( 'wp_ajax_portal_iv_import_cards', 'portal_core_iv_ajax_import' );

