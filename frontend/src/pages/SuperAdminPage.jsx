import React, { useState } from 'react'
import { ShieldCheck, Key, History, Settings, UserCheck, AlertTriangle } from 'lucide-react'

export default function SuperAdminPage() {
  const [activeTab, setActiveTab] = useState('audit')

  const auditLogs = [
    { id: 1, action: 'User Permissions Modified', user: 'forid (Super Admin)', target: 'Role: Senior Developer', ip: '127.0.0.1', timestamp: '2026-09-23 13:14:02' },
    { id: 2, action: 'System Backup Created', user: 'System Cron', target: 'MySQL S3 Snapshot', ip: 'localhost', timestamp: '2026-09-23 04:00:00' },
    { id: 3, action: 'PipraPay Webhook Executed', user: 'Payment Callback', target: 'INV-2026-0042', ip: '103.112.44.12', timestamp: '2026-09-22 14:32:15' },
    { id: 4, action: 'New Employee Account Created', user: 'forid (Super Admin)', target: 'User ID: #32', ip: '127.0.0.1', timestamp: '2026-09-21 10:20:44' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <ShieldCheck className="w-6 h-6 text-purple-400" />
            <span>Super Administrator Control Center</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Root access management, role permission matrices, global security policies, and audit trails.
          </p>
        </div>
      </div>

      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold">
        <button
          onClick={() => setActiveTab('audit')}
          className={`pb-3 transition-colors ${activeTab === 'audit' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Security Audit Logs
        </button>
        <button
          onClick={() => setActiveTab('permissions')}
          className={`pb-3 transition-colors ${activeTab === 'permissions' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Role & Permission Matrix
        </button>
      </div>

      {activeTab === 'audit' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Event Action</th>
                <th className="py-3.5 px-4">Actor</th>
                <th className="py-3.5 px-4">Affected Resource</th>
                <th className="py-3.5 px-4">Origin IP</th>
                <th className="py-3.5 px-4">Timestamp</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {auditLogs.map(l => (
                <tr key={l.id} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-semibold text-white">{l.action}</td>
                  <td className="py-3.5 px-4 text-purple-400">{l.user}</td>
                  <td className="py-3.5 px-4 text-slate-300">{l.target}</td>
                  <td className="py-3.5 px-4 font-mono text-slate-400">{l.ip}</td>
                  <td className="py-3.5 px-4 text-slate-400">{l.timestamp}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'permissions' && (
        <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 space-y-4">
          <h3 className="text-base font-bold text-white">Granular Module Permissions</h3>
          <p className="text-xs text-slate-400">Configure read, write, delete, and export permissions across roles.</p>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700">
              <span className="text-xs font-bold text-white">Super Admin</span>
              <p className="text-[11px] text-emerald-400 mt-1">Full System Root Access (All modules)</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700">
              <span className="text-xs font-bold text-white">Department Manager</span>
              <p className="text-[11px] text-indigo-400 mt-1">Manage departmental staff & tasks</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700">
              <span className="text-xs font-bold text-white">General Employee</span>
              <p className="text-[11px] text-slate-400 mt-1">View assigned tasks, log time, attendance</p>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
