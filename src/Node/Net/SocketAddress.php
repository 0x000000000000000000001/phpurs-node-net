<?php
// TODO: This FFI module is currently a stub to allow compilation.
// If you need actual implementations, PRs are very welcome!

$exports['newImpl'] = function($options) { return new \stdClass(); };
$exports['address'] = function($sa) { return ''; };
$exports['familyImpl'] = function($sa) { return ''; };
$exports['flowLabelImpl'] = function($sa) { return 0; };
$exports['port'] = function($sa) { return 0; };
return $exports;
