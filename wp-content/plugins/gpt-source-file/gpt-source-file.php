<?php
/*
 Plugin Name: GPT Source File
 Description: Позволяет администратору загрузить текстовый файл-источник для GPT и обрабатывать AJAX-запросы пользователей.
 Version:     1.2
 Author:      Ваше Имя
*/

// Подключаем автозагрузчик для сторонних библиотек (PhpWord, PdfParser)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Импорт классов для удобства
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

// 0) Подключаем скрипты медиабиблиотеки и jQuery на странице настроек
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'settings_page_gpt-source-file') {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }
});

// 1) Добавляем страницу в админке для выбора файла
add_action('admin_menu', function() {
    add_options_page(
        'GPT Source File',
        'GPT Source File',
        'manage_options',
        'gpt-source-file',
        'gpt_source_file_page'
    );
});
add_action('admin_init', function() {
    register_setting('gpt_source_group', 'gpt_source_attachment_id');
});

// Функция вывода страницы настроек
function gpt_source_file_page() {
    $attachment_id = get_option('gpt_source_attachment_id');
    ?>
    <div class="wrap">
        <h1>Настройки GPT Source File</h1>
        <form method="post" action="options.php">
            <?php settings_fields('gpt_source_group'); ?>
            <?php do_settings_sections('gpt_source_group'); ?>
            <table class="form-table">
                <tr>
                    <th>Файл-источник</th>
                    <td>
                        <input type="text" id="gpt_source_attachment" name="gpt_source_attachment_id" value="<?php echo esc_attr($attachment_id); ?>" />
                        <button class="button" id="upload_source_file">Выбрать файл</button>
                        <p class="description">Поддерживаются txt, md, json, csv, xml, docx, pdf и т.п.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(function($) {
        var frame;
        $('#upload_source_file').on('click', function(e) {
            e.preventDefault();
            if (frame) return frame.open();
            frame = wp.media({
                title: 'Выберите файл-источник',
                library: { type: '*' },
                button: { text: 'Выбрать' },
                multiple: false
            });
            frame.on('select', function() {
                var sel = frame.state().get('selection').first().toJSON();
                $('#gpt_source_attachment').val(sel.id);
            });
            frame.open();
        });
    });
    </script>
    <?php
}