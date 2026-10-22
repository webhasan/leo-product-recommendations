<?php
// Popup shell, filled by JavaScript after add to cart.
$lpr_modern = class_exists('\\LoeCoder\\Plugin\\ProductRecommendations\\Popup_Modern') && \LoeCoder\Plugin\ProductRecommendations\Popup_Modern::is_modern($this);
?>
<div class="lpr-modal woocommerce<?php echo $lpr_modern ? ' lpr-modal--modern' : ''; ?>" id="lpr-modal" style="display:none;"<?php echo $lpr_modern ? ' role="dialog" aria-modal="true" aria-labelledby="lpr-pm-title"' : ''; ?>>
    <div class="lpr-modal-dialog lpr-modal-dialog-scrollable" id="lpr-modal-content"<?php echo $lpr_modern ? ' tabindex="-1"' : ''; ?>>
        <!-- modal content will load by JavaScript -->
    </div>
</div>
