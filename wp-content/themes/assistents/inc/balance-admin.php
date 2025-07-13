<?php
// Добавляем поле "Баланс запросов" в профиль пользователя
function gpt_add_balance_field( $user ) {
    // Показываем только администраторам
    if ( ! current_user_can('edit_users') ) return;

    // Получаем текущее значение баланса
    $balance = get_user_meta( $user->ID, 'request_balance', true );
    ?>
    <h2>GPT Баланс</h2>
    <table class="form-table">
      <tr>
        <th><label for="request_balance">Баланс запросов</label></th>
        <td>
          <input type="number" name="request_balance" id="request_balance"
             value="<?php echo esc_attr( $balance ); ?>" class="regular-text" />
          <p class="description">Введите новое значение баланса запросов для этого пользователя.</p>
        </td>
      </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'gpt_add_balance_field');
add_action('edit_user_profile', 'gpt_add_balance_field');

// Сохраняем значение поля при обновлении профиля
function gpt_save_balance_field( $user_id ) {
    if ( ! current_user_can('edit_user', $user_id) ) return;

    if ( isset($_POST['request_balance']) ) {
        update_user_meta( $user_id, 'request_balance', intval( $_POST['request_balance'] ) );
    }
}
add_action('personal_options_update', 'gpt_save_balance_field');
add_action('edit_user_profile_update', 'gpt_save_balance_field');