<?php
/**
 * «Дружба» на Фукуоке (objects 17439) открылась 10 октября 2026 — правка двух полей ACF, которых нет в REST:
 * SEO-текст («…и открывается 10 октября 2026 года») и вопрос в блоке «Вопросы и ответы» («Когда открывается ресторан?»).
 * Текст записи и описание AIOSEO основная сессия уже поправила через REST.
 * Запуск: wp eval-file drujba-meta-20261018.php          — сухой прогон, только печатает
 *         wp eval-file drujba-meta-20261018.php apply    — записывает
 */

$apply = in_array( 'apply', (array) ( $args ?? array() ), true );
$id    = 17439;
$map   = array(
	'Когда открывается ресторан?'         => 'Когда открылся ресторан?',
	'и открывается 10 октября 2026 года'  => 'и открылся 10 октября 2026 года',
	'открывается 10 октября 2026 года'    => 'открылся 10 октября 2026 года',
);

if ( 'objects' !== get_post_type( $id ) || false === strpos( get_the_title( $id ), 'Дружба' ) ) {
	WP_CLI::error( "Запись $id — не «Дружба»" );
}
WP_CLI::log( $apply ? '== ЗАПИСЬ' : '== СУХОЙ ПРОГОН (добавьте apply)' );

$n = 0;
foreach ( get_post_meta( $id ) as $key => $vals ) {
	if ( 1 !== count( $vals ) || is_serialized( $vals[0] ) ) {
		continue;
	}
	$old = $vals[0];
	$new = strtr( $old, $map );
	if ( $new === $old ) {
		continue;
	}
	$n++;
	WP_CLI::log( "$key\n  было:  " . mb_substr( wp_strip_all_tags( $old ), 0, 220 ) . "\n  стало: " . mb_substr( wp_strip_all_tags( $new ), 0, 220 ) );
	if ( $apply ) {
		update_post_meta( $id, $key, wp_slash( $new ) );
	}
}
if ( $apply && $n ) {
	clean_post_cache( $id );
}
WP_CLI::success( $n ? "Полей: $n" . ( $apply ? ' — записано' : '' ) : 'Нечего менять (уже поправлено?)' );
