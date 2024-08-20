<?php
/**
 * Основные параметры WordPress.
 *
 * Этот файл содержит следующие параметры: настройки MySQL, префикс таблиц,
 * секретные ключи и ABSPATH. Дополнительную информацию можно найти на странице
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Кодекса. Настройки MySQL можно узнать у хостинг-провайдера.
 *
 * Этот файл используется скриптом для создания wp-config.php в процессе установки.
 * Необязательно использовать веб-интерфейс, можно скопировать этот файл
 * с именем "wp-config.php" и заполнить значения вручную.
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define('DB_NAME', 'p-338591_audiobook');

/** Имя пользователя MySQL */
define('DB_USER', 'p-338591_audiobook');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', 'o1N*i310k');

/** Имя сервера MySQL */
define('DB_HOST', 'localhost');

/** Кодировка базы данных для создания таблиц. */
define('DB_CHARSET', 'utf8');

/** Схема сопоставления. Не меняйте, если не уверены. */
define('DB_COLLATE', '');

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '?Cc!hR-K^WR{D;D~s3-m-cwfv|^;d]Nv7.>uONycN1rbjv>+2}5I/3BWv]N+7-t>');
define('SECURE_AUTH_KEY',  'P(o%Dj=y/.G7Xbw+=*F,6uzmxMcR49SNRQU>tLRZo2DT;;r!6zDqt-fa#qq-|Q<%');
define('LOGGED_IN_KEY',    't);T<-+k<,*Tgw8aWqB>|49j6d<(k8!_V*AD(,zo{Z)fdPBe6c%Z<?,^|^p0YY]|');
define('NONCE_KEY',        '&Yb5G8cad4$$<P$SRmw2+V=65]3.g7E:BM!{}Il[X vm!Y42uHu}8:,Mc^10-p)x');
define('AUTH_SALT',        '@TNu4I>M<&$baq!BxmPHFYq2&OzX6{jA3,[|.YqB/NO-;@Nd0`X)8?}.0mg@PA h');
define('SECURE_AUTH_SALT', 'y`kV|Pzv*B?d19+pp=!T 7hd3|LsRG<+d+v!8e9*=LZ :CtAl1-Hbh;x~LWhh8|1');
define('LOGGED_IN_SALT',   'l@y7%ZMExQ- uaj1WWhrre,D!5TL[c>TVn#-=8znJT!D3y_BxcWAr2z<=;{Oazi7');
define('NONCE_SALT',       'gp6]Fja+14zJu8CwWjQd1-D_8SSomHW}r;gF.!y_8 bJm9CGI&oPa3o4~P;!rrfN');

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix  = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 */
define('WP_DEBUG', false);

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Инициализирует переменные WordPress и подключает файлы. */
require_once(ABSPATH . 'wp-settings.php');
