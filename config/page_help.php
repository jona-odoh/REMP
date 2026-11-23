<?php

return [
    'default' => [
        'title' => 'Page Guide',
        'summary' => 'This screen is part of the Kogi State Ministry of Culture and Tourism internal back-office system.',
        'steps' => [
            'Use the left navigation sidebar to switch between Culture & Tourism, Hospitality Licensing, Projects, Reporting, Operations, and Administration modules.',
            'Required form fields are marked with an asterisk (*). Validation messages appear at the top of the page if any field needs correction.',
            'Every create, update, delete, approval, and document download is automatically recorded in the Ministry Audit Trail.',
        ],
        'tips' => 'Contact the System Administrator if you need additional departmental permissions.',
    ],

    'login' => [
        'title' => 'Staff Sign In Guide',
        'summary' => 'Sign in to access the internal KSMCT Back-Office system. Only authorised Ministry officers with active accounts may log in.',
        'steps' => [
            'Username / Official Ministry Email: Enter your assigned username (e.g. admin) or official Ministry email address (e.g. director@ksmct.kogistate.gov.ng).',
            'Password: Enter your personal or administrator-issued temporary password.',
            'Keep me signed in: Optional — leave unchecked on shared office computers.',
        ],
        'tips' => 'Login attempts are rate-limited (5 attempts per minute). If your account is locked or deactivated, contact the Super Administrator.',
    ],

    'password.force-change' => [
        'title' => 'Mandatory Password Change Guide',
        'summary' => 'Your administrator has reset your password or created a new account that requires you to set a personal password before proceeding.',
        'steps' => [
            'Temporary / Current Password: Enter the temporary password provided by your administrator.',
            'New Password: Choose a strong password (minimum 8 characters, including uppercase, lowercase, and numbers) that differs from your temporary password.',
            'Confirm New Password: Re-type the exact same new password and click Update Password & Continue.',
        ],
        'tips' => 'Never share your Ministry password with colleagues. Every action taken under your login is recorded in the Audit Trail.',
    ],

    'dashboard' => [
        'title' => 'Ministry Back-Office Overview',
        'summary' => 'Your central landing page showing live summary counts across Staff, Cultural Assets, Festivals, Kogi LGAs, and today\'s Audit Trail activity.',
        'steps' => [
            'Review the top summary cards for a quick operational snapshot.',
            'Directors and Super Admins can inspect the latest audit events directly in the Recent Audit Activity table.',
            'Use the left sidebar to open any module your role is authorised to access.',
        ],
        'tips' => 'Dates are formatted in DD/MM/YYYY (WAT) and currency figures in Nigerian Naira (₦).',
    ],

    // Phase 1: Administration & Configuration
    'admin.users.index' => [
        'title' => 'Staff & Role Management Guide',
        'summary' => 'Manage internal Ministry staff accounts, departmental assignments, and user lifecycle controls.',
        'steps' => [
            'Filter Bar: Search by officer name, email, or staff ID, or filter by Role, Department, and Active/Deactivated status.',
            'Add Staff Account: Click "+ Add Staff Account" (Super Admin only) to onboard a new officer.',
            'Lifecycle Actions: Use Edit to change role/department, Reset Pwd to issue a one-time temporary password, Force Logout to immediately end all active sessions, or Deactivate to revoke access.',
        ],
        'tips' => 'Super Admins cannot deactivate or force-logout their own account to prevent accidental lockout.',
    ],

    'admin.users.create' => [
        'title' => 'How to Create a Staff Account',
        'summary' => 'Register a new Ministry officer and assign their RBAC role and department.',
        'steps' => [
            'Full Name & Official Email (*): Enter the officer\'s full name and unique Ministry email (or username).',
            'Staff ID & Phone: Optional but recommended (e.g. KSMCT/CUL/012 and +234 phone number).',
            'Department & Role (*): Select the officer\'s department and one of the 8 Ministry roles (determines their permissions).',
            'Initial Password (*): Must be at least 8 characters with uppercase, lowercase, and numbers. Keep "Require staff member to change password on first login" checked for security.',
        ],
        'tips' => 'Account creation and role assignment are automatically logged in the Audit Trail.',
    ],

    'admin.users.edit' => [
        'title' => 'How to Edit a Staff Account',
        'summary' => 'Update an officer\'s contact details, department, or assigned RBAC role.',
        'steps' => [
            'Modify Full Name, Email, Staff ID, Phone, Department, or Role as needed.',
            'Click "Save Changes" to apply. Role changes take effect immediately and are recorded in the Audit Trail.',
        ],
        'tips' => 'To reset this officer\'s password or end their sessions, return to the Staff & Roles list and use Reset Pwd or Force Logout.',
    ],

    'admin.audit-logs.index' => [
        'title' => 'Immutable Audit Trail Guide',
        'summary' => 'Read-only compliance log capturing every Create, Update, Delete, Approve, Reject, Login, Logout, and Document access event.',
        'steps' => [
            'Search & Filter: Narrow results by officer, action type, IP/keyword, or Date From / Date To range.',
            'Inspect: Click "Inspect →" on any row to view full Before/After JSON snapshots, IP address, and browser metadata.',
        ],
        'tips' => 'Audit logs are strictly immutable — no user (including Super Admin) can edit or delete audit records.',
    ],

    'admin.audit-logs.show' => [
        'title' => 'Audit Event Detail Guide',
        'summary' => 'Detailed forensic snapshot of a single system event.',
        'steps' => [
            'Compare the Before Value (JSON) and After Value (JSON) panels to see exact field-level changes.',
            'Sensitive fields such as passwords are automatically redacted ([REDACTED]).',
        ],
        'tips' => 'Use the Back to Audit Trail link to return to your filtered search.',
    ],

    'config.lgas.index' => [
        'title' => 'Kogi State LGAs Configuration Guide',
        'summary' => 'Master reference table for all 21 Kogi State Local Government Areas, their Senatorial Districts, Headquarters, and GPS centroids.',
        'steps' => [
            'Click "+ Add LGA" (Super Admin only) to open the form.',
            'Enter LGA Name (*), unique Code (e.g. KG-LOK) (*), Senatorial District (*), Headquarters, and optional Centroid Latitude/Longitude.',
            'Centroids are used as approximate fallback coordinates when officers save draft assets or hotels without exact GPS readings.',
        ],
        'tips' => 'All LGA dropdowns across the entire system load dynamically from this table.',
    ],

    'config.asset-categories.index' => [
        'title' => 'Asset Categories Configuration Guide',
        'summary' => 'Manage the cultural and tourism asset categories used in the Resource Registry and Map filters.',
        'steps' => [
            'Enter a unique Category Name (*), an optional Description, and a Sort Order number (lower numbers appear first in dropdowns).',
            'Click "Add" to make the category immediately available in Cultural Asset forms.',
        ],
        'tips' => 'All dropdown values come from this table — no categories are hardcoded.',
    ],

    'config.license-types.index' => [
        'title' => 'License Types Configuration Guide',
        'summary' => 'Define hospitality license categories and their validity period in months.',
        'steps' => [
            'Enter License Name (*), unique Code (e.g. LIC-HTL) (*), Validity in months (default 12) (*), and optional Description.',
            'Validity months automatically calculate license expiry dates when an application is approved.',
        ],
        'tips' => 'A license type cannot be deleted while fee schedules are attached to it.',
    ],

    'config.fee-schedules.index' => [
        'title' => 'License Fee Schedules Guide (₦ NGN)',
        'summary' => 'Configure official Ministry hospitality licensing fees by license type and hotel star/classification rating.',
        'steps' => [
            'Select the License Type (*), enter Classification / Rating (e.g. 3-Star Hotel) (*), Fee Amount in Naira (₦) (*), and Effective From date (*).',
            'Click "Add Fee" to save. Classification ratings listed here populate the Hotel Registration form.',
        ],
        'tips' => 'All amounts are stored in Nigerian Naira (NGN).',
    ],

    'config.checklist-templates.index' => [
        'title' => 'Inspection Checklist Templates Guide',
        'summary' => 'Manage the standardised criteria field officers must evaluate during hotel inspections.',
        'steps' => [
            'Select a specific License Type or leave as "All License Types" for universal requirements (e.g. Fire Safety).',
            'Enter Section name (*), Inspection Criterion text (*), and check "Mandatory" if failing this item fails the overall inspection.',
        ],
        'tips' => 'Active checklist items appear automatically on every new inspection form.',
    ],

    // Phase 2: Culture & Tourism
    'culture.assets.index' => [
        'title' => 'Cultural & Tourism Asset Registry Guide',
        'summary' => 'Browse, filter, export, bulk-import, or map all documented cultural monuments, natural landmarks, palaces, and tourism sites in Kogi State.',
        'steps' => [
            'Use the filter bar to search by name or narrow by Category, LGA, and Condition Assessment.',
            'Click Map View to plot filtered assets on the Kogi State Leaflet map, or Export Excel/CSV to download the current filtered list.',
            'Click "+ Register Asset" to add a single site, or "Bulk Import" to upload a spreadsheet of multiple sites.',
        ],
        'tips' => 'Click any asset name to inspect its full revision history, attached media, and internal officer notes.',
    ],

    'culture.assets.create' => [
        'title' => 'How to Register a Cultural or Tourism Asset',
        'summary' => 'Add a new tangible heritage or tourism site to the Ministry registry with SRID 4326 GPS coordinates.',
        'steps' => [
            'Asset Name (*): Must be unique within the selected LGA (duplicate detection prevents double-entry).',
            'Category & LGA (*): Choose from configured Ministry categories and Kogi\'s 21 LGAs.',
            'GPS Latitude & Longitude: Click directly on the embedded Kogi State map (or drag the pin / click "Use Device GPS") to auto-fill exact WGS84 coordinates. Leave blank to default to the selected LGA headquarters centroid.',
            'Ownership Status & Condition Assessment (*): Select current ownership and physical state, plus optional Visitor Capacity and Historical Description.',
        ],
        'tips' => 'Offline Protection: Your form draft is automatically saved in your browser\'s localStorage as you type so flaky internet will not lose your work.',
    ],

    'culture.assets.edit' => [
        'title' => 'How to Edit a Cultural Asset (Versioned)',
        'summary' => 'Update an existing cultural asset without losing historical records.',
        'steps' => [
            'Update any field that has changed (for example, Condition Assessment or GPS coordinates after a field visit).',
            'Reason for Update (Version Note): Briefly explain what changed — this note is stored alongside the new numbered revision (v2, v3, etc.).',
            'Click "Save Revision" to append the new version while preserving all prior snapshots.',
        ],
        'tips' => 'Previous versions remain permanently inspectable in the Revision History panel on the asset detail page.',
    ],

    'culture.assets.show' => [
        'title' => 'Cultural Asset Record Guide',
        'summary' => 'Complete profile for a documented asset including GPS location, version history, local media attachments, and threaded internal notes.',
        'steps' => [
            'Revision History (right panel): Click "Inspect Snapshot" on any version (v1, v2...) to review historical values.',
            'Attached Media & Documents: Upload photos, videos, audio, or PDFs (stored locally on the Ministry server) by entering a Title, Category, Date, and selecting a file.',
            'Internal Officer Notes: Post remarks or reply to existing threads for inter-departmental coordination.',
        ],
        'tips' => 'Every document download is logged in the Ministry Audit Trail.',
    ],

    'culture.assets.import.form' => [
        'title' => 'Bulk Asset Import Guide',
        'summary' => 'Import multiple cultural and tourism assets at once from an Excel (.xlsx/.xls) or CSV file.',
        'steps' => [
            'Step 1: Click "Download Template (.xlsx)" to get the exact required column headings and an example row.',
            'Step 2: Fill your rows using valid Kogi LGA names, active Asset Category names, and the allowed ownership_status and condition_assessment codes shown on screen.',
            'Step 3: Upload the file (max 10 MB) and click "Run Bulk Import". Valid rows are saved immediately; duplicate or invalid rows are skipped and listed with row-by-row error details.',
        ],
        'tips' => 'Every imported asset automatically receives an initial v1 revision snapshot.',
    ],

    'culture.assets.map' => [
        'title' => 'Interactive Kogi State Asset Map Guide',
        'summary' => 'Visualise registered cultural and tourism assets geographically across Kogi State.',
        'steps' => [
            'Use the Category, LGA, and Condition dropdowns and click "Apply Filters" to narrow the pins shown on the map.',
            'Click any map pin to open a popup showing the asset\'s category, LGA, and condition.',
            'Click "Open Asset Record →" inside the popup to view the full record, photos, and revision history.',
        ],
        'tips' => 'Markers labelled "Approximate (LGA centroid)" have not yet had exact field GPS coordinates entered.',
    ],

    'culture.heritage.index' => [
        'title' => 'Intangible Cultural Heritage Guide',
        'summary' => 'Directory of Kogi State festivals, oral traditions, masquerades, and cultural practices (such as Ovia-Osese, New Yam Festival, and Ekuechi).',
        'steps' => [
            'Filter by keyword, LGA, or Safeguarding Status (Viable, At Risk, Urgent Safeguarding, Documented).',
            'Click "+ Document Festival / Tradition" to record a new intangible heritage entry.',
        ],
        'tips' => 'Open any festival record to attach audio recordings, video clips, photographs, or historical documents.',
    ],

    'culture.heritage.create' => [
        'title' => 'How to Document Intangible Heritage',
        'summary' => 'Record a traditional festival, ritual, or cultural practice for Ministry safeguarding.',
        'steps' => [
            'Festival / Tradition Name (*): Official name of the festival or practice.',
            'Primary LGA & Festival Season (*): Select the main LGA (or Statewide) and when it is celebrated (e.g. April Annually).',
            'Safeguarding Status (*): Assess whether the tradition is viable, at risk, or requires urgent safeguarding.',
            'Participating Communities & Historical Description (*): List host communities and document historical background.',
        ],
        'tips' => 'Draft inputs are automatically saved in localStorage as you type.',
    ],

    'culture.heritage.edit' => [
        'title' => 'Edit Intangible Heritage Record',
        'summary' => 'Update festival timing, participating communities, safeguarding status, or historical notes.',
        'steps' => [
            'Adjust any required fields and click "Save Changes".',
            'Changes are recorded in the Audit Trail with before/after JSON snapshots.',
        ],
        'tips' => 'Use the detail page to upload new multimedia documentation.',
    ],

    'culture.heritage.show' => [
        'title' => 'Intangible Heritage Detail Guide',
        'summary' => 'Full safeguarding profile and media archive for a Kogi State festival or tradition.',
        'steps' => [
            'Review safeguarding status, participating communities, and historical narrative.',
            'Use the Attached Media & Documents panel below to upload oral history audio, festival videos, or photographs.',
        ],
        'tips' => 'Files are stored exclusively on the Ministry\'s local server.',
    ],

    // Phase 3: Hospitality Licensing
    'licensing.compliance.index' => [
        'title' => 'Hospitality Compliance Dashboard Guide',
        'summary' => 'Monitor licensing compliance across Kogi State hotels, guest houses, motels, lodges, and resorts.',
        'steps' => [
            'Filter by LGA or Establishment Type to focus on a specific area or category.',
            'Review the 5 KPI cards (Total, Licensed, Pending, Expired, Unlicensed).',
            'Check the red Overdue Inspections table for scheduled inspections past their due date and click "Conduct Inspection →" to complete them.',
        ],
        'tips' => 'Expiring licenses automatically trigger 90/60/30-day reminders in your Notification Center.',
    ],

    'licensing.hotels.index' => [
        'title' => 'Hotel & Hospitality Registry Guide',
        'summary' => 'Central register of hospitality establishments operating across Kogi State.',
        'steps' => [
            'Search by establishment name or proprietor, or filter by LGA, License Status, and Establishment Type.',
            'Click "+ Register Establishment" to add a new hotel, guest house, motel, lodge, or resort.',
        ],
        'tips' => 'Click any hotel name to view its licensing history, fee receipts, attached documents, and officer notes.',
    ],

    'licensing.hotels.create' => [
        'title' => 'How to Register a Hospitality Establishment',
        'summary' => 'Create a new establishment record before filing a license application.',
        'steps' => [
            'Establishment Name & Street Address (*): Name must be unique within the selected LGA.',
            'LGA, Establishment Type & Classification Rating (*): Select from configured LGAs and fee-schedule ratings.',
            'Rooms & Beds (*): Enter total room count and bed capacity (must be at least 1).',
            'Proprietor Name & Contact (*): Enter owner/manager name and phone or email.',
            'GPS Latitude & Longitude: Click the embedded Kogi State map (or drag the pin / click "Use Device GPS") to auto-fill coordinates, or leave blank to default to the selected LGA centroid.',
        ],
        'tips' => 'Offline Protection: Form progress is auto-saved in localStorage while you type.',
    ],

    'licensing.hotels.edit' => [
        'title' => 'Edit Hospitality Establishment',
        'summary' => 'Update hotel capacity, proprietor contact, classification, or GPS coordinates.',
        'steps' => [
            'Modify the relevant fields and click "Save Changes".',
            'License status is managed automatically through the License Application workflow (not edited manually).',
        ],
        'tips' => 'All updates are logged in the Audit Trail.',
    ],

    'licensing.hotels.show' => [
        'title' => 'Hotel Profile & Licensing History Guide',
        'summary' => 'Overview of a single establishment\'s capacity, applications, fee receipts, documents, and internal notes.',
        'steps' => [
            'Click "+ New License Application" to start a new or renewal licensing workflow for this hotel.',
            'Click any application card to advance its workflow stage, or scroll down to attach documents and post internal notes.',
        ],
        'tips' => 'A hotel\'s status automatically updates to Licensed once an application is approved.',
    ],

    'licensing.applications.index' => [
        'title' => 'License Application Review Queue Guide',
        'summary' => 'Track all new and renewal hotel license applications through the Ministry approval pipeline.',
        'steps' => [
            'Filter the queue by Application #, Pipeline Stage (Draft → Submitted → Under Review → Inspection Scheduled → Inspection Completed → Approved/Rejected), or Assigned Officer.',
            'Click "Manage →" on any application to review documents, schedule inspections, or record an approval/rejection decision.',
        ],
        'tips' => 'Final approval requires Head of Hotels, Director, or Super Admin authority AND a completed inspection.',
    ],

    'licensing.applications.create' => [
        'title' => 'How to Create a License Application',
        'summary' => 'Open a new or renewal hospitality license application for a registered hotel.',
        'steps' => [
            'Select the registered Establishment (*), License Type (*), and Application Type (New or Annual Renewal) (*).',
            'Optionally assign a Licensing Officer to review the file.',
            'Check "Submit immediately" to move straight from Draft to Submitted, or leave unchecked to save as Draft while gathering documents.',
        ],
        'tips' => 'Upload CAC certificate, tax clearance, and fire safety certificates on the application detail page.',
    ],

    'licensing.applications.show' => [
        'title' => 'License Workflow Actions Guide',
        'summary' => 'Advance a license application step-by-step through the official Ministry state machine.',
        'steps' => [
            '1. Draft → Click "Submit Application".',
            '2. Submitted → Licensing Officer clicks "Start Officer Review".',
            '3. Under Review → Schedule a Field Inspection by choosing an officer and date.',
            '4. Inspection Scheduled → Officer completes the checklist on the Inspection screen (auto-advances to Inspection Completed).',
            '5. Inspection Completed → Dept Head Hotels or Director enters a mandatory Decision Reason and clicks "Approve & Issue License" or "Reject Application".',
        ],
        'tips' => 'Approval is strictly blocked by the system until at least one inspection is marked Completed. Both approval and rejection require a written reason.',
    ],

    'licensing.inspections.index' => [
        'title' => 'Hotel Inspections Schedule Guide',
        'summary' => 'Schedule field inspections and monitor overdue or completed inspection reports.',
        'steps' => [
            'Quick Schedule Form: Select a Hotel (*), Assigned Officer (*), and Scheduled Date (*), then click "Schedule".',
            'Rows shaded red are overdue (scheduled before today and not yet completed). Click "Open →" to record checklist findings.',
        ],
        'tips' => 'Completing an inspection linked to a license application automatically moves that application to Inspection Completed.',
    ],

    'licensing.inspections.show' => [
        'title' => 'How to Complete an Inspection Checklist',
        'summary' => 'Evaluate each configured Ministry safety, hygiene, and facility criterion and attach site photos.',
        'steps' => [
            'For every checklist criterion, select "Pass" or "Fail" and optionally enter an officer remark.',
            'Items marked "Mandatory" must all pass for the overall inspection verdict to be Passed.',
            'Enter Overall Field Notes and click "Submit Completed Inspection".',
            'Use the Attached Media & Documents panel below to upload site inspection photographs.',
        ],
        'tips' => 'Checklist results are saved as structured JSON and cannot be altered once completed.',
    ],

    'licensing.fees.index' => [
        'title' => 'License Fees & Revenue Tracker Guide',
        'summary' => 'Record Naira (₦) licensing fee payments, view revenue totals by month and LGA, and export revenue reports to Excel.',
        'steps' => [
            'Record Payment Form: Select Hotel (*), enter Amount in Naira (*), unique Receipt Reference (*), Paid Date (*), and Period (e.g. 2026 Annual) (*), then click "Record Payment".',
            'Filter & Export: Filter by LGA or Month, then click "Export Revenue to Excel (.xlsx)" to download.',
        ],
        'tips' => 'Duplicate receipt references are automatically rejected to prevent double-counting revenue.',
    ],

    // Phase 4: Projects
    'projects.index' => [
        'title' => 'Projects & Programs Registry Guide',
        'summary' => 'Track Ministry capital and recurrent projects, milestone completion, and budget vs. actual variance alerts.',
        'steps' => [
            'Any project whose budget variance exceeds the configured threshold (default ±15%) is highlighted in the red Budget Variance Alert banner at the top.',
            'Filter by keyword, LGA, or Project Status, or click "+ Register Project" to add a new project.',
        ],
        'tips' => 'Click any project title to manage its milestones, budget expenditures, documents, and internal notes.',
    ],

    'projects.create' => [
        'title' => 'How to Register a Ministry Project',
        'summary' => 'Create a new Ministry project or programme record.',
        'steps' => [
            'Enter Project Title (*), Target LGA (or Statewide), Status (*), Budget Line Code (*), and Funding Source (*).',
            'Enter Implementing Partner (optional), Start Date (*), End Date (* — must be on or after Start Date), and Project Scope.',
            'Click "Register Project" — then add milestones and budget vs. actual figures on the project detail page.',
        ],
        'tips' => 'Draft entries are auto-saved in localStorage as you type.',
    ],

    'projects.edit' => [
        'title' => 'Edit Ministry Project',
        'summary' => 'Update project status, dates, implementing partner, or scope.',
        'steps' => [
            'Update the needed fields (e.g. changing status from Planned to Ongoing or Completed) and click "Save Changes".',
        ],
        'tips' => 'All changes are logged in the Audit Trail.',
    ],

    'projects.show' => [
        'title' => 'Project Milestones & Budget Tracking Guide',
        'summary' => 'Manage deliverables, financial utilization, documents, and officer notes for this project.',
        'steps' => [
            'Milestones Panel: Enter a Milestone Title (*), Due Date (*), and Responsible Officer, then click "+ Add". Use "Mark Done" / "Reopen" to update completion status.',
            'Budget vs. Actual Panel: Enter Fiscal Year (*), Approved Budget (₦) (*), and Actual Expenditure (₦) (*), then click "Record". Variance % and alerts calculate automatically.',
            'Documents & Notes: Attach contracts, MOUs, valuation certificates, and post threaded internal notes below.',
        ],
        'tips' => 'Overdue milestones are highlighted with a red border.',
    ],

    // Phase 5: Reporting
    'reports.kpi' => [
        'title' => 'Executive KPI Dashboard Guide',
        'summary' => 'Decision-support dashboard aligned with the Kogi State Development Plan for the Director and Commissioner.',
        'steps' => [
            'Top Cards: Monitor documented heritage assets, hotel licensing compliance rate (%), active projects & budget utilization (%), and monthly completed inspections.',
            'Charts: Hover over the 6-Month Revenue Trend line chart and the LGA Assets & Hotels bar chart for exact figures.',
        ],
        'tips' => 'All charts are rendered locally via Chart.js with zero external internet dependency.',
    ],

    'reports.standard' => [
        'title' => 'Standard Ministry Reports Guide',
        'summary' => 'View or export official Ministry PDF and Excel reports ready for executive briefings and filings.',
        'steps' => [
            'Choose from Monthly Licensing Summary, Heritage Asset Inventory by LGA, Project Status Report, or Inspection Completion Rates.',
            'Click "View Report" to inspect the full live report table directly in your browser.',
            'Click "Download PDF" for a formatted landscape A4 document or "Download Excel (.xlsx)" for spreadsheet analysis.',
        ],
        'tips' => 'All officers with reporting access can view reports online; PDF/Excel downloads require Executive or Department Head permissions.',
    ],

    'reports.standard.show' => [
        'title' => 'Standard Report Preview Guide',
        'summary' => 'Live tabular preview of the selected Ministry standard report.',
        'steps' => [
            'Review the generated rows and record count directly on screen.',
            'Use the Print, Download PDF, or Download Excel (.xlsx) buttons at the top right to save or print a copy.',
        ],
        'tips' => 'The data shown here is live and matches the PDF and Excel exports identically.',
    ],

    'reports.query-builder' => [
        'title' => 'Ad-Hoc Query Builder Guide',
        'summary' => 'Answer ad-hoc operational questions across Hotels, Cultural Assets, or Projects without writing SQL.',
        'steps' => [
            'Step 1: Choose a module tab at the top (Hotels & Hospitality, Cultural & Tourism Assets, or Projects & Programs).',
            'Step 2: Pick any combination of filters (for example: LGA = Lokoja, License Status = Unlicensed, Minimum Rooms = 20).',
            'Step 3: Click "Run Query" to display matching records with direct links to each detail page.',
        ],
        'tips' => 'Click "Reset" to clear all filters and start a new query.',
    ],

    // Phase 6: Operations
    'documents.index' => [
        'title' => 'Central Document Repository Guide',
        'summary' => 'Search, upload, inspect, and download official Ministry documents stored securely on-premise.',
        'steps' => [
            'Upload Form: Enter a Document Title (*), Category, Document Date, optional Description/Keywords, select a file (max 25 MB), and click "Upload".',
            'Full-Text Search: Type keywords into the search box (indexed across titles and descriptions) and filter by Category or File Type.',
            'Click "Inspect" to view full metadata or "Download" to retrieve the file.',
        ],
        'tips' => 'Every document view and download is automatically recorded in the Audit Trail.',
    ],

    'documents.show' => [
        'title' => 'Document Metadata & Access Guide',
        'summary' => 'Inspect document metadata, linked Ministry record, file size, and download securely.',
        'steps' => [
            'Review the uploader, file type, MIME type, and description.',
            'Click "Download File" to save a local copy from the Ministry server.',
        ],
        'tips' => 'Opening this page has logged a View event in the Audit Trail.',
    ],

    'tasks.index' => [
        'title' => 'Task Assignment & Follow-Up Guide',
        'summary' => 'Assign operational follow-up tasks to Ministry officers and track completion before due dates.',
        'steps' => [
            'Assign New Task: Enter Task Title (*), select an Officer (*), pick a Due Date (*), add optional instructions, and click "Assign". The officer receives an instant in-app notification.',
            'Filter: Switch between "All Tasks" and "Assigned to Me".',
            'Update Status: Select Pending, In Progress, Completed, or Cancelled on any task and click "Save".',
        ],
        'tips' => 'Daily automated reminders notify officers of tasks due within 2 days or overdue.',
    ],

    'notifications.index' => [
        'title' => 'In-App Notification Center Guide',
        'summary' => 'View automated hotel license renewal alerts (90/60/30 days) and task assignment/due-date reminders.',
        'steps' => [
            'Unread notifications are highlighted in green. Click "Open →" to jump directly to the related license or task.',
            'Click "Mark Read" on individual alerts or "Mark All as Read" at the top right to clear your unread counter.',
        ],
        'tips' => 'The red badge in the sidebar shows your live unread notification count.',
    ],
];
