<?php
// TODO: This FFI module is currently a stub to allow compilation.
// If you need actual implementations, PRs are very welcome!

$exports['addAddressImpl'] = function($bl, $addr, $ty) { return false; };
$exports['addRangeImpl'] = function($bl, $start, $end, $ty) { return false; };
$exports['addSubnetImpl'] = function($bl, $net, $prefix, $ty) { return false; };
$exports['checkImpl'] = function($bl, $addr, $ty) { return false; };
$exports['rulesImpl'] = function($bl) { return []; };
return $exports;
