#!/usr/bin/env python3
"""data/history-days.csv → letfind-wp/mu-plugins/lf-history-days-data.php (только строки «проверено»).
Запуск: python3 tools/build-history-days.py [путь к letfind-wp]"""
import csv, sys, os, json
root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
wp = sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(root), 'letfind-wp')
rows = [r for r in csv.DictReader(open(os.path.join(root, 'data', 'history-days.csv'), encoding='utf-8')) if r['status'] == 'проверено']
items = [{k: r[k] for k in ('date', 'year', 'title', 'what', 'kind', 'place', 'site_url')} for r in rows]
php = "<?php\n/* Сгенерировано tools/build-history-days.py из hello-me/data/history-days.csv — руками не править. */\n"
php += "defined( 'ABSPATH' ) || exit;\nfunction lfhd_data() {\n\treturn json_decode( " + repr(json.dumps(items, ensure_ascii=False)).replace("\\'", "\\'") + ", true );\n}\n"
open(os.path.join(wp, 'mu-plugins', 'lf-history-days-data.php'), 'w', encoding='utf-8').write(php)
print(len(items), 'событий →', os.path.join(wp, 'mu-plugins', 'lf-history-days-data.php'))
