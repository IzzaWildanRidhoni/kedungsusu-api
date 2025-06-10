# 🥛 Kedung Susu API

**Kedung Susu API** adalah RESTful API backend yang dibangun menggunakan Laravel 10. API ini digunakan untuk mengelola data pengguna dan produk susu murni dengan autentikasi token dan kontrol akses berbasis role (admin & user).

API : [API LINK](https://.postman.co/workspace/My-Workspace~d7920d57-a8a8-4064-801a-1d805ceeeb4e/collection/9887809-95f558e0-b4d6-4651-9b7c-3d290b8140f1?action=share&creator=9887809)

## 🚀 Fitur Utama

- Autentikasi dengan Laravel Sanctum
- Role-based access control (Spatie Laravel Permission)
- CRUD User (khusus admin)
- CRUD Produk (admin)
- Upload gambar produk
- Format response JSON konsisten
- Validasi dan error handling lengkap

## 🧱 Teknologi

- Laravel 10
- Laravel Sanctum
- Spatie Laravel Permission
- Laravel Filesystem (storage/public)

## 📦 Instalasi

```bash
git clone https://github.com/yourusername/kedungsusu-api.git
cd kedungsusu-api

composer install
cp .env.example .env
php artisan key:generate

# Sesuaikan konfigurasi database di .env
php artisan migrate --seed
php artisan storage:link
```

## 🔐 Endpoint Autentikasi

| Method | Endpoint       | Deskripsi         |
|--------|----------------|-------------------|
| POST   | `/api/register` | Register user     |
| POST   | `/api/login`    | Login user        |
| GET    | `/api/me`       | Ambil data user   |
| POST   | `/api/logout`   | Logout user       |

## 👥 Endpoint User (Admin Only)

| Method | Endpoint             | Deskripsi           |
|--------|----------------------|---------------------|
| GET    | `/api/users`         | List semua user     |
| POST   | `/api/users`         | Tambah user         |
| GET    | `/api/users/{id}`    | Lihat detail user   |
| PUT    | `/api/users/{id}`    | Update user         |
| DELETE | `/api/users/{id}`    | Hapus user          |

## 🛒 Endpoint Produk (Admin only kecuali get all product )

| Method | Endpoint               | Role    | Deskripsi         |
|--------|------------------------|---------|-------------------|
| GET    | `/api/products`        | Semua   | List produk       |
| POST   | `/api/products`        | Admin   | Tambah produk     |
| PUT    | `/api/products/{id}`   | Admin   | Update produk     |
| DELETE | `/api/products/{id}`   | Admin   | Hapus produk      |

## 📤 Upload Gambar Produk

- Gunakan `multipart/form-data`
- Field: `image`
- File disimpan di `storage/app/public/products`
- Path disimpan dalam field `image_url`

## 🧪 Testing via Postman

Tambahkan Authorization di Header:
```
Authorization: Bearer <your_token>
```

Contoh Register:
```json
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password"
}
```

Contoh Login:
```json
POST /api/login
{
  "email": "john@example.com",
  "password": "password"
}
```

## 🔐 Role Default

- Saat register → role: `user`
- Role `admin` dapat ditambahkan via Seeder:
```php
php artisan db:seed --class=RoleSeeder
```

## 📝 Lisensi

Proyek ini menggunakan lisensi MIT. Bebas digunakan dan dikembangkan.
