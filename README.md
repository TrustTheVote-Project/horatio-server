# Absentee Ballot Server

A server for an absentee ballot request form, with [a corresponding client](https://github.com/waldoj/absentee-client).

## Overview

Fundamentally, this is a system to:

* validate provided JSON against a schema
* save the JSON
* convert the JSON into another format and transmit it by email

Which is to say that it's not very complicated.

## Instructions

1. Download and install onto a web server, into a directory named `api/`. 
1. Install [JSON Schema for PHP](https://github.com/justinrainbow/json-schema).
