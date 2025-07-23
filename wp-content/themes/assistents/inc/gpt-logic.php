<?
add_action('wp_ajax_process_gpt', 'handle_gpt_request');
add_action('wp_ajax_nopriv_process_gpt', 'handle_gpt_request');

if ( ! function_exists('soratniki_read_attachment_text') ) {
    function soratniki_read_attachment_text($id) {
        $path = get_attached_file($id);
        if (!is_readable($path)) return '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        try {
            switch ($ext) {
                case 'txt': case 'md': case 'json': case 'csv': case 'xml':
                    return file_get_contents($path);

                case 'docx':
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
                    $txt = '';
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $el) {
                            if ($el instanceof \PhpOffice\PhpWord\Element\Text) {
                                $txt .= $el->getText() . "\n";
                            } elseif ($el instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                foreach ($el->getElements() as $sub) {
                                    if (method_exists($sub, 'getText')) {
                                        $txt .= $sub->getText();
                                    }
                                }
                                $txt .= "\n";
                            }
                        }
                    }
                    return $txt;

                case 'pdf':
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf    = $parser->parseFile($path);
                    return $pdf->getText();

                default:
                    return file_get_contents($path);
            }
        } catch (\Exception $e) {
            return '';
        }
    }
}

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
    // === ВМЕСТО старого "Читаем админский attachment" ===
    $admin_text = '';
    $chosen_id  = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;

    $sources = get_option('soratniki_sources', []);
    $allowed_ids = array_map(
        static fn($r) => intval($r['attachment_id'] ?? 0),
        is_array($sources) ? $sources : []
    );

    // лог на всякий случай
    error_log( 'POST source_id=' . $chosen_id );
    error_log( 'allowed_ids=' . print_r($allowed_ids, true) );

    if ( $chosen_id && in_array($chosen_id, $allowed_ids, true) ) {
        $admin_text = soratniki_read_attachment_text($chosen_id);
    } else {
        $first = intval($allowed_ids[0] ?? 0);
        if ($first) {
            $admin_text = soratniki_read_attachment_text($first);
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
        'timeout' => 50,
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
