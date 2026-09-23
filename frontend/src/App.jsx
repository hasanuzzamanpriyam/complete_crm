import React from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from './store/useAuthStore'
import AppLayout from './components/layout/AppLayout'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'

// Main Section Pages
import CalendarPage from './pages/CalendarPage'
import MailboxPage from './pages/MailboxPage'
import ChatPage from './pages/ChatPage'
import AnnouncementsPage from './pages/AnnouncementsPage'

// CRM & Sales Pages
import ClientsPage from './pages/ClientsPage'
import LeadsPage from './pages/LeadsPage'
import OpportunitiesPage from './pages/OpportunitiesPage'
import InvoicesPage from './pages/InvoicesPage'
import EstimatesPage from './pages/EstimatesPage'
import ProposalsPage from './pages/ProposalsPage'
import PaymentsPage from './pages/PaymentsPage'
import CreditNotesPage from './pages/CreditNotesPage'
import TaxRatesPage from './pages/TaxRatesPage'
import ContractsPage from './pages/ContractsPage'
import WooCommercePage from './pages/WooCommercePage'

// Projects & Work Pages
import ProjectsPage from './pages/ProjectsPage'
import TasksPage from './pages/TasksPage'
import BugsPage from './pages/BugsPage'
import TimeSyncPage from './pages/TimeSyncPage'
import SpreadsheetPage from './pages/SpreadsheetPage'

// Finance & Accounting Pages
import AccountingPage from './pages/AccountingPage'
import TransactionsPage from './pages/TransactionsPage'
import BankAccountsPage from './pages/BankAccountsPage'

// Human Resources Pages
import DepartmentsPage from './pages/DepartmentsPage'
import AttendancePage from './pages/AttendancePage'
import LeavesPage from './pages/LeavesPage'
import PayrollPage from './pages/PayrollPage'
import RecruitmentPage from './pages/RecruitmentPage'
import PerformancePage from './pages/PerformancePage'
import LifecyclePage from './pages/LifecyclePage'

// Inventory & Stock Pages
import ItemsPage from './pages/ItemsPage'
import StockPage from './pages/StockPage'
import PurchasesPage from './pages/PurchasesPage'

// Support & Collaboration Pages
import TicketsPage from './pages/TicketsPage'
import ComplaintsPage from './pages/ComplaintsPage'
import KnowledgebasePage from './pages/KnowledgebasePage'
import MeetingsPage from './pages/MeetingsPage'
import FileManagerPage from './pages/FileManagerPage'

// System & Settings Pages
import UsersPage from './pages/UsersPage'
import ServerManagementPage from './pages/ServerManagementPage'
import ModulesPage from './pages/ModulesPage'
import DeveloperPage from './pages/DeveloperPage'
import SettingsPage from './pages/SettingsPage'

// Protected Route Guard
function ProtectedRoute({ children }) {
  const { isAuthenticated } = useAuthStore()
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />
  }
  return children
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Public Login Route */}
        <Route path="/login" element={<Login />} />

        {/* Protected App Routes with Reorganized Sidebar Shell */}
        <Route
          path="/"
          element={
            <ProtectedRoute>
              <AppLayout />
            </ProtectedRoute>
          }
        >
          {/* Main */}
          <Route index element={<Dashboard />} />
          <Route path="calendar" element={<CalendarPage />} />
          <Route path="mailbox" element={<MailboxPage />} />
          <Route path="chat" element={<ChatPage />} />
          <Route path="announcements" element={<AnnouncementsPage />} />

          {/* CRM & Sales */}
          <Route path="clients" element={<ClientsPage />} />
          <Route path="leads" element={<LeadsPage />} />
          <Route path="opportunities" element={<OpportunitiesPage />} />
          <Route path="invoices" element={<InvoicesPage />} />
          <Route path="estimates" element={<EstimatesPage />} />
          <Route path="proposals" element={<ProposalsPage />} />
          <Route path="payments" element={<PaymentsPage />} />
          <Route path="credit-notes" element={<CreditNotesPage />} />
          <Route path="tax-rates" element={<TaxRatesPage />} />
          <Route path="contracts" element={<ContractsPage />} />
          <Route path="woocommerce" element={<WooCommercePage />} />

          {/* Projects & Work */}
          <Route path="projects" element={<ProjectsPage />} />
          <Route path="tasks" element={<TasksPage />} />
          <Route path="bugs" element={<BugsPage />} />
          <Route path="timesync" element={<TimeSyncPage />} />
          <Route path="spreadsheet" element={<SpreadsheetPage />} />

          {/* Finance & Accounting */}
          <Route path="accounting/*" element={<AccountingPage />} />
          <Route path="transactions" element={<TransactionsPage />} />
          <Route path="bank-accounts" element={<BankAccountsPage />} />

          {/* Human Resources */}
          <Route path="departments" element={<DepartmentsPage />} />
          <Route path="attendance" element={<AttendancePage />} />
          <Route path="leaves" element={<LeavesPage />} />
          <Route path="payroll/*" element={<PayrollPage />} />
          <Route path="recruitment" element={<RecruitmentPage />} />
          <Route path="performance" element={<PerformancePage />} />
          <Route path="lifecycle/*" element={<LifecyclePage />} />

          {/* Inventory & Stock */}
          <Route path="items" element={<ItemsPage />} />
          <Route path="stock" element={<StockPage />} />
          <Route path="purchases" element={<PurchasesPage />} />

          {/* Support & Collaboration */}
          <Route path="tickets" element={<TicketsPage />} />
          <Route path="complaints" element={<ComplaintsPage />} />
          <Route path="kb" element={<KnowledgebasePage />} />
          <Route path="meetings" element={<MeetingsPage />} />
          <Route path="filemanager" element={<FileManagerPage />} />

          {/* System & Settings */}
          <Route path="users" element={<UsersPage />} />
          <Route path="servers/*" element={<ServerManagementPage />} />
          <Route path="modules" element={<ModulesPage />} />
          <Route path="developer" element={<DeveloperPage />} />
          <Route path="settings" element={<SettingsPage />} />

          {/* Catch-all */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
