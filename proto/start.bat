@echo off
cd /d "%~dp0"
docker compose up -d
docker compose ps
curl.exe -s -o NUL -w "Laravel status: %%{http_code}\n" http://127.0.0.1:8088/up
pause