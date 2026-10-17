<?php
/**
 * Разделы заведений и мест для всех городов (18.10.2026): страницы для lf-venue-hubs.php там, где сейчас 404
 * (/vietnam/<город>/<группа>/ и /vietnam/<город>/<группа>/<тип>/ — от 3 мест). Запуск: wp eval-file venue-hubs-20261018.php --url=https://letfind.me
 * Повторный запуск безопасен: раздел с таким _lf_vhub уже есть — пропускается. Ещё: 6 мест с опечаткой в адресе vietnam/muyne/ → vietnam/muine/.
 * Откат: удалить созданные страницы (их ID печатаются) и вернуть адреса muyne из вывода.
 */
$keys = array(
	'cantho/food',
	'cantho/food/bars',
	'condao/food',
	'condao/food/bars',
	'dalat/food',
	'dalat/food/bars',
	'danang/attractions/beaches',
	'danang/attractions/bridges',
	'danang/attractions/districts',
	'danang/attractions/museums',
	'danang/attractions/parks',
	'danang/attractions/temples',
	'danang/shopping',
	'danang/shopping/malls',
	'danang/venues',
	'danang/venues/themes',
	'donghoi/food',
	'donghoi/food/bars',
	'haiphong/food',
	'haiphong/food/bars',
	'halong/food',
	'halong/food/bars',
	'hanoi/attractions',
	'hanoi/attractions/museums',
	'hanoi/attractions/temples',
	'hanoi/food',
	'hanoi/food/bars',
	'hochiminh/attractions',
	'hochiminh/attractions/museums',
	'hochiminh/attractions/parks',
	'hochiminh/attractions/temples',
	'hochiminh/food',
	'hochiminh/food/bars',
	'hoian/attractions',
	'hoian/attractions/temples',
	'hoian/attractions/villages',
	'hoian/food',
	'hoian/food/bars',
	'hue/food',
	'hue/food/bars',
	'muine/food',
	'muine/food/bars',
	'nhatrang/attractions',
	'nhatrang/attractions/beaches',
	'nhatrang/attractions/islands',
	'nhatrang/attractions/landmarks',
	'nhatrang/attractions/museums',
	'nhatrang/attractions/temples',
	'nhatrang/food',
	'nhatrang/food/bars',
	'nhatrang/food/cafe',
	'nhatrang/food/restaurants',
	'nhatrang/venues',
	'ninhbinh/food',
	'ninhbinh/food/bars',
	'phuquoc/attractions/beaches',
	'phuquoc/attractions/farms',
	'phuquoc/attractions/parks',
	'phuquoc/food/restaurants',
	'quynhon/food',
	'quynhon/food/bars',
	'sapa/food',
	'sapa/food/bars',
	'vungtau/food',
	'vungtau/food/bars'
);
// город => [где («в Халонге»), чего («Халонга»)]
$city = array(
	'cantho' => array( 'в Кантхо', 'Кантхо' ), 'condao' => array( 'на Кондао', 'Кондао' ), 'dalat' => array( 'в Далате', 'Далата' ),
	'danang' => array( 'в Дананге', 'Дананга' ), 'donghoi' => array( 'в Донгхое', 'Донгхоя' ), 'haiphong' => array( 'в Хайфоне', 'Хайфона' ),
	'halong' => array( 'в Халонге', 'Халонга' ), 'hanoi' => array( 'в Ханое', 'Ханоя' ), 'hochiminh' => array( 'в Хошимине', 'Хошимина' ),
	'hoian' => array( 'в Хойане', 'Хойана' ), 'hue' => array( 'в Хюэ', 'Хюэ' ), 'muine' => array( 'в Муйне', 'Муйне' ),
	'nhatrang' => array( 'в Нячанге', 'Нячанга' ), 'ninhbinh' => array( 'в Ниньбине', 'Ниньбиня' ), 'phuquoc' => array( 'на Фукуоке', 'острова Фукуок' ),
	'quynhon' => array( 'в Куинёне', 'Куинёна' ), 'sapa' => array( 'в Сапе', 'Сапы' ), 'vungtau' => array( 'в Вунгтау', 'Вунгтау' ),
);
$labels = array( 'food' => 'Еда', 'restaurants' => 'Рестораны', 'bars' => 'Бары', 'street-food' => 'Уличная еда', 'cafes' => 'Кафе', 'cafe' => 'Кафе',
	'venues' => 'Развлечения', 'clubs' => 'Клубы', 'themes' => 'Тематические места', 'attractions' => 'Достопримечательности', 'beaches' => 'Пляжи',
	'farms' => 'Фермы', 'parks' => 'Парки', 'museums' => 'Музеи', 'temples' => 'Храмы', 'landmarks' => 'Знаковые места', 'villages' => 'Деревни',
	'districts' => 'Районы', 'bridges' => 'Мосты', 'islands' => 'Острова', 'shopping' => 'Шопинг', 'malls' => 'Торговые центры' );
$group_title = array(
	'food'        => array( 'Еда %1$s', 'Где поесть %1$s: рестораны, кафе и бары с адресами, маршрутом и описанием каждого места.' ),
	'attractions' => array( 'Достопримечательности %2$s', 'Что посмотреть %1$s: адреса, как добраться и описание каждого места.' ),
	'venues'      => array( 'Развлечения %2$s', 'Где развлечься %1$s: адреса, маршрут и описание каждого места.' ),
	'shopping'    => array( 'Шопинг %1$s', 'Где за покупками %1$s: торговые центры и рынки с адресами и маршрутом.' ),
);

// 1) опечатка в адресах: vietnam/muyne/ → vietnam/muine/
$uris = get_option( 'permalink-manager-uris', array() );
foreach ( $uris as $id => $u ) {
	if ( 0 === strpos( (string) $u, 'vietnam/muyne/' ) ) {
		$new = 'vietnam/muine/' . substr( $u, strlen( 'vietnam/muyne/' ) );
		WP_CLI::log( "адрес\t$id\t/$u/ → /$new/" );
		$uris[ $id ] = $new;
	}
}
update_option( 'permalink-manager-uris', $uris );

// 2) страницы разделов
$made = 0;
foreach ( $keys as $key ) {
	$p = explode( '/', $key ); $c = $p[0]; $g = $p[1]; $t = $p[2] ?? '';
	if ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'meta_key' => '_lf_vhub', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ) ) ) {
		WP_CLI::log( "есть\t$key" ); continue;
	}
	if ( empty( $city[ $c ] ) ) { WP_CLI::warning( "нет названия города: $key" ); continue; }
	if ( $t ) {
		$lab = $labels[ $t ] ?? $t;
		$title = $lab . ' ' . $city[ $c ][1];
		$intro = $lab . ' ' . $city[ $c ][1] . ': адреса, маршрут и описание каждого места.';
	} else {
		$f = $group_title[ $g ] ?? array( ( $labels[ $g ] ?? $g ) . ' %2$s', '%1$s: адреса, маршрут и описание каждого места.' );
		$title = sprintf( $f[0], $city[ $c ][0], $city[ $c ][1] );
		$intro = sprintf( $f[1], $city[ $c ][0], $city[ $c ][1] );
	}
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $c . '-' . ( $t ?: $g ), 'post_content' => $intro, 'meta_input' => array( '_lf_vhub' => $key ) ), true );
	if ( is_wp_error( $id ) ) { WP_CLI::warning( "$key: " . $id->get_error_message() ); continue; }
	$uris = get_option( 'permalink-manager-uris', array() );
	$uris[ $id ] = 'vietnam/' . $key;
	update_option( 'permalink-manager-uris', $uris );
	WP_CLI::log( "создан\t$id\t/vietnam/$key/\t$title" );
	$made++;
}
if ( function_exists( 'lfvh_flush' ) ) { lfvh_flush(); }
WP_CLI::success( "Создано разделов: $made из " . count( $keys ) . '. Сбросьте кэш SiteGround.' );
