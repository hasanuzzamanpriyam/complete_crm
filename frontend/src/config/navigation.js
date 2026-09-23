import {
  LayoutDashboard,
  Calendar,
  ShoppingBag,
  Mail,
  FileSpreadsheet,
  Folder,
  AlertTriangle,
  FileCheck2,
  Inbox,
  Video,
  FolderKanban,
  TrendingUp,
  DollarSign,
  CalendarCheck,
  CheckSquare,
  UserMinus,
  Bug,
  UserX,
  Briefcase,
  Rocket,
  Layers,
  Receipt,
  Ticket,
  BookOpen,
  Building2,
  Users,
  Building,
  Warehouse,
  Clock,
  Globe,
  Coins,
  Award,
  Plane,
  GraduationCap,
  Bell,
  Gift,
  BarChart3,
  ShieldCheck,
  Settings,
  Database,
  MessageSquare,
  Server,
  Plug,
  Code2,
  FileText,
  CreditCard,
  Percent,
  Plus,
  Compass,
  Tag,
  History,
  Key,
} from 'lucide-react'

// Complete, authentic navigation tree matching 100% of the old CRM sidebar
export const OLD_CRM_NAVIGATION = [
  // 1. Dashboard
  {
    id: 'dashboard',
    title: 'Dashboard',
    path: '/',
    icon: LayoutDashboard,
    badge: null,
  },

  // 2. Calendar
  {
    id: 'calendar',
    title: 'Calendar',
    path: '/calendar',
    icon: Calendar,
  },

  // 3. WooCommerce
  {
    id: 'woocommerce',
    title: 'WooCommerce Store',
    icon: ShoppingBag,
    path: '/woocommerce',
    children: [
      { title: 'Orders', path: '/woocommerce' },
      { title: 'Products', path: '/woocommerce' },
      { title: 'Customers', path: '/woocommerce' },
      { title: 'Stores', path: '/woocommerce' },
    ],
  },

  // 4. Mailbox
  {
    id: 'mailbox',
    title: 'Mailbox',
    path: '/mailbox',
    icon: Mail,
  },

  // 5. Spreadsheet
  {
    id: 'spreadsheet',
    title: 'Spreadsheet',
    path: '/spreadsheet',
    icon: FileSpreadsheet,
  },

  // 6. File Manager
  {
    id: 'filemanager',
    title: 'File Manager',
    path: '/filemanager',
    icon: Folder,
  },

  // 7. Client Management
  {
    id: 'client',
    title: 'Clients Directory',
    path: '/clients',
    icon: Users,
  },

  // 8. Leads & Pipeline
  {
    id: 'leads',
    title: 'Leads & Pipeline',
    path: '/leads',
    icon: Rocket,
  },

  // 9. Opportunities
  {
    id: 'opportunities',
    title: 'Opportunities',
    path: '/opportunities',
    icon: Briefcase,
  },

  // 10. Sales & Billing
  {
    id: 'sales',
    title: 'Sales & Invoicing',
    icon: Receipt,
    path: '/invoices',
    children: [
      { title: 'Invoices', path: '/invoices' },
      { title: 'POS Sales', path: '/invoices' },
      { title: 'Estimates', path: '/estimates' },
      { title: 'Credit Notes', path: '/credit-notes' },
      { title: 'Recurring Invoices', path: '/invoices' },
      { title: 'Payments Received', path: '/payments' },
      { title: 'Proposals', path: '/proposals' },
      { title: 'Tax Rates', path: '/tax-rates' },
      {
        title: 'Quotations',
        path: '/estimates',
        children: [
          { title: 'Quotations List', path: '/estimates' },
          { title: 'Quotations Form', path: '/estimates' },
        ],
      },
      { title: 'Client Award Points', path: '/clients' },
      { title: 'Best Selling Products', path: '/items' },
    ],
  },

  // 11. Projects
  {
    id: 'projects',
    title: 'Projects',
    path: '/projects',
    icon: FolderKanban,
  },

  // 12. Tasks
  {
    id: 'tasks',
    title: 'Tasks & Kanban',
    path: '/tasks',
    icon: CheckSquare,
  },

  // 13. Bugs
  {
    id: 'bugs',
    title: 'Bug & Issue Tracker',
    path: '/bugs',
    icon: Bug,
  },

  // 14. TimeSync Desktop Tracker
  {
    id: 'timesync',
    title: 'TimeSync Tracking',
    icon: Clock,
    path: '/timesync',
    badge: 'Live',
    children: [
      { title: 'TimeSync Reports', path: '/timesync' },
      { title: 'Screenshots Feed', path: '/timesync' },
      { title: 'TimeSync Settings', path: '/timesync' },
      { title: 'Usage Analytics', path: '/timesync' },
      { title: 'Tracking Teams', path: '/timesync' },
    ],
  },

  // 15. Accounting
  {
    id: 'accounting',
    title: 'General Accounting',
    icon: DollarSign,
    path: '/accounting',
    children: [
      { title: 'Journal Entry', path: '/accounting' },
      { title: 'Chart of Accounts', path: '/accounting' },
      { title: 'Payment Voucher', path: '/accounting' },
      { title: 'Receipt Voucher', path: '/accounting' },
      { title: 'Financial Reports', path: '/accounting' },
      { title: 'Accounting Settings', path: '/accounting' },
    ],
  },

  // 16. Transactions
  {
    id: 'transactions_menu',
    title: 'Transactions & Accounts',
    icon: Building2,
    path: '/transactions',
    children: [
      { title: 'Expense', path: '/transactions' },
      { title: 'Deposit', path: '/transactions' },
      { title: 'Fund Transfer', path: '/bank-accounts' },
      { title: 'Transactions Report', path: '/reports' },
      { title: 'Transfer Report', path: '/reports' },
      { title: 'Balance Sheet', path: '/accounting' },
      { title: 'Bank & Cash Accounts', path: '/bank-accounts' },
    ],
  },

  // 17. Stock & Inventory
  {
    id: 'stock',
    title: 'Stock & Inventory',
    icon: Layers,
    path: '/stock',
    children: [
      { title: 'Items Catalog', path: '/items' },
      { title: 'Suppliers Directory', path: '/purchases' },
      { title: 'Purchase Orders', path: '/purchases' },
      { title: 'Return Stock', path: '/stock' },
      { title: 'Warehouse Management', path: '/stock' },
      { title: 'Purchase Payments', path: '/purchases' },
      { title: 'Transfer Items', path: '/stock' },
    ],
  },

  // 18. Office Stock
  {
    id: 'office_stock',
    title: 'Office Stock',
    icon: Warehouse,
    path: '/stock',
    children: [
      { title: 'Stock Category', path: '/stock' },
      { title: 'Stock List', path: '/stock' },
      { title: 'Stock History', path: '/stock' },
      { title: 'Assign Stock', path: '/stock' },
      { title: 'Stock Report', path: '/reports' },
    ],
  },

  // 19. Departments
  {
    id: 'departments',
    title: 'Departments & Roles',
    path: '/departments',
    icon: Building,
  },

  // 20. Attendance
  {
    id: 'attendance',
    title: 'Attendance & Timecard',
    icon: Clock,
    path: '/attendance',
    children: [
      { title: 'Time History', path: '/attendance' },
      { title: 'Time Change Request', path: '/attendance' },
      { title: 'Attendance Report', path: '/attendance' },
      { title: 'Mark Attendance', path: '/attendance' },
      { title: 'Workplace Locations', path: '/attendance' },
      { title: 'Biometric Mapping', path: '/attendance' },
      { title: 'Biometric Device Logs', path: '/attendance' },
      { title: 'Daily Attendance', path: '/attendance' },
    ],
  },

  // 21. Job Circular & Recruitment
  {
    id: 'job_circular',
    title: 'Job Circular / Hiring',
    icon: Globe,
    path: '/recruitment',
    children: [
      { title: 'Jobs Posted', path: '/recruitment' },
      { title: 'Job Applications', path: '/recruitment' },
      { title: 'Skills Management', path: '/recruitment' },
      { title: 'Interview Schedule', path: '/recruitment' },
      { title: 'Offer Letters', path: '/recruitment' },
    ],
  },

  // 22. Payroll
  {
    id: 'payroll',
    title: 'Payroll & Salaries',
    icon: Coins,
    path: '/payroll',
    children: [
      { title: 'Salary Template', path: '/payroll' },
      { title: 'Hourly Rate', path: '/payroll' },
      { title: 'Manage Salary Details', path: '/payroll' },
      { title: 'Employee Salary List', path: '/payroll' },
      { title: 'Make Payment', path: '/payroll' },
      { title: 'Generate Payslip', path: '/payroll' },
      { title: 'Payroll Summary', path: '/payroll' },
      { title: 'Advance Salary', path: '/payroll' },
      { title: 'Provident Fund', path: '/payroll' },
      { title: 'Overtime Logs', path: '/payroll' },
      { title: 'Employee Awards', path: '/payroll' },
    ],
  },

  // 23. Performance
  {
    id: 'performance',
    title: 'Performance & Appraisals',
    icon: Award,
    path: '/performance',
    children: [
      { title: 'Performance Indicators', path: '/performance' },
      { title: 'Give Appraisal', path: '/performance' },
      { title: 'Performance Report', path: '/performance' },
    ],
  },

  // 24. Leave Management
  {
    id: 'leave_management',
    title: 'Leave Management',
    path: '/leaves',
    icon: Plane,
  },

  // 25. Training
  {
    id: 'training',
    title: 'Staff Training',
    path: '/training',
    icon: GraduationCap,
  },

  // 26. Employee Lifecycle
  {
    id: 'lifecycle',
    title: 'Employee Lifecycle',
    icon: AlertTriangle,
    path: '/lifecycle',
    children: [
      { title: 'Disciplinary Warnings', path: '/warning' },
      { title: 'Staff Promotions', path: '/promotion' },
      { title: 'Resignations', path: '/resignation' },
      { title: 'Terminations', path: '/termination' },
    ],
  },

  // 27. Contracts
  {
    id: 'contracts',
    title: 'Contracts & Agreements',
    path: '/contracts',
    icon: FileCheck2,
  },

  // 28. Consultations
  {
    id: 'consultation',
    title: 'Consultations',
    path: '/consultation',
    icon: CalendarCheck,
  },

  // 29. Support Tickets
  {
    id: 'tickets',
    title: 'Support Tickets',
    path: '/tickets',
    icon: Ticket,
  },

  // 30. Complaints
  {
    id: 'complaints',
    title: 'Customer Complaints',
    path: '/complaints',
    icon: Inbox,
  },

  // 31. Knowledgebase
  {
    id: 'knowledgebase',
    title: 'Knowledgebase',
    icon: BookOpen,
    path: '/kb',
    children: [
      { title: 'Knowledgebase Home', path: '/kb' },
      { title: 'Articles Directory', path: '/kb' },
      { title: 'Help Categories', path: '/kb' },
    ],
  },

  // 32. Video Meetings
  {
    id: 'meetings',
    title: 'Video Meetings',
    icon: Video,
    path: '/meetings',
    children: [
      { title: 'All Meetings', path: '/meetings' },
      { title: 'Zoom Meetings', path: '/zoom' },
      { title: 'Jitsi Conferences', path: '/jitsi' },
    ],
  },

  // 33. Announcements
  {
    id: 'announcements',
    title: 'Announcements',
    path: '/announcements',
    icon: Bell,
  },

  // 34. Team Chat
  {
    id: 'chat',
    title: 'Private & Team Chat',
    path: '/chat',
    icon: MessageSquare,
    badge: 'Live',
  },

  // 35. Utilities
  {
    id: 'utilities',
    title: 'Utilities & Goals',
    icon: Gift,
    path: '/utilities',
    children: [
      { title: 'Holiday Calendar', path: '/utilities' },
      { title: 'Goal Tracking', path: '/utilities' },
    ],
  },

  // 36. Reports
  {
    id: 'report',
    title: 'Reports & Analytics',
    icon: BarChart3,
    path: '/reports',
    children: [
      { title: 'Sales Report', path: '/reports' },
      {
        title: 'Transactions Reports',
        path: '/reports',
        children: [
          { title: 'Account Statement', path: '/reports' },
          { title: 'Expense Report', path: '/reports' },
          { title: 'Income Report', path: '/reports' },
          { title: 'Income vs Expense', path: '/reports' },
          { title: 'Date-wise Report', path: '/reports' },
          { title: 'Report by Month', path: '/reports' },
          { title: 'All Income', path: '/reports' },
          { title: 'All Expense', path: '/reports' },
          { title: 'All Transactions', path: '/reports' },
        ],
      },
      {
        title: 'Project Reports',
        path: '/reports',
        children: [
          { title: 'Tasks Assignment', path: '/reports' },
          { title: 'Bugs Assignment', path: '/reports' },
          { title: 'Project Velocity', path: '/reports' },
        ],
      },
      { title: 'Tasks Report', path: '/reports' },
      { title: 'Bugs Report', path: '/reports' },
      { title: 'Tickets Report', path: '/reports' },
      { title: 'Client Report', path: '/reports' },
    ],
  },

  // 37. Letter Templates
  {
    id: 'letter',
    title: 'Letters & Certificates',
    icon: FileText,
    path: '/letter',
    children: [
      { title: 'Letter Templates', path: '/letter' },
      { title: 'Generate Certificate', path: '/letter' },
    ],
  },

  // 38. Super Admin
  {
    id: 'super_admin',
    title: 'Super Admin',
    icon: ShieldCheck,
    path: '/super_admin',
    children: [
      { title: 'Super Admin Dashboard', path: '/super_admin' },
      { title: 'User Permissions Matrix', path: '/super_admin' },
      { title: 'Security Audit Logs', path: '/super_admin' },
      { title: 'Super Admin Settings', path: '/super_admin' },
    ],
  },

  // 39. Server Management
  {
    id: 'server_management',
    title: 'Server Management',
    icon: Server,
    path: '/servers',
    children: [
      { title: 'Server Dashboard', path: '/servers' },
      { title: 'Hosting Nodes', path: '/servers' },
      { title: 'Domains & SSL', path: '/servers' },
      { title: 'Billing & Orders', path: '/servers' },
      { title: 'Cloud Providers', path: '/servers' },
    ],
  },

  // 40. Modules & Add-ons
  {
    id: 'modules',
    title: 'Modules & Add-ons',
    icon: Plug,
    path: '/modules',
    children: [
      { title: 'My Installed Modules', path: '/modules' },
      { title: 'Available Marketplace', path: '/modules' },
    ],
  },

  // 41. User Management
  {
    id: 'user',
    title: 'User Management',
    path: '/users',
    icon: Users,
  },

  // 42. Settings
  {
    id: 'settings',
    title: 'System Settings',
    path: '/settings',
    icon: Settings,
  },

  // 43. Database Backup
  {
    id: 'database_backup',
    title: 'Database Backup',
    path: '/database_backup',
    icon: Database,
  },

  // 44. API Routes & Docs
  {
    id: 'developer',
    title: 'API Routes & Docs',
    path: '/developer',
    icon: Code2,
  },
]

// Also export grouped enterprise sections for organized viewing
export const NAVIGATION_SECTIONS = [
  {
    title: 'MAIN & PRODUCTIVITY',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['dashboard', 'calendar', 'mailbox', 'chat', 'announcements', 'spreadsheet', 'filemanager'].includes(i.id)
    ),
  },
  {
    title: 'CRM & CLIENT RELATIONS',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['client', 'leads', 'opportunities', 'contracts', 'consultation', 'woocommerce'].includes(i.id)
    ),
  },
  {
    title: 'SALES & FINANCIALS',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['sales', 'accounting', 'transactions_menu'].includes(i.id)
    ),
  },
  {
    title: 'PROJECTS & TRACKING',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['projects', 'tasks', 'bugs', 'timesync'].includes(i.id)
    ),
  },
  {
    title: 'HUMAN RESOURCES (HRM)',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['departments', 'attendance', 'payroll', 'job_circular', 'performance', 'leave_management', 'training', 'lifecycle', 'letter'].includes(i.id)
    ),
  },
  {
    title: 'INVENTORY & STOCK',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['stock', 'office_stock'].includes(i.id)
    ),
  },
  {
    title: 'SUPPORT & COLLABORATION',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['tickets', 'complaints', 'knowledgebase', 'meetings', 'utilities', 'report'].includes(i.id)
    ),
  },
  {
    title: 'SYSTEM & INFRASTRUCTURE',
    items: OLD_CRM_NAVIGATION.filter((i) =>
      ['super_admin', 'server_management', 'modules', 'user', 'settings', 'database_backup', 'developer'].includes(i.id)
    ),
  },
]
