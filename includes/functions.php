<?php
session_start();

/**
 * ამოწმებს მომხმარებელი ავტორიზებულია თუ არა
 * @return bool true თუ მომხმარებელი შესულია, false თუ არა
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * გადამისამართებს მომხმარებელს მითითებულ URL-ზე
 * @param string $url სამიზნე URL მისამართი
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * გაასუფთავებს და ასახავს მომხმარებლის შეყვანილ მონაცემებს XSS თავდასხმებისგან
 * @param string|null $input შეყვანილი მონაცემი
 * @return string გაწმენდილი და დაცული მონაცემი
 */
function sanitize($input)
{
    return htmlspecialchars(trim($input ?? ''));
}
?>