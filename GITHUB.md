# Quy trình làm việc GitHub cho Dự án Project Management  
(Laravel + Vue.js)

Đây là tài liệu quy định luồng làm việc (workflow) bắt buộc cho tất cả thành viên trong dự án. Mục tiêu là đảm bảo code luôn ổn định, giảm thiểu xung đột (conflict) và quản lý tiến độ hiệu quả.

Sự tuân thủ nghiêm ngặt quy trình này là chìa khóa để hoàn thành dự án trong 2 tháng.

---

## 1. Thiết lập ban đầu (Làm 1 lần)

Người Lead (hoặc người tạo) sẽ thực hiện các bước này.

### 1.1. Cấu hình Repository

Chúng ta sẽ sử dụng **2 repository riêng biệt**:
1.  `project-management-api` (Cho Laravel Backend)
2.  `project-management-web` (Cho Vue.js Frontend)

**Với mỗi repository:**
1.  Tạo repository trên GitHub, chọn **Private**.
2.  **Quan trọng:** Check vào "Add a README file" và "Add .gitignore".
    * Repo `project-management-api`, chọn `.gitignore` template là **Laravel**.
    * Repo `project-management-web`, chọn `.gitignore` template là **Vue**.
3.  Vào **Settings** > **Collaborators** > Mời 3 thành viên còn lại vào.

### 1.2. Cấu hình `.gitignore`

**Trong repo `project-management-api` (Laravel):**
```gitignore
.env
.env.testing
/vendor/
/node_modules/
/storage/app/public/
/public/storage
.DS_Store
npm-debug.log*
yarn-debug.log*
yarn-error.log*
```

**Trong repo `project-management-web` (Vue.js):**
```gitignore
.env.local
.env.development.local
.env.production.local
.env.test.local
/node_modules/
/dist/
.DS_Store
npm-debug.log*
yarn-debug.log*
yarn-error.log*
```

### 1.3. File môi trường `.env`
Tạo file `.env.example` trong cả 2 repo, copy từ `.env` và **xóa giá trị nhạy cảm**, sau đó commit lên.

---

## 2. Mô hình phân nhánh (Branching Model)

### 2.1. Các nhánh chính
- **`main`**: Code hoàn thiện, sẵn sàng deploy. 🚫 Không push trực tiếp.  
- **`develop`**: Code tích hợp (beta). 🚫 Không push trực tiếp.

### 2.2. Các nhánh hỗ trợ
- `feature/<ten-tinh-nang>` → Tạo từ `develop`, merge về `develop`
- `bugfix/<ten-bug>` → Tạo từ `develop`, merge về `develop`
- `hotfix/<ten-loi-nghiem-trong>` → Tạo từ `main`, merge về `main` và `develop`

### 2.3. Bảo vệ nhánh
Cấu hình trong GitHub:
- **main**: Require pull request + Require 1 approval  
- **develop**: Require pull request + (Optional) Require approvals

---

## 3. Quản lý công việc (Issues & Projects)

### 3.1. Milestones
Được Lead tạo theo **Gantt Chart** (ví dụ: Tuần 1 - Auth, Tuần 2 - Project Module...).  
👉 **Gantt Chart chính là bản kế hoạch tiến độ chia theo tuần**, thể hiện bằng **Milestone + Kanban Board** trong GitHub Projects.

### 3.2. Issues
Mỗi nhiệm vụ = 1 Issue.  
Ví dụ: `[API] Tạo AuthController`.  
Có **Title**, **Description**, **Assignee**, **Labels**, **Milestone**.

### 3.3. Projects (Kanban Board)
Cột: `To Do`, `In Progress`, `Needs Review`, `Done`.  
Kéo Issue tương ứng qua từng cột theo tiến trình làm việc.

---

## 4. Vòng đời của một tính năng

### Bước 1: Nhận việc
- Gán Issue cho mình.  
- Kéo sang **In Progress** trong Kanban.

### Bước 2: Cập nhật code mới nhất
```bash
git checkout develop
git pull origin develop
```

### Bước 3: Tạo nhánh làm việc
```bash
git checkout -b feature/12-project-controller
```

### Bước 4: Code + Commit
```bash
git add .
git commit -m "Feat(API): Thêm ProjectController CRUD"
```

#### Convention Commit:
- `Feat:` – thêm tính năng
- `Fix:` – sửa lỗi
- `Refactor:` – tối ưu
- `Chore:` – cập nhật phụ
- `Docs:` – chỉnh tài liệu

### Bước 5: Push lên GitHub
```bash
git push -u origin feature/12-project-controller
```

### Bước 6: Tạo Pull Request (PR)
- Base: `develop` ← Compare: `feature/...`
- Title: `Feat(API): Hoàn thành ProjectController CRUD`
- Description: `Closes #12`
- Reviewer: 1–2 người
- Issue chuyển sang **Needs Review**

---

## 5. Review & Merge

### Reviewer
- Vào tab “Files changed” → Kiểm tra code  
- Dùng **Approve** hoặc **Request changes**

### Tác giả PR
- Nếu cần sửa:
```bash
git add .
git commit -m "Fix(Review): Sửa theo góp ý reviewer"
git push
```

### Merge
Khi PR được approve:
- Chọn **“Squash and merge”**  
- Xóa nhánh feature

Sau đó:
```bash
git checkout develop
git pull origin develop
git branch -d feature/12-project-controller
```

---

## 6. Giải quyết Conflict

### Khi bị conflict
```bash
git checkout develop
git pull origin develop
git checkout feature/12-project-controller
git merge develop
```

- Mở file có conflict, chỉnh thủ công:
```
<<<<<<< HEAD
(code của bạn)
=======
(code của develop)
>>>>>>> develop
```
- Sau đó:
```bash
git add .
git commit -m "Merge: giải quyết conflict với develop"
git push
```

---

## 7. CI/CD với GitHub Actions

### Laravel CI
Tạo file `.github/workflows/laravel.yml`
```yaml
name: Laravel CI
on:
  push:
    branches: [ "main", "develop" ]
  pull_request:
    branches: [ "develop" ]
jobs:
  laravel-tests:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"
    - name: Install Dependencies
      run: composer install --prefer-dist --no-progress
    - name: Generate key
      run: php artisan key:generate
    - name: Directory Permissions
      run: chmod -R 777 storage bootstrap/cache
    - name: Create Database
      run: |
        mkdir -p database
        touch database/database.sqlite
    - name: Run Migrations & Tests
      env:
        DB_CONNECTION: sqlite
        DB_DATABASE: database/database.sqlite
      run: php artisan test
```

### Vue CI
Tạo file `.github/workflows/vue.yml`
```yaml
name: Vue CI
on:
  push:
    branches: [ "main", "develop" ]
  pull_request:
    branches: [ "develop" ]
jobs:
  build-and-test:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - name: Use Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18.x'
        cache: 'npm'
    - name: Install Dependencies
      run: npm install
    - name: Run Linter
      run: npm run lint
    - name: Run Tests
      run: npm run test:unit
```

---

## 8. Kết nối giữa GitHub và Gantt Chart

**Gantt Chart = Milestones + Kanban Workflow**

| Tuần | Milestone | Trạng thái | Mô tả |
|------|------------|------------|-------|
| Tuần 1 | Auth & User Module | 🟢 Done | Đăng ký, đăng nhập, phân quyền |
| Tuần 2 | Project CRUD | 🟡 In Progress | Tạo/sửa/xóa dự án |
| Tuần 3 | Task Module | 🔵 To Do | Quản lý công việc trong project |
| Tuần 4 | Kanban UI | ⚪ Pending | Giao diện kéo thả |
| Tuần 5 | Reports & Dashboard | ⚪ Pending | Thống kê tiến độ |
| Tuần 6 | Final Testing | ⚪ Pending | Kiểm thử & Fix lỗi |

> 👉 Bảng này chính là **bản Gantt Chart rút gọn**, giúp theo dõi tiến độ tổng thể theo thời gian.

---

## ✅ Tóm tắt Quy trình Nhóm

| Giai đoạn | Người thực hiện | Hành động chính |
|------------|----------------|----------------|
| Khởi tạo Repo | Lead | Tạo `main`, `develop`, cấu hình rule |
| Lập kế hoạch | Lead | Tạo Milestones + Issues |
| Phát triển | Dev | Code trên nhánh `feature/...` |
| Review | Reviewer | Approve hoặc Request changes |
| Merge | Lead/Dev | Squash merge vào `develop` |
| Kiểm thử & CI | GitHub Action | Tự động test trước khi merge |
| Tổng hợp | Lead | Gộp `develop` → `main` sau khi ổn định |

---

**📦 Cuối cùng:**  
Khi dự án hoàn tất, Lead sẽ:
```bash
git checkout main
git merge develop
git push origin main
```
→ Đây là phiên bản chính thức để deploy.

---

> 🧭 *Toàn bộ quy trình này cần được tuân thủ tuyệt đối để đảm bảo dự án đi đúng tiến độ và tránh xung đột code.*
