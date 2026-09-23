import {
  LayoutDashboard,
  Calendar,
  Mail,
  MessageSquare,
  Bell,
  Users,
  Briefcase,
  FolderKanban,
  CheckSquare,
  Bug,
  Clock,
  FileSpreadsheet,
  Receipt,
  FileText,
  DollarSign,
  Building2,
  Package,
  Warehouse,
  HelpCircle,
  Video,
  Folder,
  Settings,
  Server,
  Plug,
  Code2,
  ShieldCheck,
  UserCheck,
  Layers,
  ShoppingBag,
  TrendingUp,
  FileCheck2,
  AlertTriangle,
} from 'lucide-react'

export const NAVIGATION_SECTIONS = [
  {
    title: 'MAIN',
    items: [
      {
        title: 'Dashboard',
        path: '/',
        icon: LayoutDashboard,
      },
      {
        title: 'Calendar',
        path: '/calendar',
        icon: Calendar,
      },
      {
        title: 'Mailbox',
        path: '/mailbox',
        icon: Mail,
      },
      {
        title: 'Team Chat',
        path: '/chat',
        icon: MessageSquare,
        badge: 'Live',
      },
      {
        title: 'Announcements',
        path: '/announcements',
        icon: Bell,
      },
    ],
  },
  {
    title: 'CRM & SALES',
    items: [
      {
        title: 'Clients',
        path: '/clients',
        icon: Users,
      },
      {
        title: 'Leads & Pipeline',
        path: '/leads',
        icon: TrendingUp,
      },
      {
        title: 'Opportunities',
        path: '/opportunities',
        icon: Briefcase,
      },
      {
        title: 'Sales & Billing',
        icon: Receipt,
        children: [
          { title: 'Invoices', path: '/invoices' },
          { title: 'Estimates', path: '/estimates' },
          { title: 'Proposals', path: '/proposals' },
          { title: 'Payments Received', path: '/payments' },
          { title: 'Credit Notes', path: '/credit-notes' },
          { title: 'Tax Rates', path: '/tax-rates' },
        ],
      },
      {
        title: 'Contracts',
        path: '/contracts',
        icon: FileCheck2,
      },
      {
        title: 'WooCommerce Store',
        path: '/woocommerce',
        icon: ShoppingBag,
      },
    ],
  },
  {
    title: 'PROJECTS & WORK',
    items: [
      {
        title: 'Projects',
        path: '/projects',
        icon: FolderKanban,
      },
      {
        title: 'Tasks & Kanban',
        path: '/tasks',
        icon: CheckSquare,
      },
      {
        title: 'Bugs & Issues',
        path: '/bugs',
        icon: Bug,
      },
      {
        title: 'TimeSync & Tracking',
        path: '/timesync',
        icon: Clock,
        badge: 'Tracker',
      },
      {
        title: 'Spreadsheet',
        path: '/spreadsheet',
        icon: FileSpreadsheet,
      },
    ],
  },
  {
    title: 'FINANCE & ACCOUNTING',
    items: [
      {
        title: 'Accounting',
        icon: DollarSign,
        children: [
          { title: 'Chart of Accounts', path: '/accounting/accounts' },
          { title: 'Journal Entry', path: '/accounting/journal' },
          { title: 'Payment Vouchers', path: '/accounting/vouchers' },
          { title: 'Financial Reports', path: '/accounting/reports' },
        ],
      },
      {
        title: 'Transactions & Expenses',
        path: '/transactions',
        icon: Receipt,
      },
      {
        title: 'Bank & Cash Accounts',
        path: '/bank-accounts',
        icon: Building2,
      },
    ],
  },
  {
    title: 'HUMAN RESOURCES (HRM)',
    items: [
      {
        title: 'Departments & Staff',
        path: '/departments',
        icon: Users,
      },
      {
        title: 'Attendance & Clock-in',
        path: '/attendance',
        icon: UserCheck,
      },
      {
        title: 'Leave Management',
        path: '/leaves',
        icon: Calendar,
      },
      {
        title: 'Payroll',
        icon: DollarSign,
        children: [
          { title: 'Salary Templates', path: '/payroll/templates' },
          { title: 'Manage Salary Details', path: '/payroll/salary-details' },
          { title: 'Make Payment', path: '/payroll/make-payment' },
          { title: 'Generate Payslip', path: '/payroll/payslips' },
          { title: 'Payroll Summary', path: '/payroll/summary' },
        ],
      },
      {
        title: 'Recruitment / Circulars',
        path: '/recruitment',
        icon: Briefcase,
      },
      {
        title: 'Performance & Appraisals',
        path: '/performance',
        icon: ShieldCheck,
      },
      {
        title: 'Employee Lifecycle',
        icon: AlertTriangle,
        children: [
          { title: 'Warnings', path: '/lifecycle/warnings' },
          { title: 'Promotions', path: '/lifecycle/promotions' },
          { title: 'Resignations', path: '/lifecycle/resignations' },
          { title: 'Terminations', path: '/lifecycle/terminations' },
        ],
      },
    ],
  },
  {
    title: 'INVENTORY & STOCK',
    items: [
      {
        title: 'Items Catalog',
        path: '/items',
        icon: Package,
      },
      {
        title: 'Stock & Warehouses',
        path: '/stock',
        icon: Warehouse,
      },
      {
        title: 'Suppliers & Purchases',
        path: '/purchases',
        icon: ShoppingBag,
      },
    ],
  },
  {
    title: 'SUPPORT & COLLABORATION',
    items: [
      {
        title: 'Tickets & Complaints',
        path: '/tickets',
        icon: HelpCircle,
      },
      {
        title: 'Knowledgebase',
        path: '/kb',
        icon: FileText,
      },
      {
        title: 'Video Meetings (Zoom)',
        path: '/meetings',
        icon: Video,
      },
      {
        title: 'File Manager',
        path: '/filemanager',
        icon: Folder,
      },
    ],
  },
  {
    title: 'SYSTEM & SETTINGS',
    items: [
      {
        title: 'Users & Permissions',
        path: '/users',
        icon: ShieldCheck,
      },
      {
        title: 'Server Management',
        icon: Server,
        children: [
          { title: 'Server Dashboard', path: '/servers/dashboard' },
          { title: 'Hostings', path: '/servers/hosting' },
          { title: 'Domains', path: '/servers/domains' },
          { title: 'Billing & Orders', path: '/servers/billing' },
          { title: 'Providers', path: '/servers/providers' },
        ],
      },
      {
        title: 'Modules & Add-ons',
        path: '/modules',
        icon: Plug,
      },
      {
        title: 'API Routes & Docs',
        path: '/developer',
        icon: Code2,
      },
      {
        title: 'System Settings',
        path: '/settings',
        icon: Settings,
      },
    ],
  },
]
