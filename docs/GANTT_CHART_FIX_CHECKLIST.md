# 🚀 GANTT CHART FIX CHECKLIST - 5 HOUR SPRINT

**Status**: 🔴 CRITICAL - Multiple bugs blocking Gantt chart functionality  
**Last Updated**: 23 December 2025  
**Time Available**: 5 hours  
**Priority**: CRITICAL - Roadmap feature is broken

---

## 📌 CURRENT ERRORS

```
❌ Lỗi 1: "Không thể tải dữ liệu lộ trình"
❌ Lỗi 2: ReferenceError: computed is not defined (TaskDetailModal.vue:109)
❌ Lỗi 3: Failed to load resource: 401 (Unauthorized) - /api/projects/3/roadmap
❌ Lỗi 4: [Vue warn]: Unhandled error during execution of setup function
❌ Lỗi 5: Axios Error: Request failed with status code 419 when saving Epic
```

---

## 🔴 BUG #1: CRITICAL - Missing `computed` Import in TaskDetailModal

### Problem
- **Error**: `ReferenceError: computed is not defined` at line 109
- **File**: `resources/js/components/Tasks/TaskDetailModal.vue`
- **Impact**: Kanban board blocks Gantt chart rendering
- **Time**: 30 seconds

### Solution
```javascript
// BEFORE (Line 105):
import { ref } from 'vue';

// AFTER:
import { ref, computed } from 'vue';
```

### Verification
```javascript
// In browser console:
console.log('computed:', typeof computed);
// Should be 'function', not 'undefined'
```

---

## 🔴 BUG #2: CRITICAL - Frappe-Gantt JS Library Not Imported

### Problem
- **Error**: `window.Gantt is undefined` when trying to render chart
- **File**: `resources/js/app.js`
- **Impact**: Gantt chart cannot initialize → infinite loading
- **Time**: 30 seconds

### Root Cause
```javascript
// resources/js/app.js currently only imports CSS:
import 'frappe-gantt/dist/frappe-gantt.css'; // ✅ CSS only
// ❌ MISSING: JavaScript UMD bundle
```

### Solution - Add JS Import

**File**: `resources/js/app.js`

**BEFORE** (Line 1-3):
```javascript
import './bootstrap';
import '../css/app.css';
import 'frappe-gantt/dist/frappe-gantt.css';
```

**AFTER** (Add line 4):
```javascript
import './bootstrap';
import '../css/app.css';
import 'frappe-gantt/dist/frappe-gantt.css';
import 'frappe-gantt/dist/frappe-gantt.umd.js'; // ✅ ADD THIS LINE
```

### Verification
```javascript
// In browser console after rebuild:
console.log('Gantt:', window.Gantt);
// Should be: ƒ Gantt(element, tasks, options)
// NOT: undefined
```

---

## 🔴 BUG #3: CRITICAL - CSRF Token Not Sent to API (401 Error)

### Problem
- **Error**: `Failed to load resource: 401 (Unauthorized)` on `/api/projects/3/roadmap`
- **File**: `resources/js/bootstrap.js`
- **Impact**: Frontend cannot fetch epic data from backend
- **Time**: 1 minute

### Root Cause
```
When GanttChart.vue calls:
axios.get('/api/projects/{id}/roadmap')

Axios DOES NOT automatically attach CSRF token to header
→ Laravel middleware rejects with 401
```

### Solution - Add CSRF Interceptor

**File**: `resources/js/bootstrap.js`

**BEFORE** (Around line 12):
```javascript
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://localhost:8000';
window.axios.defaults.withCredentials = true;

// ❌ NO CSRF TOKEN HANDLING
```

**AFTER** (Add interceptor):
```javascript
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://localhost:8000';
window.axios.defaults.withCredentials = true;

// ✅ Add CSRF Token to every request header
window.axios.interceptors.request.use((config) => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content ||
                getCsrfTokenFromCookie();
  
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }
  
  return config;
});

function getCsrfTokenFromCookie() {
  const cookies = document.cookie.split(';');
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (name === 'XSRF-TOKEN') {
      return decodeURIComponent(value);
    }
  }
  return null;
}
```

### Verification
```javascript
// In browser console:
axios.defaults.headers.common['X-CSRF-TOKEN']
// Should be: 'token_string_here'
// NOT: undefined

// Check Network tab in DevTools:
// GET /api/projects/3/roadmap
// Status should be: 200 (not 401)
// Request Headers should include: X-CSRF-TOKEN: ...
```

---

## 🟡 BUG #4: MEDIUM - Gantt Library Check Missing in GanttChart.vue

### Problem
- **Error**: `Không thể tải dữ liệu lộ trình` when Gantt fails to load
- **File**: `resources/js/components/Roadmap/GanttChart.vue`
- **Impact**: Poor error handling, infinite loading spinner
- **Time**: 2 minutes

### Root Cause
```javascript
// Current code (Line 96):
const Gantt = window.Gantt;
if (!Gantt || !props.projectId) return; // ❌ Early return without setting loading.value = false

// If Gantt is undefined, component exits but loading state never changes
// Result: User sees infinite loading spinner forever
```

### Solution - Improve fetchAndRenderGantt()

**File**: `resources/js/components/Roadmap/GanttChart.vue`

**BEFORE** (Line 85-100):
```javascript
const fetchAndRenderGantt = async () => {
  const Gantt = window.Gantt;
  if (!Gantt || !props.projectId) return;

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/projects/${props.projectId}/roadmap`);
```

**AFTER** (Improved):
```javascript
const fetchAndRenderGantt = async () => {
  loading.value = true;
  error.value = null;

  // ✅ Check Gantt library FIRST with proper error handling
  if (typeof window.Gantt === 'undefined') {
    error.value = '❌ Frappe-Gantt library failed to load. Please refresh the page.';
    loading.value = false;
    console.error('[GanttChart] window.Gantt is undefined');
    return;
  }

  // ✅ Check projectId
  if (!props.projectId) {
    error.value = '❌ Project ID not found in URL.';
    loading.value = false;
    console.error('[GanttChart] projectId is missing');
    return;
  }

  try {
    console.log(`[GanttChart] Fetching roadmap for project: ${props.projectId}`);
    const response = await axios.get(`/api/projects/${props.projectId}/roadmap`);
    console.log('[GanttChart] API Response:', response.data);
    const tasks = response.data;
    
    // ... rest of code
  } catch (err) {
    console.error('[GanttChart] Error:', err);
    error.value = `❌ Không thể tải dữ liệu lộ trình. ${err.message}`;
  } finally {
    loading.value = false; // ✅ ALWAYS set to false
  }
};
```

### Verification
```javascript
// Check browser console:
[GanttChart] Fetching roadmap for project: 3
[GanttChart] API Response: [...]

// Check error message:
// Should show: "❌ Không thể tải dữ liệu lộ trình" with specific error
// NOT: Loading spinner forever
```

---

## 🟡 BUG #5: MEDIUM - handleSaveEpic() Missing CSRF Token

### Problem
- **Error**: `AxiosError: Request failed with status code 419` when saving Epic
- **File**: `resources/js/components/Roadmap/GanttChart.vue` (handleSaveEpic function)
- **Impact**: Cannot create/save epics from Gantt chart
- **Time**: 1 minute

### Root Cause
```javascript
// Current handleSaveEpic (Line 71):
const handleSaveEpic = async (formData) => {
  await axios.post('/api/epics', {
    ...formData,
    project_id: props.projectId
  });
  // ❌ CSRF token not sent → 419 error
};
```

### Solution - Ensure CSRF Token Before POST

**File**: `resources/js/components/Roadmap/GanttChart.vue`

**BEFORE** (Line 71-80):
```javascript
const handleSaveEpic = async (formData) => {
  try {
    await axios.post('/api/epics', {
      ...formData,
      project_id: props.projectId
    });
    isModalOpen.value = false;
```

**AFTER** (Improved):
```javascript
const handleSaveEpic = async (formData) => {
  try {
    // ✅ Ensure CSRF token is loaded first
    await axios.get('/sanctum/csrf-cookie');
    
    console.log('[GanttChart] Saving epic:', formData);
    const response = await axios.post('/api/epics', {
      ...formData,
      project_id: props.projectId
    });
    
    console.log('[GanttChart] Epic saved:', response.data);
    isModalOpen.value = false;
    await fetchAndRenderGantt(); // ✅ Refresh chart
  } catch (error) {
    console.error('[GanttChart] Failed to save epic:', error);
    error.value = `❌ Không thể lưu Epic. ${error.response?.data?.message || error.message}`;
  }
};
```

### Verification
```javascript
// In browser console when saving:
[GanttChart] Saving epic: {name: "Sprint 1", starts_at: "2025-01-01", ...}
[GanttChart] Epic saved: {id: 1, name: "Sprint 1", ...}

// Check Network tab:
// POST /api/epics
// Status: 201 (Created)
// NOT: 419 (CSRF mismatch)
```

---

## 🟢 BUG #6: LOW - Authorization Check in Backend

### Problem
- **Error**: Sometimes 401/403 when accessing roadmap
- **File**: `app/Http/Controllers/Api/Pages/RoadMapController.php`
- **Time**: 5 minutes (verify logic)

### Verification
- Check that authorization logic exists and is correct
- Verify middleware applies to route
- Ensure user is authenticated

---

## 📋 EXECUTION PLAN (5 Hours)

### ⏱️ PHASE 1: FIX CODE (3 minutes)

- [ ] **Fix 1**: Add `computed` to TaskDetailModal.vue imports (30 seconds)
  ```bash
  File: resources/js/components/Tasks/TaskDetailModal.vue
  Line: 105
  Add: , computed
  ```

- [ ] **Fix 2**: Add Frappe-Gantt UMD JS to app.js (30 seconds)
  ```bash
  File: resources/js/app.js
  Line: 3 (after CSS import)
  Add: import 'frappe-gantt/dist/frappe-gantt.umd.js';
  ```

- [ ] **Fix 3**: Add CSRF Interceptor to bootstrap.js (1 minute)
  ```bash
  File: resources/js/bootstrap.js
  After: window.axios.defaults.withCredentials = true;
  Add: CSRF interceptor code (see above)
  ```

- [ ] **Fix 4**: Improve fetchAndRenderGantt() in GanttChart.vue (2 minutes)
  ```bash
  File: resources/js/components/Roadmap/GanttChart.vue
  Line: 85-100
  Replace: With improved version (see above)
  ```

- [ ] **Fix 5**: Improve handleSaveEpic() in GanttChart.vue (1 minute)
  ```bash
  File: resources/js/components/Roadmap/GanttChart.vue
  Line: 71-80
  Replace: With improved version (see above)
  ```

### ⏱️ PHASE 2: BACKEND (2 minutes)

- [ ] **Run migrations** (30 seconds)
  ```bash
  php artisan migrate
  ```

- [ ] **Run epic seeder** (30 seconds)
  ```bash
  php artisan db:seed --class=TicketStatusSeeder
  php artisan epics:recalculate-progress
  ```

### ⏱️ PHASE 3: BUILD & DEPLOY (2 minutes)

- [ ] **Rebuild frontend** (1 minute)
  ```bash
  npm run build
  ```

- [ ] **Restart server** (30 seconds)
  ```bash
  php artisan serve
  ```

### ⏱️ PHASE 4: VERIFY (Remaining time)

- [ ] **Check browser console**
  ```javascript
  window.Gantt // Should be: ƒ Gantt(...)
  axios.defaults.headers.common['X-CSRF-TOKEN'] // Should be: token_string
  ```

- [ ] **Check Network tab**
  ```
  GET /api/projects/3/roadmap
  Status: 200 (not 401/419)
  ```

- [ ] **Test Gantt chart**
  - Navigate to `/projects/{id}/roadmap`
  - Should see chart with epics
  - Should NOT see loading spinner forever
  - Should NOT see error messages

- [ ] **Test Save Epic**
  - Click "Create Epic" button
  - Fill form
  - Click Save
  - Should NOT get 419 error
  - Should see new epic on chart

---

## 🎯 IF YOU HAVE ONLY 15 MINUTES (PRIORITY FIXES)

Do these in order:

```bash
# 1. Fix Code (5 minutes)
# - app.js: Add Frappe-Gantt UMD JS import
# - bootstrap.js: Add CSRF interceptor
# - TaskDetailModal.vue: Add computed import

# 2. Backend (2 minutes)
php artisan migrate

# 3. Build (2 minutes)
npm run build

# 4. Restart (1 minute)
php artisan serve

# 5. Test (3 minutes)
# - Check window.Gantt in console
# - Navigate to /projects/{id}/roadmap
# - Try creating epic
```

---

## 🔍 DEBUGGING COMMANDS (Copy-Paste into Browser Console)

```javascript
// === GANTT CHART DEBUG ===
console.clear();
console.log('=== GANTT CHART STATUS ===\n');

// 1. Check Gantt library
console.log('1️⃣ Gantt Library:');
console.log('   Status:', typeof window.Gantt === 'undefined' ? '❌ NOT LOADED' : '✅ LOADED');
console.log('   Value:', window.Gantt);

// 2. Check CSRF token
console.log('\n2️⃣ CSRF Token:');
console.log('   Header:', axios.defaults.headers.common['X-CSRF-TOKEN'] ? '✅ SET' : '❌ NOT SET');
console.log('   Value:', axios.defaults.headers.common['X-CSRF-TOKEN']);

// 3. Check Axios
console.log('\n3️⃣ Axios:');
console.log('   Loaded:', typeof axios !== 'undefined' ? '✅ YES' : '❌ NO');
console.log('   Base URL:', axios.defaults.baseURL);
console.log('   Credentials:', axios.defaults.withCredentials);

// 4. Test API call
console.log('\n4️⃣ Testing API Call:');
const projectId = window.location.pathname.match(/projects\/(\d+)/)?.[1] || '1';
console.log('   Project ID:', projectId);

fetch(`/api/projects/${projectId}/roadmap`, {
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    'Accept': 'application/json'
  }
})
  .then(r => {
    console.log(`   Status: ${r.status} ${r.statusText} ${r.status === 200 ? '✅' : '❌'}`);
    return r.json();
  })
  .then(data => {
    console.log('   ✅ Response Data:', data);
    console.log('   Epic Count:', Array.isArray(data) ? data.length : 'N/A');
  })
  .catch(e => console.log('   ❌ Error:', e));

console.log('\n=== END DEBUG ===');
```

---

## 📞 COMMON ERRORS & SOLUTIONS

### Error: "window.Gantt is undefined"
**Solution**: Rebuild frontend after adding import to app.js
```bash
npm run build
```

### Error: "401 Unauthorized" on API call
**Solution**: Ensure CSRF interceptor is added to bootstrap.js
```bash
# Verify in console:
axios.defaults.headers.common['X-CSRF-TOKEN']
# Should NOT be undefined
```

### Error: "419 CSRF token mismatch" on POST
**Solution**: Call `/sanctum/csrf-cookie` before POST
```javascript
await axios.get('/sanctum/csrf-cookie');
await axios.post('/api/epics', data);
```

### Error: "computed is not defined"
**Solution**: Add `computed` to Vue imports
```javascript
import { ref, computed } from 'vue';
```

### Error: Infinite loading spinner
**Solution**: Ensure all loading.value = false statements exist
```javascript
// In finally block or error handling
finally {
  loading.value = false;
}
```

---

## ✅ SUCCESS CRITERIA

- [ ] Browser console shows: `window.Gantt` is function, not undefined
- [ ] Network tab shows: `/api/projects/{id}/roadmap` returns 200
- [ ] Roadmap page loads: Gantt chart visible with epics
- [ ] Create epic works: No 419/401 errors
- [ ] Progress bars render: Shows % completion for each epic
- [ ] Error messages: If errors occur, they're descriptive (not infinite loading)

---

## 📚 RELATED FILES

### Frontend
- `resources/js/app.js` - ✅ FIX #2: Add Frappe-Gantt UMD JS
- `resources/js/bootstrap.js` - ✅ FIX #3: Add CSRF Interceptor
- `resources/js/components/Tasks/TaskDetailModal.vue` - ✅ FIX #1: Add computed
- `resources/js/components/Roadmap/GanttChart.vue` - ✅ FIX #4, #5: Improve error handling
- `resources/js/components/Epics/EpicFormModal.vue` - Reference

### Backend
- `app/Http/Controllers/Api/Pages/RoadMapController.php` - getRoadmap() logic
- `app/Http/Controllers/Api/Resources/EpicController.php` - Epic CRUD
- `app/Models/Epic.php` - recalculateProgress() method
- `routes/api.php` - API route definitions

### Database
- `database/migrations/2025_12_23_074930_add_progress_to_epics_table.php` - Progress column
- `database/seeders/TicketStatusSeeder.php` - is_final flag

---

**Last Updated**: 23 December 2025  
**Status**: Ready for implementation  
**Estimated Time**: 30 minutes for fixes + 5 minutes for testing
