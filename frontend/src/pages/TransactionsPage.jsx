import React, { useState } from 'react'
import { Receipt, Search, Plus, Filter, ArrowUpRight, ArrowDownLeft, Calendar } from 'lucide-react'

export default function TransactionsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedType, setSelectedType] = useState('All')

  const [transactions, setTransactions] = useState([
    { id: 'TXN-701', type: 'Income', category: 'Client Invoice Payment', desc: 'Settlement for INV-2026-0042', amount: 12500, date: '2026-09-22', account: 'Operating Bank' },
    { id: 'TXN-700', type: 'Expense', category: 'Cloud Infrastructure', desc: 'AWS monthly cloud server cluster', amount: 1850, date: '2026-09-21', account: 'Operating Bank' },
    { id: 'TXN-699', type: 'Expense', category: 'Office Rent & Utilities', desc: 'Corporate HQ floor lease', amount: 3200, date: '2026-09-18', account: 'Operating Bank' },
    { id: 'TXN-698', type: 'Income', category: 'Software Support', desc: 'CloudWave monthly maintenance retainer', amount: 4500, date: '2026-09-15', account: 'Operating Bank' },
  ])

  const filtered = transactions.filter(t => {
    const matchesSearch = t.desc.toLowerCase().includes(searchQuery.toLowerCase()) || t.category.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesType = selectedType === 'All' || t.type === selectedType
    return matchesSearch && matchesType
  })

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Receipt className="w-6 h-6 text-indigo-400" />
            <span>Transactions & Expense Register</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Track daily incoming revenues and operational expenditures with category breakdowns.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 flex items-center gap-2">
            <ArrowDownLeft className="w-4 h-4" />
            <span>Add Income</span>
          </button>
          <button className="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-rose-600/30 flex items-center gap-2">
            <ArrowUpRight className="w-4 h-4" />
            <span>Add Expense</span>
          </button>
        </div>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Transaction ID</th>
              <th className="py-3.5 px-4">Type</th>
              <th className="py-3.5 px-4">Category</th>
              <th className="py-3.5 px-4">Description</th>
              <th className="py-3.5 px-4">Account</th>
              <th className="py-3.5 px-4">Date</th>
              <th className="py-3.5 px-4 text-right">Amount</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(t => (
              <tr key={t.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-mono font-semibold text-slate-300">{t.id}</td>
                <td className="py-3.5 px-4">
                  <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    t.type === 'Income' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                  }`}>
                    {t.type === 'Income' ? <ArrowDownLeft className="w-3 h-3" /> : <ArrowUpRight className="w-3 h-3" />}
                    {t.type}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-slate-300">{t.category}</td>
                <td className="py-3.5 px-4 text-slate-200 font-medium">{t.desc}</td>
                <td className="py-3.5 px-4 text-slate-400">{t.account}</td>
                <td className="py-3.5 px-4 text-slate-400">{t.date}</td>
                <td className={`py-3.5 px-4 text-right font-bold text-sm ${t.type === 'Income' ? 'text-emerald-400' : 'text-rose-400'}`}>
                  {t.type === 'Income' ? '+' : '-'}${t.amount.toLocaleString()}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
