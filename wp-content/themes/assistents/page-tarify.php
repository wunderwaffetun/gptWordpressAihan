<?php
/* Template Name: Тарифы */
get_header();
?>

<style>
  .tarify-container {
    max-width: 1280px;
    margin: 40px auto;
    padding: 24px;
    font-family: 'Inter', sans-serif;
    color: #042E5D;
  }

  .offer-section {
    float: left;
    width: 100%;
    margin-bottom: 40px;
  }

  .offer-section h1 {
    font-size: 48px;
    margin-bottom: 20px;
  }

  .offer-link {
    display: inline-block;
    margin-bottom: 20px;
    font-weight: 600;
    color: #48A7E5;
    text-decoration: underline;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 40px;
  }

  table th, table td {
    padding: 12px;
    border: 1px solid #ccc;
    text-align: left;
  }

  table th {
    background-color: #f4f6f9;
  }

  .pricing-policy {
    clear: both;
    margin-top: 40px;
    padding: 24px;
    background: #f4f6f9;
    border-radius: 12px;
  }

  .pricing-policy h2 {
    font-size: 28px;
    margin-bottom: 16px;
  }

  .pricing-policy p {
    font-size: 16px;
    line-height: 1.6;
  }
</style>

<div class="tarify-container">

  <div class="offer-section">
    <h1>ОФЕРТА</h1>
    <a class="offer-link" href="/docs/offer.pdf" target="_blank">Скачать оферту (PDF)</a>

    <table>
      <thead>
        <tr>
          <th>Соратник</th>
          <th>Вебинар в записи</th>
          <th>Предоплата 6 мес</th>
          <th>После 6 мес</th>
          <th>10 запросов сверх лимита</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Иван</td>
          <td>✅</td>
          <td>1000 ₽</td>
          <td>1200 ₽</td>
          <td>300 ₽</td>
        </tr>
        <tr>
          <td>Мария</td>
          <td>✅</td>
          <td>1500 ₽</td>
          <td>1700 ₽</td>
          <td>400 ₽</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="pricing-policy">
    <h2>ЦЕНОВАЯ ПОЛИТИКА</h2>
    <p>
      Тарифы устанавливаются исходя из уровня квалификации соратника, загрузки и сезонности.
      Предоплата позволяет зафиксировать выгодную цену на 6 месяцев.  
      Запросы сверх лимита оплачиваются отдельно.  
      Цены могут быть пересмотрены в случае изменения рыночной ситуации.
    </p>
  </div>

</div>

<?php get_footer(); ?>
