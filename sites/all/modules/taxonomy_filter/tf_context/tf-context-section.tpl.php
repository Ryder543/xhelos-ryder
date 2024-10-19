<?php
// $Id: tf-context-section.tpl.php,v 1.3 2009/05/18 04:52:39 solotandem Exp $

// <div class="section tf_context<?php if ($class) { print " $class"; }
?>
<div<?php if ($class) { print ' class="'. $class .'"'; } ?>>
<h3><?php print $title ?></h3>
<?php if (isset($parents)): ?>
    <h4><?php print $parents_title ?></h4>
    <ul>
    <?php print $parents ?>
    </ul>
<?php endif; ?>
<?php if (isset($children)): ?>
    <h4><?php print $children_title ?></h4>
    <ul>
    <?php print $children ?>
    </ul>
<?php endif; ?>
<?php if (isset($related)): ?>
    <h4><?php print $related_title ?></h4>
    <ul>
    <?php print $related ?>
    </ul>
<?php endif; ?>
<?php if (isset($toplevel)): ?>
    <h4><?php print $toplevel_title ?></h4>
    <ul>
    <?php print $toplevel ?>
    </ul>
<?php endif; ?>
</div>