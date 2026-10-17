<?php
/**
 * Хаб темы «Культура и традиции» (lf_theme 602) — 18.10.2026.
 * Запуск: wp eval-file topic-culture-20261018.php          — сухой прогон, только печатает
 *         wp eval-file topic-culture-20261018.php apply    — записывает
 * Повторный запуск безопасен: существующее не дублируется.
 * Темы материалам ставятся через wp_set_object_terms (дата «Обновлено» у материалов не меняется).
 */

$apply = in_array( 'apply', (array) ( $args ?? array() ), true );
$say   = function ( $s ) { WP_CLI::log( $s ); };
$say( $apply ? '== ЗАПИСЬ' : '== СУХОЙ ПРОГОН (добавьте apply)' );

$theme = get_term( 602, 'lf_theme' );
if ( ! $theme || is_wp_error( $theme ) || 'culture' !== $theme->slug ) { WP_CLI::error( 'Тема 602 не «culture»' ); }

// 1. Сюжеты: порядок и новый сюжет «Народы и наследие»
$plots = array( 'culture-etiquette' => 1, 'culture-holidays' => 2, 'culture-religion' => 3, 'culture-family' => 4, 'culture-heritage' => 5 );
$heritage = get_term_by( 'slug', 'culture-heritage', 'lf_theme' );
if ( ! $heritage ) {
	$say( 'сюжет  создать «Народы и наследие» (culture-heritage)' );
	if ( $apply ) {
		$r = wp_insert_term( 'Народы и наследие', 'lf_theme', array( 'slug' => 'culture-heritage', 'parent' => 602 ) );
		if ( is_wp_error( $r ) ) { WP_CLI::error( $r->get_error_message() ); }
		$heritage = get_term( $r['term_id'], 'lf_theme' );
	}
}
foreach ( $plots as $slug => $n ) {
	$t = get_term_by( 'slug', $slug, 'lf_theme' );
	if ( ! $t ) { $say( "сюжет  $slug ещё нет (будет создан)" ); continue; }
	if ( (int) $t->parent !== 602 ) { WP_CLI::error( "$slug не внутри темы 602" ); }
	$say( "сюжет  {$t->name}: порядок $n" );
	if ( $apply ) { update_term_meta( $t->term_id, 'lf_order', $n ); }
}

// 2. Материалы в сюжеты (добавляются к уже стоящим темам, ничего не снимается)
$assign = array(
	'culture-holidays'  => array( 16332 ),                     // 2 сентября — День независимости (сейчас только «История»)
	'culture-etiquette' => array( 16718, 17403 ),              // интервью «20 лет во Вьетнаме: культурный код»; куда вьетнамцы девают деньги (hụi)
	'culture-family'    => array( 7831, 17322, 16234 ),        // знакомства; лимит на двоих детей; семейный ужин-шоу
	'culture-religion'  => array( 5309, 4551, 4544 ),          // пагода Чан Куок; храм Хо Куок; пагода Су Муон
	'culture-heritage'  => array( 16920, 14268, 11584, 17015, 4934 ), // народы; Золушка; Ba Cây; барабан Нгок Лу; культурно-исторический туризм
);
foreach ( $assign as $slug => $ids ) {
	$t = get_term_by( 'slug', $slug, 'lf_theme' );
	foreach ( $ids as $id ) {
		$p = get_post( $id );
		if ( ! $p || 'publish' !== $p->post_status ) { $say( "  пропуск $id: нет или не опубликован" ); continue; }
		if ( ! is_object_in_taxonomy( $p->post_type, 'lf_theme' ) ) { $say( "  пропуск $id: тип {$p->post_type} без тем" ); continue; }
		$has = $t && has_term( $t->term_id, 'lf_theme', $id );
		$say( sprintf( '  %s %d «%s» → %s', $has ? 'уже ' : 'в    ', $id, wp_strip_all_tags( $p->post_title ), $slug ) );
		if ( $apply && $t && ! $has ) {
			$mod = array( $p->post_modified, $p->post_modified_gmt );
			wp_set_object_terms( $id, array( (int) $t->term_id ), 'lf_theme', true );
			$q = get_post( $id );
			if ( $q->post_modified !== $mod[0] ) { WP_CLI::warning( "у $id сменилась дата изменения" ); }
		}
	}
}

// 3. Поля темы
$meta = array(
	'lf_h1'    => 'Культура и традиции Вьетнама',
	'lf_intro' => 'Как во Вьетнаме принято вести себя в гостях и за столом, какие праздники закрывают офисы и рынки, во что верят, как женятся и какие народы живут в горах. Чтобы понимать, что происходит вокруг, а не только смотреть.',
	'lf_facts' => wp_json_encode( array(
		array( '54', 'народа живут во Вьетнаме, у многих свой язык, религия и архитектура', 16920, 'Где увидеть другие народы' ),
		array( '7 дней', 'отдыхают госслужащие на Тет 2027 — с 4 по 10 февраля', 17486, 'Праздники 2027 года' ),
		array( 'до 40%', 'вьетнамцев носят фамилию Нгуен', 16283, 'Почему каждый третий — Нгуен' ),
		array( '5–10%', 'чаевые в туристических ресторанах за хороший сервис — по желанию', 5628, 'Чаевые во Вьетнаме' ),
	), JSON_UNESCAPED_UNICODE ),
	'lf_paths' => wp_json_encode( array(
		array( 'Приехал в отпуск', 'Чтобы не попасть впросак в первый же день', array( 922, 5628, 16248 ) ),
		array( 'Живу или зимую', 'Календарь, Тет и почему всё делается «когда захочется»', array( 4944, 11314, 16518 ) ),
		array( 'Позвали на свадьбу или в гости', 'Что подарить, как себя вести, что будет за столом', array( 6988, 3832, 16231 ) ),
		array( 'Хочу понять глубже', 'Религия, народы и обычаи горцев', array( 13790, 16920, 18487 ) ),
	), JSON_UNESCAPED_UNICODE ),
);
foreach ( $meta as $k => $v ) {
	$say( "поле   $k = " . mb_substr( $v, 0, 90 ) . ( mb_strlen( $v ) > 90 ? '…' : '' ) );
	if ( $apply ) { update_term_meta( 602, $k, $v ); }
}

// 4. Страница-хаб /vietnam/topics/culture/
$pid  = (int) get_term_meta( 602, 'lf_page_id', true );
$page = $pid ? get_post( $pid ) : null;
if ( ! $page ) {
	$say( 'стр.   создать «Культура и традиции Вьетнама» → vietnam/topics/culture' );
	if ( $apply ) {
		$pid = wp_insert_post( array(
			'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Культура и традиции Вьетнама', 'post_name' => 'culture-topic',
			'post_content' => "<!-- wp:shortcode -->\n[lf_theme_page slug=\"culture\"]\n<!-- /wp:shortcode -->",
		), true );
		if ( is_wp_error( $pid ) ) { WP_CLI::error( $pid->get_error_message() ); }
		update_term_meta( 602, 'lf_page_id', $pid );
		$tpl = get_post_meta( 17488, '_wp_page_template', true ); // как у хаба «История»
		if ( $tpl ) { update_post_meta( $pid, '_wp_page_template', $tpl ); }
		// адрес — после wp_insert_post: Permalink Manager пишет свою копию адресов при сохранении записи (урок задания 15)
		$uris = get_option( 'permalink-manager-uris', array() );
		$uris[ $pid ] = 'vietnam/topics/culture';
		update_option( 'permalink-manager-uris', $uris );
		if ( class_exists( 'Permalink_Manager_URI_Functions' ) ) { global $permalink_manager_uris; $permalink_manager_uris = $uris; }
		$say( "стр.   создана $pid" );
	}
} else {
	$say( "стр.   уже есть $pid (" . get_permalink( $pid ) . ')' );
}

// 5. Открыть хаб: тема в lf_topics_live
$live = get_option( 'lf_topics_live', array() );
$live = is_array( $live ) ? $live : array();
if ( ! in_array( 'culture', $live, true ) ) {
	$say( 'живой  добавить culture в lf_topics_live (сейчас: ' . implode( ', ', $live ) . ')' );
	if ( $apply ) { $live[] = 'culture'; update_option( 'lf_topics_live', $live ); }
} else {
	$say( 'живой  culture уже в lf_topics_live' );
}

WP_CLI::success( $apply ? 'Готово. ID страницы-хаба: ' . $pid : 'Сухой прогон окончен.' );
