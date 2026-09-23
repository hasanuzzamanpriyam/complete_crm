import React from 'react'
import { Server, CheckCircle2, Shield, Zap, Database, Cpu } from 'lucide-react'

export default function SystemHealthWidget() {
  const metrics = [
    { label: 'REST API Engine', value: 'CodeIgniter 3.1.13', status: 'Healthy', ping: '18ms', icon: Server, color: 'text-emerald-400' },
    { label: 'Database Cluster', value: 'MySQL 8.0 InnoDB', status: 'Optimal', ping: '1.2ms', icon: Database, color: 'text-emerald-400' },
    { label: 'PipraPay Gateway', value: 'Production v2.4', status: 'Connected', ping: 'Active', icon: Shield, color: 'text-indigo-400' },
    { label: 'TimeSync Daemon', value: 'WebSocket Polling', status: 'Streaming', ping: '31 Active', icon: Zap, color: 'text-cyan-400' },
  ]

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur shadow-xl flex flex-col justify-between">
      <div>
        <div className="flex items-center justify-between mb-4">
          <div>
            <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
              <Cpu className="w-4 h-4 text-purple-400" />
              <span>Infrastructure & Service Health</span>
            </h2>
            <p className="text-xs text-slate-400 mt-0.5">
              Live heartbeat telemetry of server, database, and payment gateways.
            </p>
          </div>
          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            99.98% SLA
          </span>
        </div>

        <div className="space-y-3">
          {metrics.map((m, idx) => {
            const Icon = m.icon
            return (
              <div
                key={idx}
                className="p-3 rounded-xl bg-slate-800/40 border border-slate-800 flex items-center justify-between hover:bg-slate-800/70 transition-colors"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-lg bg-slate-900 border border-slate-700/50">
                    <Icon className={`w-4 h-4 ${m.color}`} />
                  </div>
                  <div>
                    <p className="text-xs font-semibold text-slate-200">{m.label}</p>
                    <p className="text-[11px] text-slate-400">{m.value}</p>
                  </div>
                </div>
                <div className="text-right">
                  <span className="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-400">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                    {m.status}
                  </span>
                  <p className="text-[10px] text-slate-500 font-mono">{m.ping}</p>
                </div>
              </div>
            )
          })}
        </div>
      </div>

      <div className="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
        <span>PHP 8.2 • Apache/2.4 (Win64)</span>
        <span className="text-indigo-400 font-medium">Memory: 42MB / 512MB</span>
      </div>
    </div>
  )
}
