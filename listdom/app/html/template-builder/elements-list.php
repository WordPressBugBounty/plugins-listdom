<?php
/**
 * Template builder elements list.
 *
 * @var string $current_type
 */

$grouped_elements = LSD_Template::grouped_elements($current_type);
?>
<?php foreach ($grouped_elements as $category_key => $items): if (empty($items)) continue; ?>
    <section class="lsd-template-editor-section">
        <h4 class="lsd-template-editor-section__title lsd-admin-subtitle-tiny">
            <?php echo LSD_Template::category_label($category_key); ?>
        </h4>

        <ul class="lsd-template-editor-element-list">
            <?php foreach ($items as $item): ?>
                <li class="lsd-template-editor-element-list__item"
                    draggable="false"
                    data-lsd-element-type="<?php echo esc_attr($item['key']); ?>"
                    data-lsd-element-label="<?php echo esc_attr($item['label']); ?>"
                    data-lsd-element-icon="<?php echo esc_attr($item['icon']); ?>">
                    <a href="#"
                       role="button"
                       draggable="false"
                       class="lsd-template-editor-element-list__button"
                       data-lsd-element-type="<?php echo esc_attr($item['key']); ?>"
                       data-lsd-element-label="<?php echo esc_attr($item['label']); ?>"
                       data-lsd-element-icon="<?php echo esc_attr($item['icon']); ?>">
                        <i class="<?php echo esc_attr($item['icon']); ?>"></i>
                        <span class="lsd-template-editor-element-list__label"><?php echo esc_html($item['label']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endforeach; ?>
