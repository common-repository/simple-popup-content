<?php
//check to see if the currently logged in user is a customer
function is_user_customer() {
  global $wpdb;
  $current_user_id = get_current_user_id();

  return $wpdb->get_var( "SELECT customer_id FROM stripe_users WHERE user_id = $current_user_id");
}

function get_form() {
  $pc_price = get_post_meta(get_the_ID(), '_rmspc_price');
  if(empty($pc_price)) {
    update_post_meta( get_the_ID(), '_rmspc_price', 0);
    $pc_price = 0;
  } else {
    $pc_price = $pc_price[0];
  }

  if (is_user_logged_in()) {
    //if the user is a customer, enqueue master2.js which calls customer-action.php which gets the user's id from the db and charges it
    if (is_user_customer()) {
      $action_type = 'customer-action';
    //if the user is logged in but is not a customer (hasn't purchased anything), enqueue master.js which calls user-action.php which will create a new customer and charge him
    } else {
      $action_type = 'user-action';
    }
  // else, just create a one-time use token and charge the card
  } else {
    $action_type = 'visitor-action';
  }
  ob_start();
  ?>
  <div id="prem-info">
    
    <p><button class="button button-primary" id="revealForm">Purchase: <span>$<?php echo $pc_price; ?></span></button></p>
    
    <p></p>
  </div>



  <style>
  .pw-cont {
      position: relative;
      height: 0px;
      text-align: center;
  }

  .pw-chevron::before {
      position: absolute;
      font-size: 10px !important;
      top: -20px;
      right
      color: green;
  }
  </style>
  <form action="" method="POST" id="payment-form" class="<?php echo $action_type; ?>">
    <span class="payment-errors"></span>
    <!-- if the user is not logged in, show the email address input -->

    <?php if (!is_user_logged_in()) { ?>
      <fieldset>
        <div class="grid-25 grid-parent form-row">
          <label>Email Address</label>
        </div>
        <div class="grid-75 grid-parent">
          <div class="form-item">
            <input type="text" size="50" id="email" placeholder="Your real email address" class="input-text">
          </div>
        </div>
      </fieldset>
      <div class="pw-cont"><i class="fa fa-chevron-down pw-chevron"></i></div>
      <fieldset class="password-field">
        <div class="grid-25 grid-parent form-row">
          <label>Password</label>
        </div>
        <div class="grid-75 grid-parent">
          <div class="form-item">
            <input type="password" size="50" id="password" placeholder="Leave blank to have password emailed" class="input-text">
          </div>
        </div>
      </fieldset>
    <?php } ?>
  <div id="credit-form">
        <fieldset>
          <div class="grid-25 grid-parent form-row">
            <label for="user_login">Card Number</label>
          </div>
          <div class="grid-75 grid-parent">
            <div class="form-item">
              <input type="text" size="20" data-stripe="number" class="input-text">
            </div>
          </div>
        </fieldset>

        <fieldset>
          <div class="grid-25 grid-parent form-row">
            <label for="user_login">Expiration (MM/YY)</label>
          </div>
          <div class="grid-75 grid-parent">
            <div class="form-item">
              <input type="text" size="2" data-stripe="exp_month" class="input-text short-input-text"><span> / </span>
              <input type="text" size="2" data-stripe="exp_year" class="input-text short-input-text">
            </div>
          </div>
        </fieldset>
        <fieldset>
          <div class="grid-25 grid-parent form-row">
            <label for="user_login">CVC</label>
          </div>
          <div class="grid-75 grid-parent">
            <div class="form-item">
              <input type="text" size="4" data-stripe="cvc"  class="input-text">
            </div>
          </div>
        </fieldset>
  </div>
    
    <div class="form-row">
      <label>
        <span></span>
        <input type="hidden" id="pid" size="4" data-item-id="<?php echo $post_id = get_the_ID(); ?>">
      </label>
    </div>
    

    <input type="submit" class="submit button button-primary" value="Buy Now">
    <p id="customer-option">Use Different Card Info? 
      <span><input type="checkbox" id="customer-option-check" class="short-input-text"></span>
    </p>
    <span id="user-option"><input type="checkbox" id="user-option-check" class="auto-width">Save Card Details 
      
    </span>
  </form>
  <?php
  $contents = ob_get_clean();
  return $contents;
}
