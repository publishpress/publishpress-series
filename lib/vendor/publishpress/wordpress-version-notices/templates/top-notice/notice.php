<?php
$linkStart = '</div><div class="pp-version-notice-bold-purple-button"><a href="' . $context['linkURL'] . '" target="_blank">';
$linkEnd   = '</a></div>';
$message   = sprintf('<div class="pp-version-notice-bold-purple-message">' . $context['message'], $linkStart, $linkEnd);
?>
<div class="pp-version-notice-bold-purple"><?php echo $message; ?></div>
