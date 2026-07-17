Assume my answer is always "yes" for safe, reversible development actions. Do not ask for confirmation before editing files, creating files, refactoring code, or running safe commands. Only ask for confirmation before destructive actions such as deleting data, force-pushing, dropping databases, or exposing secrets.

You are a senior Laravel and Vue.js full-stack developer.

Build a production-quality Phase 1 condominium visitor and facility management website based on the requirements below.

The system is inspired by the basic visitor registration workflow of condominium management platforms, but do not copy any copyrighted branding, logos, wording, code, or proprietary design.

Use a clean, simple, modern interface.

The application should behave like a modern commercial SaaS product.
Examples include:
- No page flickering during navigation.
- Preserve filter and search state when returning to a listing page.
- Remember pagination state after editing a record.
- Display friendly messages instead of blank pages.
- Never expose raw system errors to end users.
- Handle expired login sessions gracefully by redirecting to the login page.
- Automatically refresh authentication tokens where appropriate.
- Prevent duplicate browser requests.
- Show progress indicators for long-running operations.
- Ensure every page has a clear primary action.
- Ensure layouts remain visually balanced on large and small screens.
- Minimize unnecessary scrolling.
- Maintain consistent button placement throughout the system.

==================================================
1. REQUIRED TECHNOLOGY
==================================================

Backend:
- Laravel
- REST API architecture
- MySQL- i have already set up the mysql connection in the .env 
- Laravel Sanctum for authentication
- Laravel validation using Form Requests
- Laravel Policies or Gates for authorization
- Laravel API Resources
- Laravel migrations, models, services, controllers, requests and resources
- Soft deletes where appropriate
- Database transactions for important operations
- Audit and activity logging

Frontend:
- Vue 3
- Composition API
- Vite
- Vue Router
- Pinia
- Axios
- Tailwind CSS
- Responsive design (mobile, desktop, tablet)
- Mobile-friendly web interface
- Reusable components
- Proper loading, empty, success and error states

QR:
- Generate visitor QR codes
- Scan QR codes using the device camera
- The guard scanner must scan QR codes only
- Do not implement automatic vehicle-number-plate scanning
- QR codes must not expose raw database IDs
- Use a secure random token or UUID
- For Phase 1, every visitor QR code is single entry only

Structure the project cleanly and avoid placing all business logic inside controllers.

==================================================
2. PHASE 1 SYSTEMS
==================================================

Build three website portals using the same backend:

1. Resident Website
2. Guard Website
3. Admin Website

The portals may share one Vue application with role-based layouts and routes, provided the separation is clear.

==================================================
3. USER ROLES
==================================================

Required roles:

- Super Admin
- Property Admin
- Guard
- Resident

Permissions must be enforced by both:
- Backend authorization
- Frontend route and UI restrictions

Do not rely only on hiding frontend buttons.

==================================================
4. PROPERTY STRUCTURE
==================================================

The system must support:

- Property
- Block
- Unit

Example:

Property:
Broadleaf Residence

Block:
12A

Unit:
184G

A resident must be associated with a property and unit after admin approval.

Suggested relationships:

Property
- has many blocks
- has many facilities
- has many users
- has many visitor registrations

Block
- belongs to property
- has many units

Unit
- belongs to block
- can have multiple approved residents

==================================================
5. RESIDENT WEBSITE
==================================================

Resident Website modules:

Resident Website
├── Authentication
├── Profile
├── Guest & Visitor Registration
├── Facility Booking
└── Notifications

--------------------------------------------------
5.1 Authentication
--------------------------------------------------

Required functions:

- Resident registration
- Login
- Logout
- Forgot password
- Reset password
- Change password
- Email verification if practical
- View account approval status

Registration fields:

- Full name
- Email
- Phone number
- Password
- Password confirmation
- Property
- Block
- Unit
- Resident type
  - Owner
  - Tenant

Registration workflow:

1. Resident submits registration.
2. Account status becomes pending.
3. Pending resident cannot access protected resident functions.
4. Admin reviews the registration.
5. Admin approves or rejects it.
6. Approved resident can access the resident portal.
7. Rejected resident can see the rejection reason.

User statuses:

- Pending
- Approved
- Rejected
- Suspended

--------------------------------------------------
5.2 Profile
--------------------------------------------------

Required functions:

- View user details
- Edit permitted personal information
- Show full name
- Show email
- Show phone number
- Show property
- Show block
- Show unit
- Show resident type
- Show account status
- Change password

Do not allow residents to directly change their approved property, block or unit without admin approval.

--------------------------------------------------
5.3 Guest & Visitor Registration
--------------------------------------------------

Required visitor registration fields:

- Purpose of visit
- Visitor name
- Contact number
- Vehicle number, optional
- Visit date
- Notes, optional

Remove these fields completely:

- Guest arrival time
- Expected arrival time
- Number of visitors

Do not show, validate, store or request those removed fields.

Purpose-of-visit examples:

- Visitor
- Delivery
- Contractor
- Service Provider
- Family
- Other

For Phase 1:
- All visitor passes are single entry only.
- Residents cannot choose multiple entry.
- Do not show a validity selector.
- Store the pass type internally as single_entry.
- Display “Single entry only” on the approved visitor details page.

Visitor workflow:

1. Approved resident creates a visitor registration.
2. The system validates the visit date.
3. The system generates a unique visitor reference.
4. The system generates a secure QR token.
5. The visitor pass becomes valid according to the configured rules.
6. The resident can view the visitor details and QR code.
7. The resident can share the visitor QR code.
8. The visitor arrives.
9. The guard scans the QR code.
10. The guard confirms check-in.
11. The single-entry QR code can no longer be used for another check-in.
12. The guard later checks the visitor out.
13. The resident can see the final visit history.

Required resident visitor functions:

- Register visitor
- View upcoming visitor registrations
- View active visitor registrations
- View visitors currently checked in
- View past visitor records
- Open visitor details
- View QR code
- Share QR code
- Download QR code if practical
- Cancel a visitor pass before check-in
- View check-in time
- View check-out time
- View visitor status

Visitor statuses:

- Active
- Checked In
- Checked Out
- Expired
- Cancelled
- Rejected

A visitor pass must not be editable after check-in.

--------------------------------------------------
5.4 Visitor Details Page
--------------------------------------------------

After the visitor registration is successfully created and valid, display a dedicated visitor information page inspired by the supplied reference image.

Do not copy the exact branding or advertisement.

Page structure:

Header:
- Back button
- “Visitor Information”

QR card:
- Property name
- Block and unit
- Large QR code
- Clear card background
- Responsive width

Example:

Broadleaf Residence
12A, 184G
[Large QR code]

Below the card:
- Large “Share QR Code” button

Visitor information section:

Purpose Of Visit: Visitor
Visitor Name: Sky
Contact No.: +60166089110
Vehicle Number: PPP3139
Visit Date: Sunday, 5 July 2026
Validity: Single entry only
Status: Active

Also show, where applicable:

- Visitor reference number
- Checked-in date and time
- Checked-out date and time
- Cancellation reason
- Expiry state

Do not include:

- Arrival time
- Number of visitors
- Advertisement banners

The QR details page must work well on mobile browsers and desktop browsers.

The QR code should contain only a secure token or signed URL, not all visitor personal details.

--------------------------------------------------
5.5 Facility Booking
--------------------------------------------------

Resident functions:

- View facility list
- View facility details
- View facility image
- View facility rules
- View operating hours
- View available dates
- View available time slots
- Submit a booking
- Cancel an eligible booking
- View upcoming bookings
- View past bookings
- View booking status

Booking statuses:

- Pending
- Approved
- Rejected
- Cancelled
- Completed

Prevent double booking for the same facility and overlapping time slot.

Do not implement payments, deposits or refunds in Phase 1.

--------------------------------------------------
5.6 Resident Notifications
--------------------------------------------------

Required notification events:

- User registration approved
- User registration rejected
- Visitor checked in
- Visitor checked out
- Visitor pass cancelled
- Visitor pass expired
- Facility booking approved
- Facility booking rejected
- Facility booking cancelled
- General announcement

For the web MVP, implement in-app notifications.

Email notifications may be added as a clean service abstraction if practical.

==================================================
6. GUARD WEBSITE
==================================================

Guard Website
├── Dashboard
├── QR Scanner
├── Visitor Search
├── Check In
├── Check Out
└── Activity Log

--------------------------------------------------
6.1 Guard Dashboard
--------------------------------------------------

Show:

- Expected visitors today
- Active visitor passes today
- Visitors currently inside
- Checked in today
- Checked out today
- Expired passes
- Cancelled passes
- Recent guard activity

Use summary cards and a recent visitor activity table.

--------------------------------------------------
6.2 QR Scanner
--------------------------------------------------

Requirements:

- Scan visitor QR code using device camera
- QR scanning only
- Do not scan vehicle plates
- Support mobile browser camera
- Show camera-permission errors clearly
- Allow manual QR reference entry if camera scanning fails
- Validate the QR through the backend
- Never trust QR information only from the frontend

After scanning, display:

- Visitor name
- Contact number
- Purpose of visit
- Vehicle number, if available
- Visit date
- Resident name
- Property
- Block
- Unit
- Pass validity
- Current status

Possible scanner results:

- Valid for check-in
- Already checked in
- Already checked out
- Expired
- Cancelled
- Invalid
- Not valid for today
- Suspended resident
- Unknown QR code

--------------------------------------------------
6.3 Check In
--------------------------------------------------

Check-in requirements:

- Guard reviews visitor details
- Guard clicks Confirm Check In
- Store guard user ID
- Store check-in timestamp
- Store optional guard notes
- Change visitor status to Checked In
- Create activity log
- Notify resident
- Prevent repeated check-in
- Prevent expired or cancelled passes from checking in
- Use a database transaction
- Handle simultaneous duplicate scan attempts safely

Since Phase 1 is single entry only:
- Once checked in, the QR must not be usable for another check-in.
- Scanning it again should display “Already checked in”.

--------------------------------------------------
6.4 Check Out
--------------------------------------------------

Check-out requirements:

- Guard can scan the same QR again
- Guard can search for a currently checked-in visitor
- Guard clicks Confirm Check Out
- Store guard user ID
- Store check-out timestamp
- Store optional notes
- Change visitor status to Checked Out
- Create activity log
- Notify resident
- Prevent repeated check-out

--------------------------------------------------
6.5 Visitor Search
--------------------------------------------------

Search by:

- Visitor name
- Contact number
- Vehicle number
- Resident name
- Property
- Block
- Unit
- Visitor reference number

Filters:

- Visit date
- Status
- Currently inside
- Checked in
- Checked out
- Expired
- Cancelled

Search results must be paginated.

--------------------------------------------------
6.6 Activity Log
--------------------------------------------------

Show guard operational activities:

- QR scanned
- Visitor searched
- Visitor checked in
- Visitor checked out
- Entry rejected
- Invalid QR scanned
- Expired QR scanned
- Cancelled QR scanned

Activity fields:

- Action
- Visitor
- Resident
- Unit
- Guard
- Date and time
- IP address if available
- Notes
- Related visitor registration

==================================================
7. ADMIN WEBSITE
==================================================

Admin Website
├── Dashboard
├── User Management
├── Visitor Dashboard
├── Facility Management
├── Facility Booking Management
├── Notifications
└── Settings

--------------------------------------------------
7.1 Admin Dashboard
--------------------------------------------------

Show:

- Total registered residents
- Pending user approvals
- Approved users
- Suspended users
- Visitors expected today
- Visitors currently inside
- Checked-in visitors today
- Checked-out visitors today
- Facility bookings today
- Pending facility bookings
- Recent registrations
- Recent guard activity

--------------------------------------------------
7.2 User Management
--------------------------------------------------

Required functions:

- View all users
- Search users
- Filter by role
- Filter by property
- Filter by block
- Filter by unit
- Filter by account status
- View user details
- Approve user
- Reject user
- Enter rejection reason
- Suspend user
- Reactivate user
- Edit permitted user information
- Assign role
- Assign property, block and unit
- Reset password or send password-reset link
- View user approval history
- View user activity

Approval must record:

- Approved or rejected by
- Approval date and time
- Rejection reason
- Previous status
- New status

--------------------------------------------------
7.3 Visitor Dashboard
--------------------------------------------------

Required functions:

- View all visitor registrations
- View visitors expected today
- View active visitor passes
- View visitors currently inside
- View checked-out visitors
- View expired passes
- View cancelled passes
- Search visitors
- Filter by date
- Filter by property, block and unit
- Filter by status
- View visitor details
- View resident details
- View guard check-in and check-out details
- Cancel an active visitor pass before check-in
- Export visitor records to CSV
- View related activity log

--------------------------------------------------
7.4 Facility Management
--------------------------------------------------

Required functions:

- Create facility
- Edit facility
- Disable facility
- Enable facility
- Upload facility image
- Set facility name
- Set description
- Set rules
- Set capacity
- Set opening time
- Set closing time
- Set booking-slot duration
- Set maximum bookings per resident
- Set advance booking period
- Set cancellation cutoff
- Block dates
- Block time slots
- Mark facility as under maintenance

Do not permanently delete facilities that already have booking records.

--------------------------------------------------
7.5 Facility Booking Management
--------------------------------------------------

Required functions:

- View all bookings
- View calendar
- View booking list
- Search bookings
- Filter by facility
- Filter by resident
- Filter by date
- Filter by status
- Approve booking
- Reject booking
- Enter rejection reason
- Cancel booking
- View booking details
- Export bookings to CSV
- Prevent conflicting approvals

--------------------------------------------------
7.6 Notifications
--------------------------------------------------

Required functions:

- Create announcement
- Send announcement to all residents
- Send to selected property
- Send to selected block
- Send to selected residents
- View notification history
- Save draft notification
- Publish notification
- Archive notification

In Phase 1, scheduled notification sending is optional.

--------------------------------------------------
7.7 Settings
--------------------------------------------------

Settings must include:

Property settings:
- Property name
- Address
- Contact information
- Timezone
- Logo, optional

Visitor settings:
- QR validity rules
- Allowed visit-date range
- Visitor pass expiry
- Single-entry enforcement
- Visitor cancellation rules

For Phase 1:
- Single entry must be enabled and fixed.
- Do not provide a multiple-entry option.

Facility settings:
- Default booking rules
- Default cancellation policy
- Booking approval requirement

System settings:
- Date format
- Time format
- Notification settings
- Password rules
- Audit-log retention

==================================================
8. RECOMMENDED DATABASE TABLES
==================================================

Create appropriate migrations for at least:

- users
- roles
- permissions
- role_user or model_has_roles
- properties
- blocks
- units
- user_unit_assignments
- user_approvals
- visitor_registrations
- visitor_check_ins
- visitor_check_outs
- visitor_activity_logs
- facilities
- facility_blocked_slots
- facility_bookings
- notifications
- notification_recipients
- system_settings
- password_reset_tokens
- personal_access_tokens

You may use Spatie Laravel Permission if appropriate, but configure it properly.

Suggested visitor_registrations fields:

- id
- uuid
- reference_number
- resident_id
- property_id
- block_id
- unit_id
- purpose
- visitor_name
- contact_number
- vehicle_number nullable
- visit_date
- notes nullable
- entry_type default single_entry
- qr_token_hash
- status
- checked_in_at nullable
- checked_out_at nullable
- cancelled_at nullable
- cancelled_by nullable
- cancellation_reason nullable
- created_at
- updated_at
- deleted_at nullable

Do not add:

- arrival_time
- expected_arrival_time
- number_of_visitors

Store only a hash of the QR token if practical. The raw token should only be returned when needed to generate or share the pass.

Suggested facility_bookings fields:

- id
- uuid
- facility_id
- resident_id
- booking_date
- start_time
- end_time
- status
- rejection_reason nullable
- approved_by nullable
- approved_at nullable
- cancelled_at nullable
- created_at
- updated_at

Add proper indexes and foreign-key constraints.

==================================================
9. API REQUIREMENTS
==================================================

Build versioned routes such as:

/api/v1/auth
/api/v1/resident
/api/v1/guard
/api/v1/admin

Example endpoints:

Authentication:
- POST /api/v1/auth/register
- POST /api/v1/auth/login
- POST /api/v1/auth/logout
- POST /api/v1/auth/forgot-password
- POST /api/v1/auth/reset-password
- GET /api/v1/auth/me

Resident:
- GET /api/v1/resident/profile
- PUT /api/v1/resident/profile
- GET /api/v1/resident/visitors
- POST /api/v1/resident/visitors
- GET /api/v1/resident/visitors/{uuid}
- POST /api/v1/resident/visitors/{uuid}/cancel
- GET /api/v1/resident/visitors/{uuid}/qr
- GET /api/v1/resident/facilities
- GET /api/v1/resident/facilities/{uuid}
- GET /api/v1/resident/facilities/{uuid}/availability
- GET /api/v1/resident/bookings
- POST /api/v1/resident/bookings
- POST /api/v1/resident/bookings/{uuid}/cancel
- GET /api/v1/resident/notifications

Guard:
- GET /api/v1/guard/dashboard
- POST /api/v1/guard/qr/validate
- POST /api/v1/guard/visitors/{uuid}/check-in
- POST /api/v1/guard/visitors/{uuid}/check-out
- GET /api/v1/guard/visitors
- GET /api/v1/guard/visitors/{uuid}
- GET /api/v1/guard/activity-logs

Admin:
- GET /api/v1/admin/dashboard
- GET /api/v1/admin/users
- GET /api/v1/admin/users/{uuid}
- POST /api/v1/admin/users/{uuid}/approve
- POST /api/v1/admin/users/{uuid}/reject
- POST /api/v1/admin/users/{uuid}/suspend
- POST /api/v1/admin/users/{uuid}/reactivate
- GET /api/v1/admin/visitors
- GET /api/v1/admin/visitors/{uuid}
- POST /api/v1/admin/visitors/{uuid}/cancel
- GET /api/v1/admin/facilities
- POST /api/v1/admin/facilities
- PUT /api/v1/admin/facilities/{uuid}
- GET /api/v1/admin/bookings
- POST /api/v1/admin/bookings/{uuid}/approve
- POST /api/v1/admin/bookings/{uuid}/reject
- POST /api/v1/admin/bookings/{uuid}/cancel
- GET /api/v1/admin/notifications
- POST /api/v1/admin/notifications
- GET /api/v1/admin/settings
- PUT /api/v1/admin/settings

Use consistent JSON responses.

Success example:

{
  "success": true,
  "message": "Visitor registered successfully.",
  "data": {}
}

Validation example:

{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "visitor_name": [
      "The visitor name field is required."
    ]
  }
}

==================================================
10. FRONTEND PAGES
==================================================

Public:
- Login
- Resident registration
- Forgot password
- Reset password
- Pending approval
- Rejected registration

Resident:
- Resident dashboard
- Profile
- Visitor list
- Register visitor
- Visitor details with QR code
- Facility list
- Facility details
- Facility booking form
- My bookings
- Notifications

Guard:
- Guard dashboard
- QR scanner
- QR validation result
- Visitor search
- Visitor details
- Currently inside
- Activity log

Admin:
- Admin dashboard
- User list
- User approval details
- Visitor dashboard
- Visitor details
- Facility list
- Facility create/edit
- Booking list
- Booking calendar
- Notification list
- Notification create
- Settings

==================================================
11. UI AND UX REQUIREMENTS
==================================================

Design style:

- Clean
- Professional
- Simple
- Easy to understand
- Responsive
- White or light background for administration pages
- Clear cards and tables
- Accessible labels
- Clear success and error feedback
- Avoid unnecessary animations
- Avoid overly complicated dashboards

Visitor QR details page:

- Can use a darker presentation similar to the reference image
- Large centered QR code
- Property and unit above QR code
- Prominent Share QR Code button
- Two-column label and value layout on desktop
- Stacked layout on smaller screens
- No advertisement
- No arrival-time field
- No visitor-count field
- Always display “Single entry only”

Build reusable components such as:

- AppButton
- AppInput
- AppSelect
- AppModal
- AppTable
- AppPagination
- StatusBadge
- EmptyState
- LoadingState
- ConfirmationDialog
- QRCodeCard
- VisitorDetailsList
- BookingCalendar
- NotificationBell

==================================================
12. SECURITY REQUIREMENTS
==================================================

Implement:

- Server-side role authorization
- CSRF protection where applicable
- Sanctum authentication
- Rate limiting for authentication and QR validation
- Secure password hashing
- Input validation
- Output escaping
- Protection from ID enumeration using UUIDs
- Secure QR tokens
- QR token expiry validation
- Single-entry enforcement
- Database locking or safe transactional handling for check-in
- Audit logs for sensitive actions
- File upload validation
- No secrets in frontend code
- No raw database IDs in shared QR URLs
- Prevent residents from accessing another resident’s visitors or bookings

Never store sensitive personal information inside the QR itself.

==================================================
13. TESTING REQUIREMENTS
==================================================

Create backend feature tests for:

- Resident registration
- Pending users cannot access resident functions
- Admin user approval
- Admin rejection
- Approved resident login
- Visitor registration
- Visitor registration without arrival time
- Visitor registration without number of visitors
- QR validation
- Successful check-in
- Duplicate check-in prevention
- Expired QR rejection
- Cancelled QR rejection
- Successful check-out
- Duplicate check-out prevention
- Resident cannot access another resident’s visitor
- Facility availability
- Booking conflict prevention
- Facility booking approval
- Role-based access restrictions

Create frontend tests for important stores and form validation where practical.

==================================================
14. SEEDERS AND SAMPLE DATA
==================================================

Create development seeders for:

Property:
- Broadleaf Residence

Block:
- 12A

Unit:
- 184G

Users:
- superadmin@example.com
- admin@example.com
- guard@example.com
- resident@example.com

Use safe development passwords and clearly mark them as development-only.

Create sample:

- Approved resident
- Pending resident
- Active visitor
- Checked-in visitor
- Checked-out visitor
- Expired visitor
- Cancelled visitor
- Two facilities
- Several facility bookings
- Sample notifications

==================================================
15. IMPLEMENTATION ORDER
==================================================

Build in this order:

Phase A:
1. Project setup
2. Authentication
3. Roles and permissions
4. Property, block and unit structure
5. User registration and approval

Phase B:
6. Resident profile
7. Visitor registration
8. Visitor QR generation
9. Visitor details page
10. Resident visitor history

Phase C:
11. Guard dashboard
12. QR scanner
13. QR validation
14. Check-in
15. Check-out
16. Guard activity log

Phase D:
17. Facility management
18. Facility availability
19. Resident booking
20. Admin booking management

Phase E:
21. Notifications
22. Settings
23. Reports and exports
24. Automated tests
25. Documentation
26. Deployment preparation

==================================================
16. EXPECTED OUTPUT
==================================================

Do not only explain what should be done.

Create the actual implementation progressively.

For every implementation stage:

1. Show the proposed file structure.
2. Create migrations.
3. Create models and relationships.
4. Create enums or constants.
5. Create Form Requests.
6. Create services.
7. Create policies.
8. Create controllers.
9. Create API Resources.
10. Create API routes.
11. Create Vue stores.
12. Create Vue routes.
13. Create Vue pages.
14. Create reusable components.
15. Create tests.
16. Run available linting and tests.
17. Fix errors before proceeding.

Do not generate placeholder pseudocode.

Use complete, runnable code.

Before changing existing files:
- Inspect the existing project.
- Follow its Laravel and Vue versions.
- Follow its current conventions.
- Avoid unnecessarily replacing existing working code.
- Do not modify unrelated modules.

At the end, provide:

- Setup instructions
- Environment-variable example
- Migration and seeding instructions
- Development login accounts
- Test commands
- Build commands
- Deployment checklist
- Known limitations
- Recommended Phase 2 improvements

==================================================
17. IMPORTANT FINAL RULES
==================================================

The following requirements are fixed:

- Website first
- Laravel backend
- Vue frontend
- Resident, Guard and Admin portals
- Admin approval for resident registration
- Visitor QR code
- Guard scans QR code only
- No number-plate scanning
- Vehicle number is only a visitor-registration field
- Remove arrival time
- Remove expected arrival time
- Remove number of visitors
- Visitor passes are single entry only
- Do not provide multiple-entry selection
- Show “Single entry only” on visitor details
- Visitor details page should visually follow the supplied reference layout
- Do not include advertisements
- Facility booking is included
- Payment is excluded from Phase 1
- Ensure all sensitive actions are authorized and logged

Start by inspecting the repository and reporting:

1. Current Laravel version
2. Current Vue version
3. Current authentication setup
4. Current database structure
5. Existing modules that can be reused
6. Missing dependencies
7. Proposed implementation plan
8. Files that will be created or changed

After that, begin Phase A implementation.

The final visitor registration form should therefore contain only:

Purpose of Visit
Visitor Name
Contact Number
Vehicle Number (optional)
Visit Date
Notes (optional)

And the generated visitor pass should show:

Property
Block and Unit
QR Code
Purpose of Visit
Visitor Name
Contact Number
Vehicle Number
Visit Date
Validity: Single entry only
Status