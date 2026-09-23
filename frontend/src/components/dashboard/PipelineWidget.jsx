import React from 'react'
import { Link } from 'react-router-dom'
import { TrendingUp, ArrowRight, DollarSign, Target, Award } from 'lucide-react'

export default function PipelineWidget() {
  const stages = [
    { name: 'Prospecting', count: 12, value: '$45,200', pct: '25%', color: 'bg-blue-500' },
    { name: 'Qualification', count: 8, value: '$32,800', pct: '45%', color: 'bg-indigo-500' },
    { name: 'Proposal Sent', count: 5, value: '$54,000', pct: '70%', color: 'bg-purple-500' },
    { name: 'Negotiation', count: 3, value: '$28,500', pct: '85%', color: 'bg-amber-500' },
    { name: 'Won (This Mo)', count: 9, value: '$84,000', pct: '100%', color: 'bg-emerald-500' },
  ]

  const totalValue = '$244,500'
  const winRate = '68.4%'

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur shadow-xl flex flex-col justify-between">
      <div>
        <div className="flex items-center justify-between mb-4">
          <div>
            <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
              <TrendingUp className="w-4 h-4 text-amber-400" />
              <span>Sales Pipeline & Revenue Forecast</span>
            </h2>
            <p className="text-xs text-slate-400 mt-0.5">
              Live funnel stages and conversion probability across active deals.
            </p>
          </div>
          <Link
            to="/leads"
            className="text-xs text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1 transition-colors"
          >
            <span>View Funnel</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {/* Top Summary Banner */}
        <div className="grid grid-cols-2 gap-3 mb-4">
          <div className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
            <span className="text-[10px] text-slate-400 font-medium">Pipeline Value</span>
            <div className="text-base font-bold text-emerald-400 flex items-center gap-1 mt-0.5">
              <span>{totalValue}</span>
            </div>
          </div>
          <div className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
            <span className="text-[10px] text-slate-400 font-medium">Monthly Win Rate</span>
            <div className="text-base font-bold text-indigo-400 flex items-center gap-1 mt-0.5">
              <Target className="w-3.5 h-3.5" />
              <span>{winRate}</span>
            </div>
          </div>
        </div>

        {/* Stage Bars */}
        <div className="space-y-3">
          {stages.map((stg, i) => (
            <div key={i} className="group">
              <div className="flex items-center justify-between text-xs mb-1">
                <span className="font-medium text-slate-300 group-hover:text-white transition-colors">
                  {stg.name} ({stg.count})
                </span>
                <span className="font-semibold text-slate-200">{stg.value}</span>
              </div>
              <div className="w-full h-2 rounded-full bg-slate-800/80 overflow-hidden">
                <div
                  className={`h-full rounded-full ${stg.color} transition-all duration-500`}
                  style={{ width: stg.pct }}
                />
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
        <span className="flex items-center gap-1.5">
          <Award className="w-3.5 h-3.5 text-emerald-400" />
          <span>Top performing rep: <strong>Elena R. ($62k)</strong></span>
        </span>
        <span className="text-emerald-400 font-medium">+18.2% vs last mo</span>
      </div>
    </div>
  )
}
