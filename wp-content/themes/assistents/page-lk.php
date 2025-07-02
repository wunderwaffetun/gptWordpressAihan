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

  .block-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 16px;
  }

  .flex {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  textarea {
    width: 100%;
    border: 1px solid #ccc;
    padding: 8px;
    border-radius: 6px;
    font-family: 'Inter', sans-serif;
    resize: vertical;
  }

  button {
    background: #48A7E5;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }

  .gpt-response {
    margin-top: 24px;
    padding: 16px;
    background: #f4f6f9;
    border-radius: 6px;
  }
</style>

<div class="lk-container">
  <div class="block-title">Профиль сотрудника</div>

  <?php $balance = get_request_balance( get_current_user_id() ); ?>
  <div class="gpt-balance" style="margin-bottom: 1em;">
    <strong>В этом сеансе осталось запросов в GPT:</strong>
    <span id="remaining-calls"><?php echo esc_html( $balance ); ?></span>
  </div>

  <div class="flex">
    <textarea id="queryInput" rows="4" placeholder="Введите ваш запрос..."></textarea>
    <button id="sendBtn">Отправить</button>
  </div>
  <div id="response-box" class="gpt-response" style="display: none;"></div>
</div>

<!-- Рендеринг Markdown и очистка HTML -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>

<script>
(async () => {
  const ajaxUrl = <?php echo json_encode( admin_url('admin-ajax.php') ); ?>;

  const input = document.getElementById('queryInput');
  const btn = document.getElementById('sendBtn');
  const box = document.getElementById('response-box');
  const balanceEl = document.getElementById('remaining-calls');

  btn.addEventListener('click', async () => {
    const query = input.value.trim();
    if (!query) {
      alert('Введите запрос');
      return;
    }
    box.style.display = 'block';
    box.innerHTML = '<p>Загрузка...</p>';

    try {
      const res = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'process_gpt',
          prompt: JSON.stringify({ question: query })
        })
      });
      if (!res.ok) {
        box.innerHTML = '<p>Ошибка, повторите запрос</p>';
        return;
      }
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); }
      catch { box.innerHTML = '<p>Ошибка, повторите запрос</p>'; return; }

      if (data.success !== true || !data.data?.response) {
        box.innerHTML = '<p>Ошибка, повторите запрос</p>';
        return;
      }
      // рендерим Markdown и очищаем
      const html = DOMPurify.sanitize( marked.parse( data.data.response ) );
      box.innerHTML = html;
      // обновляем баланс
      if (typeof data.data.remaining_calls === 'number') {
        balanceEl.textContent = data.data.remaining_calls;
      }
    } catch (e) {
      console.error(e);
      box.innerHTML = '<p>Ошибка, повторите запрос</p>';
    }
  });
})();
</script>

<?php
get_footer();
?>
