<?php
/**
 * გასვლის ფუნქცია
 * 
 * ამ ფაილში:
 * 1. იწყებს session-ს
 * 2. ანადგურებს session-ს (გასვლა)
 * 3. გადამისამართებს მთავარ გვერდზე
 */
session_start();
session_destroy(); // session-ის განადგურება
header("Location: index.php"); // მთავარ გვერდზე გადამისამართება
exit;
?>