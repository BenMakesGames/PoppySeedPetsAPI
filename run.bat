@echo off
echo Starting PoppySeedPets API and SPA...

REM Start the Symfony API server in a new window
start "PoppySeedPets API" cmd /k "cd /d %~dp0api && symfony serve"

REM Start the Angular SPA dev server in a new window
start "PoppySeedPets SPA" cmd /k "cd /d %~dp0webapp && ng serve"

echo Both servers are starting in separate windows.
echo   API:  http://localhost:8000
echo   SPA:  http://localhost:4200
