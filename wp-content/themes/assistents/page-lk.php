<?php
if ( ! is_user_logged_in() ) {
  wp_redirect( home_url('/login') );
  exit;
}
get_header();
?>

<style>
  .lk-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 24px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    font-family: 'Inter', sans-serif;
  }
  .block-title { font-size: 20px; font-weight: 600; margin-bottom: 16px; }
  .gpt-balance { margin-bottom: 1em; }
  .flex { display: flex; flex-direction: column; gap: 12px; }
  textarea, input[type="file"] {
    width: 100%; border: 1px solid #ccc; padding: 8px; border-radius: 6px;
    font-family: 'Inter', sans-serif; resize: vertical; box-sizing: border-box;
  }
  button {
    background: #48A7E5; color: #fff; padding: 10px 20px;
    border: none; border-radius: 6px; cursor: pointer; align-self: flex-start;
  }
  .gpt-response {
    margin-top: 24px; padding: 16px; background: #f4f6f9;
    border-radius: 6px; white-space: pre-wrap;
  }
</style>

<div class="lk-container">
  <div class="block-title">Профиль сотрудника</div>
  <?php 
    $balance = get_request_balance( get_current_user_id() );
  ?>
  <div class="gpt-balance">
    <strong>Осталось запросов в GPT:</strong>
    <span id="remaining-calls"><?php echo esc_html( $balance ); ?></span>
  </div>

  <div class="flex">
    <label for="fileInput">Прикрепить файл с запросом:</label>
    <input type="file" id="fileInput" accept=".txt,.md,.json,.csv,.xml,.docx,.pdf" />

    <label for="queryInput">Дополните или отредактируйте запрос:</label>
    <textarea id="queryInput" rows="4" placeholder="Введите текст запроса..."></textarea>

    <button id="sendBtn">Отправить</button>
  </div>

  <div id="response-box" class="gpt-response" style="display:none;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
<script>
(async () => {
  const ajaxUrl = <?php echo json_encode( admin_url('admin-ajax.php') ); ?>;
  const nonce  = <?php echo json_encode( wp_create_nonce('process_gpt') ); ?>;
  const fileInput = document.getElementById('fileInput');
  const input     = document.getElementById('queryInput');
  const btn       = document.getElementById('sendBtn');
  const box       = document.getElementById('response-box');
  const balEl     = document.getElementById('remaining-calls');

  btn.addEventListener('click', async () => {
    const form = new FormData();
    form.append('action', 'process_gpt');
    form.append('nonce', nonce);
    if (fileInput.files.length) {
      form.append('file', fileInput.files[0]);
    }
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
        fileInput.value = '';
        input.value = '';
      }
    } catch (e) {
      console.error(e);
      box.innerHTML = '<p>Ошибка сервера, повторите позднее</p>';
    }
  });
})();
</script>

<?php get_footer(); ?>
