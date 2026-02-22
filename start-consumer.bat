@echo off
start /B php bin/console messenger:consume async -vv > NUL 2>&1
echo ✅ Consumer démarré