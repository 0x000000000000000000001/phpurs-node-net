<?php
// TODO: This FFI module is currently a stub to allow compilation.
// If you need actual implementations (e.g. using Amphp for networking), PRs are very welcome!

$exports['newImpl'] = function($o) { return new \stdClass(); };
$exports['addressImpl'] = function($s) { return new \stdClass(); };
$exports['bytesReadImpl'] = function($s) { return $s->bytesRead ?? 0; };
$exports['bytesWrittenImpl'] = function($s) { return $s->bytesWritten ?? 0; };
$exports['createConnectionImpl'] = function($o) { return new \stdClass(); };
$exports['connectTcpImpl'] = function($s, $o) { return $s; };
$exports['connectIpcImpl'] = function($s, $p) { return $s; };
$exports['connectingImpl'] = function($s) { return false; };
$exports['destroySoonImpl'] = function($s) { return $s; };
$exports['localAddressImpl'] = function($s) { return ''; };
$exports['localPortImpl'] = function($s) { return 0; };
$exports['localFamilyImpl'] = function($s) { return ''; };
$exports['pendingImpl'] = function($s) { return false; };
$exports['refImpl'] = function($s) { return $s; };
$exports['remoteAddressImpl'] = function($s) { return ''; };
$exports['remotePortImpl'] = function($s) { return 0; };
$exports['remoteFamilyImpl'] = function($s) { return ''; };
$exports['resetAndDestroyImpl'] = function($s) { return $s; };
$exports['setKeepAliveImpl'] = function($s) { return $s; };
$exports['setKeepAliveBooleanImpl'] = function($s, $b) { return $s; };
$exports['setKeepAliveInitialDelayImpl'] = function($s, $d) { return $s; };
$exports['setKeepAliveAllImpl'] = function($s, $b, $d) { return $s; };
$exports['setNoDelayImpl'] = function($s) { return $s; };
$exports['setNoDelayBooleanImpl'] = function($s, $b) { return $s; };
$exports['setTimeoutImpl'] = function($s, $msecs) { return $s; };
$exports['timeoutImpl'] = function($s) { return 0; };
$exports['unrefImpl'] = function($s) { return $s; };
$exports['readyStateImpl'] = function($s) { return ''; };
return $exports;
