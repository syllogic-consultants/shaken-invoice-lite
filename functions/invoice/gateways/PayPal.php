<?php

/**
 * PayPal Payment Gateway
 * @gateway PayPal
 *
 * @since 1.0.0
 * 
 **/
 
global $post, $payment_gateway_account; ?>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="pay-form">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="<?php echo $payment_gateway_account; ?>">
    <input type="hidden" name="item_name" value="<?php _e( 'Invoice', 'shaken' ); ?> #<?php sh_invoice_number(); ?> | <?php the_title(); ?>">
    <input type="hidden" name="amount" value="<?php echo sh_get_the_invoice_subtotal(); ?>">
    <input type="hidden" name="tax" value="<?php echo sh_get_the_invoice_tax(); ?>">
    <input type="hidden" name="quantity" value="1">
    <input type="hidden" name="currency_code" value="<?php sh_invoice_currency_code(); ?>">
    <input type="hidden" name="first_name" value="<?php sh_invoice_client(); ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="return" value="<?php the_permalink(); ?>">
    <input type="hidden" name="cancel_return" value="<?php the_permalink(); ?>">
    <input type="hidden" name="notify_url" value="<?php echo add_query_arg('diap', 'yes', get_permalink($post->ID)); ?>">
    <input type="submit" value="<?php _e('Pay Now', 'sh_invoice'); ?>" name="submit" class="pay">
</form>
