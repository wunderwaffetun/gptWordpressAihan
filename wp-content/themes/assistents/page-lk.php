<?php
/*
Template Name: Личный кабинет
*/

if ( ! is_user_logged_in() ) {
    wp_redirect( home_url('/login') );
    exit;
}

get_header();

// Источники из админки
$sources = get_option('soratniki_sources', []);
$balance = get_request_balance( get_current_user_id() );
error_log($sources);
$ajax_url = admin_url('admin-ajax.php');
$nonce    = wp_create_nonce('process_gpt');
?>
<style>
  .lk-wrap{
    max-width:1100px;margin:40px auto;display:grid;grid-template-columns:220px 1fr;gap:24px;
    font-family:'Inter',sans-serif;
  }
  .lk-box{
    background:#fff;padding:24px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.05);
  }
  .lk-sources h3{margin:0 0 12px;font-size:16px;font-weight:600;}
  .src-btn{
    width:100%;margin-bottom:8px;padding:8px 10px;border:1px solid #d0d4da;background:#f4f6f9;
    border-radius:6px;text-align:left;cursor:pointer;font-size:14px;line-height:1.3;
  }
  .src-btn.active{border-color:#48A7E5;background:#e8f4ff;}
  .block-title{font-size:20px;font-weight:600;margin-bottom:16px;}
  .gpt-balance{margin-bottom:1em;}
  .lk-form label{font-size:14px;margin-top:8px;}
  .lk-form textarea,
  .lk-form input[type="file"]{
    width:100%;border:1px solid #ccc;padding:8px;border-radius:6px;resize:vertical;box-sizing:border-box;
    font-family:'Inter',sans-serif;
  }
  .lk-actions{display:flex;gap:12px;margin-top:8px;}
  .lk-actions button{
    background:#48A7E5;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;
  }
  #resetBtn{background:#999;}
  .gpt-response{
    margin-top:24px;padding:16px;background:#f4f6f9;border-radius:6px;white-space:pre-wrap;
  }
</style>

<div class="lk-wrap">

  <!-- Левая колонка: кнопки источников -->
  <aside class="lk-box lk-sources">
    <h3>Источник для GPT</h3>
    <?php if ($sources): ?>
      <?php foreach ($sources as $row):
        $id = intval($row['attachment_id'] ?? 0);
        if (!$id) continue;
        $label = $row['label'] ?: 'Без имени';
      ?>
        <button type="button" class="src-btn" data-id="<?php echo esc_attr($id); ?>">
          <?php echo esc_html($label); ?>
        </button>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="font-size:13px;color:#555;margin:0;">Нет источников. Добавьте их в «Настройки → GPT Источники».</p>
    <?php endif; ?>
  </aside>

  <!-- Правая колонка: форма -->
  <section class="lk-box">
    <div class="block-title">Профиль сотрудника</div>

    <div class="gpt-balance">
      <strong>Осталось запросов в GPT:</strong>
      <span id="remaining-calls"><?php echo esc_html( $balance ); ?></span>
    </div>

    <div class="lk-form">
      <label for="fileInput">Прикрепить файл с запросом:</label>
      <input type="file" id="fileInput" accept=".txt,.md,.json,.csv,.xml,.docx,.pdf" />

      <label for="queryInput">Дополните или отредактируйте запрос:</label>
      <textarea id="queryInput" rows="4" placeholder="Введите текст запроса..."></textarea>

      <div class="lk-actions">
        <button id="sendBtn">Отправить</button>
        <button id="resetBtn" type="button">Сброс</button>
      </div>
    </div>

    <div id="response-box" class="gpt-response" style="display:none;"></div>
  </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
<script>
(function(){
  const ajaxUrl   = <?php echo json_encode( $ajax_url ); ?>;
  const nonce     = <?php echo json_encode( $nonce ); ?>;

  const fileInput = document.getElementById('fileInput');
  const input     = document.getElementById('queryInput');
  const sendBtn   = document.getElementById('sendBtn');
  const resetBtn  = document.getElementById('resetBtn');
  const box       = document.getElementById('response-box');
  const balEl     = document.getElementById('remaining-calls');
  const srcBtns   = document.querySelectorAll('.src-btn');

  let selectedSourceId = null;

  srcBtns.forEach(btn => {
    btn.addEventListener('click', ()=>{
      srcBtns.forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      selectedSourceId = btn.dataset.id;
    });
  });

  sendBtn.addEventListener('click', async () => {
    const form = new FormData();
    form.append('action', 'process_gpt');
    form.append('nonce',  nonce);
    if (selectedSourceId) form.append('source_id', selectedSourceId);
    if (fileInput.files.length) form.append('file', fileInput.files[0]);

    const txt = input.value.trim();
    if (txt) form.append('question', txt);

    if (!form.has('file') && !form.has('question')) {
      alert('Прикрепите файл или введите текст запроса');
      return;
    }

    box.style.display = 'block';
    box.innerHTML = '<p>Загрузка...</p>';

    try {
      const res = await fetch(ajaxUrl, { method:'POST', credentials:'same-origin', body: form });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      if (!data.success || !data.data.response) throw new Error('API error');

      box.innerHTML = DOMPurify.sanitize( marked.parse(data.data.response) );
      if (typeof data.data.remaining_calls === 'number') {
        balEl.textContent = data.data.remaining_calls;
      }
    } catch (e) {
      console.error(e);
      box.innerHTML = '<p>Ошибка сервера, повторите позднее</p>';
    }
  });
  console.log('selectedSourceId =', selectedSourceId);
  resetBtn.addEventListener('click', () => {
    fileInput.value = '';
    input.value = '';
    selectedSourceId = null;
    srcBtns.forEach(b=>b.classList.remove('active'));
    // опционально скрыть ответ:
    // box.style.display = 'none'; box.innerHTML='';
  });
})();
</script>

<?php get_footer();
