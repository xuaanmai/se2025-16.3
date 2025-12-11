# Hướng dẫn chạy dự án trên Windows

## Yêu cầu
- PHP >= 8.0.2 (cài XAMPP)
- Composer
- Node.js >= 16.0
- MySQL (hoặc dùng XAMPP)

---

## Các bước chạy

### 1. Cài đặt dependencies

```bash
npm install
composer install
```

### 2. Tạo file .env

```bash
copy .env.example .env
```

### 3. Cấu hình database trong file `.env`

Mở file `.env` và sửa:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management
DB_USERNAME=root
DB_PASSWORD= 
```

**Lưu ý**: Tạo database `project_management` trong MySQL trước!

### 4. Generate key và setup database

```bash
php artisan migrate
php artisan db:seed
```

**Lưu ý quan trọng:**
Để có một bộ dữ liệu mẫu đầy đủ (bao gồm nhiều user, project, ticket...) để kiểm thử, bạn nên chạy lệnh sau thay cho 2 lệnh trên. Lệnh này sẽ xóa toàn bộ dữ liệu cũ và tạo lại từ đầu:
```bash
php artisan migrate:fresh --seed
```

### 5. Build frontend

```bash
npm run build
```

### 6. Chạy servers

**Terminal 1 - Backend:**
```bash
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
npm run dev
```

---

## Truy cập

- **Vue Frontend**: http://localhost:8000/app/login
- **Filament Frontend**: http://localhost:8000
- **Login mặc định**: 
  - Email: `john.doe@helper.app`
  - Password: `Passw@rd`

Một tài khoản manager@planora.app với mật khẩu password.
Nhiều người dùng, dự án, và công việc mẫu để bạn thoải mái kiểm thử các chức năng.

---