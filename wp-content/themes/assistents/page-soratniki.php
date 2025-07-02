<?php
/* Template Name: Соратники */
get_header();
?>

<style>
  .soratniki-wrapper {
    display: flex;
    gap: 40px;
    max-width: 1280px;
    margin: 40px auto;
    padding: 0 24px;
    font-family: 'Inter', sans-serif;
  }

  .soratniki-main {
    flex: 3;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
  }

  .soratnik-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    padding: 16px;
    text-align: center;
  }

  .soratnik-card h3 {
    margin-bottom: 8px;
    font-size: 20px;
    color: #042E5D;
  }

  .soratnik-card p {
    font-size: 14px;
    color: #333;
    margin-bottom: 12px;
  }

  .soratnik-card button {
    background: #48A7E5;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
  }

  .sidebar {
    flex: 1;
    background: #f4f6f9;
    padding: 16px;
    border-radius: 12px;
  }

  .sidebar h4 {
    font-size: 18px;
    margin-bottom: 12px;
  }

  .sidebar ul {
    list-style: disc;
    padding-left: 20px;
    font-size: 14px;
  }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }

  .modal.active {
    display: flex;
  }

  .modal-content {
    background: white;
    padding: 24px;
    max-width: 600px;
    border-radius: 12px;
    text-align: left;
    position: relative;
  }

  .modal-content h3 {
    margin-top: 0;
  }

  .modal-content iframe {
    width: 100%;
    height: 300px;
    border-radius: 8px;
    margin-top: 16px;
  }

  .modal-close {
    position: absolute;
    top: 12px;
    right: 16px;
    cursor: pointer;
    font-size: 20px;
    font-weight: bold;
  }
</style>

<div class="soratniki-wrapper">

  <!-- Main content -->
  <div class="soratniki-main">
    <?php for ($i = 1; $i <= 8; $i++): ?>
      <div class="soratnik-card" data-name="Соратник <?= $i ?>" data-desc="Полное описание соратника <?= $i ?> с примерами кейсов и видео" data-video="https://www.youtube.com/embed/dQw4w9WgXcQ">
        <h3>Соратник <?= $i ?></h3>
        <p>2-строчное описание краткого функционала</p>
        <button class="open-modal-btn">Подробнее</button>
      </div>
    <?php endfor; ?>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Курсы и тренинги</h4>
    <ul>
      <li>Юридическая грамотность</li>
      <li>Финансовая аналитика</li>
      <li>Работа с Excel и PowerPoint</li>
      <li>Подготовка к совещаниям</li>
    </ul>
  </div>
</div>

<!-- Modal -->
<div class="modal" id="soratnikModal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeModal()">×</span>
    <h3 id="modalTitle">Заголовок</h3>
    <p id="modalDesc">Описание</p>
    <iframe id="modalVideo" src="" frameborder="0" allowfullscreen></iframe>
  </div>
</div>

<script>
  const modal = document.getElementById('soratnikModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalDesc = document.getElementById('modalDesc');
  const modalVideo = document.getElementById('modalVideo');

  document.querySelectorAll('.open-modal-btn').forEach(button => {
    button.addEventListener('click', () => {
      const card = button.closest('.soratnik-card');
      modalTitle.textContent = card.dataset.name;
      modalDesc.textContent = card.dataset.desc;
      modalVideo.src = card.dataset.video;
      modal.classList.add('active');
    });
  });

  function closeModal() {
    modal.classList.remove('active');
    modalVideo.src = ''; // останавливаем видео
  }

  window.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });
</script>

<?php get_footer(); ?>
