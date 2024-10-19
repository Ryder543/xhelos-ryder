<?php
// $Id: taxonomy-filter-section.tpl.php,v 1.1 2008/07/13 22:52:33 styro Exp $
?>
<div<?php if ($class) { print ' class="'. $class .'"'; } ?>>
<h3><?php print $title ?></h3>
<?php if ($is_list): ?>
    <ul>
<?php endif; ?>
    <?php print $content ?>
<?php if ($is_list): ?>
    </ul>
<?php endif; ?>
</div>