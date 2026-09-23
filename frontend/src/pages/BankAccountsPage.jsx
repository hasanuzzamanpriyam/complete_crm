import React, { useState } from 'react'
import { Building2, Plus, ArrowRightLeft, CreditCard, DollarSign } from 'lucide-react'

export default function BankAccountsPage() {
  const [accounts, setAccounts] = useState([
    { id: 1, name: 'BRAC Corporate Operating Account', number: '•••• •••• 4492', balance: '$148,920.00', currency: 'USD', branch: 'Gulshan 2, Dhaka', status: 'Active' },
    { id: 2, name: 'City Bank Payroll & Disbursal', number: '•••• •••• 1083', balance: '$42,300.00', currency: 'USD', branch: 'Principal Branch', status: 'Active' },
    { id: 3, name: 'Corporate Petty Cash Safe', number: 'CASH-VAULT-01', balance: '$12,450.00', currency: 'USD', branch: 'HQ Treasury Desk', status: 'Active' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Building2 className="w-6 h-6 text-indigo-400" />
            <span>Bank & Cash Accounts</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Manage commercial bank accounts, balances, cash registers, and inter-account transfers.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-2">
            <ArrowRightLeft className="w-4 h-4" />
            <span>Transfer Funds</span>
          </button>
          <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2">
            <Plus className="w-4 h-4" />
            <span>Add Account</span>
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {accounts.map(acc => (
          <div key={acc.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-semibold text-slate-400 font-mono">{acc.number}</span>
                <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                  {acc.status}
                </span>
              </div>
              <h3 className="text-base font-bold text-white">{acc.name}</h3>
              <p className="text-xs text-slate-400 mt-1">{acc.branch}</p>
            </div>

            <div className="mt-6 pt-4 border-t border-slate-800 flex items-baseline justify-between">
              <div>
                <span className="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Available Funds</span>
                <p className="text-2xl font-black text-emerald-400 mt-0.5">{acc.balance}</p>
              </div>
              <span className="text-xs font-mono font-bold text-slate-400">{acc.currency}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
