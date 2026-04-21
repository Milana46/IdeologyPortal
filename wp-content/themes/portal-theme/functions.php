<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$portal_theme_mediabank_inc = get_template_directory() . '/inc/mediabank.php';
if ( file_exists( $portal_theme_mediabank_inc ) ) {
    require_once $portal_theme_mediabank_inc;
}

$portal_theme_ideology_inc = get_template_directory() . '/inc/ideology.php';
if ( file_exists( $portal_theme_ideology_inc ) ) {
    require_once $portal_theme_ideology_inc;
}

$portal_theme_sovetnik_inc = get_template_directory() . '/inc/sovetnik-plus.php';
if ( file_exists( $portal_theme_sovetnik_inc ) ) {
    require_once $portal_theme_sovetnik_inc;
}

$portal_theme_kse_inc = get_template_directory() . '/inc/kalendar-klyuchevyy-sobytiy.php';
if ( file_exists( $portal_theme_kse_inc ) ) {
    require_once $portal_theme_kse_inc;
}

$portal_theme_cal_merop_inc = get_template_directory() . '/inc/kalendar-meropriyatiy.php';
if ( file_exists( $portal_theme_cal_merop_inc ) ) {
    require_once $portal_theme_cal_merop_inc;
}

$portal_theme_bp_inc = get_template_directory() . '/inc/biblioteka-praktik.php';
if ( file_exists( $portal_theme_bp_inc ) ) {
    require_once $portal_theme_bp_inc;
}

$portal_theme_analytics_inc = get_template_directory() . '/inc/analytics-page.php';
if ( file_exists( $portal_theme_analytics_inc ) ) {
    require_once $portal_theme_analytics_inc;
}

add_action(
    'after_setup_theme',
    function () {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
    }
);

add_action(
    'wp_enqueue_scripts',
    function () {
        wp_enqueue_style(
            'portal-theme-style',
            get_stylesheet_uri(),
            array(),
            '0.3.0'
        );

        wp_enqueue_style(
            'portal-layout',
            get_template_directory_uri() . '/assets/css/layout.css',
            array( 'portal-theme-style' ),
            '0.3.0'
        );

        if ( is_front_page() ) {
            wp_enqueue_style(
                'portal-home',
                get_template_directory_uri() . '/assets/css/home.css',
                array( 'portal-layout' ),
                '0.3.0'
            );

            $tabs_js = get_template_directory() . '/assets/js/home-tabs.js';

            if ( file_exists( $tabs_js ) ) {
                wp_enqueue_script(
                    'portal-home-tabs',
                    get_template_directory_uri() . '/assets/js/home-tabs.js',
                    array(),
                    (string) filemtime( $tabs_js ),
                    true
                );
            }
        }

        if ( is_page( 'osnovy-ideologa' ) || is_page_template( 'page-osnovy-ideologa.php' ) ) {
            $idl_css = get_template_directory() . '/assets/css/ideology.css';
            $idl_js  = get_template_directory() . '/assets/js/ideology-materials.js';
            if ( file_exists( $idl_css ) ) {
                wp_enqueue_style(
                    'portal-ideology',
                    get_template_directory_uri() . '/assets/css/ideology.css',
                    array( 'portal-layout' ),
                    (string) filemtime( $idl_css )
                );
            }
            if ( file_exists( $idl_js ) ) {
                wp_enqueue_script(
                    'portal-ideology-materials',
                    get_template_directory_uri() . '/assets/js/ideology-materials.js',
                    array(),
                    (string) filemtime( $idl_js ),
                    true
                );
                wp_localize_script(
                    'portal-ideology-materials',
                    'portalIdeology',
                    array(
                        'isLocal' => function_exists( 'portal_theme_ideology_is_local' ) && portal_theme_ideology_is_local() ? '1' : '0',
                        'strings' => array(
                            'download'           => __( 'Скачать файл', 'portal-theme' ),
                            'previewUnavailable' => __( 'Предпросмотр недоступен. Скачайте файл или откройте сайт по публичному адресу для просмотра Office-документов.', 'portal-theme' ),
                            'readingLabel'       => __( 'Описание', 'portal-theme' ),
                        ),
                    )
                );
            }
        }

        if ( is_page_template( 'page-mediabank.php' ) ) {
            $mb_css = get_template_directory() . '/assets/css/mediabank.css';
            $mb_js  = get_template_directory() . '/assets/js/mediabank.js';

            if ( file_exists( $mb_css ) ) {
                wp_enqueue_style(
                    'portal-mediabank',
                    get_template_directory_uri() . '/assets/css/mediabank.css',
                    array( 'portal-layout' ),
                    (string) filemtime( $mb_css )
                );
            }

            if ( file_exists( $mb_js ) ) {
                wp_enqueue_script(
                    'portal-mediabank',
                    get_template_directory_uri() . '/assets/js/mediabank.js',
                    array(),
                    (string) filemtime( $mb_js ),
                    true
                );
            }
        }

        if ( is_page_template( 'page-kalendar-meropriyatiy.php' ) ) {
            $cal_css = get_template_directory() . '/assets/css/calendar-page.css';
            $cal_js  = get_template_directory() . '/assets/js/calendar-page.js';

            if ( file_exists( $cal_css ) ) {
                wp_enqueue_style(
                    'portal-calendar-page',
                    get_template_directory_uri() . '/assets/css/calendar-page.css',
                    array( 'portal-layout' ),
                    (string) filemtime( $cal_css )
                );
            }

            if ( file_exists( $cal_js ) ) {
                wp_enqueue_script(
                    'portal-calendar-page',
                    get_template_directory_uri() . '/assets/js/calendar-page.js',
                    array(),
                    (string) filemtime( $cal_js ),
                    true
                );
                $cal_items = function_exists( 'portal_theme_cal_merop_collect_for_js' )
                    ? portal_theme_cal_merop_collect_for_js()
                    : array();
                wp_localize_script(
                    'portal-calendar-page',
                    'portalCalendarMerop',
                    array(
                        'events'  => $cal_items,
                        'strings' => array(
                            'close'         => __( 'Закрыть', 'portal-theme' ),
                            'modalTitle'    => __( 'События на дату', 'portal-theme' ),
                            'sectionState'  => __( 'Государственные праздники', 'portal-theme' ),
                            'sectionMerop'  => __( 'Мероприятия', 'portal-theme' ),
                            'noHolidayDay'  => __( 'На эту дату нет государственных праздников.', 'portal-theme' ),
                            'noMeropDay'    => __( 'На эту дату нет мероприятий.', 'portal-theme' ),
                            'emptyHolidays' => __( 'Нет ближайших праздников по запросу.', 'portal-theme' ),
                            'emptyMerop'    => __( 'Нет ближайших мероприятий по запросу.', 'portal-theme' ),
                        ),
                    )
                );
            }
        }

        if ( is_page_template( 'page-sovetnik-plus.php' ) ) {
            $sov_css = get_template_directory() . '/assets/css/sovetnik-plus.css';
            $sov_js  = get_template_directory() . '/assets/js/sovetnik-plus.js';

            if ( file_exists( $sov_css ) ) {
                wp_enqueue_style(
                    'portal-sovetnik-plus',
                    get_template_directory_uri() . '/assets/css/sovetnik-plus.css',
                    array( 'portal-layout' ),
                    (string) filemtime( $sov_css )
                );
            }

            if ( file_exists( $sov_js ) ) {
                wp_enqueue_script(
                    'portal-sovetnik-plus',
                    get_template_directory_uri() . '/assets/js/sovetnik-plus.js',
                    array(),
                    (string) filemtime( $sov_js ),
                    true
                );
                wp_localize_script(
                    'portal-sovetnik-plus',
                    'portalSovetnik',
                    array(
                        'isLocal' => function_exists( 'portal_theme_ideology_is_local' ) && portal_theme_ideology_is_local() ? '1' : '0',
                        'strings' => array(
                            'download'           => __( 'Скачать файл', 'portal-theme' ),
                            'previewUnavailable' => __( 'Предпросмотр недоступен. Скачайте файл или откройте сайт по публичному адресу для просмотра Office-документов.', 'portal-theme' ),
                            'readingLabel'       => __( 'Описание', 'portal-theme' ),
                        ),
                    )
                );
            }
        }

        if ( is_page_template( 'page-biblioteka-praktik.php' ) ) {
            $kse_shared = get_template_directory() . '/assets/css/kalendar-klyuchevyy-sobytiy.css';
            if ( file_exists( $kse_shared ) ) {
                wp_enqueue_style(
                    'portal-kalendar-klyuchevyy-sobytiy',
                    get_template_directory_uri() . '/assets/css/kalendar-klyuchevyy-sobytiy.css',
                    array( 'portal-layout' ),
                    (string) filemtime( $kse_shared )
                );
            }

            $bp_css = get_template_directory() . '/assets/css/biblioteka-praktik.css';
            $bp_js  = get_template_directory() . '/assets/js/biblioteka-praktik.js';

            $bp_style_deps = array( 'portal-layout' );
            if ( file_exists( $kse_shared ) ) {
                $bp_style_deps[] = 'portal-kalendar-klyuchevyy-sobytiy';
            }

            if ( file_exists( $bp_css ) ) {
                wp_enqueue_style(
                    'portal-biblioteka-praktik',
                    get_template_directory_uri() . '/assets/css/biblioteka-praktik.css',
                    $bp_style_deps,
                    (string) filemtime( $bp_css )
                );
            }

            if ( file_exists( $bp_js ) ) {
                wp_enqueue_script(
                    'portal-biblioteka-praktik',
                    get_template_directory_uri() . '/assets/js/biblioteka-praktik.js',
                    array(),
                    (string) filemtime( $bp_js ),
                    true
                );
            }
        }

        if ( is_page_template( 'page-kalendar-klyuchevyy-sobytiy.php' ) ) {
            $kse_css = get_template_directory() . '/assets/css/kalendar-klyuchevyy-sobytiy.css';
            $kse_js  = get_template_directory() . '/assets/js/kalendar-klyuchevyy-sobytiy.js';

            if ( file_exists( $kse_css ) ) {
                wp_enqueue_style(
                    'portal-kalendar-klyuchevyy-sobytiy',
                    get_template_directory_uri() . '/assets/css/kalendar-klyuchevyy-sobytiy.css',
                    array( 'portal-layout' ),
                    (string) filemtime( $kse_css )
                );
            }

            if ( file_exists( $kse_js ) ) {
                wp_enqueue_script(
                    'portal-kalendar-klyuchevyy-sobytiy',
                    get_template_directory_uri() . '/assets/js/kalendar-klyuchevyy-sobytiy.js',
                    array(),
                    (string) filemtime( $kse_js ),
                    true
                );
                $kse_events = function_exists( 'portal_theme_kse_collect_events_for_js' )
                    ? portal_theme_kse_collect_events_for_js()
                    : array();
                wp_localize_script(
                    'portal-kalendar-klyuchevyy-sobytiy',
                    'portalKse',
                    array(
                        'events'  => $kse_events,
                        'strings' => array(
                            'close'         => __( 'Закрыть', 'portal-theme' ),
                            'noEvents'      => __( 'На эту дату нет событий, подходящих под выбранные фильтры.', 'portal-theme' ),
                            'upcomingEmpty' => __( 'На текущей неделе нет событий. Измените поиск или фильтр.', 'portal-theme' ),
                            'typeVideo'     => __( 'Видеоконференция', 'portal-theme' ),
                            'typeDocs'      => __( 'Подача документов', 'portal-theme' ),
                        ),
                    )
                );
            }
        }

    }
);

/**
 * Группа формата для фильтра: document | presentation | video | image.
 *
 * @param string      $mime MIME-тип.
 * @param string|null $path Путь к файлу (для расширения).
 */
function portal_theme_bp_format_group_from_mime( $mime, $path = null ) {
	$mime = is_string( $mime ) ? $mime : '';
	$ext  = is_string( $path ) && $path !== '' ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';

	if ( preg_match( '#^image/#', $mime ) || in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg' ), true ) ) {
		return 'image';
	}
	if ( preg_match( '#^video/#', $mime ) || in_array( $ext, array( 'mp4', 'webm', 'ogg', 'ogv', 'mov', 'm4v' ), true ) ) {
		return 'video';
	}
	if ( preg_match( '/presentation|powerpoint|opendocument\.presentation/', $mime ) || in_array( $ext, array( 'ppt', 'pptx', 'odp', 'pps', 'ppsx' ), true ) ) {
		return 'presentation';
	}
	return 'document';
}

/**
 * Группа формата по имени файла (материалы из папки темы).
 *
 * @param string $filename Имя файла.
 */
function portal_theme_bp_format_group_from_filename( $filename ) {
	if ( ! is_string( $filename ) || $filename === '' ) {
		return 'document';
	}
	return portal_theme_bp_format_group_from_mime( '', $filename );
}

/**
 * Режим просмотра в модальном окне.
 *
 * @param string $format_group document|presentation|video|image.
 * @param string $mime         MIME.
 * @param bool   $is_local     Локальный хост.
 * @param string $pdf_url      URL PDF (если есть).
 * @param string $doc_url      URL основного файла.
 */
function portal_theme_bp_media_viewer_mode( $format_group, $mime, $is_local, $pdf_url, $doc_url ) {
	if ( 'image' === $format_group ) {
		return 'image';
	}
	if ( 'video' === $format_group ) {
		return 'video';
	}
	if ( $mime === 'application/pdf' || ( $pdf_url && $pdf_url === $doc_url ) ) {
		return 'pdf';
	}
	if ( $pdf_url && $pdf_url !== '' ) {
		return 'pdf';
	}
	if ( $doc_url && ! $is_local && ( 'document' === $format_group || 'presentation' === $format_group ) ) {
		return 'office';
	}
	return 'fallback';
}

/**
 * Локальный хост (Office Online недоступен).
 */
function portal_theme_ideology_is_local() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$home_host = is_string( $home_host ) ? strtolower( $home_host ) : '';
	$is_local  = $home_host && in_array( $home_host, array( 'localhost', '127.0.0.1', '::1' ), true );
	if ( ! $is_local && $home_host ) {
		$hl = strlen( $home_host );
		if ( $hl > 6 && substr( $home_host, -6 ) === '.local' ) {
			$is_local = true;
		} elseif ( $hl > 5 && substr( $home_host, -5 ) === '.test' ) {
			$is_local = true;
		}
	}
	$cached = $is_local;
	return $cached;
}

/**
 * Данные для модального просмотра материала «Основы идеолога».
 *
 * @param array<string,mixed> $item Строка материала.
 * @return array<string,mixed>
 */
function portal_theme_ideology_build_open_data( array $item ) {
	$title   = isset( $item['title'] ) ? (string) $item['title'] : '';
	$excerpt = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';
	$att_id  = isset( $item['attachment_id'] ) ? (int) $item['attachment_id'] : 0;

	$doc_url   = '';
	$pdf_url   = '';
	$file_name = '';
	$mime      = '';
	$viewer    = 'reading';
	$is_local  = portal_theme_ideology_is_local();

	if ( $att_id > 0 && get_post( $att_id ) && get_post_type( $att_id ) === 'attachment' ) {
		$doc_url = wp_get_attachment_url( $att_id );
		if ( $doc_url ) {
			$path = get_attached_file( $att_id );
			$file_name = is_string( $path ) ? basename( $path ) : '';
			$mime      = get_post_mime_type( $att_id );
			$mime      = is_string( $mime ) ? $mime : '';
			if ( 'application/pdf' === $mime ) {
				$pdf_url = $doc_url;
			}
			$format = function_exists( 'portal_theme_bp_format_group_from_mime' )
				? portal_theme_bp_format_group_from_mime( $mime, is_string( $path ) ? $path : null )
				: 'document';
			$viewer = function_exists( 'portal_theme_bp_media_viewer_mode' )
				? portal_theme_bp_media_viewer_mode( $format, $mime, $is_local, $pdf_url, $doc_url )
				: 'reading';
		}
	}

	return array(
		'title'    => $title,
		'excerpt'  => $excerpt,
		'viewer'   => $viewer,
		'docUrl'   => $doc_url,
		'pdfUrl'   => $pdf_url,
		'fileName' => $file_name,
		'isLocal'  => $is_local,
	);
}

/**
 * Разметка карточки материала (статический файл темы или вложение).
 *
 * @param array<string,mixed> $args Аргументы.
 * @return string
 */
function portal_theme_bp_render_material_card( array $args ) {
	$defaults = array(
		'title'          => '',
		'excerpt'        => '',
		'category'       => 'templates',
		'format'         => '',
		'img_card'       => 'icon_book.png',
		'theme_img_base' => '',
		'filename'       => '',
		'attachment_id'  => 0,
		'order'          => 1,
		'is_local'       => null,
		'thumb_override' => '',
	);
	$a = wp_parse_args( $args, $defaults );

	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$home_host = is_string( $home_host ) ? strtolower( $home_host ) : '';
	$is_local  = $a['is_local'];
	if ( null === $is_local ) {
		$is_local = $home_host && in_array( $home_host, array( 'localhost', '127.0.0.1', '::1' ), true );
		if ( ! $is_local && $home_host ) {
			$hl = strlen( $home_host );
			if ( $hl > 6 && substr( $home_host, -6 ) === '.local' ) {
				$is_local = true;
			} elseif ( $hl > 5 && substr( $home_host, -5 ) === '.test' ) {
				$is_local = true;
			}
		}
	}

	$doc_plain  = '';
	$pdf_plain  = '';
	$file_label = '';
	$mime       = '';
	$path       = '';

	if ( ! empty( $a['attachment_id'] ) ) {
		$doc_plain = wp_get_attachment_url( (int) $a['attachment_id'] );
		$path      = get_attached_file( (int) $a['attachment_id'] );
		$file_label = is_string( $path ) ? basename( $path ) : '';
		$mime       = get_post_mime_type( (int) $a['attachment_id'] );
		$mime       = is_string( $mime ) ? $mime : '';
		if ( $mime === 'application/pdf' && $doc_plain ) {
			$pdf_plain = $doc_plain;
		}
	} elseif ( ! empty( $a['filename'] ) && function_exists( 'portal_theme_bp_resolve_doc' ) ) {
		$res        = portal_theme_bp_resolve_doc( $a['filename'], $is_local );
		$doc_plain  = $res['doc'];
		$pdf_plain  = $res['pdf'];
		$file_label = $a['filename'];
	}

	$format_group = '';
	if ( isset( $a['format'] ) && is_string( $a['format'] ) && $a['format'] !== '' ) {
		$format_group = $a['format'];
	}
	if ( $format_group === '' && ! empty( $a['attachment_id'] ) ) {
		$format_group = portal_theme_bp_format_group_from_mime( $mime, is_string( $path ) ? $path : null );
	}
	if ( $format_group === '' && ! empty( $a['filename'] ) ) {
		$format_group = portal_theme_bp_format_group_from_filename( $a['filename'] );
	}
	if ( $format_group === '' ) {
		$format_group = 'document';
	}

	$viewer = $doc_plain
		? portal_theme_bp_media_viewer_mode( $format_group, $mime, $is_local, $pdf_plain, $doc_plain )
		: 'fallback';

	$thumb_img_url = '';
	if ( ! empty( $a['thumb_override'] ) && is_string( $a['thumb_override'] ) ) {
		$thumb_img_url = $a['thumb_override'];
	} elseif ( 'image' === $format_group && ! empty( $a['attachment_id'] ) && $doc_plain ) {
		$t = wp_get_attachment_image_url( (int) $a['attachment_id'], 'medium' );
		$thumb_img_url = $t ? $t : $doc_plain;
	}

	$title_t    = $a['title'];
	$excerpt_t  = $a['excerpt'];
	$cat        = $a['category'];
	$raw_s      = wp_strip_all_tags( $title_t . ' ' . $excerpt_t );
	$search_idx = function_exists( 'mb_strtolower' ) ? mb_strtolower( $raw_s, 'UTF-8' ) : strtolower( $raw_s );

	$open_attrs = '';
	if ( $doc_plain ) {
		$open_attrs = sprintf(
			' data-doc-url="%s" data-pdf-url="%s" data-doc-title="%s" data-doc-name="%s" data-media-viewer="%s"',
			esc_attr( $doc_plain ),
			esc_attr( $pdf_plain ),
			esc_attr( $title_t ),
			esc_attr( $file_label ),
			esc_attr( $viewer )
		);
	}

	ob_start();
	?>
	<article class="bp-card" data-bp-category="<?php echo esc_attr( $cat ); ?>" data-bp-search="<?php echo esc_attr( $search_idx ); ?>" data-bp-order="<?php echo esc_attr( (string) (int) $a['order'] ); ?>">
		<div class="bp-card__thumb bp-card__thumb--img" aria-hidden="true">
			<?php if ( $thumb_img_url ) : ?>
				<img src="<?php echo esc_url( $thumb_img_url ); ?>" alt="">
			<?php else : ?>
				<img src="<?php echo esc_url( $a['theme_img_base'] . '/' . $a['img_card'] ); ?>" alt="">
			<?php endif; ?>
		</div>
		<div class="bp-card__main">
			<div class="bp-card__title-row">
				<span class="bp-type-dot bp-type-dot--<?php echo esc_attr( $cat ); ?>"></span>
				<h3 class="bp-card__title"><?php echo esc_html( $title_t ); ?></h3>
			</div>
			<p class="bp-card__excerpt"><?php echo esc_html( $excerpt_t ); ?></p>
		</div>
		<div class="bp-card__action">
			<?php if ( $doc_plain ) : ?>
				<button type="button" class="bp-btn bp-btn--green bp-open-doc"<?php echo $open_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php esc_html_e( 'Открыть', 'portal-theme' ); ?>
				</button>
				<a href="<?php echo esc_url( $doc_plain ); ?>" class="bp-btn bp-btn--outline bp-download-direct" download="<?php echo esc_attr( $file_label ); ?>"><?php esc_html_e( 'Скачать', 'portal-theme' ); ?></a>
			<?php else : ?>
				<span class="bp-btn bp-btn--green bp-btn--disabled"><?php esc_html_e( 'Открыть', 'portal-theme' ); ?></span>
			<?php endif; ?>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Карточка из записи portal_bp_material.
 *
 * @param WP_Post $post      Запись.
 * @param string  $theme_img URI каталога assets/img темы.
 * @param bool    $is_local  Локальный хост.
 * @return string
 */
function portal_theme_bp_render_material_card_from_post( $post, $theme_img, $is_local ) {
	if ( ! $post instanceof WP_Post || 'portal_bp_material' !== $post->post_type ) {
		return '';
	}
	$pid = (int) $post->ID;
	$fid = (int) get_post_meta( $pid, '_portal_bp_file_id', true );
	if ( $fid <= 0 ) {
		return '';
	}
	$cat = get_post_meta( $pid, '_portal_bp_category', true );
	$cat = is_string( $cat ) ? sanitize_key( $cat ) : 'templates';
	if ( function_exists( 'portal_theme_bp_category_slugs' ) && ! in_array( $cat, portal_theme_bp_category_slugs(), true ) ) {
		$cat = 'templates';
	}
	$mime = get_post_mime_type( $fid );
	$path = get_attached_file( $fid );
	$fmt  = portal_theme_bp_format_group_from_mime( is_string( $mime ) ? $mime : '', is_string( $path ) ? $path : null );

	$thumb_override = '';
	if ( has_post_thumbnail( $post ) ) {
		$tu = get_the_post_thumbnail_url( $post, 'medium' );
		if ( is_string( $tu ) && $tu !== '' ) {
			$thumb_override = $tu;
		}
	}

	$order = (int) get_post_time( 'U', true, $post );
	if ( $order < 1 ) {
		$order = time();
	}

	$excerpt = $post->post_excerpt;
	if ( ! is_string( $excerpt ) ) {
		$excerpt = '';
	}

	return portal_theme_bp_render_material_card(
		array(
			'title'          => get_the_title( $post ),
			'excerpt'        => $excerpt,
			'category'       => $cat,
			'format'         => $fmt,
			'img_card'       => 'icon_book.png',
			'theme_img_base' => $theme_img,
			'attachment_id'  => $fid,
			'order'          => $order,
			'is_local'       => $is_local,
			'thumb_override' => $thumb_override,
		)
	);
}

/**
 * HTML карточки материала «Основы идеолога».
 *
 * @param array<string,mixed> $item Поля из portal_theme_ideology_post_to_item_array(): title, excerpt, category, attachment_id, thumb_url, id.
 */
function portal_theme_ideology_render_card( array $item ) {
	$allowed = array( 'symbolika', 'akty', 'pasport' );
	$cat     = isset( $item['category'] ) ? sanitize_key( $item['category'] ) : 'akty';
	if ( ! in_array( $cat, $allowed, true ) ) {
		$cat = 'akty';
	}
	$title_t   = isset( $item['title'] ) ? (string) $item['title'] : '';
	$excerpt_t = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';

	$labels = array(
		'symbolika' => __( 'Государственная символика', 'portal-theme' ),
		'akty'      => __( 'Акты', 'portal-theme' ),
		'pasport'   => __( 'Социальный паспорт предприятия', 'portal-theme' ),
	);
	$img_alt = isset( $labels[ $cat ] ) ? $labels[ $cat ] : __( 'Материал', 'portal-theme' );

	$raw_s      = wp_strip_all_tags( $title_t . ' ' . $excerpt_t );
	$search_idx = function_exists( 'mb_strtolower' ) ? mb_strtolower( $raw_s, 'UTF-8' ) : strtolower( $raw_s );

	$theme_img   = get_template_directory_uri() . '/assets/img';
	$img_default = ( 'symbolika' === $cat ? 'card_flag.png' : 'card_doc.png' );
	$thumb_url   = isset( $item['thumb_url'] ) && is_string( $item['thumb_url'] ) ? $item['thumb_url'] : '';
	$img_src     = $thumb_url !== '' ? $thumb_url : $theme_img . '/' . $img_default;

	$open_data = portal_theme_ideology_build_open_data( $item );
	$open_json = wp_json_encode( $open_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	ob_start();
	?>
	<article class="ideology-card" data-ideology-category="<?php echo esc_attr( $cat ); ?>" data-ideology-search="<?php echo esc_attr( $search_idx ); ?>">
		<div class="ideology-card__image">
			<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>">
		</div>
		<div class="ideology-card__main">
			<div class="ideology-card__content">
				<h2><?php echo esc_html( $title_t ); ?></h2>
				<p><?php echo esc_html( $excerpt_t ); ?></p>
			</div>
			<div class="ideology-card__meta" aria-hidden="true">
				<span></span><span></span><span></span>
			</div>
		</div>
		<div class="ideology-card__footer">
			<button type="button" class="portal-btn portal-btn--green ideology-card__open" data-open="<?php echo esc_attr( $open_json ); ?>">
				<?php esc_html_e( 'Открыть', 'portal-theme' ); ?>
			</button>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}
