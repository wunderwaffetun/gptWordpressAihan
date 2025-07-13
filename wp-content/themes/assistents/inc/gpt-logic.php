<?
add_action('wp_ajax_process_gpt', 'handle_gpt_request');
add_action('wp_ajax_nopriv_process_gpt', 'handle_gpt_request');
function handle_gpt_request() {
    check_ajax_referer('process_gpt','nonce'); // Защита от CSRF

    if ( ! is_user_logged_in() ) {
        wp_send_json_error([ 'message' => 'Ошибка, повторите запрос' ], 403 );
        exit;
    }
    $user_id = get_current_user_id();

    // 1) Проверяем баланс
    $balance = get_request_balance( $user_id );
    if ( $balance <= 0 ) {
        wp_send_json_error([ 'message' => 'Лимит запросов исчерпан.' ], 429 );
        exit;
    }

    // 2) Читаем админский attachment
    $admin_text = '';
    if ( $admin_id = get_option('gpt_source_attachment_id') ) {
        $path = get_attached_file( $admin_id );
        if ( is_readable($path) ) {
            $ext = strtolower( pathinfo($path, PATHINFO_EXTENSION) );
            switch ( $ext ) {
                case 'txt': case 'md': case 'json': case 'csv': case 'xml':
                    $admin_text = file_get_contents($path);
                    break;
                case 'docx':
                    try {
                        $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
                        $txt = '';
                        foreach ( $phpWord->getSections() as $section ) {
                            foreach ( $section->getElements() as $el ) {
                                if ( method_exists($el,'getText') ) {
                                    $txt .= $el->getText() . "\n";
                                }
                            }
                        }
                        $admin_text = $txt;
                    } catch ( Exception $e ) {
                        $admin_text = '';
                    }
                    break;
                case 'pdf':
                    try {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf    = $parser->parseFile($path);
                        $admin_text = $pdf->getText();
                    } catch ( Exception $e ) {
                        $admin_text = '';
                    }
                    break;
                default:
                    $admin_text = file_get_contents($path);
                    break;
            }
        }
    }

    // 3) Читаем пользовательский файл, если есть
    $user_text = '';
    if ( ! empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']) ) {
        $tmp = $_FILES['file']['tmp_name'];
        $ext = strtolower( pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) );
        switch ( $ext ) {
            case 'txt': case 'md': case 'json': case 'csv': case 'xml':
                $user_text = file_get_contents($tmp);
                break;
            case 'docx':
                try {
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmp);
                    $txt = '';
                    foreach ( $phpWord->getSections() as $section ) {
                        foreach ( $section->getElements() as $el ) {
                            if ( method_exists($el,'getText') ) {
                                $txt .= $el->getText() . "\n";
                            }
                        }
                    }
                    $user_text = $txt;
                } catch ( Exception $e ) {
                    $user_text = '';
                }
                break;
            case 'pdf':
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf    = $parser->parseFile($tmp);
                    $user_text = $pdf->getText();
                } catch ( Exception $e ) {
                    $user_text = '';
                }
                break;
            default:
                $user_text = @file_get_contents($tmp) ?: '';
                break;
        }
    }

    // 4) Берём текст из textarea
    $textarea = sanitize_text_field( $_POST['question'] ?? '' );

    // 5) Объединяем всё в единый промпт
    $parts = array_filter([ $admin_text, $user_text, $textarea ]);
    $prompt = implode("\n\n", $parts);
    if ( ! trim($prompt) ) {
        wp_send_json_error([ 'message' => 'Нечего отправлять' ], 400 );
        exit;
    }

    // 6) Отправляем в GPT
    $resp = wp_remote_post('https://open-api-js-proxy.onrender.com/proxy', [
        'headers' => [ 'Content-Type'=>'application/json' ],
        'body'    => wp_json_encode([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                [ 'role'=>'system', 'content'=> $prompt ]
            ],
        ]),
        'timeout' => 30,
    ]);
    if ( is_wp_error($resp) ) {
        wp_send_json_error([ 'message' => 'Ошибка, повторите запрос' ], 500 );
        exit;
    }
    $json = json_decode( wp_remote_retrieve_body($resp), true );
    if ( empty($json['choices'][0]['message']['content']) ) {
        wp_send_json_error([ 'message' => 'Ошибка, повторите запрос' ], 500 );
        exit;
    }

    // 7) Списываем баланс и возвращаем ответ
    $out = $json['choices'][0]['message']['content'];
    $new_balance = decrement_request_balance( $user_id );

    wp_send_json_success([
        'response'        => $out,
        'remaining_calls' => $new_balance,
    ]);
    exit;
}


/////////////////////////////////////////////////////////////////////////////////////////////////gpt-end

// REST-эндпоинт для AJAX-проверки пользователя на существование email/ логина при регистрации 
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