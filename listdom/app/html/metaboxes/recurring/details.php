<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$recurring = new LSD_Payments_Recurring($post);
$total = $recurring->get_total();
$currency = $recurring->get_currency();
$gateway_key = $recurring->get_gateway();
$gateway = $recurring->get_gateway_instance();
$first_order = $recurring->get_first_order();
$user = $recurring->get_user();
$gateway_meta = $recurring->get_gateway_meta();

$link_patterns = [];
if ($gateway_key === 'stripe')
{
    $link_patterns = [
        'stripe_customer_id' => 'customers/%s',
        'stripe_subscription_id' => 'subscriptions/%s',
        'stripe_payment_method_id' => 'payment_methods/%s',
        'stripe_setup_intent_id' => 'setup_intents/%s',
    ];
}

$gateway_meta_rows = [];
foreach ($gateway_meta as $meta_key => $meta_value)
{
    if (!is_scalar($meta_value) || $meta_value === '') continue;

    $label = ucwords(str_replace('_', ' ', (string) $meta_key));
    $value = (string) $meta_value;
    $link = '';

    if (isset($link_patterns[$meta_key]))
    {
        $path = sprintf($link_patterns[$meta_key], rawurlencode($value));
        $link = 'https://dashboard.stripe.com/' . ltrim($path, '/');
    }

    $display_value = $value;
    if (!$link && $display_value !== '')
    {
        $display_value = ucfirst($display_value);
    }

    $gateway_meta_rows[] = [
        'label' => $label,
        'value' => $value,
        'display_value' => $display_value,
        'link' => $link,
    ];
}

$customer_parts = [];
if ($first_order)
{
    if ($name = $first_order->get_name()) $customer_parts[] = esc_html($name);
    if ($email = $first_order->get_email()) $customer_parts[] = esc_html($email);
}
else if ($user)
{
    $customer_parts[] = esc_html($user->display_name);
    if ($user->user_email) $customer_parts[] = esc_html($user->user_email);
}
?>
<div class="lsd-metabox lsd-order-metabox">
    <table class="widefat fixed striped lsd-order-table">
        <tbody>
            <tr>
                <th><?php esc_html_e('Amount', 'listdom'); ?></th>
                <td><?php echo $this->render_price($total, $currency, false, false); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Gateway', 'listdom'); ?></th>
                <td><?php echo esc_html($gateway ? $gateway->name() : $gateway_key); ?></td>
            </tr>
            <?php if ($customer_parts): ?>
            <tr>
                <th><?php esc_html_e('Customer', 'listdom'); ?></th>
                <td><?php echo implode(' - ', $customer_parts); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($gateway_meta_rows): ?>
        <h4 class="lsd-mt-4"><?php esc_html_e('Gateway Details', 'listdom'); ?></h4>
        <table class="widefat fixed striped lsd-order-table">
            <tbody>
                <?php foreach ($gateway_meta_rows as $meta_row): ?>
                <tr>
                    <th><?php echo esc_html($meta_row['label']); ?></th>
                    <td>
                        <?php if ($meta_row['link']): ?>
                            <a href="<?php echo esc_url($meta_row['link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($meta_row['value']); ?></a>
                        <?php else: ?>
                            <?php echo esc_html($meta_row['display_value']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
