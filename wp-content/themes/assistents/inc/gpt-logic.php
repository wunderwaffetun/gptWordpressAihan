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
    $parts = array_filter([ $admin_text, $textarea, $user_text ]);
    $prompt = implode("\n\n", $parts);
    error_log($prompt);
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
                [ 'role'=>'user', 'content'=> $prompt ]
            ],
        ]),
        'timeout' => 30,
    ]);
    error_log(json_encode($resp));
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
