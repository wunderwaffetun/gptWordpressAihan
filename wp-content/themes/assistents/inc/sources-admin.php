<?php
/**
 * Админ-страница: список «кнопка → файл»
 * опция soratniki_sources = [
 *   [ 'label' => 'Соратник 1', 'attachment_id' => 123 ],
 *   ...
 * ]
 */

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'settings_page_soratniki-gpt-sources') {
        wp_enqueue_media(); // даёт wp.media
    }
});

add_action('admin_menu', function () { 
    add_options_page(
        'GPT Источники', // заголовок страницы 
        'GPT Источники', // текст пункта меню в админке 
        'manage_options', // право доступа, сейчас админское 
        'soratniki-gpt-sources', // слаг страницы, используется в URL
        'soratniki_render_sources_page' // функция, рисующая страницу 
    );
});

add_action('admin_init', function () { // хук, срабатывающий при загрузке админки 
    register_setting('soratniki_sources_group', 'soratniki_sources', [ // soratniki_sources, она относится к группе soratniki_sources_group, как её чистить и читать по умолчанию 
        'type'              => 'array',
        'sanitize_callback' => 'soratniki_sources_sanitize',
        'default'           => [],
    ]);
});

function soratniki_sources_sanitize($input) {
    if (!is_array($input)) return [];
    $out = [];
    foreach ($input as $row) {
        if (empty($row['label'])) continue;
        $out[] = [
            'label'         => sanitize_text_field($row['label']),
            'attachment_id' => intval($row['attachment_id'] ?? 0),
        ];
    }
    return $out;
}

function soratniki_render_sources_page() {
    $rows = get_option('soratniki_sources', []); // достать текущее значение массив кнопка -> файл 
    ?>
    <div class="wrap">
      <h1>GPT Источники</h1>
      <form method="post" action="options.php">
        <?php settings_fields('soratniki_sources_group'); ?>
        <table class="widefat striped" id="soratniki-sources-table">
          <thead>
            <tr>
              <th style="width:40%">Название кнопки</th>
              <th style="width:40%">Файл (attachment)</th>
              <th style="width:20%">Действие</th>
            </tr>
          </thead>
          <tbody>
          <?php if($rows): foreach ($rows as $i => $row): ?>
            <tr>
              <td>
                <input type="text" name="soratniki_sources[<?php echo $i;?>][label]"
                       value="<?php echo esc_attr($row['label']);?>" class="regular-text">
              </td>
              <td>
                <input type="hidden" class="src-attach-id" name="soratniki_sources[<?php echo $i;?>][attachment_id]"
                       value="<?php echo esc_attr($row['attachment_id']);?>">
                <span class="src-file-name">
                  <?php echo $row['attachment_id'] ? esc_html(basename(get_attached_file($row['attachment_id']))) : '—'; ?>
                </span>
                <button type="button" class="button select-file">Выбрать</button>
              </td>
              <td><button type="button" class="button remove-row">Удалить</button></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
        <p><button type="button" class="button button-primary" id="add-source-row">Добавить кнопку</button></p>
        <?php submit_button(); ?>
      </form>
    </div>

    <script>
      document.addEventListener('click', (e) => {
      if (!e.target.matches('.select-file')) return;
      e.preventDefault();

      if (!window.wp || !wp.media) {
        alert('wp.media не загружен');
        return;
      }

      const btn = e.target;
      const frame = wp.media({
        title: 'Выберите файл',
        button: { text: 'Использовать' },
        multiple: false
      });

      frame.on('select', () => {
        const att  = frame.state().get('selection').first().toJSON();
        const cell = btn.closest('td');
        cell.querySelector('.src-attach-id').value   = att.id;
        cell.querySelector('.src-file-name').textContent = att.filename;
      });

      frame.open();
    });

    document.getElementById('add-source-row')?.addEventListener('click', () => {
      const tbody = document.querySelector('#soratniki-sources-table tbody');
      const i = tbody.querySelectorAll('tr').length;
      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td><input type="text" name="soratniki_sources[${i}][label]" class="regular-text"></td>
          <td>
            <input type="hidden" class="src-attach-id" name="soratniki_sources[${i}][attachment_id]" value="">
            <span class="src-file-name">—</span>
            <button type="button" class="button select-file">Выбрать</button>
          </td>
          <td><button type="button" class="button remove-row">Удалить</button></td>
        </tr>
      `);
    });

    document.addEventListener('click', (e) => {
      if (e.target.matches('.remove-row')) {
        e.preventDefault();
        e.target.closest('tr').remove();
      }
    });
    </script>
    <?php
}
