import React, { useState } from 'react'
import { Settings, Save, Check, Mail, Building, Shield, Database, Globe } from 'lucide-react'

export default function SettingsPage() {
  const [activeTab, setActiveTab] = useState('general')
  const [saved, setSaved] = useState(false)

  const [company, setCompany] = useState({
    name: 'TIC Enterprise Technologies Ltd.',
    email: 'support@ticcrm.com',
    phone: '+880 1700-000000',
    address: 'Level 14, BDBL Bhaban, Kawran Bazar, Dhaka',
    currency: 'USD ($)',
    timezone: 'Asia/Dhaka (GMT+6)',
  })

  const [smtp, setSmtp] = useState({
    host: 'smtp.sendgrid.net',
    port: '587',
    user: 'apikey',
    pass: '••••••••••••••••••••••',
    encryption: 'tls',
  })

  const [piprapay, setPiprapay] = useState({
    apiKey: 'pk_live_89f0293a8e91024bd90',
    apiSecret: 'sk_live_99014b2d308f910481024',
    webhookUrl: 'https://ticcrm.com/api/piprapay/webhook',
    sandboxMode: false,
  })

  const handleSave = (e) => {
    e.preventDefault()
    setSaved(true)
    setTimeout(() => setSaved(false), 2500)
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Settings className="w-6 h-6 text-indigo-400" />
            <span>System Configuration & Preferences</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Global CRM settings: company profile, email SMTP delivery, PipraPay credentials, and database backups.
          </p>
        </div>
        <button
          onClick={handleSave}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2"
        >
          {saved ? <Check className="w-4 h-4" /> : <Save className="w-4 h-4" />}
          <span>{saved ? 'Changes Saved' : 'Save Settings'}</span>
        </button>
      </div>

      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold overflow-x-auto pb-1">
        <button
          onClick={() => setActiveTab('general')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'general' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Company Profile
        </button>
        <button
          onClick={() => setActiveTab('smtp')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'smtp' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Email (SMTP)
        </button>
        <button
          onClick={() => setActiveTab('piprapay')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'piprapay' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          PipraPay Payment Gateway
        </button>
        <button
          onClick={() => setActiveTab('backup')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'backup' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Database Backups
        </button>
      </div>

      {activeTab === 'general' && (
        <form onSubmit={handleSave} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 max-w-2xl space-y-4 text-xs">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-slate-300 font-medium mb-1">Company Legal Name</label>
              <input
                type="text"
                value={company.name}
                onChange={e => setCompany({ ...company, name: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
            <div>
              <label className="block text-slate-300 font-medium mb-1">Support Email</label>
              <input
                type="email"
                value={company.email}
                onChange={e => setCompany({ ...company, email: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-slate-300 font-medium mb-1">Phone Number</label>
              <input
                type="text"
                value={company.phone}
                onChange={e => setCompany({ ...company, phone: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
            <div>
              <label className="block text-slate-300 font-medium mb-1">Default Base Currency</label>
              <input
                type="text"
                value={company.currency}
                onChange={e => setCompany({ ...company, currency: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
          </div>
          <div>
            <label className="block text-slate-300 font-medium mb-1">HQ Physical Address</label>
            <textarea
              rows={2}
              value={company.address}
              onChange={e => setCompany({ ...company, address: e.target.value })}
              className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
            />
          </div>
        </form>
      )}

      {activeTab === 'smtp' && (
        <form onSubmit={handleSave} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 max-w-2xl space-y-4 text-xs">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-slate-300 font-medium mb-1">SMTP Host</label>
              <input
                type="text"
                value={smtp.host}
                onChange={e => setSmtp({ ...smtp, host: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
            <div>
              <label className="block text-slate-300 font-medium mb-1">SMTP Port</label>
              <input
                type="text"
                value={smtp.port}
                onChange={e => setSmtp({ ...smtp, port: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-slate-300 font-medium mb-1">Username / Key ID</label>
              <input
                type="text"
                value={smtp.user}
                onChange={e => setSmtp({ ...smtp, user: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
            <div>
              <label className="block text-slate-300 font-medium mb-1">Password / API Secret</label>
              <input
                type="password"
                value={smtp.pass}
                onChange={e => setSmtp({ ...smtp, pass: e.target.value })}
                className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
              />
            </div>
          </div>
        </form>
      )}

      {activeTab === 'piprapay' && (
        <form onSubmit={handleSave} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 max-w-2xl space-y-4 text-xs">
          <div>
            <label className="block text-slate-300 font-medium mb-1">PipraPay Public API Key</label>
            <input
              type="text"
              value={piprapay.apiKey}
              onChange={e => setPiprapay({ ...piprapay, apiKey: e.target.value })}
              className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-mono"
            />
          </div>
          <div>
            <label className="block text-slate-300 font-medium mb-1">PipraPay Private Secret Key</label>
            <input
              type="password"
              value={piprapay.apiSecret}
              onChange={e => setPiprapay({ ...piprapay, apiSecret: e.target.value })}
              className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-mono"
            />
          </div>
          <div>
            <label className="block text-slate-300 font-medium mb-1">Instant Webhook Endpoint Callback</label>
            <input
              type="text"
              readOnly
              value={piprapay.webhookUrl}
              className="w-full px-3 py-2 rounded-lg bg-slate-800/50 border border-slate-700 text-indigo-400 font-mono"
            />
          </div>
        </form>
      )}

      {activeTab === 'backup' && (
        <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 max-w-2xl space-y-4 text-xs">
          <h3 className="text-base font-bold text-white mb-2">Automated MySQL Database Snapshots</h3>
          <p className="text-slate-400 mb-4">Daily automatic backups are archived and encrypted with AES-256.</p>
          <div className="p-4 rounded-xl bg-slate-800/40 border border-slate-700 space-y-2">
            <div className="flex justify-between text-slate-300"><span>Last Snapshot:</span><strong className="text-emerald-400">2026-09-23 04:00 AM (48.2 MB)</strong></div>
            <div className="flex justify-between text-slate-300"><span>Storage Destination:</span><strong className="text-white">Amazon S3 (Glacier Vault)</strong></div>
          </div>
          <button className="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-semibold text-white">
            Create Instant Backup Now
          </button>
        </div>
      )}
    </div>
  )
}
