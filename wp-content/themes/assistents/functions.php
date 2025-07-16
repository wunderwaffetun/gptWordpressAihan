<?php
/*
Theme Name: Soratniki Landing
Theme URI: http://example.com/
Author: Корсаков Егор
Description: Custom landing theme for "Soratniki"
Version: 1.0
*/
require_once ABSPATH . 'vendor/autoload.php'; // чтобы читались файлы 
require_once __DIR__ . '/inc/balance-admin.php';
require_once __DIR__ . '/inc/gpt-logic.php';
require_once __DIR__ . '/inc/settings.php'; // чтобы изменять промокод в настройках админки 


@ini_set( 'upload_max_size' , '1000M' );
@ini_set( 'post_max_size', '1000M');

function soratniki_enqueue_assets() {
    wp_enqueue_style('soratniki-style', get_stylesheet_uri());
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
}
add_action('wp_enqueue_scripts', 'soratniki_enqueue_assets');

function soratniki_register_menus() {
    register_nav_menu('main_menu', __('Main Menu'));
}
add_action('init', 'soratniki_register_menus');

add_theme_support('title-tag');

// Авторизация при входе на сайт (обязательная)
function soratniki_force_login() {
    if (!is_user_logged_in() && !is_page('wp-login.php') && !is_admin()) {
        auth_redirect();
    }
}
// add_action('template_redirect', 'soratniki_force_login');

function custom_redirect_login_page() { // Кастомная авторизация с промокодом, логином, паролем вместо стандрартной 
    $login_page  = home_url('/login/'); 
    $page_viewed = basename($_SERVER['REQUEST_URI']);

    if ($page_viewed === "wp-login.php" && $_SERVER['REQUEST_METHOD'] === 'GET') {
        wp_redirect($login_page);
        exit;
    }
}
add_action('init', 'custom_redirect_login_page');

function custom_logout_redirect() { // При logout перенаправление не на авторизацию, а на главную
    wp_redirect(home_url());
    exit;
}
add_action('wp_logout', 'custom_logout_redirect');


/////////////////////////////////////////////////////////////////////////////////////////////// логика изменения баланса 
/**
 * Получить текущий баланс запросов пользователя,
 * если ещё не задан — инициализировать значением 3.
 *
 * @param int $user_id
 * @return int
 */
function get_request_balance( $user_id ) {
    $balance = (int) get_user_meta( $user_id, 'request_balance', true );
    if ( $balance <= 0 ) {
        // либо если совсем не было ключа, инициализируем
        if ( ! metadata_exists( 'user', $user_id, 'request_balance' ) ) {
            $balance = 3;
            update_user_meta( $user_id, 'request_balance', $balance );
        }
    }
    return $balance;
}

/**
 * Уменьшить баланс запросов у пользователя на 1
 *
 * @param int $user_id
 * @return int Новый баланс
 */
function decrement_request_balance( $user_id ) {
    $balance = get_request_balance( $user_id );
    if ( $balance > 0 ) {
        $balance--;
        update_user_meta( $user_id, 'request_balance', $balance );
    }
    return $balance;
}


add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/check-user', [
        'methods'  => 'POST',
        'callback' => 'custom_check_user_exists',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Callback для проверки существования username и email
 */
function custom_check_user_exists(\WP_REST_Request $request) {
    $params   = $request->get_json_params();
    $username = sanitize_user($params['username'] ?? '');
    $email    = sanitize_email($params['email'] ?? '');
    $errors   = [];

    if ($username && username_exists($username)) {
        $errors['username'] = 'Пользователь уже существует';
    }
    if ($email && email_exists($email)) {
        $errors['email'] = 'Email уже зарегистрирован';
    }

    $status = empty($errors) ? 200 : 400;
    return new WP_REST_Response(['errors' => $errors], $status);
}



add_action('admin_init', function() {
    // Если это AJAX-запрос к admin-ajax.php — пропускаем
    if ( defined('DOING_AJAX') && DOING_AJAX ) { // Когда зареганный юзер отправлял запрос GPT, ему приходил в ответ html вместо ответа gpt из-за того, что его редиректило на /lk/, надо разрешить юзерам вызвать AJAX
        return;
    }
    // Иначе редиректим не-админов
    if ( ! current_user_can('manage_options') ) {
        wp_redirect( home_url('/lk/') );
        exit;
    }
});

add_action('after_setup_theme', function() {
    if ( ! current_user_can('administrator') ) {
        show_admin_bar(false);
    }
});
