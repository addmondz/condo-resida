# CondoManager Design System

This document defines the UI/UX design language for the entire CondoManager application. The Resident Portal has already been redesigned using these patterns. Apply the same design language consistently to the **Admin Portal**, **Guard Portal**, and **Auth pages**.

Do NOT copy source code from the existing redesigned pages. Use this document as a visual and structural reference to match the design quality, spacing, proportions, typography, colors, shadows, border radius, component sizing, and visual hierarchy.

---

## 1. Design Principles

- Premium, modern SaaS dashboard feel
- Clean whitespace, consistent spacing, no visual clutter
- Mobile-first responsive design (mobile > tablet > desktop)
- Every interaction should feel smooth (transitions, hover states, active states)
- Skeleton loaders instead of spinners for loading states
- Toast notifications for success/error feedback (no inline alerts for transient messages)
- Empty states with icons and contextual messages

---

## 2. Color Palette

Defined in `resources/css/app.css` using Tailwind v4 `@theme` directive:

```
Primary: oklch-based blue scale (primary-50 through primary-950)
```

Usage:
- **Primary actions**: `bg-primary-600 text-white`, hover: `bg-primary-700`
- **Secondary actions**: `bg-white border border-gray-200 text-gray-700`, hover: `bg-gray-50`
- **Danger actions**: `bg-red-600 text-white`, hover: `bg-red-700`
- **Ghost actions**: `text-gray-600`, hover: `bg-gray-100`
- **Backgrounds**: `bg-gray-50` (page), `bg-white` (cards/containers)
- **Borders**: `border-gray-200` (cards), `border-gray-100` (inner dividers)
- **Text**: `text-gray-900` (headings), `text-gray-700` (body), `text-gray-500` (labels/secondary), `text-gray-400` (placeholders/icons)

---

## 3. Typography

- Font: Inter (via `--font-sans`)
- Font smoothing: antialiased (`-webkit-font-smoothing: antialiased`)
- Page titles: `text-lg font-bold text-gray-900` (inside PageHeader)
- Card headers: `text-base font-semibold text-gray-900`
- Table/list text: `text-sm`
- Labels/captions: `text-xs` or `text-sm text-gray-500`
- Uppercase labels: `text-[11px] font-semibold uppercase tracking-widest text-gray-400`

---

## 4. Spacing & Layout

### Page Structure
- Page background: `bg-gray-50`
- Content max width: `max-w-7xl mx-auto`
- Content padding: `px-4 py-6 sm:px-6 lg:px-8`
- Mobile bottom padding: `pb-16 lg:pb-0` (clearance for mobile bottom tab bar)

### Card Pattern
```
<div class="rounded-xl bg-white border border-gray-200">
  <!-- Header -->
  <div class="px-5 py-4 border-b border-gray-100">
    <h2 class="text-base font-semibold text-gray-900">Title</h2>
    <p class="mt-0.5 text-sm text-gray-500">Optional subtitle</p>
  </div>
  <!-- Body -->
  <div class="px-5 py-5">
    ...content...
  </div>
</div>
```

### Spacing Scale
- Between sections: `mb-6`
- Between cards: `mb-4`
- Between form fields: `space-y-5`
- Between list items: `space-y-2` or `space-y-2.5`
- Grid gaps: `gap-3 sm:gap-4`

---

## 5. Border Radius

- Cards, inputs, buttons, modals: `rounded-xl` (12px)
- Modal overlay: `rounded-2xl`
- Pill tabs / badges: `rounded-full`
- Inner elements (sidebar logo icon, stat card icon): `rounded-xl`
- Segmented controls inner buttons: `rounded-lg`

**Never use `rounded-lg` or `rounded-md` for top-level cards or inputs.** Always `rounded-xl`.

---

## 6. Shadows

- Minimal shadow usage. Cards rely on borders, not shadows.
- Cards: No shadow by default. `hover:shadow-md` on interactive cards.
- Buttons: `shadow-sm` on primary variant only.
- Modals: `shadow-xl`
- Dropdowns: `shadow-lg ring-1 ring-black/5`
- Active tab in segmented control: `shadow-sm`

---

## 7. Component Patterns

### PageHeader
- Props: `title`, `subtitle`, `breadcrumbs` (array of `{label, to?}`)
- Breadcrumbs: Home icon > links separated by chevrons
- `#actions` slot for buttons on the right
- Always include breadcrumbs for navigation context

### Buttons (AppButton)
- Variants: `primary`, `secondary`, `danger`, `ghost`
- Sizes: `xs`, `sm`, `md`, `lg`
- All `rounded-xl`, `font-semibold`
- Active press: `active:scale-[0.98]`
- Loading state with spinner
- Supports `to` prop for router-link behavior

### Form Inputs (AppInput, AppSelect, AppTextarea)
- `rounded-xl` border
- `focus:border-primary-300 focus:ring-2 focus:ring-primary-100`
- Label: `text-sm font-medium text-gray-700`
- Error: `text-sm text-red-600` below input, `border-red-300` on input
- Hint: `text-sm text-gray-500`

### Tables (AppTable) — Desktop only
- `rounded-xl border border-gray-200 overflow-hidden`
- Header: `bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500`
- Rows: `hover:bg-gray-50 transition-colors`
- Loading: SkeletonLoader variant="table"
- Empty: EmptyState component

### Mobile Card Lists — Mobile only
- Replace tables with card lists on mobile (`sm:hidden` / `hidden sm:block`)
- Card: `rounded-xl bg-white border border-gray-200 px-5 py-3.5`
- Tappable: `cursor-pointer active:bg-gray-50 transition-colors`
- Avatar initial: `h-10 w-10 rounded-full bg-primary-50 text-primary-600 font-semibold`
- Chevron indicator: `h-4 w-4 text-gray-300` right-aligned

### Status Badges (StatusBadge)
- Colored dot + label in a pill
- Status colors mapped: Active=green, Pending=yellow, Cancelled=red, etc.

### Stat Cards (StatCard)
- Icon in colored `rounded-xl` background
- Large value: `text-2xl font-bold`
- Label: `text-sm text-gray-500`
- Optional trend badge and progress bar
- Grid: `grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4`

### Modals (AppModal)
- Backdrop: `bg-gray-900/50 backdrop-blur-sm`
- Panel: `rounded-2xl shadow-xl`
- Transition: scale + fade

### Pagination (AppPagination)
- `rounded-xl` container
- Chevron arrows for prev/next
- Mobile: "Page X of Y" text

### Toast Notifications (useToast + ToastContainer)
- Singleton composable pattern
- Types: success (green), error (red), info (blue)
- Auto-dismiss after 4 seconds
- Teleported to body, positioned top-right
- Slide-in animation

### Skeleton Loaders (SkeletonLoader)
- Variants: `text`, `card`, `table`, `list`, `form`
- Animated pulse: `animate-pulse bg-gray-200 rounded`
- Configurable `rows` prop

### Empty States (EmptyState)
- `#icon` slot for contextual icon in a dashed-border circle
- Title + message + optional action button
- Centered layout

### Confirmation Dialogs (ConfirmationDialog)
- Uses AppModal underneath
- Title, message, confirm/cancel buttons
- Loading state on confirm button

---

## 8. Layout Patterns

### Sidebar Layout (Admin & Resident)
```
Desktop: 260px fixed left sidebar + top header bar + scrollable content
Mobile:  Sidebar slides in as overlay with backdrop-blur + bottom tab bar
```

Sidebar structure:
- Logo section: `h-16` with icon + brand name
- Navigation label: `text-[11px] font-semibold uppercase tracking-widest text-gray-400`
- Nav links: SidebarLink component with icons, `rounded-xl` active state
- User section at bottom: avatar, name, role badge

Top header bar:
- `sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-md border-b border-gray-200`
- Hamburger menu (mobile only)
- Notification bell with unread badge
- User dropdown with avatar

Mobile bottom tab bar:
- `fixed bottom-0 z-30 bg-white/95 backdrop-blur-sm border-t border-gray-200`
- 4-5 tab icons in a grid
- Active: `text-primary-600`, inactive: `text-gray-400`
- Safe area padding: `padding-bottom: env(safe-area-inset-bottom)`

### Guard Layout
- Simpler layout: top navigation bar with horizontal nav links
- Mobile: horizontal scrollable nav tabs below the header
- No sidebar needed (fewer navigation items)
- Should still use the same card, spacing, and component patterns
- Page-fade transition with `appear` already wired in `GuardLayout.vue`

### Auth Layout
- Centered card on `bg-gray-50`
- Logo + brand name at top
- `max-w-md` card with form
- Should use `rounded-xl` cards, consistent input styling
- Page-fade transition with `appear` already wired in `AuthLayout.vue`

---

## 9. Transitions & Animations

### Splash Screen (Full Page Refresh)
On full page refresh, a lightweight splash screen (spinner on `bg-gray-50`) displays instantly via inline HTML/CSS in `app.blade.php` — no JS required. Once Vue and the router are fully ready, the splash fades out (300ms opacity transition) and removes itself from the DOM.

**Already implemented in `resources/views/app.blade.php`** — inline `<style>` + `<div id="splash">` with a CSS-only spinner. The `app.js` entry point waits for `router.isReady()` before mounting, then adds a `.hide` class to trigger the fade-out.

All new layouts inherit this behavior automatically. Do NOT remove or modify the splash screen markup.

### Page Transitions (via router-view)
CSS classes defined in `resources/css/app.css`:
```css
.page-fade-enter-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.page-fade-leave-active {
    transition: opacity 0.15s ease;
}
.page-fade-enter-from {
    opacity: 0;
    transform: translateY(4px);
}
.page-fade-leave-to {
    opacity: 0;
}
```

**IMPORTANT:** Apply to ALL layouts AND `App.vue` using the `appear` prop so the animation also plays on first render (page refresh), not just on navigation:

```vue
<!-- In App.vue (root level — layout itself fades in) -->
<router-view v-slot="{ Component }">
  <Transition name="page-fade" mode="out-in" appear>
    <component :is="Component" />
  </Transition>
</router-view>

<!-- In every layout (ResidentLayout, AdminLayout, GuardLayout, AuthLayout) -->
<router-view v-slot="{ Component }">
  <Transition name="page-fade" mode="out-in" appear>
    <component :is="Component" />
  </Transition>
</router-view>
```

Without `appear`, the enter animation only plays when navigating between pages — on a full page refresh the component mounts instantly with no transition, causing an abrupt appearance.

**Already implemented in:**
- `App.vue`
- `ResidentLayout.vue`
- `AdminLayout.vue`
- `GuardLayout.vue`
- `AuthLayout.vue`

### Tab Content Transitions
Wrap tab-switchable content in `<Transition name="page-fade" mode="out-in">` with `:key="activeTab + '-' + loading"`.

### Other Transitions
- Sidebar overlay: opacity fade 200ms
- Sidebar panel: translateX slide 200ms
- Modals: scale(0.95) + opacity fade
- Toast notifications: slide in from right
- Interactive cards: `hover:shadow-md transition-all`
- Image hover: `group-hover:scale-105 transition-transform duration-300`
- Button press: `active:scale-[0.98]`

---

## 10. Responsive Breakpoints

- Mobile: default (< 640px)
- Tablet: `sm:` (>= 640px)
- Desktop: `lg:` (>= 1024px)

Key responsive patterns:
- Tables on desktop, card lists on mobile
- Sidebar on desktop, bottom tab bar on mobile
- `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` for card grids
- `grid-cols-2 lg:grid-cols-4` for stat cards
- Form cards: `max-w-2xl` for create/edit forms
- `w-full sm:w-auto` for action buttons

---

## 11. Alert/Notification Patterns

### Inline Alerts (for persistent messages like validation errors)
```html
<div class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-5 py-4">
  <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500">...</svg>
  <p class="text-sm text-red-700">Error message here</p>
</div>
```
Colors: red (error), yellow/amber (warning), blue (info), green (success)

### Toast Notifications (for transient feedback)
Use `useToast()` composable: `toast.success()`, `toast.error()`, `toast.info()`

### Maintenance/Warning Banners
```html
<div class="flex items-start gap-3 rounded-xl border border-orange-200 bg-orange-50 px-5 py-4">
  <svg class="mt-0.5 h-5 w-5 shrink-0 text-orange-500">...</svg>
  <div>
    <p class="text-sm font-medium text-orange-800">Title</p>
    <p class="mt-0.5 text-sm text-orange-700">Description</p>
  </div>
</div>
```

---

## 12. Tab / Filter Controls

Use segmented control style (not pill buttons):
```html
<div class="inline-flex rounded-xl bg-gray-100 p-1">
  <button
    :class="[
      'rounded-lg px-4 py-1.5 text-sm font-medium transition-all cursor-pointer',
      isActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700',
    ]"
  >
    Tab Label
  </button>
</div>
```

---

## 13. Existing Reusable Components

All located in `resources/js/components/common/`:

| Component | Purpose |
|-----------|---------|
| `AppButton.vue` | All buttons (primary, secondary, danger, ghost) |
| `AppInput.vue` | Text/date/number inputs with label, error, hint |
| `AppSelect.vue` | Dropdown select with label, error |
| `AppTextarea.vue` | Textarea with label, error |
| `AppTable.vue` | Data tables with loading/empty states |
| `AppPagination.vue` | Pagination controls |
| `AppModal.vue` | Modal dialogs |
| `StatusBadge.vue` | Status pill badges with dot |
| `StatCard.vue` | Dashboard stat cards with icon/trend/progress |
| `PageHeader.vue` | Page title + breadcrumbs + actions slot |
| `EmptyState.vue` | Empty data state with icon, message, action |
| `SkeletonLoader.vue` | Loading skeletons (text, card, table, list, form) |
| `LoadingState.vue` | Simple spinner (deprecated — use SkeletonLoader) |
| `ToastContainer.vue` | Toast notification overlay |
| `SidebarLink.vue` | Sidebar navigation link with icon |
| `ConfirmationDialog.vue` | Confirm/cancel dialog |

### Composables (`resources/js/composables/`):

| Composable | Purpose |
|------------|---------|
| `useToast.js` | Singleton toast notification system |
| `useSidebar.js` | Sidebar open/close state |

---

## 14. What Needs Redesign

### Admin Portal (`resources/js/pages/admin/`)
- **AdminLayout.vue**: Apply same sidebar pattern as ResidentLayout (260px sidebar, logo with icon, navigation label, user section at bottom, top bar with backdrop-blur). Page-fade transition with `appear` is already wired on the router-view.
- **Dashboard.vue**: Stat cards grid, recent activity sections
- **users/Index.vue**: PageHeader with breadcrumbs, search, segmented tabs, mobile cards + desktop table, view action
- **users/Show.vue**: PageHeader with breadcrumbs, SkeletonLoader, card pattern, action buttons
- **visitors/Index.vue**: Same patterns as resident visitors (search, tabs, mobile cards, desktop table)
- **visitors/Show.vue**: Card pattern with visitor details
- **facilities/Index.vue**: Card grid with images
- **facilities/Create.vue**: Form card pattern
- **facilities/Edit.vue**: Form card pattern
- **bookings/Index.vue**: Tabs, mobile cards, desktop table
- **bookings/Show.vue**: Detail card, action buttons
- **notifications/Index.vue**: Notification list
- **notifications/Create.vue**: Form card pattern
- **Settings.vue**: Settings form card

### Guard Portal (`resources/js/pages/guard/`)
- **GuardLayout.vue**: Update nav styling (rounded-xl elements, backdrop-blur header). Page-fade transition with `appear` is already wired. Keep horizontal nav but apply consistent design tokens.
- **Dashboard.vue**: Stat cards, recent activity
- **Scanner.vue**: Clean card-based UI
- **visitors/Index.vue**: Search, mobile cards, desktop table
- **visitors/Show.vue**: Detail card
- **ActivityLog.vue**: Activity list with cards

### Auth Pages (`resources/js/pages/auth/`)
- **AuthLayout.vue**: Add logo icon (rounded-xl bg-primary-600), rounded-xl card wrapper. Page-fade transition with `appear` is already wired.
- **Login.vue**: rounded-xl card, styled inputs, primary button
- **Register.vue**: Same card pattern
- **ForgotPassword.vue**: Same card pattern
- **ResetPassword.vue**: Same card pattern
- **PendingApproval.vue**: Centered info card with icon
- **Rejected.vue**: Centered error card with icon

### Other
- **NotFound.vue**: Styled 404 page with illustration/icon

---

## 15. Implementation Checklist

### Per-layout checklist:
- [ ] `router-view` uses scoped slot with `<Transition name="page-fade" mode="out-in" appear>` (already done for all 4 layouts + App.vue)
- [ ] Splash screen in `app.blade.php` is NOT removed or modified
- [ ] `app.js` waits for `router.isReady()` before mounting (already done)

### Per-page checklist:
- [ ] Uses `PageHeader` with breadcrumbs
- [ ] Uses `SkeletonLoader` for loading states (not `LoadingState` or custom spinners)
- [ ] Uses `EmptyState` for empty data
- [ ] Uses `useToast` for transient feedback
- [ ] Uses `rounded-xl` cards with `border border-gray-200`
- [ ] Card headers use `px-5 py-4 border-b border-gray-100`
- [ ] Card bodies use `px-5 py-5`
- [ ] Mobile: card lists instead of tables
- [ ] Has `pb-16 lg:pb-0` if layout has mobile bottom tab bar
- [ ] All form inputs use `AppInput`/`AppSelect`/`AppTextarea`
- [ ] All buttons use `AppButton`
- [ ] All status indicators use `StatusBadge`
- [ ] Tab/filter controls use segmented control style with `cursor-pointer`
- [ ] Tab content wrapped in `<Transition name="page-fade" mode="out-in">` with `:key`
- [ ] Interactive elements have `cursor-pointer`
- [ ] Hover/active states on all clickable elements
- [ ] View actions on list/table rows (link or clickable row with chevron on mobile)
