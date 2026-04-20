@echo off
setlocal
cd /d "C:\xampp\htdocs\ecommerce-website"
start "Laravel Server" cmd /k "php artisan serve"
start "Vite Dev" cmd /k "npm run dev"
endlocal
