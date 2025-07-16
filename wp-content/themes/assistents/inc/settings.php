<?php
add_action('admin_init', function() {
    // 1. Регистрируем саму опцию
    register_setting('general', 'soratniki_promo_code', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ]);

    // 2. Добавляем поле на страницу Общие настройки
    add_settings_field(
        'soratniki_promo_code',               // ID поля
        'Промокод для регистрации',           // Заголовок
        function() {                          // Callback: вывод HTML
            $val = get_option('soratniki_promo_code', '');
            echo '<input name="soratniki_promo_code" type="text" '
               . 'value="' . esc_attr($val) . '" class="regular-text">';
        },
        'general',                            // Страница (general = Общие настройки)
        'default',                            // Секция (default = Основная секция)
        [ 'label_for' => 'soratniki_promo_code' ]
    );
});