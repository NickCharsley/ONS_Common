<?php
function endswith($haystack, $needle) {
    $new_str = strrev($haystack);
    $new_needle = strrev($needle);
    $pos = strpos($new_str, $new_needle);
    if ($pos === FALSE || $pos > 0) {
        return FALSE;
    }
    return TRUE;
}

function startswith($haystack,$needle) {
    $pos = strpos($haystack, $needle);
    if ($pos === 0) {
        return True;
    }
    return False;
}
?>
