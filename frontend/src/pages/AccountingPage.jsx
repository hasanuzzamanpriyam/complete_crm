import React, { useState } from 'react'
import { DollarSign, BookOpen, Plus, Search, FileText, CheckCircle2, ArrowRight } from 'lucide-react'

export default function AccountingPage() {
  const [activeTab, setActiveTab] = useState('accounts')

  const accounts = [
    { code: '1001', name: 'Cash in Hand', type: 'Asset', balance: '$12,450.00', status: 'Active' },
    { code: '1002', name: 'Primary Operating Bank (BRAC)', type: 'Asset', balance: '$148,920.00', status: 'Active' },
    { code: '1003', name: 'Accounts Receivable (Clients)', type: 'Asset', balance: '$45,800.00', status: 'Active' },
    { code: '2001', name: 'Accounts Payable (Vendors)', type: 'Liability', balance: '$18,300.00', status: 'Active' },
    { code: '3001', name: 'Retained Earnings', type: 'Equity', balance: '$120,000.00', status: 'Active' },
    { code: '4001', name: 'Software Development Revenue', type: 'Revenue', balance: '$210,500.00', status: 'Active' },
    { code: '5001', name: 'Server Infrastructure Costs', type: 'Expense', balance: '$24,100.00', status: 'Active' },
  ]

  const vouchers = [
    { id: 'VCH-992', date: '2026-09-22', type: 'Payment Voucher', debit: '5001 - Server Costs', credit: '1002 - Operating Bank', amount: '$2,400.00', narration: 'Monthly AWS EC2 & RDS cluster fee' },
    { id: 'VCH-991', date: '2026-09-20', type: 'Receipt Voucher', debit: '1002 - Operating Bank', credit: '4001 - Dev Revenue', amount: '$15,000.00', narration: 'Sprint 2 milestone payment Apex Retail' },
    { id: 'VCH-990', date: '2026-09-18', type: 'Journal Voucher', debit: '1003 - Accounts Rec.', credit: '4001 - Dev Revenue', amount: '$8,900.00', narration: 'Accrued billing CloudWave Tech' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <DollarSign className="w-6 h-6 text-emerald-400" />
            <span>General Accounting & Ledger</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Double-entry bookkeeping, chart of accounts, journal vouchers, and automated trial balance.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Journal Entry</span>
        </button>
      </div>

      {/* Tabs */}
      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold">
        <button
          onClick={() => setActiveTab('accounts')}
          className={`pb-3 transition-colors ${activeTab === 'accounts' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Chart of Accounts
        </button>
        <button
          onClick={() => setActiveTab('vouchers')}
          className={`pb-3 transition-colors ${activeTab === 'vouchers' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Journal & Payment Vouchers
        </button>
        <button
          onClick={() => setActiveTab('reports')}
          className={`pb-3 transition-colors ${activeTab === 'reports' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Financial Reports (Trial Balance)
        </button>
      </div>

      {activeTab === 'accounts' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Account Code</th>
                <th className="py-3.5 px-4">Account Name</th>
                <th className="py-3.5 px-4">Account Type</th>
                <th className="py-3.5 px-4">Current Ledger Balance</th>
                <th className="py-3.5 px-4">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {accounts.map(a => (
                <tr key={a.code} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-mono font-bold text-indigo-400">{a.code}</td>
                  <td className="py-3.5 px-4 font-medium text-slate-200">{a.name}</td>
                  <td className="py-3.5 px-4">
                    <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                      {a.type}
                    </span>
                  </td>
                  <td className="py-3.5 px-4 font-bold text-white">{a.balance}</td>
                  <td className="py-3.5 px-4">
                    <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                      {a.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'vouchers' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Voucher #</th>
                <th className="py-3.5 px-4">Date</th>
                <th className="py-3.5 px-4">Voucher Type</th>
                <th className="py-3.5 px-4">Debit Account</th>
                <th className="py-3.5 px-4">Credit Account</th>
                <th className="py-3.5 px-4">Amount</th>
                <th className="py-3.5 px-4">Narration</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {vouchers.map(v => (
                <tr key={v.id} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-mono text-emerald-400 font-bold">{v.id}</td>
                  <td className="py-3.5 px-4 text-slate-400">{v.date}</td>
                  <td className="py-3.5 px-4 text-slate-300 font-medium">{v.type}</td>
                  <td className="py-3.5 px-4 text-slate-200">{v.debit}</td>
                  <td className="py-3.5 px-4 text-slate-200">{v.credit}</td>
                  <td className="py-3.5 px-4 font-bold text-white">{v.amount}</td>
                  <td className="py-3.5 px-4 text-slate-400 max-w-xs">{v.narration}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'reports' && (
        <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 space-y-4">
          <h3 className="text-base font-bold text-white">Summary Trial Balance (YTD 2026)</h3>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div className="p-4 rounded-xl bg-slate-800/60">
              <span className="text-xs text-slate-400">Total Debits</span>
              <p className="text-xl font-bold text-white mt-1">$207,170.00</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-800/60">
              <span className="text-xs text-slate-400">Total Credits</span>
              <p className="text-xl font-bold text-white mt-1">$207,170.00</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-800/60">
              <span className="text-xs text-slate-400">Net Profit</span>
              <p className="text-xl font-bold text-emerald-400 mt-1">+$186,400.00</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-800/60">
              <span className="text-xs text-slate-400">Balance Integrity</span>
              <p className="text-xl font-bold text-indigo-400 mt-1">Balanced ✓</p>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
