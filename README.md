<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Project

Project ini merupakan layanan API yang menyediakan data anime dengan token Otentikasi. Respon yang diberikan dari layanan ini seperti judul anime, total episode, penulis, tanggal rilis, deskripsi, rating, genre, dan lain-lain. Seperti API publik pada umumnya, layanan ini juga memiliki dokumentasi berbasis website. Pengguna juga dapat berlangganan VIP dengan mengunjungi websitenya dan melakukan pembayaran melalui payment gateway yang desediakan untuk mendapatkan token VIP. Video yang diupload akan otomatis dibuatkan short clip nya yang berdurasi 10 detik, dimana short clip ini dapat dijadikan preview dari anime tersebut. Project ini juga sudah tercover feature test dan uni test untuk memudahkan pengembangan nantinya. 

## How To Use

- composer install
- install ffmpeg download disini https://ffmpeg.org/download.html
- pada .env setting FFMPEG_BINARIES= //sesuai lokasi ffmpeg.exe dalam forlder bin
- pada .env setting FFPROBE_BINARIES= //sesuai lokasi ffprobe.exe dalam forlder bin
- php artisan migrate:fresh
- php artisan queue:work
- php artisan schedule:run
- npm install
- npm run dev

## How To Run Testing
- php artisan test --filter feature
- php artisan test --filter unit

## Built With

- Laravel 10
- MySQL
- PHP Midtrans Payment Gateway (Snap version)
- Laravel-ffmpeg & Laravel-ffprobe
- Laravel sanctum
- dyrynda/laravel-cascade-soft-deletes
- Bootstrap
- Jquery

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
