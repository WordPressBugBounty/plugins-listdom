<?php
defined('ABSPATH') || die();

$social_options = LSD_Options::socials();
$socials = new LSD_Socials();
$editable = false;
?>
<?php foreach ($profile_body as $field): ?>
    <?php if ($field['kind'] === 'layout'): ?>
        <?php include lsd_template('auth/user-field-layout.php'); ?>
    <?php else: ?>
        <?php
        $key = $field['key'];
        $value = $field['value'];
        if ($field['kind'] === 'custom')
        {
            include lsd_template('auth/user-field.php');
            continue;
        }
        if ($field['social'])
        {
            $social = $socials->get($key, $social_options[$key]);
            echo '<ul class="lsd-profile-socials"><li>' . LSD_Kses::element($social->owner($value)) . '</li></ul>';
            continue;
        }
        $link = '';
        if ($key === 'website') $link = esc_url($value);
        if ($key === 'email') $link = esc_url('mailto:' . $value);
        if (in_array($key, ['phone', 'mobile'], true)) $link = esc_url('tel:' . $value);
        ?>
        <?php if ($key === 'bio'): ?>
            <p class="lsd-profile-bio"><?php echo nl2br(esc_html($value)); ?></p>
        <?php else: ?>
            <div class="lsd-profile-<?php echo esc_attr($key); ?>">
                <strong><?php echo esc_html($field['label']); ?></strong>
                <?php if ($link): ?>
                    <a href="<?php echo $link; ?>"><?php echo esc_html($value); ?></a>
                <?php else: ?>
                    <span><?php echo esc_html($value); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>
