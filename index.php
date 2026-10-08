<?php
/**
 * VerifyHub Root Redirect
 * Sends all root requests to the public folder
 */
header("Location: public/index.php");
exit();
?>