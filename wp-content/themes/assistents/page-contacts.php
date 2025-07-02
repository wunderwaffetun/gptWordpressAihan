<?php
/* Template Name: Контакты */
get_header();
?>

<style>
  .kontakty-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 24px;
    font-family: 'Inter', sans-serif;
    color: #042E5D;
  }

  .kontakty-container h1 {
    font-size: 40px;
    margin-bottom: 32px;
  }

  .contact-block {
    margin-bottom: 48px;
    font-size: 18px;
    line-height: 1.8;
  }

  .faq-title {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 24px;
    text-align: left;
  }

  .accordion {
    border-top: 1px solid #ccc;
  }

  .accordion-item {
    border-bottom: 1px solid #ccc;
  }

  .accordion-button {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    padding: 16px;
    font-size: 18px;
    cursor: pointer;
    position: relative;
  }

  .accordion-button::after {
    content: '+';
    position: absolute;
    right: 16px;
    font-weight: bold;
  }

  .accordion-button.active::after {
    content: '–';
  }

  .accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: #f4f6f9;
    padding: 0 16px;
  }

  .accordion-content p {
    padding: 16px 0;
    margin: 0;
  }
</style>

<div class="kontakty-container">
  <h1>Контакты и справка</h1>

  <div class="contact-block">
    <p><strong>Для связи:</strong></p>
    <p>Почта: <a href="mailto:soratniks@yandex.ru">soratniks@yandex.ru</a></p>
    <p>Telegram: <a href="https://t.me/SofLida" target="_blank">@SofLida</a></p>
  </div>

  <div class="faq-title">FAQ</div>
  <div class="accordion">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <div class="accordion-item">
        <button class="accordion-button">ВОПРОС <?= $i ?></button>
        <div class="accordion-content">
          <p>Моковый ответ на вопрос <?= $i ?>. Здесь будет текст, редактируемый через админку.</p>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<script>
  const buttons = document.querySelectorAll('.accordion-button');

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const content = btn.nextElementSibling;
      const isActive = btn.classList.contains('active');

      // Закрыть все
      buttons.forEach(b => {
        b.classList.remove('active');
        b.nextElementSibling.style.maxHeight = null;
      });

      // Открыть текущий
      if (!isActive) {
        btn.classList.add('active');
        content.style.maxHeight = content.scrollHeight + 'px';
      }
    });
  });
</script>

<?php get_footer(); ?>
