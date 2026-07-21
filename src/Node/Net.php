<?php
$exports['isIPImpl'] = function($input) {
    if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return 4;
    if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return 6;
    return 0;
};
$exports['isIPv4'] = function($input) { return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false; };
$exports['isIPv6'] = function($input) { return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false; };
return $exports;
