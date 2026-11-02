# boutique — WordPress theme

מערכת ניהול לבוטיק כשר (kosherboutique.co.il). התבנית היא כל הפרויקט, אין כאן התקנת WordPress מלאה.

## שפה
- תמיד לכתוב בעברית: כל התשובות, ההסברים, הסיכומים וההודעות למשתמש, בלי יוצא מן הכלל (גם אחרי העלאה, בדיקה או פעולה טכנית).
- רק קוד, שמות קבצים ופקודות באנגלית.

## מבנה
- `functions.php`: נקודת הכניסה. טוען את כל קבצי ה-PHP ומגדיר enqueue ל-CSS/JS.
- `b-functions.php`, `table_functions.php`, `queries.php`, `users.php`: הלוגיקה העיקרית (AJAX, שאילתות, טבלאות, משתמשים).
- `design_tool.php`: כלי העיצוב (קובץ גדול).
- `*-template.php`: תבניות עמודים.
- `lib/`: ייבוא Excel וייצוא PDF/Excel (phpspreadsheet, mpdf, xlsxwriter).
- `assets/`: ספריות צד שלישי (bootstrap, datatables, jquery.validate, autoNumeric) ו-`lists.php`.
- JS: `script.js`, `rivka.js`, `fridi.js`.

## מוסכמות
- כשמשנים CSS/JS, להעלות את מספר הגרסה (`$ver`) ב-`boutique_enqueue_scripts` כדי לעקוף את המטמון.
- ממשק בעברית, RTL.
- לא לערוך קבצים ב-`assets/` שהם ספריות צד שלישי (`*.min.*`).
- קבצים עם `_BKP` / `_old` הם גיבויים, לא לערוך אותם.

## העלאה לשרת
- ההעלאה נעשית ב-FTP.

## עבודה
- לשאול לפני כל פעולה שמשנה קבצים, מתקינה משהו או מבצעת commit/push.
- Remote: github.com/fr-system/boutique, ענף master.
