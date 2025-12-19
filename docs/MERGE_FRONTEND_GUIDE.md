# Hướng dẫn Hợp nhất Frontend Nâng cao

## Bối cảnh và Mục tiêu

**Mục tiêu:** Tích hợp giao diện người dùng (frontend) hiện đại, đã được sửa lỗi và nâng cấp của **Project A** (dự án chúng ta vừa làm việc) vào **Project B** (dự án mới có backend tốt hơn của bạn).

**Tại sao cần làm theo hướng dẫn này?**

Bạn không thể chỉ copy-paste thư mục `resources` một cách đơn giản vì frontend không hoạt động độc lập. Nó phụ thuộc chặt chẽ vào:
1.  **API Backend:** Frontend được lập trình để "nói chuyện" với một bộ API có cấu trúc cụ thể. Nếu API của Project B khác biệt, frontend sẽ không thể hiển thị dữ liệu.
2.  **Các gói phụ thuộc (`package.json`):** Frontend cần các thư viện như Vue, Pinia, Chart.js... để chạy. Các thư viện này phải được khai báo chính xác.
3.  **Công cụ Build (`vite.config.js`):** Các file cấu hình này cần thiết để biên dịch mã nguồn thành sản phẩm cuối cùng.

Việc làm theo hướng dẫn từng bước dưới đây sẽ đảm bảo tất cả các yếu tố trên được đồng bộ, giảm thiểu lỗi và giúp quá trình hợp nhất thành công.

## So sánh hai phiên bản dự án

| | Project A (Nguồn Frontend) | Project B (Đích đến) |
|---|---|---|
| **Frontend** | **Vượt trội.** Giao diện đã được thiết kế lại, sửa rất nhiều lỗi nghiêm trọng, và có các tính năng nâng cao (Kanban động). | **Cơ bản.** Giao diện chỉ ở mức sườn, thiếu tính năng và chưa được gỡ lỗi. |
| **Backend** | **Ổn định.** API hoạt động sau khi đã sửa các lỗi về định dạng dữ liệu và database. | **Tốt hơn.** Về lý thuyết, có một bộ API hoàn thiện hơn ngay từ đầu. |
| **Lỗi nghiêm trọng** | Đã sửa hết. | **Vẫn còn lỗi.** Backend thiếu cột `is_closed` trong database, sẽ gây crash trang Dashboard. **Bước 0** của hướng dẫn này là để khắc phục điều đó. |

Tóm lại, kế hoạch của chúng ta là lấy phần **frontend tốt nhất** của Project A và đặt nó lên phần **backend tốt hơn** của Project B.

---

## BƯỚC 0: Sửa lỗi Database (BẮT BUỘC)

Đây là bước quan trọng nhất. Backend của dự án này đang thiếu một cột trong database, gây ra lỗi `500` ở trang Dashboard. Nếu không sửa, frontend mới sẽ không hoạt động.

**1. Tạo file migration:**
Mở terminal trong thư mục gốc của dự án này và chạy lệnh:
```bash
php artisan make:migration add_is_closed_to_ticket_statuses_table --table=ticket_statuses
```

**2. Cập nhật file migration:**
Lệnh trên sẽ tạo một file mới trong `database/migrations/`. Mở file đó và thay thế toàn bộ nội dung của nó bằng đoạn code sau:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->dropColumn('is_closed');
        });
    }
};
```

**3. Chạy migration:**
Quay lại terminal và chạy lệnh:
```bash
php artisan migrate
```
Sau bước này, database của bạn đã sẵn sàng.

---

## BƯỚC 1: Cập nhật các gói phụ thuộc (`package.json`)

Frontend mới yêu cầu thêm một số thư viện để hoạt động (biểu đồ, kéo-thả).

**1. Mở file `package.json`** của dự án này.

**2. Thay thế toàn bộ nội dung** của nó bằng đoạn code dưới đây:

```json
{
    "private": true,
    "scripts": {
        "dev": "vite",
        "build": "vite build"
    },
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.3",
        "@tailwindcss/typography": "^0.5.7",
        "autoprefixer": "^10.4.13",
        "axios": "^1.1.2",
        "laravel-vite-plugin": "^0.6.0",
        "lodash": "^4.17.19",
        "postcss": "^8.1.14",
        "sass": "^1.55.0",
        "tailwindcss": "^3.2.1",
        "tippy.js": "^6.3.7",
        "vite": "^3.0.0"
    },
    "dependencies": {
        "@vitejs/plugin-vue": "^3.2.0",
        "chart.js": "^4.5.1",
        "flowbite": "^1.5.3",
        "pinia": "^2.3.1",
        "vue": "^3.5.24",
        "vue-chartjs": "^5.3.3",
        "vue-router": "^4.6.3",
        "vuedraggable": "^4.1.0"
    }
}
```

**3. Cài đặt các gói:**
Mở terminal và chạy lệnh:
```bash
npm install
```

---

## BƯỚC 2: Sao chép Cấu hình Build

Sao chép các file sau từ dự án cũ (Project A) và ghi đè vào dự án này (Project B):
- `vite.config.js`
- `tailwind.config.js`
- `postcss.config.js`

---

## BƯỚC 3: Sao chép Mã nguồn Frontend

**1. Xóa** hai thư mục sau trong dự án này (nếu có):
   - `resources/js`
   - `resources/css`

**2. Sao chép** hai thư mục `resources/js` và `resources/css` từ dự án cũ và dán vào thư mục `resources` của dự án này.

---

## BƯỚC 4: Kiểm tra Laravel Web Route

Để ứng dụng Vue hoạt động, file `routes/web.php` cần có một "catch-all" route. Mở file `routes/web.php` của dự án này và đảm bảo nó có một route tương tự như sau:

```php
Route::get('/{any}', function () {
    return view('app'); // 'app' là tên file blade chính của bạn
})->where('any', '.*');
```
*Lưu ý: Route này thường được đặt ở cuối file.*

---

## BƯỚC 5: Build và Kiểm tra

1.  Chạy lệnh `npm run dev` để build lại toàn bộ frontend mới.
2.  Chạy `php artisan serve` để khởi động backend.
3.  Mở trình duyệt và kiểm tra ứng dụng.

---

## LƯU Ý QUAN TRỌNG VỀ API

Hướng dẫn này giả định rằng API của dự án này (Project B) tương thích với những gì frontend mới mong đợi. Frontend mới đã được sửa để xử lý nhiều định dạng dữ liệu khác nhau, nhưng nếu có những thay đổi lớn về API (ví dụ: đổi tên endpoint, đổi tên các trường trong response), bạn sẽ cần phải tự mình vào các file trong `resources/js/stores/` để cập nhật lại logic gọi API cho phù hợp.

---

## Tình trạng

**Cập nhật ngày 09/12/2025:**
Quá trình hợp nhất frontend theo hướng dẫn này đã được thực hiện và xác nhận hoàn tất. Toàn bộ mã nguồn frontend mới, các tệp cấu hình và các phụ thuộc đã được áp dụng thành công.
