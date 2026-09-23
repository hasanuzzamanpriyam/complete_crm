import React, { useState } from 'react'
import { Users, Search, Plus, Shield, CheckCircle2, XCircle, Key, UserCheck, MoreVertical, X } from 'lucide-react'

export default function UsersPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [users, setUsers] = useState([
    { id: 1, username: 'forid', name: 'Forid Admin', email: 'forid@ticcrm.com', role: 'Super Administrator', status: 'Active', lastLogin: 'Just now' },
    { id: 2, username: 'marcus_v', name: 'Marcus Vance', email: 'm.vance@apexretail.com', role: 'Project Manager', status: 'Active', lastLogin: '2 hours ago' },
    { id: 3, username: 'elena_r', name: 'Elena Rostova', email: 'elena@cloudwave.io', role: 'Staff / Developer', status: 'Active', lastLogin: '1 day ago' },
    { id: 4, username: 'sarah_j', name: 'Sarah Jenkins', email: 'sarah.j@ticcrm.com', role: 'Accountant', status: 'Active', lastLogin: '3 hours ago' },
    { id: 5, username: 'michael_s', name: 'Michael Scott', email: 'michael@ticcrm.com', role: 'Staff / Developer', status: 'Active', lastLogin: 'Yesterday' },
  ])

  const [showModal, setShowModal] = useState(false)
  const [form, setForm] = useState({ username: '', name: '', email: '', role: 'Staff / Developer' })

  const handleToggle = (id) => {
    setUsers(users.map(u => u.id === id ? { ...u, status: u.status === 'Active' ? 'Suspended' : 'Active' } : u))
  }

  const handleCreate = (e) => {
    e.preventDefault()
    setUsers([...users, { id: Date.now(), ...form, status: 'Active', lastLogin: 'Never' }])
    setShowModal(false)
    setForm({ username: '', name: '', email: '', role: 'Staff / Developer' })
  }

  const filtered = users.filter(u =>
    u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    u.username.toLowerCase().includes(searchQuery.toLowerCase()) ||
    u.email.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Shield className="w-6 h-6 text-indigo-400" />
            <span>Staff Accounts & Role Permissions</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Manage user accounts, assign role permissions, reset credentials, and enforce 2FA security.
          </p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          <span>Add New User</span>
        </button>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search username, name, email..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
          />
        </div>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">User</th>
              <th className="py-3.5 px-4">Username</th>
              <th className="py-3.5 px-4">System Role</th>
              <th className="py-3.5 px-4">Status</th>
              <th className="py-3.5 px-4">Last Login</th>
              <th className="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(u => (
              <tr key={u.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4">
                  <div className="flex items-center gap-2.5">
                    <div className="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
                      {u.name.charAt(0)}
                    </div>
                    <div>
                      <p className="font-semibold text-slate-200">{u.name}</p>
                      <p className="text-[10px] text-slate-400">{u.email}</p>
                    </div>
                  </div>
                </td>
                <td className="py-3.5 px-4 font-mono text-indigo-400">@{u.username}</td>
                <td className="py-3.5 px-4">
                  <span className="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                    {u.role}
                  </span>
                </td>
                <td className="py-3.5 px-4">
                  <button
                    onClick={() => handleToggle(u.id)}
                    className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold cursor-pointer border ${
                      u.status === 'Active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                    }`}
                  >
                    {u.status}
                  </button>
                </td>
                <td className="py-3.5 px-4 text-slate-400">{u.lastLogin}</td>
                <td className="py-3.5 px-4 text-right">
                  <button className="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-white">
                    <MoreVertical className="w-4 h-4" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <div className="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-base font-bold text-white">Create Staff Account</h2>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleCreate} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-medium mb-1">Full Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Alex Morgan"
                  value={form.name}
                  onChange={e => setForm({ ...form, name: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Username</label>
                  <input
                    type="text"
                    required
                    placeholder="alex_m"
                    value={form.username}
                    onChange={e => setForm({ ...form, username: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Role</label>
                  <select
                    value={form.role}
                    onChange={e => setForm({ ...form, role: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  >
                    <option>Super Administrator</option>
                    <option>Project Manager</option>
                    <option>Staff / Developer</option>
                    <option>Accountant</option>
                    <option>Client Portal</option>
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-slate-300 font-medium mb-1">Email Address</label>
                <input
                  type="email"
                  required
                  placeholder="alex@ticcrm.com"
                  value={form.email}
                  onChange={e => setForm({ ...form, email: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>
              <div className="pt-2 flex justify-end gap-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 rounded-lg bg-slate-800 text-slate-300">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">
                  Create User
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
