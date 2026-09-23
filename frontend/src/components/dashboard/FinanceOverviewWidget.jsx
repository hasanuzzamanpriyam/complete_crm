import React from 'react'
import {
  TrendingUp,
  TrendingDown,
  DollarSign,
  ArrowUpRight,
  ArrowDownRight,
  CreditCard,
  Wallet,
} from 'lucide-react'

export default function FinanceOverviewWidget() {
  const stats = [
    {
      label: 'Today Income',
      amount: '$1,250.00',
      change: '+14.2%',
      isPositive: true,
      icon: TrendingUp,
      color: 'text-emerald-400',
      bg: 'bg-emerald-500/10',
    },
    {
      label: 'Today Expense',
      amount: '$340.50',
      change: '-5.1%',
      isPositive: false,
      icon: TrendingDown,
      color: 'text-rose-400',
      bg: 'bg-rose-500/10',
    },
    {
      label: 'This Month Income',
      amount: '$28,450.00',
      change: '+22.5%',
      isPositive: true,
      icon: Wallet,
      color: 'text-indigo-400',
      bg: 'bg-indigo-500/10',
    },
    {
      label: 'This Month Expense',
      amount: '$8,120.00',
      change: '+3.4%',
      isPositive: false,
      icon: CreditCard,
      color: 'text-amber-400',
      bg: 'bg-amber-500/10',
    },
  ]

  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
  const incomeBars = [45, 60, 55, 75, 80, 70, 90, 85, 95]
  const expenseBars = [25, 30, 28, 35, 40, 32, 45, 38, 42]

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-5">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <DollarSign className="w-4 h-4 text-emerald-400" />
            <span>Financial Overview & Cashflow</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Real-time income vs. expense performance summary
          </p>
        </div>
        <div className="flex items-center gap-4 text-xs">
          <span className="flex items-center gap-1.5 text-slate-300">
            <span className="w-2.5 h-2.5 rounded-sm bg-indigo-500" /> Income
          </span>
          <span className="flex items-center gap-1.5 text-slate-300">
            <span className="w-2.5 h-2.5 rounded-sm bg-rose-500" /> Expense
          </span>
        </div>
      </div>

      {/* 4 Quick Stat Pills */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {stats.map((item, idx) => {
          const Icon = item.icon
          return (
            <div
              key={idx}
              className="p-3.5 rounded-xl bg-slate-800/50 border border-slate-700/40"
            >
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-medium text-slate-400">
                  {item.label}
                </span>
                <div className={`p-1.5 rounded-lg ${item.bg} ${item.color}`}>
                  <Icon className="w-3.5 h-3.5" />
                </div>
              </div>
              <p className="mt-2 text-base sm:text-lg font-bold text-white">
                {item.amount}
              </p>
              <div className="mt-1 flex items-center gap-1 text-[10px]">
                {item.isPositive ? (
                  <span className="text-emerald-400 flex items-center font-semibold">
                    <ArrowUpRight className="w-3 h-3" /> {item.change}
                  </span>
                ) : (
                  <span className="text-rose-400 flex items-center font-semibold">
                    <ArrowDownRight className="w-3 h-3" /> {item.change}
                  </span>
                )}
                <span className="text-slate-400">vs last period</span>
              </div>
            </div>
          )
        })}
      </div>

      {/* Visual Bar Chart */}
      <div className="pt-2">
        <div className="flex items-end justify-between h-40 gap-2 sm:gap-3 px-2">
          {months.map((m, idx) => (
            <div key={m} className="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
              <div className="w-full flex items-end justify-center gap-1 h-32">
                <div
                  style={{ height: `${incomeBars[idx]}%` }}
                  className="w-1/2 max-w-[14px] bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-t-sm group-hover:brightness-125 transition-all"
                  title={`Income: $${incomeBars[idx] * 300}`}
                />
                <div
                  style={{ height: `${expenseBars[idx]}%` }}
                  className="w-1/2 max-w-[14px] bg-gradient-to-t from-rose-600 to-rose-400 rounded-t-sm group-hover:brightness-125 transition-all"
                  title={`Expense: $${expenseBars[idx] * 200}`}
                />
              </div>
              <span className="text-[10px] font-medium text-slate-400 group-hover:text-slate-200">
                {m}
              </span>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
