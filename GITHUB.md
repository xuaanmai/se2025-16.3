# Quy trình làm việc GitHub (Phiên bản Đơn giản hóa)
(Laravel + Vue.js)

Đây là tài liệu quy định luồng làm việc (workflow) **đơn giản hóa** cho dự án. Mục tiêu là để 4 thành viên phối hợp nhịp nhàng, chỉ sử dụng các tính năng có sẵn của GitHub.

Mô hình này được gọi là **GitHub Flow**: rất nhanh, gọn, và mọi thứ đều xoay quanh nhánh `main`.

---

## 1. Thiết lập ban đầu (Làm 1 lần)

Người Lead sẽ thực hiện các bước này.

### 1.1. Cấu hình Repository

Chúng ta vẫn sử dụng **2 repository riêng biệt**:
1.  `project-management-api` (Cho Laravel Backend)
2.  `project-management-web` (Cho Vue.js Frontend)

**Với mỗi repository:**
1.  Tạo repository trên GitHub, chọn **Private**.
2.  Check "Add a README file" và "Add .gitignore" (Chọn template `Laravel` hoặc `Vue` tương ứng).
3.  Vào **Settings** > **Collaborators** > Mời 3 thành viên còn lại vào.

### 1.2. Cấu hình `.gitignore` (Bắt buộc)

File này đảm bảo bạn không "commit" file rác, file thư viện (`vendor`, `node_modules`) hoặc file nhạy cảm (`.env`) lên GitHub.

**Repo `project-management-api` (Laravel):**

/vendor/
/node_modules/
.env
.env.testing
/storage/app/public/
/public/storage
.DS_Store

**Repo `project-management-web` (Vue.js):**

/node_modules/
/dist/
.env.local
.env.development.local
.env.production.local
.env.test.local
.DS_Store

### 1.3. File môi trường `.env.examCommit
🧭 I. Cấu trúc repository
Repo: se2025-16.3

Nhánh chính: main (ổn định, để release cuối cùng)

Nhánh phát triển: test01 (nhánh nhóm làm việc)
Cấu trúc thư mục:


/frontend   ← Vue.js
/backend    ← Laravel
/docs       ← tài liệu báo cáo, ERD, UML, hướng dẫn
👥 II. Vai trò nhóm (4 người ví dụ)
Thành viênVai tròNhánh phụ tráchAFrontend lead (UI, Gantt, Vue Router, API call)feature/frontend-*BBackend lead (Laravel API, Model, Controller)feature/backend-*CDatabase + Auth + API integrationfeature/integration-*DTest + Fix bug + Deploy + Documentfix/*, docs/*
🔀 III. Quy trình làm việc chuẩn (GitHub Flow)
1️⃣ Bắt đầu từ nhánh phát triển

git checkout test01
git pull origin test01
2️⃣ Tạo nhánh tính năng riêng

git checkout -b feature/frontend-login
3️⃣ Làm việc & commit rõ ràng
Ví dụ:


git add .
git commit -m "feat(frontend-login): tạo form đăng nhập và gọi API login"
4️⃣ Đẩy lên GitHub

git push origin feature/frontend-login
5️⃣ Tạo Pull Request
Lên GitHub → Pull requests → New pull request
Chọn:
Base: test01
Compare: feature/frontend-login
Ghi mô tả ngắn gọn: “Hoàn thành giao diện và API login phía frontend.”
Assign cho Leader review & merge.
6️⃣ Merge xong xóa nhánh cũ
Sau khi merge PR, leader hoặc dev chạy:


git branch -d feature/frontend-login
🧩 IV. Quy ước commit message (rất quan trọng)
Loại commitCú phápVí dụ✨ Thêm tính năngfeat(scope): mô tảfeat(auth): thêm API đăng nhập🐞 Sửa lỗifix(scope): mô tảfix(ui): căn chỉnh nút Save🧹 Dọn dẹp / cải tiếnrefactor(scope): mô tảrefactor(api): gom code axios🧪 Kiểm thửtest(scope): mô tảtest(api): thêm test đăng nhập📝 Tài liệudocs(scope): mô tảdocs(readme): cập nhật hướng dẫn setup


## 🎯 Tổng kết

- **Chỉ 1 nhánh chính (`main`)** → sạch sẽ, dễ kiểm soát.  
- **Mỗi tính năng = 1 nhánh feature riêng** → tách biệt, dễ review.  
- **Mọi PR đều cần review** → đảm bảo chất lượng code.  
- **GitHub Projects + Issues** → quản lý trực quan, không cần tool ngoài.  
- **GitHub Actions** → tự động kiểm thử, đảm bảo ổn định trước khi merge.

