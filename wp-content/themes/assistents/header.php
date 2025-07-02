<html>
  <head>
    <style>
      body {
        margin: 0;
        font-family: 'Inter', sans-serif;
        background: #f4f6f9;
      }

      header {
        background: #fff;
        padding: 16px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
      }

      .header-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .logo a {
        font-size: 24px;
        font-weight: 600;
        color: #042E5D;
        text-decoration: none;
      }

      nav ul.nav {
        list-style: none;
        display: flex;
        gap: 24px;
        margin: 0;
        padding: 0;
      }

      nav ul.nav li {
        margin: 0;
      }

      nav ul.nav li a {
        text-decoration: none;
        font-weight: 500;
        color: #042E5D;
        padding: 8px 12px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
      }

      nav ul.nav li a:hover {
        background: #eaf5fc;
        color: #48A7E5;
      }

      .auth-link a {
        background: #48A7E5;
        color: #fff;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.3s;
      }

      .auth-link a:hover {
        background: #3693d2;
      }
    </style>
    <?php wp_head(); ?>
  </head>
  <body>
    <header>
      <div class="header-container">
        <div class="logo">
          <a href="<?php echo esc_url(home_url('/')); ?>">Soratniks</a>
        </div>
        <nav>
          <?php wp_nav_menu([
            'theme_location' => 'main_menu',
            'container' => false,
            'menu_class' => 'nav'
          ]); ?>
        </nav>
        <div class="auth-link">
          <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Выйти</a>
          <?php else : ?>
            <a href="<?php echo esc_url( home_url('/login/') ); ?>">Вход/Регистрация</a>
          <?php endif; ?>
        </div>
      </div>
    </header>
