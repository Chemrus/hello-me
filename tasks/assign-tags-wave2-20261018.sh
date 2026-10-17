#!/bin/bash
# Вторая волна меток: 9 меток на 50 материалов (61 простановка), 18.10.2026. wp post term add не пересохраняет запись — дата «Обновлено» не меняется.
set -u
U=--url=https://letfind.me
before=$(wp db query "SELECT CONCAT(COUNT(*),'-',SUM(UNIX_TIMESTAMP(post_modified)),'-',MAX(post_modified)) FROM $(wp db prefix $U)posts WHERE ID IN (884,1523,1531,1822,2372,4082,4364,4643,5197,5733,6268,7866,9063,9218,10325,10851,11000,11082,11174,11543,11951,11975,12448,13500,13638,13718,14780,14812,14927,15022,16254,16311,16527,16531,16567,16665,16860,16888,17019,17026,17090,17494,17846,17851,17855,17857,17859,17863,17894,17896)" $U --skip-column-names)

wp post term add 884 post_tag atm --by=slug $U  # Полезная информация о Вьетнаме
wp post term add 1523 post_tag cambodia --by=slug $U  # Путешествие из острова Фукуок в Камбоджу
wp post term add 1531 post_tag thailand --by=slug $U  # Таиланд: Рай для путешественников
wp post term add 1822 post_tag atm --by=slug $U  # Обмен валюты во Вьетнаме: классические и современные способы
wp post term add 2372 post_tag sim-esim --by=slug $U  # Как подготовиться к поездке во Вьетнам: главное
wp post term add 4082 post_tag thailand --by=slug $U  # Таиланд тестирует оплату криптовалютой
wp post term add 4364 post_tag laos visa-run --by=slug $U  # Визаран из Дананга в Лаос: личный опыт
wp post term add 4643 post_tag atm --by=slug $U  # Как оплачивать покупки и услуги во Вьетнаме
wp post term add 5197 post_tag thailand --by=slug $U  # Взрослые развлечения во Вьетнаме: практическое руководство
wp post term add 5733 post_tag atm cambodia --by=slug $U  # Все про онлайн-мошенничество и скам во Вьетнаме
wp post term add 6268 post_tag vapes --by=slug $U  # Новые штрафы за электронные сигареты и вейпы
wp post term add 7866 post_tag atm sim-esim --by=slug $U  # Как обменять деньги в Дананге: полный гид на 2026 год
wp post term add 9063 post_tag atm sim-esim --by=slug $U  # Обмен валюты, крипты на острове Фукуок: самое важное на 2026
wp post term add 9218 post_tag cambodia --by=slug $U  # История острова Фукуок: самое главное
wp post term add 10325 post_tag vapes --by=slug $U  # Запрет на ввоз вейпов, кальянов во Вьетнам
wp post term add 10851 post_tag apostille visa-run --by=slug $U  # Вьетнамская виза для IT и digital‑специалистов
wp post term add 11000 post_tag visa-run --by=slug $U  # Переезд, зимовка, отдых во Вьетнаме
wp post term add 11082 post_tag apostille e-arrival visa-run --by=slug $U  # Гайд по легальной жизни и работе во Вьетнаме
wp post term add 11174 post_tag apostille --by=slug $U  # Справка о несудимости во Вьетнаме
wp post term add 11543 post_tag laos --by=slug $U  # Азиатская визовая революция 2026 года
wp post term add 11951 post_tag atm --by=slug $U  # Как получить дебетовую карту во Вьетнаме
wp post term add 11975 post_tag cambodia --by=slug $U  # История иностранного криминала в Камбодже
wp post term add 12448 post_tag thailand --by=slug $U  # Таиланд готовится сократить безвизовый срок
wp post term add 13500 post_tag apostille cambodia thailand --by=slug $U  # Где релокантам хорошо, а где не очень: паспорт РФ
wp post term add 13638 post_tag vapes --by=slug $U  # Декрет 90/2026 о штрафах за электронные сигареты
wp post term add 13718 post_tag visa-run --by=slug $U  # Поставили не тот штамп на визаране: пошаговая инструкция
wp post term add 14780 post_tag e-arrival --by=slug $U  # Электронная карта прибытия во Вьетнам: главное
wp post term add 14812 post_tag e-arrival --by=slug $U  # Гайд по получению QR-кода для въезда во Вьетнам
wp post term add 14927 post_tag cambodia --by=slug $U  # Как Камбоджа превратилась в криминальный хаб Азии и почему э
wp post term add 15022 post_tag vapes --by=slug $U  # Резолюция 173/2024/QH15: запрет вейпов и IQOS
wp post term add 16254 post_tag visa-run --by=slug $U  # Вся легальная жизнь во Вьетнаме на одном экране
wp post term add 16311 post_tag laos --by=slug $U  # ЛАОС НОВЫЙ ЛОГИСТИЧЕСКИЙ ПЛАЦДАРМ
wp post term add 16527 post_tag atm --by=slug $U  # Банкомат во списал деньги, но наличные не выдал
wp post term add 16531 post_tag thailand --by=slug $U  # Почему экспаты бегут из Таиланда во Вьетнам
wp post term add 16567 post_tag apostille --by=slug $U  # Апостиль во Вьетнаме с 11 сентября 2026: кому нужен, а кому 
wp post term add 16665 post_tag apostille --by=slug $U  # Если иностранец умер во Вьетнаме: что делать по шагам
wp post term add 16860 post_tag thailand --by=slug $U  # Central Group из Таиланда вложит 3,5 млрд долларов во Вьетна
wp post term add 16888 post_tag thailand --by=slug $U  # Вьетнам обогнал Таиланд и Индонезию и стал крупнейшим рынком
wp post term add 17019 post_tag cambodia laos --by=slug $U  # Самые безумные развлечения Юго-Восточной Азии: от РПГ до кан
wp post term add 17026 post_tag vapes --by=slug $U  # Полный запрет вейпов и IQOS во Вьетнаме с 1 марта 2026
wp post term add 17090 post_tag e-arrival visa-run --by=slug $U  # Нужна ли виза во Вьетнам в 2026 году: по странам и ситуациям
wp post term add 17494 post_tag visa-run --by=slug $U  # Как остаться во Вьетнаме дольше в 2026 году: продление, виза
wp post term add 17846 post_tag sim-esim --by=slug $U  # Сим-карта и eSIM во Вьетнаме в 2026 году: где купить, что ну
wp post term add 17851 post_tag apostille --by=slug $U  # ВНЖ и гражданство Вьетнама в 2026 году: за инвестиции, недви
wp post term add 17855 post_tag e-arrival --by=slug $U  # Виза во Вьетнам для граждан Беларуси, Узбекистана, Кыргызста
wp post term add 17857 post_tag vapes --by=slug $U  # Что нельзя ввозить во Вьетнам в 2026 году: вейпы, алкоголь, 
wp post term add 17859 post_tag sim-esim --by=slug $U  # Зимовка во Вьетнаме 2026–2027: где лучше, сколько стоит и чт
wp post term add 17863 post_tag thailand --by=slug $U  # Вьетнам или Таиланд в 2026 году: где лучше отдыхать и зимова
wp post term add 17894 post_tag sim-esim --by=slug $U  # Первая неделя в Дананге: что сделать по дням — от аэропорта 
wp post term add 17896 post_tag sim-esim --by=slug $U  # Зимовка на острове Фукуок 2026–2027: план от визы до отъезда

after=$(wp db query "SELECT CONCAT(COUNT(*),'-',SUM(UNIX_TIMESTAMP(post_modified)),'-',MAX(post_modified)) FROM $(wp db prefix $U)posts WHERE ID IN (884,1523,1531,1822,2372,4082,4364,4643,5197,5733,6268,7866,9063,9218,10325,10851,11000,11082,11174,11543,11951,11975,12448,13500,13638,13718,14780,14812,14927,15022,16254,16311,16527,16531,16567,16665,16860,16888,17019,17026,17090,17494,17846,17851,17855,17857,17859,17863,17894,17896)" $U --skip-column-names)
[ "$before" = "$after" ] && echo "Даты 50 материалов не сдвинулись — OK" || echo "ВНИМАНИЕ: даты сдвинулись ($before → $after)"
for s in thailand cambodia laos visa-run e-arrival apostille sim-esim atm vapes; do printf "%s " $s; wp term get post_tag $s --by=slug --field=count $U; done
wp cache flush $U
echo "Сбросьте кэш SiteGround."
