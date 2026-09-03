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
			'numberposts' => 100,
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
