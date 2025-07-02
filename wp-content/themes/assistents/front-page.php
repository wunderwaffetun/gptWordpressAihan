<?php
/*
 Template Name: Личный кабинет
*/
 get_header(); ?>

<style>
  body {
    font-family: 'Inter', sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #042E5D;
  }

  /* Hero */
  .hero-centered {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 80vh;
    text-align: center;
    padding: 40px 20px;
  }

  .hero-inner {
    max-width: 700px;
  }

  .hero-inner h1 {
    font-size: 42px;
    margin-bottom: 24px;
    color: #042E5D;
  }

  .hero-inner p {
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 24px;
  }

  .hero-inner h2 {
    font-size: 24px;
    margin-bottom: 16px;
    color: #48A7E5;
  }

  .hero-inner ul {
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
  }

  .hero-inner ul li {
    position: relative;
    padding-left: 32px;
    margin-bottom: 12px;
    font-size: 18px;
    line-height: 1.5;
  }

  .hero-inner ul li:before {
    content: '🔹';
    position: absolute;
    left: 0;
    top: 0;
    font-size: 20px;
    line-height: 1;
  }

  /* 3-блочный контент */
  .wide-section {
    background: white;
    padding: 60px 20px;
    width: 100%;
  }

  .three-columns {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    max-width: 1280px;
    margin: 0 auto;
  }

  .three-columns .block {
    background: #eaf5fc;
    padding: 24px;
    border-radius: 12px;
  }

  /* Результат */
  .result-section {
    text-align: center;
    padding: 80px 20px 40px;
  }

  .result-section h2 {
    font-size: 40px;
    margin-bottom: 16px;
  }

  .video-wrapper {
    max-width: 800px;
    margin: 20px auto 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  /* Кто лидер */
  .leader-section {
    background: #fff;
    padding: 60px 20px;
    text-align: center;
    position: relative;
  }

  .leader-flex {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    max-width: 1000px;
    margin: 0 auto;
    flex-wrap: wrap;
    text-align: left;
  }

  .leader-circle {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: #ccc;
    flex-shrink: 0;
  }

  .leader-text h3 {
    margin: 0 0 12px;
    font-size: 24px;
    color: #48A7E5;
  }

  .leader-text p {
    margin: 0;
    font-size: 16px;
    line-height: 1.6;
  }

  /* Большой текст */
  .text-heavy {
    padding: 60px 20px;
    max-width: 800px;
    margin: 0 auto;
    font-size: 18px;
    line-height: 1.8;
  }

  footer {
    background: #eaf5fc;
    text-align: center;
    padding: 40px 20px;
    font-size: 14px;
  }

  footer a {
    color: #042E5D;
    margin: 0 12px;
    text-decoration: underline;
  }
</style>

<!-- Hero -->
<section class="hero-centered">
  <div class="hero-inner">
    <h1>ВАС ПРИВЕТСТВУЮТ ВАШИ СОРАТНИКИ!</h1>

    <p>
      Мы помогаем компаниям автоматизировать и интеллектуально поддерживать процессы,
      связанные с людьми, продажами и сервисом, а также сокращать издержки и повышать продуктивность.
      Мы — ваши цифровые партнёры, помогающие руководителям развивать сотрудников клиентских подразделений: продажи и сервис.
    </p>

    <h2>Соратники работают так:</h2>
    <ul>
      <li>Анализируют ваши данные (переговоры, встречи, профили)</li>
      <li>Дают рекомендации — что и как улучшить</li>
      <li>Автоматизируют рутину, освобождая время для клиентов и стратегии</li>
    </ul>

    <h2>Мы делаем ваш бизнес сильнее:</h2>
    <ul>
      <li>Автоматизируйте рутину — экономьте часы на подготовке к переговорам и совещаниям</li>
      <li>Снижайте риски — прогнозируйте поведение сотрудников и клиентов с помощью точных профилей личности</li>
      <li>Увеличивайте продажи — находите слабые места в переговорах и превращайте их в точки роста</li>
      <li>Внедряйте инновации — используйте AI-аналитику для HR и sales-решений</li>
      <li>Укрепляйте команду — давайте персонализированную обратную связь, снижая конфликты</li>
      <li>Оптимизируйте процессы — получайте структурированные отчёты по каждому сотруднику и встрече</li>
      <li>Масштабируйте лучшие практики — тиражируйте успешные кейсы на всю команду</li>
      <li>Повышайте мотивацию — управляйте через мотивацию, понимая, что важно каждому сотруднику</li>
    </ul>
  </div>
</section>

<!-- Три инфоблока -->
<section class="wide-section">
  <div class="three-columns">
    <div class="block">
      <h3>Что мы делаем</h3>
      <p>Мы помогаем компаниям и экспертам автоматизировать подготовку, верификацию и оформление документов, отчётов и презентаций.</p>
    </div>
    <div class="block">
      <h3>Как это работает</h3>
      <p>Вы загружаете файл, выбираете ассистента, и получаете результат с комментариями и доработками через личный кабинет.</p>
    </div>
    <div class="block">
      <h3>Для кого</h3>
      <p>Бухгалтеры, HR, юристы, стартапы, студенты — все, кому нужен надёжный цифровой помощник.</p>
    </div>
  </div>
</section>

<!-- Результат -->
<section class="result-section">
  <h2>РЕЗУЛЬТАТ</h2>
  <div class="video-wrapper">
    <iframe
      width="100%"
      height="400"
      src="https://rutube.ru/play/embed/YOUR_VIDEO_ID"
      frameborder="0"
      allowfullscreen
      allow="clipboard-write; autoplay"
    ></iframe>
  </div>
</section>


<!-- Кто лидер -->
<section class="leader-section">
  <div class="leader-flex">
    <div class="leader-circle"></div>
    <div class="leader-text">
      <h3>КТО ЛИДЕР</h3>
      <p>Ирина Сафина — эксперт в legal-tech. Более 10 лет опыта в аналитике, автоматизации бизнес-процессов и цифровых продуктах.</p>
    </div>
  </div>
</section>

<!-- Большой текстовый блок -->
<section class="text-heavy">
  <p>
    Мы верим, что каждый специалист должен сосредоточиться на важном, а рутинные задачи — делегировать. Soratniki — это команда цифровых ассистентов, которые анализируют документы, оформляют презентации и возвращают результат быстро и качественно. Сервис идеально подойдёт тем, кто работает в условиях многозадачности и хочет сэкономить время на шаблонной работе. Наши ассистенты обучены работать в разных форматах, включая Excel, PowerPoint, Word, PDF. Никакого дополнительного софта — всё через личный кабинет.
  </p>
</section>

<!-- Footer -->
<footer>
  <p>&copy; <?php echo date('Y'); ?> Soratniki</p>
  <a href="/docs/privacy.pdf" target="_blank">Политика ПД</a> |
  <a href="/docs/offer.pdf" target="_blank">Договор-оферта</a> |
  <a href="/docs/requisites.pdf" target="_blank">Реквизиты</a>
  <br><br>
  <p>Контакты: <a href="mailto:soratniks@yandex.ru">soratniks@yandex.ru</a> | Telegram: <a href="https://t.me/SofLida" target="_blank">@SofLida</a></p>
</footer>

<?php get_footer(); ?>
