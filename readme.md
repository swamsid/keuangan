# Swamsid/Keuangan

![GitHub issues](https://img.shields.io/github/issues/swamsid/keuangan.svg)

Laravel Package Untuk Modul Keuangan Software-software di Alamraya.

## Getting Started

Berdoalah Terlebih Dahulu. 
jangan Lupa Untuk Membaca Instruksi Ini Dengan Seksama dan Perlahan-lahan.

### Installing

- Menggunakan Composer.

```bash
composer require swamsid/keuangan
```

- Registrasi Provider di config/app.

```bash
Swamsid\Keuangan\KeuanganServiceProvider::class,
	
```

## Cara Menggunakan
Perlu anda ketahui bahwa package ini adalah full resource package. Sehingga ketika anda berhasil menginstall ini, Anda telah memiliki akses penuh terhadap controller, model, bahkan tampilan (view) dari fitur-fitur keuangan yang akan digunakan. Setelah Anda Berhasil Menginstall, lakukan beberapa hal dibawah ini.

### Publish vendor
```bash
php artisan vendor:publish
```
**Lalu, Masukkan Nomor Provider: Swamsid\Keuangan\KeuanganServiceProvider (contoh)**
```bash
Which provider or tag's files would you like to publish?:
  [0] Publish files from all providers and tags listed below
  [1] Provider: Fideloper\Proxy\TrustedProxyServiceProvider
  [2] Provider: Illuminate\Mail\MailServiceProvider
  [3] Provider: Illuminate\Notifications\NotificationServiceProvider
  [4] Provider: Illuminate\Pagination\PaginationServiceProvider
  [5] Provider: Laravel\Tinker\TinkerServiceProvider
  [6] Provider: Swamsid\Keuangan\KeuanganServiceProvider
  [7] Tag: laravel-mail
  [8] Tag: laravel-notifications
  [9] Tag: laravel-pagination
 > 6
```

publishing secara otomatis membuat folder/file baru di project anda. Folder/File tersebut adalah : 

* **app\Http\Controllers\Keuangan** - *file ini berisi semua controller dari fitur-fitur keuangan*
* **app\Model\keuangan** - *file ini berisi semua Model dari fitur-fitur keuangan*
* **database\migrations\swamsid-keuangan** - *file ini berisi semua Migration dari table-table yang ada di fitur-fitur keuangan*
* **resources\views\keuangan** - *file ini berisi semua view yang digunakan di fitur-fitur keuangan*

Jika publishing anda berhasil, maka seharusnya project anda akan memiliki folder tersebut dan semua file didalamnya. karena Ini Adalah 'full resource package' tentu anda dapat mengedit semuanya sesuai kebutuhan anda.

### Database Migration
Fitur keuangan memiliki beberapa table database yang mungkin belum anda miliki, Sehingga anda perlu membuat Migration database dari Folder **database\migrations\swamsid-keuangan** yang tadi secara otomatis terbuat.

**Jika anda hanya ingin melakukan migration untuk fitur keuangan saja, lakukan ini. (direkomendasikan)**
```bash
php artisan migrate:refresh --path=database/migrations/swamsid-keuangan
```

**Atau Jika anda ingin melakukan migration di Semua file migration Anda, lakukan ini. (tidak direkomendasikan)**
```bash
php artisan migrate:refresh
```
### Routing
Package ini memerlukan routes yang akan dapat dipanggil dan terhubung langsung controller-controller yang ada. Anda Bisa Melihatnya melalui link dibawah ini. 

* [Route](https://docs.google.com/document/d/1pJ9SJi7ZSvH_qpoaDEf-mQ6YRg4COojfPHqasISEL7U/edit?usp=sharing) - Copy dan Paste-kan di File Route Project Anda

## Selesai
Lakukan uji coba dengan memanggil salah satu route yang tadi anda copy, apabila tidak ada pesan error, maka package ini siap anda gunakan. Pastikan anda mengecek sekali lagi apabila ingin merubah baris code di package ini.

### Terima Kasih ...