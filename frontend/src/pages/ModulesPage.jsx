import React, { useState } from 'react'
import { Plug, Plus, CheckCircle2, XCircle, Search, Sparkles } from 'lucide-react'

export default function ModulesPage() {
  const [modules, setModules] = useState([
    { id: 1, name: 'TimeSync Live Telemetry & Tracking', desc: 'Syncs desktop electron app logs, active windows, screenshots, and live idle statuses with CRM.', enabled: true, version: '2.4.1', author: 'TIC Core Team' },
    { id: 2, name: 'PipraPay Instant Payment Gateway', desc: 'Local and international multi-currency payment checkout with automated webhook status callbacks.', enabled: true, version: '1.8.0', author: 'PipraPay Official' },
    { id: 3, name: 'WooCommerce Automatic Two-Way Sync', desc: 'Sync products, customer orders, and statuses directly into CRM sales ledger.', enabled: true, version: '2.1.0', author: 'TIC Core Team' },
    { id: 4, name: 'Zoom & Jitsi Meeting Scheduler', desc: 'Generate instant video conference rooms and attach them to client meetings and ticket responses.', enabled: true, version: '1.2.4', author: 'TIC Labs' },
    { id: 5, name: 'SMS & WhatsApp Gateway Notifications', desc: 'Send automated invoice payment reminders and ticket update alerts to customer phones.', enabled: false, version: '1.0.2', author: 'Community Addon' },
  ])

  const handleToggle = (id) => {
    setModules(modules.map(m => m.id === id ? { ...m, enabled: !m.enabled } : m))
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Plug className="w-6 h-6 text-indigo-400" />
            <span>Modules & Add-ons Marketplace</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Enable, configure, and install official CRM extension plugins and third-party integrations.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Upload Custom Add-on (.zip)</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {modules.map(m => (
          <div key={m.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <span className="text-[11px] font-mono text-slate-400">v{m.version} • {m.author}</span>
                <button
                  onClick={() => handleToggle(m.id)}
                  className={`px-3 py-1 rounded-full text-xs font-semibold cursor-pointer border transition-colors ${
                    m.enabled ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-800 text-slate-400 border-slate-700'
                  }`}
                >
                  {m.enabled ? 'Enabled' : 'Disabled'}
                </button>
              </div>
              <h3 className="text-base font-bold text-white">{m.name}</h3>
              <p className="text-xs text-slate-400 mt-2 leading-relaxed">{m.desc}</p>
            </div>

            <div className="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
              <span className="flex items-center gap-1.5 text-emerald-400">
                <Sparkles className="w-3.5 h-3.5" />
                <span>Verified Compatible</span>
              </span>
              <button className="text-indigo-400 hover:text-indigo-300 font-semibold">Settings</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
