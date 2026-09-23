import React, { useState } from 'react'
import { BarChart3, TrendingUp, Calendar, Download, DollarSign, FileText, ArrowRight } from 'lucide-react'

export default function ReportsPage() {
  const [activeReport, setActiveReport] = useState('sales')

  const reportCategories = [
    { id: 'sales', label: 'Sales & Invoicing Report', desc: 'Gross revenue, tax collected, and collection ratios' },
    { id: 'account_statement', label: 'Account Statements', desc: 'Debits, credits, and ledger trail across accounts' },
    { id: 'expense', label: 'Expense & Overhead Breakdown', desc: 'Operational expenditures grouped by department' },
    { id: 'project_tasks', label: 'Projects & Tasks Assignment', desc: 'Staff task velocity, sprint burndown, and ticket SLA' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <BarChart3 className="w-6 h-6 text-indigo-400" />
            <span>Executive Business Analytics & Reports</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Generate detailed audit statements, sales performance graphs, tax reports, and project velocity.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Download className="w-4 h-4" />
          <span>Export Complete PDF/Excel</span>
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {reportCategories.map(cat => (
          <div
            key={cat.id}
            onClick={() => setActiveReport(cat.id)}
            className={`p-5 rounded-2xl border cursor-pointer transition-all ${
              activeReport === cat.id
                ? 'bg-indigo-600/10 border-indigo-500 shadow-lg shadow-indigo-500/10'
                : 'bg-slate-900/70 border-slate-800 hover:border-slate-700'
            }`}
          >
            <h3 className={`text-sm font-bold ${activeReport === cat.id ? 'text-indigo-400' : 'text-white'}`}>
              {cat.label}
            </h3>
            <p className="text-xs text-slate-400 mt-1">{cat.desc}</p>
          </div>
        ))}
      </div>

      {/* Report Data Preview */}
      <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <TrendingUp className="w-4 h-4 text-emerald-400" />
            <span>Detailed Data Breakdown — {reportCategories.find(c => c.id === activeReport)?.label}</span>
          </h2>
          <span className="text-xs text-slate-400">Date Range: YTD 2026</span>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700/60">
            <span className="text-xs text-slate-400">Total Filtered Volume</span>
            <p className="text-2xl font-bold text-emerald-400 mt-1">$284,500.00</p>
          </div>
          <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700/60">
            <span className="text-xs text-slate-400">Average Transaction</span>
            <p className="text-2xl font-bold text-white mt-1">$7,486.00</p>
          </div>
          <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700/60">
            <span className="text-xs text-slate-400">Tax / Deductions</span>
            <p className="text-2xl font-bold text-indigo-400 mt-1">$34,200.00</p>
          </div>
        </div>
      </div>
    </div>
  )
}
