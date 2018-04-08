<?php

use OrganizeSeries\domain\model\LicenseKey;
/**
 * Template for license key forms
 * @var LicenseKey $license_key
 * @var string $extension_slug
 */
$key = $license_key->getLicenseKey();
?>
<div id="os-license-key-container-<?php echo $extension_slug; ?>" class="wrap os-license-key-container">
    <div class="os-license-notices">
    </div>
    <h3>
        <?php printf(
            esc_html__('License Key for %1$s', 'organize-series'), $license_key->getItemName()
        ); ?>
    </h3>
    <label class="description" for="os-license-key-<?php echo $extension_slug; ?>"><?php esc_html_e('Enter your license key: ', 'organize-series'); ?></label>
    <input id="os-license-key-<?php echo $extension_slug; ?>" type="text" name="os_license_key_<?php echo $extension_slug; ?>" class="regular-text" value="<?php echo $key; ?>">
    <div class="os-license-key-meta">
        <?php if (! empty($key) && ! empty($license_key->getExpires())) : ?>
        <p>
            <strong>Expires:</strong> <?php echo $license_key->getExpires(); ?>
        </p>
        <?php endif;?>
    </div>
    <div class="license-key-submit-button">
        <?php wp_nonce_field('os_license_key_nonce_' . $extension_slug, 'os_license_key_nonce_' . $extension_slug); ?>
        <?php if ($license_key->getStatus() === 'valid') : ?>
            <input data-extension="<?php echo $extension_slug; ?>" type="submit" class="button-secondary deactivation-button js-license-submit" name="os_license_key_deactivate" value="<?php esc_html_e('Deactivate License', 'organize-series'); ?>">
        <?php else: ?>
            <input data-extension="<?php echo $extension_slug; ?>" type="submit" class="button-secondary activation-button js-license-submit" name="os_license_key_activate" value="<?php esc_html_e('Activate License', 'organize-series'); ?>">
        <?php endif; ?>
        <span class="spinner"></span>
    </div>
</div>