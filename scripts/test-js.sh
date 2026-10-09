#!/bin/bash
# Script pour exécuter les tests JavaScript avec Vitest
# Ce script contourne les problèmes de permissions et de GLIBCXX

cd /home/omist/public/js
LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libstdc++.so.6 /usr/bin/node ./node_modules/.bin/vitest run
