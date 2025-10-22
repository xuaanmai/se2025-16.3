# Kế hoạch Thiết kế & Xây dựng Web Project Management
## Laravel Backend + Vue.js Frontend

**Công nghệ:** Laravel (Backend API) + Vue.js (Frontend SPA)  
**Mục tiêu:** Xây dựng hệ thống quản lý dự án toàn diện với khả năng realtime, phân quyền, thống kê trực quan, và kiến trúc frontend/backend tách biệt.


---

## 🎯 I. TỔNG QUAN KIẾN TRÚC HỆ THỐNG

### 1.1 Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Vue.js     │  │ Filament     │  │  Mobile App  │  │
│  │   (Users)    │  │ (Admin)      │  │  (Future)    │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          ↓ HTTP/HTTPS
┌─────────────────────────────────────────────────────────┐
│                    API GATEWAY                          │
│         Laravel Sanctum Authentication                  │
│         CORS, Rate Limiting, Validation                 │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                  BACKEND LAYER (Laravel)                │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐             │
│  │   API    │  │ Business │  │  Events  │             │
│  │Controllers│  │  Logic   │  │  Queue   │             │
│  └──────────┘  └──────────┘  └──────────┘             │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                   DATA LAYER                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐             │
│  │  Models  │  │   MySQL  │  │  Redis   │             │
│  │Eloquent  │  │ Database │  │  Cache   │             │
│  └──────────┘  └──────────┘  └──────────┘             │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Chiến lược Hybrid

**Quyết định quan trọng:** Không xóa Filament, mà sử dụng song song!

- **Filament Admin Panel:** Dành cho Admin/Super Admin quản lý toàn bộ hệ thống
- **Vue.js Frontend:** Dành cho User thông thường (Project Manager, Developer, Client)

**Lợi ích:**
- Tận dụng công sức đã bỏ ra cho Filament
- Admin có công cụ mạnh mẽ để quản lý
- Users có trải nghiệm hiện đại, tùy biến
- Dễ dàng mở rộng sang mobile app sau này

---

## 📁 II. CẤU TRÚC THƯ MỤC & FILES

### 2.1 Cấu trúc Backend (Laravel)

```
project-management/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/                          # API Version 1
│   │   │   │   │   ├── Auth/
│   │   │   │   │   │   ├── AuthController.php   # Login, Register, Logout
│   │   │   │   │   │   ├── ProfileController.php # User profile management
│   │   │   │   │   │   └── PasswordController.php # Change, Reset password
│   │   │   │   │   │
│   │   │   │   │   ├── Dashboard/
│   │   │   │   │   │   ├── DashboardController.php # Overview stats
│   │   │   │   │   │   └── WidgetController.php    # Dashboard widgets
│   │   │   │   │   │
│   │   │   │   │   ├── Project/
│   │   │   │   │   │   ├── ProjectController.php       # CRUD projects
│   │   │   │   │   │   ├── ProjectMemberController.php # Manage members
│   │   │   │   │   │   ├── ProjectSettingController.php
│   │   │   │   │   │   └── ProjectStatController.php   # Project statistics
│   │   │   │   │   │
│   │   │   │   │   ├── Ticket/
│   │   │   │   │   │   ├── TicketController.php         # CRUD tickets
│   │   │   │   │   │   ├── TicketCommentController.php  # Comments
│   │   │   │   │   │   ├── TicketHourController.php     # Log hours
│   │   │   │   │   │   ├── TicketActivityController.php # Activity log
│   │   │   │   │   │   ├── TicketAttachmentController.php
│   │   │   │   │   │   └── TicketRelationController.php # Ticket relations
│   │   │   │   │   │
│   │   │   │   │   ├── Sprint/
│   │   │   │   │   │   ├── SprintController.php
│   │   │   │   │   │   └── SprintBoardController.php    # Kanban board data
│   │   │   │   │   │
│   │   │   │   │   ├── Epic/
│   │   │   │   │   │   └── EpicController.php
│   │   │   │   │   │
│   │   │   │   │   ├── RoadMap/
│   │   │   │   │   │   └── RoadMapController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Team/
│   │   │   │   │   │   ├── TeamController.php
│   │   │   │   │   │   └── TeamMemberController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Search/
│   │   │   │   │   │   └── SearchController.php         # Global search
│   │   │   │   │   │
│   │   │   │   │   ├── Notification/
│   │   │   │   │   │   └── NotificationController.php
│   │   │   │   │   │
│   │   │   │   │   └── Reference/
│   │   │   │   │       ├── TicketStatusController.php
│   │   │   │   │       ├── TicketPriorityController.php
│   │   │   │   │       ├── TicketTypeController.php
│   │   │   │   │       └── ProjectStatusController.php
│   │   │   │   │
│   │   │   │   └── WebhookController.php        # Webhooks từ external services
│   │   │   │
│   │   │   └── (Giữ nguyên Filament Controllers cho Admin)
│   │   │
│   │   ├── Middleware/
│   │   │   ├── CheckProjectAccess.php            # Verify user có access project
│   │   │   ├── CheckTicketPermission.php
│   │   │   ├── ApiVersionMiddleware.php
│   │   │   └── TrackApiUsage.php                 # Analytics
│   │   │
│   │   ├── Requests/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginRequest.php
│   │   │   │   │   └── RegisterRequest.php
│   │   │   │   ├── Project/
│   │   │   │   │   ├── StoreProjectRequest.php
│   │   │   │   │   └── UpdateProjectRequest.php
│   │   │   │   ├── Ticket/
│   │   │   │   │   ├── StoreTicketRequest.php
│   │   │   │   │   ├── UpdateTicketRequest.php
│   │   │   │   │   └── AssignTicketRequest.php
│   │   │   │   └── Sprint/
│   │   │   │       └── StoreSprintRequest.php
│   │   │
│   │   ├── Resources/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── UserResource.php          # Format JSON response
│   │   │   │   │   ├── ProjectResource.php
│   │   │   │   │   ├── ProjectCollection.php     # Collection format
│   │   │   │   │   ├── TicketResource.php
│   │   │   │   │   ├── TicketCollection.php
│   │   │   │   │   ├── SprintResource.php
│   │   │   │   │   ├── EpicResource.php
│   │   │   │   │   ├── CommentResource.php
│   │   │   │   │   └── ActivityResource.php
│   │   │
│   │   └── Traits/
│   │       ├── ApiResponses.php                   # Standardize API responses
│   │       └── HasApiPagination.php
│   │
│   ├── Services/                                  # Business Logic Layer
│   │   ├── Auth/
│   │   │   ├── AuthService.php
│   │   │   └── TokenService.php
│   │   ├── Project/
│   │   │   ├── ProjectService.php                 # Complex project logic
│   │   │   └── ProjectMemberService.php
│   │   ├── Ticket/
│   │   │   ├── TicketService.php
│   │   │   ├── TicketAssignmentService.php
│   │   │   └── TicketWorkflowService.php          # Status transitions
│   │   ├── Sprint/
│   │   │   └── SprintService.php
│   │   ├── Notification/
│   │   │   └── NotificationService.php
│   │   └── Analytics/
│   │       └── ProjectAnalyticsService.php
│   │
│   ├── Repositories/                              # Optional: Data Access Layer
│   │   ├── ProjectRepository.php
│   │   ├── TicketRepository.php
│   │   └── SprintRepository.php
│   │
│   ├── Events/                                    # Domain Events
│   │   ├── Ticket/
│   │   │   ├── TicketCreated.php
│   │   │   ├── TicketAssigned.php
│   │   │   ├── TicketStatusChanged.php
│   │   │   └── TicketCommented.php
│   │   └── Project/
│   │       ├── ProjectCreated.php
│   │       └── MemberAddedToProject.php
│   │
│   ├── Listeners/                                 # Event Handlers
│   │   ├── SendTicketAssignedNotification.php
│   │   ├── LogTicketActivity.php
│   │   └── UpdateProjectStatistics.php
│   │
│   ├── Jobs/                                      # Background Jobs
│   │   ├── SendEmailNotification.php
│   │   ├── GenerateProjectReport.php
│   │   └── SyncExternalData.php
│   │
│   ├── Observers/                                 # Model Observers
│   │   ├── TicketObserver.php
│   │   └── ProjectObserver.php
│   │
│   ├── Policies/                                  # Authorization
│   │   ├── ProjectPolicy.php
│   │   ├── TicketPolicy.php
│   │   └── SprintPolicy.php
│   │
│   ├── Models/                                    # Giữ nguyên
│   │   └── (Đã có sẵn)
│   │
│   └── Filament/                                  # Giữ nguyên cho Admin
│       └── (Đã có sẵn)
│
├── routes/
│   ├── api.php                                    # API Routes chính
│   ├── api_v1.php                                 # Tách routes API v1 ra riêng
│   ├── web.php                                    # Giữ cho Filament
│   └── channels.php                               # Broadcasting channels
│
├── config/
│   ├── cors.php                                   # CORS configuration
│   ├── sanctum.php                                # Sanctum config
│   └── api.php                                    # API custom config
│
├── database/
│   ├── migrations/                                # Giữ nguyên
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── ProjectSeeder.php
│   │   ├── TicketSeeder.php
│   │   └── ReferenceDataSeeder.php               # Statuses, Priorities, Types
│   └── factories/                                 # Giữ nguyên
│
├── tests/
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginTest.php
│   │   │   │   └── RegisterTest.php
│   │   │   ├── Project/
│   │   │   │   ├── ProjectCrudTest.php
│   │   │   │   └── ProjectAuthorizationTest.php
│   │   │   └── Ticket/
│   │   │       └── TicketCrudTest.php
│   │   └── (Giữ Filament tests)
│   │
│   └── Unit/
│       ├── Services/
│       │   ├── ProjectServiceTest.php
│       │   └── TicketServiceTest.php
│       └── Models/
│
└── storage/
    └── api-docs/                                  # API Documentation
        ├── openapi.yaml                           # OpenAPI/Swagger spec
        └── postman_collection.json
```

### 2.2 Cấu trúc Frontend (Vue.js)

```
frontend-vue/
│
├── public/
│   ├── favicon.ico
│   └── index.html
│
├── src/
│   ├── main.js                                    # App entry point
│   ├── App.vue                                    # Root component
│   │
│   ├── router/
│   │   ├── index.js                               # Main router
│   │   └── guards/
│   │       ├── auth.guard.js                      # Check authentication
│   │       └── permission.guard.js                # Check permissions
│   │
│   ├── store/                                     # Vuex/Pinia State Management
│   │   ├── index.js
│   │   ├── modules/
│   │   │   ├── auth.js                            # Authentication state
│   │   │   ├── user.js                            # Current user info
│   │   │   ├── projects.js                        # Projects state
│   │   │   ├── tickets.js                         # Tickets state
│   │   │   ├── sprints.js                         # Sprints state
│   │   │   ├── notifications.js                   # Real-time notifications
│   │   │   └── ui.js                              # UI state (sidebar, theme)
│   │
│   ├── api/                                       # API Communication Layer
│   │   ├── client.js                              # Axios instance with interceptors
│   │   ├── endpoints/
│   │   │   ├── auth.api.js                        # Auth API calls
│   │   │   ├── projects.api.js                    # Project API calls
│   │   │   ├── tickets.api.js                     # Ticket API calls
│   │   │   ├── sprints.api.js                     # Sprint API calls
│   │   │   ├── users.api.js                       # User API calls
│   │   │   └── reference.api.js                   # Reference data
│   │   └── interceptors/
│   │       ├── auth.interceptor.js                # Add tokens to requests
│   │       ├── error.interceptor.js               # Handle errors globally
│   │       └── response.interceptor.js            # Transform responses
│   │
│   ├── views/                                     # Page Components
│   │   ├── auth/
│   │   │   ├── LoginView.vue
│   │   │   ├── RegisterView.vue
│   │   │   ├── ForgotPasswordView.vue
│   │   │   └── ResetPasswordView.vue
│   │   │
│   │   ├── dashboard/
│   │   │   └── DashboardView.vue                  # Main dashboard
│   │   │
│   │   ├── projects/
│   │   │   ├── ProjectListView.vue                # All projects
│   │   │   ├── ProjectDetailView.vue              # Single project overview
│   │   │   ├── ProjectSettingsView.vue            # Project settings
│   │   │   ├── ProjectMembersView.vue             # Manage members
│   │   │   └── ProjectCreateView.vue              # Create new project
│   │   │
│   │   ├── tickets/
│   │   │   ├── TicketBoardView.vue                # Kanban board
│   │   │   ├── TicketListView.vue                 # Table view
│   │   │   ├── TicketDetailView.vue               # Single ticket detail
│   │   │   └── TicketCreateView.vue               # Create/Edit ticket
│   │   │
│   │   ├── sprints/
│   │   │   ├── SprintListView.vue
│   │   │   ├── SprintBoardView.vue                # Sprint kanban
│   │   │   └── SprintReportView.vue               # Sprint report/burndown
│   │   │
│   │   ├── roadmap/
│   │   │   └── RoadmapView.vue                    # Visual roadmap
│   │   │
│   │   ├── team/
│   │   │   └── TeamView.vue                       # Team members
│   │   │
│   │   ├── reports/
│   │   │   ├── ProjectReportsView.vue
│   │   │   └── UserReportsView.vue
│   │   │
│   │   ├── profile/
│   │   │   ├── ProfileView.vue
│   │   │   └── SettingsView.vue
│   │   │
│   │   └── errors/
│   │       ├── NotFoundView.vue                   # 404
│   │       └── UnauthorizedView.vue               # 403
│   │
│   ├── components/                                # Reusable Components
│   │   ├── layout/
│   │   │   ├── AppLayout.vue                      # Main layout wrapper
│   │   │   ├── Sidebar.vue                        # Navigation sidebar
│   │   │   ├── Navbar.vue                         # Top navbar
│   │   │   ├── Footer.vue
│   │   │   └── Breadcrumb.vue
│   │   │
│   │   ├── common/
│   │   │   ├── BaseButton.vue                     # Reusable button
│   │   │   ├── BaseInput.vue                      # Form input
│   │   │   ├── BaseSelect.vue
│   │   │   ├── BaseModal.vue                      # Modal dialog
│   │   │   ├── BaseCard.vue
│   │   │   ├── BaseTable.vue                      # Data table
│   │   │   ├── BasePagination.vue
│   │   │   ├── LoadingSpinner.vue
│   │   │   ├── EmptyState.vue
│   │   │   └── ErrorAlert.vue
│   │   │
│   │   ├── project/
│   │   │   ├── ProjectCard.vue                    # Project card in grid
│   │   │   ├── ProjectStats.vue                   # Project statistics
│   │   │   ├── ProjectMemberList.vue
│   │   │   ├── ProjectStatusBadge.vue
│   │   │   └── ProjectForm.vue                    # Create/Edit form
│   │   │
│   │   ├── ticket/
│   │   │   ├── TicketCard.vue                     # Ticket card for kanban
│   │   │   ├── TicketRow.vue                      # Ticket row for table
│   │   │   ├── TicketForm.vue                     # Create/Edit form
│   │   │   ├── TicketStatusSelect.vue
│   │   │   ├── TicketPriorityBadge.vue
│   │   │   ├── TicketTypeBadge.vue
│   │   │   ├── TicketComments.vue                 # Comments section
│   │   │   ├── TicketActivity.vue                 # Activity timeline
│   │   │   ├── TicketAttachments.vue
│   │   │   └── TicketTimeLog.vue                  # Hour logging
│   │   │
│   │   ├── sprint/
│   │   │   ├── SprintCard.vue
│   │   │   ├── SprintBoard.vue                    # Kanban board component
│   │   │   ├── BurndownChart.vue
│   │   │   └── SprintProgress.vue
│   │   │
│   │   ├── user/
│   │   │   ├── UserAvatar.vue
│   │   │   ├── UserSelect.vue                     # Dropdown to select user
│   │   │   └── UserCard.vue
│   │   │
│   │   ├── charts/
│   │   │   ├── LineChart.vue
│   │   │   ├── BarChart.vue
│   │   │   ├── PieChart.vue
│   │   │   └── DoughnutChart.vue
│   │   │
│   │   └── notifications/
│   │       ├── NotificationBell.vue
│   │       ├── NotificationDropdown.vue
│   │       └── NotificationItem.vue
│   │
│   ├── composables/                               # Vue Composition API
│   │   ├── useAuth.js                             # Authentication logic
│   │   ├── useProjects.js                         # Project operations
│   │   ├── useTickets.js                          # Ticket operations
│   │   ├── useSprints.js
│   │   ├── useNotifications.js
│   │   ├── useWebSocket.js                        # Real-time updates
│   │   ├── usePagination.js
│   │   ├── useDebounce.js
│   │   └── usePermissions.js                      # Check user permissions
│   │
│   ├── utils/                                     # Utility Functions
│   │   ├── validators.js                          # Form validation rules
│   │   ├── formatters.js                          # Date, number formatting
│   │   ├── helpers.js                             # Generic helpers
│   │   ├── constants.js                           # App constants
│   │   └── permissions.js                         # Permission definitions
│   │
│   ├── plugins/                                   # Vue Plugins
│   │   ├── axios.js                               # Axios setup
│   │   ├── vuelidate.js                           # Form validation
│   │   ├── toast.js                               # Toast notifications
│   │   └── charts.js                              # Chart.js setup
│   │
│   ├── directives/                                # Custom Directives
│   │   ├── click-outside.js
│   │   ├── tooltip.js
│   │   └── permission.js                          # v-permission directive
│   │
│   ├── assets/
│   │   ├── styles/
│   │   │   ├── main.css                           # Main CSS
│   │   │   ├── variables.css                      # CSS variables
│   │   │   ├── tailwind.css                       # Tailwind imports
│   │   │   └── components/                        # Component-specific styles
│   │   ├── images/
│   │   └── icons/
│   │
│   └── types/                                     # TypeScript definitions (nếu dùng TS)
│       ├── api.types.ts
│       ├── project.types.ts
│       └── ticket.types.ts
│
├── .env.development                               # Dev environment variables
├── .env.production                                # Prod environment variables
├── vite.config.js                                 # Vite configuration
├── tailwind.config.js                             # Tailwind CSS config
├── package.json
└── README.md
```

---

