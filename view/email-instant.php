<?php

// template for email

?>
<h2><?php echo $email_subject; ?></h2>
<p>It appears someone has hit a 404 on your site, they attempted to go to: <b><?php echo $_SERVER['REQUEST_URI']; ?></b>.</p>
<p>Cheers,<br />The Management</p>
