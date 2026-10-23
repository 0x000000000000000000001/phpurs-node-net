# phpurs-node-net

> PHPurs checkout: see the [PHP library development guide](https://github.com/0x000000000000000000001/phpurs/blob/main/docs/libraries.md) for the TAST toolchain, FFI conventions and validation commands. Run `./bin/test` from this package with the PHP toolchain prepared.

[![Latest release](http://img.shields.io/github/release/purescript-node/purescript-node-net.svg)](https://github.com/purescript-node/purescript-node-net/releases)
[![Build status](https://github.com/purescript-node/purescript-node-net/workflows/CI/badge.svg?branch=master)](https://github.com/purescript-node/purescript-node-net/actions?query=workflow%3ACI+branch%3Amaster)
[![Pursuit](https://pursuit.purescript.org/packages/purescript-node-net/badge)](https://pursuit.purescript.org/packages/purescript-node-net)

A wrapper for Node's net API.

## PHP runtime

`src/Node/Net.php` provides IP-address validation. The socket module and the
`PhpursMockNetServer` constructors are placeholders: they do not open TCP or IPC
sockets. Server operations delegate to HTTP objects through `httpListen` when
present; the HTTP library owns its own in-process dispatch. Address and connection
queries currently return fixed values.

These limits belong to the PHP implementation; the upstream Node API documentation
below covers the full bindings.

## Installation

```
spago install node-net
```

## Documentation

Module documentation is [published on Pursuit](http://pursuit.purescript.org/packages/purescript-node-net).
