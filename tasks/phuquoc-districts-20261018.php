<?php
/**
 * Задание 25: шесть новых районов острова Фукуок + кубик на /account/.
 * Запуск: wp eval-file phuquoc-districts-20261018.php --url=https://letfind.me
 * Повторный запуск безопасен.
 */
$pages = array(
	18809 => 'vietnam/phuquoc/baidai',
	18810 => 'vietnam/phuquoc/sonasea',
	18811 => 'vietnam/phuquoc/anthoi',
	18812 => 'vietnam/phuquoc/baikhem',
	18813 => 'vietnam/phuquoc/hamninh',
	18814 => 'vietnam/phuquoc/ganhdau',
);
$uris = get_option( 'permalink-manager-uris', array() );
$taken = array_flip( $uris );
foreach ( $pages as $id => $uri ) {
	if ( 'page' !== get_post_type( $id ) ) { WP_CLI::warning( "$id — не страница, пропуск" ); continue; }
	if ( isset( $taken[ $uri ] ) && (int) $taken[ $uri ] !== $id ) { WP_CLI::warning( "$uri уже занят записью {$taken[ $uri ]} — пропуск $id" ); continue; }
	$uris[ $id ] = $uri;
	WP_CLI::log( "адрес $id → /$uri/" );
}
update_option( 'permalink-manager-uris', $uris );
foreach ( array_keys( $pages ) as $id ) {
	if ( 'publish' !== get_post_status( $id ) ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		WP_CLI::log( "опубликована $id" );
	}
	WP_CLI::log( "$id: " . get_permalink( $id ) );
}
$acc = get_post( 13715 );
if ( $acc && false === strpos( $acc->post_content, '[lf_cube]' ) ) {
	wp_update_post( array( 'ID' => 13715, 'post_content' => rtrim( $acc->post_content ) . "\n\n[lf_cube]" ) );
	WP_CLI::log( 'кубик добавлен на /account/ (13715)' );
}
