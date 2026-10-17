#!/bin/bash
# Цвета дизайн-системы LetFind, шаг 1 (18.10.2026): сводим оттенки к палитре. Меняются только HEX-значения, разметка и логика не трогаются.
#   bash ds-colors-20261018.sh            — пробный проход: сколько замен в каких файлах, ничего не меняет
#   bash ds-colors-20261018.sh --apply    — резервная копия (tar) и замена
# Запускать из wp-content. Не трогает: карточку заведения (page-object-v2.php — её зелёная гамма, решение владельца),
# сжатые *.min.css, резервные копии (*before*, *.next.*, *.bak).
set -eu
APPLY=${1:-}
MAP=(
  '#7b4bec=#7B4AEC'   # опечатка основного фиолетового
  '#794ae6=#7B4AEC'   # почти тот же фиолетовый
  '#592fbc=#5B34C9'   # тёмный фиолетовый -> brand-hover
  '#5a2fc4=#5B34C9'
  '#6629de=#5B34C9'
  '#2c2c32=#2C2C31'   # цвет текста -> ink
  '#333333=#2C2C31'
  '#222222=#2C2C31'
  '#323036=#2C2C31'
  '#292727=#2C2C31'
  '#151515=#2C2C31'
  '#0d0d0d=#2C2C31'
)
mapfile -d '' FILES < <(find mu-plugins themes/letfind -type f \( -name '*.php' -o -name '*.css' \) \
  ! -name '*.min.css' ! -name 'page-object-v2.php' ! -name '*before*' ! -name '*.next.*' ! -name '*.bak' -print0 | sort -z)
if [ "$APPLY" = "--apply" ]; then
  B=ds-colors-backup-$(date +%Y%m%d-%H%M).tar.gz
  tar -czf "$B" "${FILES[@]}" && echo "Резервная копия: wp-content/$B"
fi
total=0
for f in "${FILES[@]}"; do
  n=0
  for pair in "${MAP[@]}"; do
    from=${pair%%=*}; to=${pair#*=}
    # только целый HEX: #333 не задевает #3333ff; регистр не важен
    c=$(perl -ne 'BEGIN{$r=0} $r += () = /\Q'"$from"'\E(?![0-9a-fA-F])/gi; END{print $r}' "$f")
    if [ "$c" -gt 0 ]; then
      n=$((n+c))
      if [ "$APPLY" = "--apply" ]; then perl -pi -e 's/\Q'"$from"'\E(?![0-9a-fA-F])/'"$to"'/gi' "$f"; fi
    fi
  done
  if [ "$n" -gt 0 ]; then echo "$n	$f"; fi
  total=$((total+n))
done
echo "Всего замен: $total"
if [ "$APPLY" = "--apply" ]; then for f in "${FILES[@]}"; do case "$f" in *.php) php -l "$f" >/dev/null || echo "ОШИБКА PHP: $f";; esac; done; fi
if [ "$APPLY" = "--apply" ]; then echo "Готово. Сбросьте кэш SiteGround. Откат: tar -xzf $B"; fi
exit 0
