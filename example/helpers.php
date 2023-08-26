<?php


function generateRandomString($length = 10)
{
    // Check if the length is valid
    if ($length <= 0) {
        return false;
    }

    // Generate random bytes and convert to a string
    $randomBytes = random_bytes($length);
    $randomString = base64_encode($randomBytes);

    // Remove any non-alphanumeric characters
    $randomString = preg_replace('/[^a-zA-Z0-9]/', '', $randomString);

    // Truncate or pad the string to the desired length
    $randomString = substr($randomString, 0, $length);

    return $randomString;
}