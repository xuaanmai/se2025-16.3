# Change Log

## Gỡ lỗi & Tạm dừng (13/12/2025)
- **Tình trạng:** Tạm dừng phiên làm việc để lưu lại trạng thái.
- **Sự cố:** Sau khi hoàn thành các tính năng cho Gantt Chart, ứng dụng gặp lỗi không tải được component `ProjectGantt.vue`. Nguyên nhân được xác định là do một file component cũ, không được sử dụng (`components/Roadmap/GanttChart.vue`) chứa một câu lệnh `import` CSS bị lỗi, làm sập trình build của Vite.
- **Gỡ lỗi:**
  - Đã sửa lỗi import CSS của thư viện `frappe-gantt` bằng cách sao chép file CSS vào cục bộ và import trong `app.js`.
  - Đã xác định và sửa lỗi trong file component "ma" (`components/Roadmap/GanttChart.vue`).
  - Phát hiện lỗi logic mới: Gantt chart hiển thị "No tasks to display". Nguyên nhân là do một bộ lọc quá chặt trong `RoadMapController`.
- **Trạng thái hiện tại:**
  - Đã xóa bộ lọc trong `RoadMapController` để hiển thị lại tất cả các task.
  - File `ProjectGantt.vue` đang ở trạng thái **tối giản** để phục vụ việc gỡ lỗi và chưa có đầy đủ chức năng.
  - Cần tiếp tục gỡ lỗi vấn đề "No tasks" hoặc "tải chậm" trong phiên làm việc tiếp theo.

## Hoàn thiện Gantt Chart (13/12/2025)
- **Nâng cấp API và Giao diện Gantt Chart (Giai đoạn 1):**
  - **Backend:** Cập nhật API endpoint `GET /api/projects/{project}/gantt` (`RoadMapController@getGanttData`) để cung cấp dữ liệu về **dependencies** (quan hệ `blocks`) và **custom_class** (dựa trên trạng thái ticket) cho việc tô màu.
  - **Frontend:** Nâng cấp component `ProjectGantt.vue` để trở thành một biểu đồ Gantt hoàn chỉnh.
    - Tích hợp `frappe-gantt` và import CSS cần thiết.
    - Thêm các style CSS để tô màu thanh công việc theo trạng thái (`.gantt-bar-to-do`, `.gantt-bar-in-progress`, `.gantt-bar-done`...).
    - Biểu đồ giờ đây đã hiển thị được các đường phụ thuộc giữa các công việc.
    - Thêm tính năng điều hướng: click vào một công việc sẽ chuyển đến trang chi tiết của công việc đó.
- **Tương tác hai chiều (Giai đoạn 2):**
  - **Backend:** Tạo API endpoint `PUT /api/tickets/{ticket}/dates` và phương thức `TicketController@updateDates` để cho phép cập nhật `due_date` của ticket.
  - **Frontend:** Kích hoạt sự kiện `on_date_change` trong `ProjectGantt.vue`. Khi người dùng kéo-thả để thay đổi ngày kết thúc của công việc, thay đổi sẽ được tự động lưu vào database.
- **Cải tiến UX/UI (Giai đoạn 3):**
  - **Frontend:** Thêm "Bộ chọn Chế độ xem" (View Mode Switcher) vào `ProjectGantt.vue`, cho phép người dùng dễ dàng chuyển đổi giữa các chế độ xem Ngày, Tuần, và Tháng.

## Triển khai Gantt Chart (11/12/2025)
- **Thêm tính năng Gantt Chart cho Project:**
  - Cài đặt thư viện `frappe-gantt` để hiển thị biểu đồ Gantt.
  - Tạo API endpoint mới `GET /api/projects/{project}/gantt` để cung cấp dữ liệu.
  - Thêm phương thức `getGanttData` vào `RoadMapController` để xử lý logic.
  - Tạo component `ProjectGantt.vue` để hiển thị biểu đồ.
  - Tích hợp biểu đồ vào trang chi tiết dự án thông qua một tab "Gantt" mới, sử dụng nested routes của Vue Router.

## Cải tiến & Hoàn thiện Giao diện (11/12/2025)
- **Tài liệu hóa nghiệp vụ:**
  - Tạo file `docs/BUSINESS_LOGIC.md` để định nghĩa chi tiết các yêu cầu nghiệp vụ mở rộng.
  - Cập nhật `tasks.md` để ghi nhận việc định nghĩa nghiệp vụ.
- **Tạo dữ liệu mẫu (Seeding):**
  - Tạo file `database/seeders/DemoDataSeeder.php` để sinh dữ liệu mẫu (users, projects, tickets, comments, etc.).
  - Cập nhật `DatabaseSeeder.php` để gọi seeder dữ liệu mẫu, giúp việc kiểm thử dễ dàng hơn.
- **Cải tiến UX & Component hóa:**
  - Tạo component tái sử dụng `ConfirmationModal.vue` cho các hành động cần xác nhận.
  - Thay thế `window.confirm()` bằng `ConfirmationModal` khi xóa Project, User, và Ticket, giúp giao diện chuyên nghiệp và nhất quán.
- **Sửa lỗi & Cải tiến logic:**
  - **Dashboard:** Sửa component `StatusPieChart` để có thể tái sử dụng, và thêm biểu đồ "Tickets by Priority" vào Dashboard.
  - **Create Ticket:** Sửa lỗi logic phân quyền của nút "Create Ticket". Nút ở trang chung được hiển thị lại, đồng thời một nút mới được thêm vào trang chi tiết dự án với logic kiểm tra vai trò (owner/manager) chính xác.
  - **Kanban Board:** Sửa lỗi `TaskCard.vue` không hiển thị khi ticket chưa được gán (unassigned). Sửa lỗi logic, chỉ cho phép chọn và hiển thị các dự án loại 'kanban', khắc phục lỗi 422.

## Frontend Merge (09/12/2025)
- **Hoàn thành hợp nhất Frontend:** Áp dụng thành công giao diện người dùng Vue 3 mới vào dự án theo `MERGE_FRONTEND_GUIDE.md`.
  - Cập nhật `package.json` với các phụ thuộc mới (Pinia, Vue Router, Chart.js, vuedraggable).
  - Sao chép và áp dụng các tệp cấu hình `vite.config.js`, `tailwind.config.js`, `postcss.config.js`.
  - Thay thế toàn bộ mã nguồn trong `resources/js` và `resources/css` bằng phiên bản mới.
  - Sửa lỗi database với migration `add_is_closed_to_ticket_statuses_table`.
  - Xác nhận route "catch-all" cho ứng dụng Vue SPA đã được cấu hình trong `routes/web.php`.
- **Tình trạng:** Dự án sẵn sàng để build và phát triển tiếp với frontend mới.

## Frontend (Vue 3)
- Rebuilt SPA với Vite, TailwindCSS, Vue Router, Pinia
- Thêm các components có thể tái sử dụng: Layout với sidebar, DataTable, Modal, FormInput, FormSelect
- Triển khai các pages: `Login`, `Dashboard`, `Projects`, `Tickets`, `Users`
- Cấu hình router guards, axios service, auth store, Tailwind content paths
- Sửa lỗi URL duplicate và Vue compiler errors

## Backend (API)

### API Controllers mới
- `ActivityController` - CRUD cho Activities
- `ProjectStatusController` - CRUD cho Project Statuses với logic default status
- `TicketPriorityController` - CRUD cho Ticket Priorities với logic default priority
- `TicketStatusController` - CRUD cho Ticket Statuses với order management
- `TicketTypeController` - CRUD cho Ticket Types với logic default type

### Cập nhật ProjectController
- **Cover Image Upload**: Hỗ trợ upload ảnh cover qua SpatieMediaLibrary
- **Default Values**: Tự động set `owner_id` = current user và `status_id` = default status nếu không có
- **Validation Logic**:
  - `ticket_prefix` không thể thay đổi nếu project đã có tickets
  - `status_type` không thể thay đổi nếu project đã có tickets
- **User Management**: Hỗ trợ attach/sync users qua `user_ids` array
- **Delete Cover**: Thêm endpoint `DELETE /api/projects/{project}/cover` để xóa cover image

### Routes
- Thêm `apiResource` routes cho tất cả referential resources
- Thêm route xóa cover image
- Giữ backward compatibility với `/referential/*` endpoints

## Cleanup
- Xóa các file không cần thiết:
  - `resources/js/views/Home.vue` (không được sử dụng)
  - `resources/js/README.md` và `resources/js/components/README.md` (documentation)
  - `readme-logo.png` (logo cho README đã xóa)
  - `public/gantt-1.json`, `gantt-2.json`, `gantt-3.json` (file demo)
  - `yarn.lock` (không cần nếu dùng npm)
  - `.rnd` (OpenSSL random seed file)

## So sánh với Filament
- API ProjectController đã khớp với Filament về:
  - Default values (owner_id, status_id)
  - Validation rules (ticket_prefix, status_type)
  - Cover image upload
  - User management
- Còn thiếu trong Vue frontend (có thể thêm sau):
  - Cover image upload UI
  - Status type field trong form
  - RichEditor cho description
  - Helper text cho project type

## API Controllers mới (Hoàn chỉnh)

### Permission & Role Management
- **PermissionController**: CRUD permissions
- **RoleController**: CRUD roles, sync permissions

### Timesheet Management
- **TimesheetController**: CRUD timesheet entries (TicketHour), filter by user/ticket/activity

### Sprint & Epic Management
- **SprintController**: CRUD sprints, start/stop sprint, associate tickets với sprint
- **EpicController**: CRUD epics, roadmap data

### Ticket Advanced Features
- **TicketCommentController**: CRUD comments cho tickets (nested routes)
- **TicketRelationController**: CRUD ticket relations (nested routes)

### Project Advanced Features (bổ sung vào ProjectController)
- `toggleFavorite()`: Toggle favorite project
- `exportHours()`: Export project hours as CSV
- `getUsers()`: List users với roles trong project
- `attachUser()`: Attach user với role
- `updateUserRole()`: Update user role trong project
- `detachUser()`: Detach user từ project
- `getSprints()`: List sprints của project
- `getStatuses()`: List custom statuses (nếu status_type = custom)

### Ticket Advanced Features (bổ sung vào TicketController)
- `getSubscribers()`: List subscribers
- `subscribe()`: Subscribe user to ticket
- `unsubscribe()`: Unsubscribe user from ticket
- `getHours()`: List logged hours
- `logHours()`: Log hours cho ticket
- `exportHours()`: Export ticket hours as CSV

## Routes mới

### Core Resources
- `POST /api/projects/{project}/favorite` - Toggle favorite
- `GET /api/projects/{project}/export-hours` - Export CSV
- `GET /api/projects/{project}/users` - List users
- `POST /api/projects/{project}/users` - Attach user
- `PUT /api/projects/{project}/users/{user}` - Update role
- `DELETE /api/projects/{project}/users/{user}` - Detach user
- `GET /api/projects/{project}/sprints` - List sprints
- `GET /api/projects/{project}/statuses` - List custom statuses

### Ticket Nested Resources
- `GET/POST /api/tickets/{ticket}/comments` - Comments CRUD
- `GET/PUT/DELETE /api/tickets/{ticket}/comments/{comment}` - Comment operations
- `GET/POST /api/tickets/{ticket}/relations` - Relations CRUD
- `GET/PUT/DELETE /api/tickets/{ticket}/relations/{relation}` - Relation operations
- `GET /api/tickets/{ticket}/subscribers` - List subscribers
- `POST /api/tickets/{ticket}/subscribers/{user}` - Subscribe
- `DELETE /api/tickets/{ticket}/subscribers/{user}` - Unsubscribe
- `GET /api/tickets/{ticket}/hours` - List hours
- `POST /api/tickets/{ticket}/hours` - Log hours
- `GET /api/tickets/{ticket}/export-hours` - Export CSV

### New Resources
- `apiResource('permissions')` - Permissions CRUD
- `apiResource('roles')` - Roles CRUD
- `apiResource('timesheets')` - Timesheet CRUD
- `apiResource('sprints')` - Sprints CRUD
- `POST /api/sprints/{sprint}/start` - Start sprint
- `POST /api/sprints/{sprint}/stop` - Stop sprint
- `POST /api/sprints/{sprint}/tickets` - Associate tickets
- `apiResource('epics')` - Epics CRUD

## API Controllers mới (Special Pages)

### Board/Kanban/Scrum APIs
- **BoardController**: 
  - `getProjects()`: List projects accessible for board selection
  - `getStatuses()`: Get Kanban/Scrum board statuses với ticket counts
  - `getKanbanTickets()`: Get Kanban board tickets với filters
  - `getScrumSprint()`: Get current sprint info cho Scrum projects
  - `getScrumTickets()`: Get Scrum board tickets cho current sprint
  - `moveTicket()`: Move ticket (update status & order) khi drag & drop

### RoadMap APIs
- **RoadMapController**:
  - `getRoadmap()`: Get roadmap data với epics và tickets
  - `getRoadmapDates()`: Get dates cho Gantt chart (first_date, last_date, scroll_to)

### Dashboard Widgets APIs (bổ sung vào DashboardController)
- `favoriteProjects()`: Favorite projects widget data
- `latestActivities()`: Latest activities widget data
- `latestComments()`: Latest comments widget data
- `latestProjects()`: Latest projects widget data
- `latestTickets()`: Latest tickets widget data
- `ticketsByPriority()`: Tickets by priority chart data
- `ticketsByType()`: Tickets by type chart data
- `ticketTimeLogged()`: Ticket time logged chart data
- `userTimeLogged()`: User time logged chart data

### Timesheet Dashboard APIs
- **TimesheetDashboardController**:
  - `monthlyReport()`: Monthly timesheet report data
  - `weeklyReport()`: Weekly timesheet report data
  - `activitiesReport()`: Activities report data

## Routes mới (Special Pages)

### Board/Kanban/Scrum
- `GET /api/board/projects` - List projects for board selection
- `GET /api/projects/{project}/kanban/statuses` - Get Kanban statuses
- `GET /api/projects/{project}/kanban/tickets` - Get Kanban tickets
- `GET /api/projects/{project}/scrum/sprint` - Get Scrum sprint info
- `GET /api/projects/{project}/scrum/tickets` - Get Scrum tickets
- `PUT /api/tickets/{ticket}/move` - Move ticket

### RoadMap
- `GET /api/projects/{project}/roadmap` - Get roadmap data
- `GET /api/projects/{project}/roadmap/dates` - Get roadmap dates

### Dashboard Widgets
- `GET /api/dashboard/favorite-projects` - Favorite projects
- `GET /api/dashboard/latest-activities` - Latest activities
- `GET /api/dashboard/latest-comments` - Latest comments
- `GET /api/dashboard/latest-projects` - Latest projects
- `GET /api/dashboard/latest-tickets` - Latest tickets
- `GET /api/dashboard/tickets-by-priority` - Tickets by priority chart
- `GET /api/dashboard/tickets-by-type` - Tickets by type chart
- `GET /api/dashboard/ticket-time-logged` - Ticket time logged chart
- `GET /api/dashboard/user-time-logged` - User time logged chart

### Timesheet Dashboard
- `GET /api/timesheet/monthly-report` - Monthly report
- `GET /api/timesheet/weekly-report` - Weekly report
- `GET /api/timesheet/activities-report` - Activities report

## Notes
- Filament backend vẫn hoạt động; API mới tạo nền tảng để migrate các tính năng còn lại sang Vue
- Tailwind/Vite vẫn cần thiết cho Vue setup hiện tại
- `node_modules/` không nên commit (tái tạo qua `npm install`)
- Tất cả API endpoints đã được bảo vệ bởi middleware `web` và `auth`
- Nested routes cho comments và relations tuân theo RESTful convention
- **Tổng cộng: ~90+ API endpoints** - Hoàn chỉnh 100% các chức năng từ Filament
