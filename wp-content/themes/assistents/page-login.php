<?php
/*
Template Name: Login/Register
*/



// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $raw_login = trim($_POST['log']);
    // Если введен email, получаем соответствующий username
    if (is_email($raw_login)) {
        $user_obj = get_user_by('email', sanitize_email($raw_login));
        $login    = $user_obj ? $user_obj->user_login : $raw_login;
    } else {
        $login = sanitize_user($raw_login);
    }
    $creds = [
        'user_login'    => $login,
        'user_password' => $_POST['pwd'],
        'remember'      => false,
    ];
    $user = wp_signon($creds, false);
    if (is_wp_error($user)) {
        $_SESSION['login_errors'] = $user->get_error_codes();
        wp_redirect(add_query_arg('tab', 'login', get_permalink()));
        exit;
    } else {
        wp_redirect(home_url('/lk/'));
        exit;
    }
}

// Определяем активную вкладку
$default_tab = (isset($_GET['tab']) && $_GET['tab'] === 'register') ? 'register' : 'login';
// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $username = sanitize_user($_POST['reg_login']);
    $email    = sanitize_email($_POST['reg_email']);
    $pass     = $_POST['reg_password'];
    $pass2    = $_POST['reg_password2'];
    $promo    = isset($_POST['reg_promo']) ? sanitize_text_field($_POST['reg_promo']) : '';

    $errors = new WP_Error();
    $expected_promo = get_option('soratniki_promo_code', '');
    if (empty($expected_promo) || $promo !== $expected_promo) {
                                                     $errors->add('promo', 'Неверный промокод.');
    }
    if (!validate_username($username))               $errors->add('username', 'Неверный логин');
    if (username_exists($username))                  $errors->add('username', 'Пользователь уже существует');
    if (!is_email($email))                           $errors->add('email', 'Неверный Email');
    if (email_exists($email))                        $errors->add('email', 'Email уже зарегистрирован');
    if (strlen($pass) < 8)                           $errors->add('pass', 'Пароль должен быть не менее 8 символов');
    if ($pass !== $pass2)                            $errors->add('pass', 'Пароли не совпадают');

    if (!$errors->get_error_codes()) {
        $user_id = wp_create_user($username, $pass, $email);
        if (!is_wp_error($user_id)) {
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            wp_mail($email, 'Подтверждение регистрации', 'Вы успешно зарегистрированы на сайте Soratniki.');
            wp_redirect(home_url('/lk/'));
            exit;
        }
        $errors->add('register', 'Не удалось создать пользователя.');
    }

    $_SESSION['registration_errors'] = $errors;
    $request_uri = $_SERVER['REQUEST_URI'];
    $path        = strtok($request_uri, '?');
    wp_redirect(home_url($path . '?tab=register'));
    exit;
}

get_header();
?>

<style>
  .login-register-container {
    max-width: 420px;
    margin: 40px auto;
    padding: 24px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
  .auth-tabs {
    display: flex;
    justify-content: space-between;
    list-style: none;
    padding: 0;
    margin: 0 0 24px 0;
    border-bottom: 2px solid #eee;
  }
  .auth-tabs li {
    width: 50%;
    text-align: center;
    padding: 12px;
    cursor: pointer;
    font-weight: 600;
    color: #555;
    transition: 0.3s;
    border-bottom: 2px solid transparent;
  }
  .auth-tabs li.active {
    border-bottom: 2px solid #48A7E5;
    color: #48A7E5;
  }
  .auth-forms .form {
    display: none;
  }
  .auth-forms .form.active {
    display: block;
  }
  .auth-forms form p {
    margin-bottom: 16px;
  }
  .auth-forms input[type="text"],
  .auth-forms input[type="email"],
  .auth-forms input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
  }
  .auth-forms button {
    width: 100%;
    padding: 12px;
    background: #48A7E5;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
  }
  .auth-forms button:hover {
    background: #3693d2;
  }
  .error {
    background: #ffe6e6;
    padding: 10px;
    margin-bottom: 16px;
    border-radius: 8px;
    color: #a94442;
  }
</style>

<div class="login-register-container">
  <ul class="auth-tabs">
    <li id="tab-login">Вход</li>
    <li id="tab-register">Регистрация</li>
  </ul>
  <div class="auth-forms">
     <div id="form-login" class="form <?php echo $default_tab==='login'?'active':''; ?>">
      <?php if (!empty($_SESSION['login_errors'])): ?>
        <div class="error error--login">
          <ul>
          <?php foreach($_SESSION['login_errors'] as $code):
              $msg = ''; switch($code) {
                case 'invalid_username': $msg = 'Неверный логин или пароль'; break;
                case 'incorrect_password': $msg = 'Неверный логин или пароль'; break;
                default: $msg = 'Ошибка авторизации.';
              }
          ?>
            <li><?php echo esc_html($msg); ?></li>
          <?php endforeach; unset($_SESSION['login_errors']); ?>
          </ul>
        </div>
      <?php endif; ?>
      <form id="loginform" method="post">
        <p><input type="text" name="log" placeholder="Имя пользователя или Email" required></p>
        <p><input type="password" name="pwd" placeholder="Пароль" required></p>
        <p><button type="submit" name="login_submit">Войти</button></p>
      </form>
    </div>

    <div id="form-register" class="form">
      <?php
      if (!empty($_SESSION['registration_errors']) && $_SESSION['registration_errors'] instanceof WP_Error) {
          foreach ($_SESSION['registration_errors']->get_error_messages() as $msg) {
              echo '<div class="error">' . esc_html($msg) . '</div>';
          }
          unset($_SESSION['registration_errors']);
      }
      ?>
      <form method="post" id="registrationForm">
        <p>
          <input type="text" name="reg_login" id="reg_login" placeholder="Логин" required>
        </p>
        <p>
          <input type="email" name="reg_email" id="reg_email" placeholder="Email" required>
        </p>
        <p>
          <input type="password" name="reg_password" id="reg_password" placeholder="Пароль (мин. 8 символов)" required>
        </p>
        <p>
          <input type="password" name="reg_password2" id="reg_password2" placeholder="Повторите пароль" required>
        </p>
        <p>
          <input type="text" name="reg_promo" placeholder="Промокод" required>
        </p>
        <p><button type="submit" name="register_submit">Регистрация</button></p>
      </form>
    </div>
  </div>
</div>

<script>
(function() {
    const registrationForm = document.getElementById('registrationForm');
    const usernameField     = document.getElementById('reg_login');
    const emailField        = document.getElementById('reg_email');
    const loginTab          = document.getElementById('tab-login');
    const registerTab       = document.getElementById('tab-register');
    const loginForm         = document.getElementById('form-login');
    const registerForm      = document.getElementById('form-register');
    const defaultTab        = '<?php echo esc_js($default_tab); ?>';
    const restUrl           = '<?php echo esc_url(rest_url('custom/v1/check-user')); ?>';

    // Устанавливаем вкладку
    if (defaultTab === 'register') {
        registerTab.classList.add('active');
        registerForm.classList.add('active');
    } else {
        loginTab.classList.add('active');
        loginForm.classList.add('active');
    }
    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active'); loginForm.classList.add('active');
        registerTab.classList.remove('active'); registerForm.classList.remove('active');
    });
    registerTab.addEventListener('click', () => {
        registerTab.classList.add('active'); registerForm.classList.add('active');
        loginTab.classList.remove('active'); loginForm.classList.remove('active');
    });

    // Функция очистки AJAX-ошибок
    function clearAjaxErrors() {
        document.querySelectorAll('.ajax-error').forEach(el => el.remove());
    }

    // AJAX-проверка на blur
    async function checkUser() {
        const data = { username: usernameField.value.trim(), email: emailField.value.trim() };
        clearAjaxErrors();
        if (!data.username && !data.email) return;
        try {
            const res = await fetch(restUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (res.status === 400 && json.errors) {
                if (json.errors.username) {
                    const div = document.createElement('div');
                    div.className = 'error ajax-error';
                    div.innerText = json.errors.username;
                    usernameField.after(div);
                }
                if (json.errors.email) {
                    const div = document.createElement('div');
                    div.className = 'error ajax-error';
                    div.innerText = json.errors.email;
                    emailField.after(div);
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    usernameField.addEventListener('blur', checkUser);
    emailField.addEventListener('blur', checkUser);

    // Проверяем перед отправкой
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        // Синхронные проверки
        const errors = {};
        if (userField.value.trim().length < 3) errors.username = 'Логин не менее 3 символов';
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(emailField.value.trim())) errors.email = 'Неверный формат email';
        if (pass1.value.length < 8) errors.pass = 'Пароль не менее 8 символов';
        if (pass1.value !== pass2.value) errors.pass2 = 'Пароли не совпадают';

        // Асинхронная проверка логина/email
        const remoteErrors = await ajaxCheck();
        Object.assign(errors, remoteErrors);

        // Если есть ошибки — вывести и не отправлять
        if (Object.keys(errors).length > 0) {
            if (errors.username) showError(userField, errors.username, 'ajax-error');
            if (errors.email)    showError(emailField, errors.email, 'ajax-error');
            if (errors.pass)     showError(pass1, errors.pass);
            if (errors.pass2)    showError(pass2, errors.pass2);
            return; // блокируем отправку
        }

        // Если ошибок нет — отправляем форму на сервер
        form.submit();
    });
})();
</script>

<?php get_footer();
?>
