# CondoManager Portal User Guide

This guide explains how to use the condominium portal as a resident, guard, property admin, and super admin.

## 1. What The Portal Does

CondoManager is a Phase 1 condominium management portal with three main areas:

- Resident portal: visitor registration, QR visitor passes, facility booking, profile, and notifications.
- Guard portal: QR validation, visitor check-in/check-out, visitor list, and activity log.
- Admin portal: user approval, visitor monitoring, facility management, booking approval, announcements, and settings.

All users sign in from the same login page:

```text
http://127.0.0.1:8000/login
```

After login, the portal redirects users to the correct dashboard based on their role.

## 2. User Roles

The system has 4 roles.

| Role | Main Purpose | Portal Area |
| --- | --- | --- |
| Super Admin | Full system administration | Admin portal |
| Property Admin | Day-to-day property management | Admin portal |
| Guard | Visitor verification and entry control | Guard portal |
| Resident | Visitor registration and facility booking | Resident portal |

Access is controlled in both the frontend routes and backend API. A resident cannot use admin or guard endpoints just by changing the URL.

## 3. Demo Users

A fresh seeded database creates 6 demo users:

| User | Email | Role | Status | Default Seed Password |
| --- | --- | --- | --- | --- |
| Super Admin | `superadmin@example.com` | Super Admin | Approved | `password` |
| Property Admin | `admin@example.com` | Property Admin | Approved | `password` |
| Security Guard | `guard@example.com` | Guard | Approved | `password` |
| John Resident | `resident@example.com` | Resident | Approved | `password` |
| Jane Pending | `pending@example.com` | Resident | Pending | `password` |
| Bob Rejected | `rejected@example.com` | Resident | Rejected | `password` |

Note: your current local database may have different passwords if accounts were changed manually. You have been using `superadmin@example.com` with `123456` locally.

## 4. Property Setup In Demo Data

The seed data includes:

- Property: Broadleaf Residence
- Address: 1, Jalan Broadleaf, 50000 Kuala Lumpur
- Blocks: 12A and 8B
- Units:
  - Block 12A: 184G
  - Block 8B: 101, 202, 303

The approved demo resident is assigned to:

```text
Broadleaf Residence / 12A / 184G
```

## 5. General Login And Logout

1. Open `http://127.0.0.1:8000/login`.
2. Enter email and password.
3. Click `Sign in`.
4. The portal redirects you based on role:
   - Super Admin or Property Admin: `/admin/dashboard`
   - Guard: `/guard/dashboard`
   - Resident: `/resident/dashboard`
   - Pending resident: `/pending-approval`
   - Rejected resident: `/rejected`
5. To logout, click the user name menu at the top right and select `Logout`.

## 6. Resident Portal Guide

Resident navigation includes:

- Dashboard
- Visitors
- Facilities
- Bookings
- Notifications
- Profile

### 6.1 Register A New Resident Account

1. Open `/register`.
2. Fill in:
   - Full Name
   - Email
   - Phone
   - Property
   - Block
   - Unit
   - Resident Type: Owner or Tenant
   - Password
   - Confirm Password
3. Click `Register`.
4. The account becomes `Pending`.
5. The resident cannot use protected resident functions until an admin approves the account.

### 6.2 Pending And Rejected Accounts

Pending residents see a pending approval page after login.

Rejected residents see a rejected page. If the admin entered a rejection reason, the reason is shown to the resident.

### 6.3 Register A Visitor

Use this when a resident wants to create a single-entry QR pass for a guest.

1. Login as an approved resident.
2. Go to `Visitors`.
3. Click the action to create/register a visitor.
4. Fill in:
   - Purpose of Visit: Visitor, Delivery, Contractor, Service Provider, Family, or Other
   - Visitor Name
   - Contact Number
   - Vehicle Number, optional
   - Visit Date
   - Notes, optional
5. Click `Register Visitor`.
6. The portal opens the visitor detail page and shows the QR token/pass.
7. Send or show the QR pass to the visitor.

Important rules:

- Visitor QR passes use secure tokens, not database IDs.
- Phase 1 visitor QR passes are single-entry only.
- After check-in, the resident cannot retrieve the QR token again from the normal QR endpoint.
- Cancelled, expired, checked-out, or invalid passes cannot be used for entry.

### 6.4 View Or Cancel A Visitor Pass

1. Go to `Visitors`.
2. Open a visitor record.
3. Review visitor details, status, property/unit, and QR information.
4. If the pass is still active, use `Cancel` to cancel it.

### 6.5 Book A Facility

1. Go to `Facilities` to view available facilities.
2. Open a facility or go to `Bookings` and create a booking.
3. Select:
   - Facility
   - Date
   - Available time slot
4. Click `Book Now`.
5. The booking appears in the resident bookings list.

Facility booking rules come from the facility setup:

- Opening and closing time
- Slot duration
- Capacity
- Maximum bookings per resident
- Advance booking days
- Cancellation cutoff hours
- Maintenance status

### 6.6 View Bookings

1. Go to `Bookings`.
2. Review current and past bookings.
3. Booking statuses include pending, approved, rejected, cancelled, and completed.
4. If allowed by the cancellation rules, cancel the booking from the booking record/list.

### 6.7 Notifications

1. Click the bell icon or go to `Notifications`.
2. Read announcements and system notifications.
3. Mark individual notifications as read or use mark-all-read if available.

Residents may receive notifications for:

- Visitor check-in
- Visitor check-out
- Admin announcements
- Booking or account updates, depending on workflow

### 6.8 Profile And Password

1. Click the user menu.
2. Select `Profile`.
3. View account details:
   - Name
   - Email
   - Phone
   - Property
   - Block
   - Unit
   - Resident type
   - Account status
4. Update allowed personal information.
5. Use the change password section to change password.

Residents should not directly change approved property, block, or unit. That information is controlled by admin approval.

## 7. Guard Portal Guide

Guard navigation includes:

- Dashboard
- Scanner
- Visitors
- Activity Log

### 7.1 Validate A Visitor QR Pass

1. Login as a guard.
2. Go to `Scanner`.
3. Paste or enter the QR token.
4. Click `Validate QR Code`.
5. The system shows whether the pass is valid and what action is allowed.

The scanner result may allow:

- Check In Visitor
- Check Out Visitor
- No action, if invalid, expired, cancelled, already checked out, or blocked by rules

### 7.2 Check In A Visitor

1. Validate the QR token.
2. Confirm visitor details:
   - Visitor name
   - Contact
   - Vehicle number
   - Purpose
   - Visit date
   - Property
   - Unit
   - Reference number
3. If the result allows entry, click `Check In Visitor`.
4. The activity is logged.
5. The resident receives a check-in notification.

### 7.3 Check Out A Visitor

1. Validate the same visitor pass or open the visitor record.
2. If the visitor is checked in, click `Check Out Visitor`.
3. The pass becomes used/checked out.
4. The activity is logged.
5. The resident receives a check-out notification.

### 7.4 Visitor List And Activity Log

Use `Visitors` to search and review visitor records.

Use `Activity Log` to review guard actions such as:

- QR scanned
- Visitor checked in
- Visitor checked out
- Invalid scan attempts

## 8. Admin Portal Guide

Admin navigation includes:

- Dashboard
- Users
- Visitors
- Facilities
- Bookings
- Notifications
- Settings

Super Admin and Property Admin share the admin portal. Super Admin has full permissions. Property Admin has day-to-day management permissions but does not have full settings-edit access in the seeded permission setup.

### 8.1 Admin Dashboard

The dashboard summarizes:

- Total users
- Pending approval users
- Approved users
- Suspended users
- Visitor activity
- Booking activity

Use the dashboard for quick access to pending users and pending bookings.

### 8.2 Approve A Resident

1. Go to `Users`.
2. Filter or search for pending users.
3. Open a user record.
4. Review:
   - Name
   - Email
   - Phone
   - Role
   - Resident type
   - Property/unit
   - Registration date
5. Click `Approve`.
6. The resident status changes to `Approved`.
7. The resident can now login and use resident portal functions.

### 8.3 Reject A Resident

1. Go to `Users`.
2. Open a pending user.
3. Click `Reject`.
4. Enter a rejection reason.
5. Confirm rejection.
6. The resident status changes to `Rejected`.
7. The rejected resident sees the rejection page and reason after login.

### 8.4 Suspend Or Reactivate A User

To suspend:

1. Go to `Users`.
2. Open an approved user.
3. Click `Suspend`.
4. Enter a reason if needed.
5. Confirm.

To reactivate:

1. Open a suspended user.
2. Click `Reactivate`.

Suspended residents should not be allowed to continue normal resident operations.

### 8.5 Send Password Reset

1. Go to `Users`.
2. Open the user.
3. Click `Send Password Reset`.
4. The system sends a password reset link using the configured mail setup.

### 8.6 Manage Visitors

1. Go to `Visitors`.
2. Review all visitor registrations.
3. Use filters/search where available.
4. Open a visitor record for full details and activity logs.
5. Cancel a visitor pass if needed.
6. Export visitors if required.

Admins can monitor visitor status across the property, including pending/active, checked-in, checked-out, cancelled, and expired records.

### 8.7 Manage Facilities

1. Go to `Facilities`.
2. View facility list.
3. Create a new facility or edit an existing one.
4. Configure:
   - Name
   - Description
   - Rules
   - Capacity
   - Opening time
   - Closing time
   - Slot duration
   - Maximum bookings per resident
   - Advance booking days
   - Cancellation cutoff hours
   - Active/maintenance status
5. Save changes.

Admins can also create blocked slots for facility maintenance or unavailable periods.

### 8.8 Manage Facility Bookings

1. Go to `Bookings`.
2. Review booking requests.
3. Open a booking.
4. Approve, reject, or cancel the booking.
5. If rejecting, provide a reason.
6. Export bookings if needed.

Booking statuses include:

- Pending
- Approved
- Rejected
- Cancelled
- Completed

### 8.9 Create Notifications Or Announcements

1. Go to `Notifications`.
2. Click create/new notification.
3. Enter notification title and message.
4. Select the target audience, depending on available options:
   - Residents
   - Selected residents
   - Property-level recipients
   - Other configured target types
5. Save or publish the notification.
6. Published notifications appear in resident notification inboxes.

Admins can also archive notifications when they are no longer needed.

### 8.10 Settings

1. Go to `Settings`.
2. Review system settings.
3. Super Admin can edit settings where permission allows.
4. Property Admin may have restricted settings permissions.

## 9. Common Daily Workflow

### Resident Visitor Entry Workflow

1. Resident registers visitor.
2. Resident gives QR pass/token to visitor.
3. Visitor arrives at guard house.
4. Guard validates QR token.
5. Guard checks visitor details.
6. Guard clicks `Check In Visitor`.
7. Resident receives check-in notification.
8. Visitor leaves.
9. Guard checks visitor out.
10. Resident receives check-out notification.

### Resident Registration Approval Workflow

1. Resident registers account.
2. Resident status becomes pending.
3. Admin opens `Users`.
4. Admin reviews pending account.
5. Admin approves or rejects.
6. Approved resident can use the portal.
7. Rejected resident sees rejection reason.

### Facility Booking Workflow

1. Resident views facilities.
2. Resident selects a date and available time slot.
3. Resident submits booking.
4. Booking status becomes pending unless business rules auto-approve in future.
5. Admin reviews booking.
6. Admin approves or rejects.
7. Resident checks booking status from `Bookings`.

## 10. Status Reference

### User Statuses

| Status | Meaning |
| --- | --- |
| Pending | Registered but not approved yet |
| Approved | Can access permitted portal functions |
| Rejected | Registration was rejected |
| Suspended | Account access is restricted |

### Visitor Statuses

| Status | Meaning |
| --- | --- |
| Active/Pending | Pass created and not yet used |
| Checked In | Visitor entered the property |
| Checked Out | Visitor left; single-entry pass is used |
| Cancelled | Pass cancelled by resident/admin |
| Expired | Visit date/pass is no longer valid |

### Booking Statuses

| Status | Meaning |
| --- | --- |
| Pending | Waiting for admin review |
| Approved | Booking accepted |
| Rejected | Booking declined |
| Cancelled | Booking cancelled |
| Completed | Booking date/time has passed |

## 11. Recommended Testing Checklist

Use this checklist after starting the app.

1. Login as Super Admin and confirm `/admin/dashboard` loads.
2. Login as Guard and confirm `/guard/dashboard` loads.
3. Login as approved Resident and confirm `/resident/dashboard` loads.
4. Login as pending Resident and confirm the pending approval page loads.
5. Login as rejected Resident and confirm the rejection page loads.
6. Register a new resident and approve it from Admin > Users.
7. Register a visitor as resident.
8. Validate the visitor QR token as guard.
9. Check in and check out the visitor as guard.
10. Create a facility booking as resident.
11. Approve or reject the booking as admin.
12. Send a notification as admin and confirm the resident can read it.

## 12. Troubleshooting

If login redirects incorrectly or appears frozen:

1. Clear browser local storage for `127.0.0.1:8000`.
2. Refresh the page.
3. Login again.

If Vite assets are not updating:

```bash
yarn dev
```

If Laravel routes are not responding:

```bash
php artisan serve
```

If the database is empty:

```bash
php artisan migrate --seed
```

If seeded passwords do not work, check whether the local database was changed manually or reset the user password from Admin > Users.
