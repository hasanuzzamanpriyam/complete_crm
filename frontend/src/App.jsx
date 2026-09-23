import React from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from './store/useAuthStore'
import AppLayout from './components/layout/AppLayout'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import PlaceholderPage from './pages/PlaceholderPage'

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
          <Route path="calendar" element={<PlaceholderPage title="Calendar & Events" category="Main" />} />
          <Route path="mailbox" element={<PlaceholderPage title="Mailbox & Communications" category="Main" />} />
          <Route path="chat" element={<PlaceholderPage title="Team Live Chat" category="Main" />} />
          <Route path="announcements" element={<PlaceholderPage title="Company Announcements" category="Main" />} />

          {/* CRM & Sales */}
          <Route path="clients" element={<PlaceholderPage title="Clients Directory" category="CRM & Sales" />} />
          <Route path="leads" element={<PlaceholderPage title="Leads & Opportunity Pipeline" category="CRM & Sales" />} />
          <Route path="opportunities" element={<PlaceholderPage title="Business Opportunities" category="CRM & Sales" />} />
          <Route path="invoices" element={<PlaceholderPage title="Invoices Management" category="CRM & Sales" />} />
          <Route path="estimates" element={<PlaceholderPage title="Price Estimates" category="CRM & Sales" />} />
          <Route path="proposals" element={<PlaceholderPage title="Sales Proposals" category="CRM & Sales" />} />
          <Route path="payments" element={<PlaceholderPage title="Payments Received" category="CRM & Sales" />} />
          <Route path="credit-notes" element={<PlaceholderPage title="Credit Notes" category="CRM & Sales" />} />
          <Route path="tax-rates" element={<PlaceholderPage title="Tax Rates" category="CRM & Sales" />} />
          <Route path="contracts" element={<PlaceholderPage title="Contracts Management" category="CRM & Sales" />} />
          <Route path="woocommerce" element={<PlaceholderPage title="WooCommerce Stores" category="CRM & Sales" />} />

          {/* Projects & Work */}
          <Route path="projects" element={<PlaceholderPage title="Projects Management" category="Projects & Work" />} />
          <Route path="tasks" element={<PlaceholderPage title="Tasks & Kanban Board" category="Projects & Work" />} />
          <Route path="bugs" element={<PlaceholderPage title="Bug Tracking System" category="Projects & Work" />} />
          <Route path="timesync" element={<PlaceholderPage title="TimeSync Desktop Tracker" category="Projects & Work" />} />
          <Route path="spreadsheet" element={<PlaceholderPage title="Spreadsheet & Worksheets" category="Projects & Work" />} />

          {/* Finance & Accounting */}
          <Route path="accounting/*" element={<PlaceholderPage title="General Accounting" category="Finance & Accounting" />} />
          <Route path="transactions" element={<PlaceholderPage title="Transactions & Expenses" category="Finance & Accounting" />} />
          <Route path="bank-accounts" element={<PlaceholderPage title="Bank & Cash Accounts" category="Finance & Accounting" />} />

          {/* Human Resources */}
          <Route path="departments" element={<PlaceholderPage title="Departments & Designations" category="HRM" />} />
          <Route path="attendance" element={<PlaceholderPage title="Attendance & Biometrics" category="HRM" />} />
          <Route path="leaves" element={<PlaceholderPage title="Leave Management" category="HRM" />} />
          <Route path="payroll/*" element={<PlaceholderPage title="Payroll & Salary System" category="HRM" />} />
          <Route path="recruitment" element={<PlaceholderPage title="Recruitment & Job Circulars" category="HRM" />} />
          <Route path="performance" element={<PlaceholderPage title="Performance Appraisals" category="HRM" />} />
          <Route path="lifecycle/*" element={<PlaceholderPage title="Employee Lifecycle" category="HRM" />} />

          {/* Inventory & Stock */}
          <Route path="items" element={<PlaceholderPage title="Items Catalog" category="Inventory" />} />
          <Route path="stock" element={<PlaceholderPage title="Stock & Warehouses" category="Inventory" />} />
          <Route path="purchases" element={<PlaceholderPage title="Suppliers & Purchases" category="Inventory" />} />

          {/* Support & Collaboration */}
          <Route path="tickets" element={<PlaceholderPage title="Support Tickets" category="Support" />} />
          <Route path="complaints" element={<PlaceholderPage title="Customer Complaints" category="Support" />} />
          <Route path="kb" element={<PlaceholderPage title="Knowledgebase & Help Articles" category="Support" />} />
          <Route path="meetings" element={<PlaceholderPage title="Video Meetings" category="Support" />} />
          <Route path="filemanager" element={<PlaceholderPage title="File Manager" category="Support" />} />

          {/* System & Settings */}
          <Route path="users" element={<PlaceholderPage title="Users & Access Permissions" category="System" />} />
          <Route path="servers/*" element={<PlaceholderPage title="Server Management" category="System" />} />
          <Route path="modules" element={<PlaceholderPage title="System Modules & Add-ons" category="System" />} />
          <Route path="developer" element={<PlaceholderPage title="API Routes & Documentation" category="System" />} />
          <Route path="settings" element={<PlaceholderPage title="System Configuration" category="System" />} />

          {/* Catch-all */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
