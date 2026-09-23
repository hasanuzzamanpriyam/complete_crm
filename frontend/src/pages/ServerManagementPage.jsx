import React, { useState } from 'react'
import { Server, Globe, Cpu, HardDrive, Plus, RefreshCw, CheckCircle2, Shield, Activity } from 'lucide-react'

export default function ServerManagementPage() {
  const [activeTab, setActiveTab] = useState('dashboard')

  const servers = [
    { id: 1, name: 'Prod-App-Node-01', ip: '159.89.142.90', os: 'Ubuntu 24.04 LTS', cpu: '28%', ram: '4.2GB / 16GB', disk: '45GB / 250GB', status: 'Online', uptime: '99.98%' },
    { id: 2, name: 'Prod-DB-Cluster-Primary', ip: '159.89.142.92', os: 'MySQL 8.0 Enterprise', cpu: '14%', ram: '18.4GB / 32GB', disk: '120GB / 500GB', status: 'Online', uptime: '100%' },
    { id: 3, name: 'Staging-Sandbox-Host', ip: '143.198.80.12', os: 'Debian 12', cpu: '8%', ram: '2.1GB / 8GB', disk: '15GB / 100GB', status: 'Online', uptime: '99.85%' },
  ]

  const domains = [
    { domain: 'ticcrm.com', registrar: 'Cloudflare', expiry: '2028-11-20', ssl: 'Active (Let\'s Encrypt)', autoRenew: true },
    { domain: 'timesyncapp.io', registrar: 'Namecheap', expiry: '2027-04-15', ssl: 'Active (Cloudflare Wildcard)', autoRenew: true },
    { domain: 'apexretail.net', registrar: 'GoDaddy', expiry: '2026-12-10', ssl: 'Active', autoRenew: false },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Server className="w-6 h-6 text-indigo-400" />
            <span>Infrastructure & Server Management</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Monitor VPS cloud nodes, DNS records, domain expirations, provider billing, and server health.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-2">
            <RefreshCw className="w-3.5 h-3.5" />
            <span>Poll Telemetry</span>
          </button>
          <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2">
            <Plus className="w-4 h-4" />
            <span>Deploy Server</span>
          </button>
        </div>
      </div>

      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold overflow-x-auto pb-1">
        <button
          onClick={() => setActiveTab('dashboard')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'dashboard' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Server Nodes & Hosting
        </button>
        <button
          onClick={() => setActiveTab('domains')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'domains' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Domains & SSL ({domains.length})
        </button>
        <button
          onClick={() => setActiveTab('providers')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'providers' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Cloud Providers & Billing
        </button>
      </div>

      {activeTab === 'dashboard' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {servers.map(s => (
            <div key={s.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs font-mono font-semibold text-indigo-400">{s.ip}</span>
                  <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {s.status}
                  </span>
                </div>
                <h3 className="text-base font-bold text-white">{s.name}</h3>
                <p className="text-xs text-slate-400 mt-0.5">{s.os}</p>

                <div className="space-y-3 mt-4 pt-3 border-t border-slate-800/80 text-xs">
                  <div>
                    <div className="flex justify-between text-slate-400 mb-1">
                      <span className="flex items-center gap-1.5"><Cpu className="w-3.5 h-3.5" /> CPU Load</span>
                      <strong className="text-white">{s.cpu}</strong>
                    </div>
                    <div className="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                      <div className="bg-indigo-500 h-full rounded-full" style={{ width: s.cpu }} />
                    </div>
                  </div>
                  <div>
                    <div className="flex justify-between text-slate-400 mb-1">
                      <span className="flex items-center gap-1.5"><Activity className="w-3.5 h-3.5" /> RAM</span>
                      <strong className="text-white">{s.ram}</strong>
                    </div>
                  </div>
                  <div>
                    <div className="flex justify-between text-slate-400 mb-1">
                      <span className="flex items-center gap-1.5"><HardDrive className="w-3.5 h-3.5" /> Disk</span>
                      <strong className="text-white">{s.disk}</strong>
                    </div>
                  </div>
                </div>
              </div>

              <div className="mt-5 pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <span>Uptime: <strong className="text-emerald-400">{s.uptime}</strong></span>
                <button className="text-indigo-400 hover:text-indigo-300 font-semibold">Terminal SSH</button>
              </div>
            </div>
          ))}
        </div>
      )}

      {activeTab === 'domains' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Domain Name</th>
                <th className="py-3.5 px-4">Registrar</th>
                <th className="py-3.5 px-4">SSL Certificate</th>
                <th className="py-3.5 px-4">Expiration Date</th>
                <th className="py-3.5 px-4">Auto-Renew</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {domains.map((d, i) => (
                <tr key={i} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-bold text-white flex items-center gap-2">
                    <Globe className="w-4 h-4 text-indigo-400" />
                    <span>{d.domain}</span>
                  </td>
                  <td className="py-3.5 px-4 text-slate-300">{d.registrar}</td>
                  <td className="py-3.5 px-4 text-emerald-400 font-medium">{d.ssl}</td>
                  <td className="py-3.5 px-4 text-slate-400">{d.expiry}</td>
                  <td className="py-3.5 px-4">
                    <span className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                      d.autoRenew ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-400'
                    }`}>
                      {d.autoRenew ? 'Enabled' : 'Disabled'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'providers' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800">
            <h3 className="text-base font-bold text-white mb-2">DigitalOcean Cloud</h3>
            <p className="text-xs text-slate-400 mb-4">NYC3 & SGP1 regions • 4 Droplets active</p>
            <div className="flex justify-between items-baseline pt-4 border-t border-slate-800">
              <span className="text-xs text-slate-400">Current Monthly Accrual:</span>
              <strong className="text-lg font-bold text-white">$142.80 / mo</strong>
            </div>
          </div>
          <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800">
            <h3 className="text-base font-bold text-white mb-2">Amazon Web Services (AWS)</h3>
            <p className="text-xs text-slate-400 mb-4">ap-southeast-1 (Singapore) • RDS + S3 CloudFront</p>
            <div className="flex justify-between items-baseline pt-4 border-t border-slate-800">
              <span className="text-xs text-slate-400">Current Monthly Accrual:</span>
              <strong className="text-lg font-bold text-white">$289.40 / mo</strong>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
