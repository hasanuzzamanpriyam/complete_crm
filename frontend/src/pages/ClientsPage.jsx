import React, { useState } from 'react'
import {
  Users,
  Search,
  Plus,
  Filter,
  DollarSign,
  FolderKanban,
  MoreVertical,
  Mail,
  Phone,
  Building,
  CheckCircle,
} from 'lucide-react'

export default function ClientsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedGroup, setSelectedGroup] = useState('All')
  const [showModal, setShowModal] = useState(false)

  const [clients, setClients] = useState([
    {
      id: 1,
      name: 'Apex Global Retail',
      contact: 'Marcus Vance',
      email: 'm.vance@apexretail.com',
      phone: '+1 (555) 234-8901',
      group: 'Enterprise',
      projectsCount: 4,
      dueAmount: '$4,800.00',
      paidAmount: '$42,500.00',
      status: 'Active',
    },
    {
      id: 2,
      name: 'CloudWave Technologies',
      contact: 'Elena Rostova',
      email: 'elena@cloudwave.io',
      phone: '+1 (555) 892-1204',
      group: 'VIP Corporate',
      projectsCount: 2,
      dueAmount: '$0.00',
      paidAmount: '$18,900.00',
      status: 'Active',
    },
    {
      id: 3,
      name: 'Vanguard Health Tech',
      contact: 'Dr. Arthur Pendelton',
      email: 'arthur@vanguardhealth.org',
      phone: '+1 (555) 431-7789',
      group: 'Enterprise',
      projectsCount: 3,
      dueAmount: '$7,250.00',
      paidAmount: '$31,000.00',
      status: 'Active',
    },
    {
      id: 4,
      name: 'Omni Media Global',
      contact: 'Chloe Bennett',
      email: 'chloe@omnimediagroup.com',
      phone: '+1 (555) 678-9012',
      group: 'Small Business',
      projectsCount: 1,
      dueAmount: '$2,100.00',
      paidAmount: '$9,400.00',
      status: 'Pending Renewal',
    },
  ])

  const [newClientName, setNewClientName] = useState('')
  const [newContact, setNewContact] = useState('')
  const [newEmail, setNewEmail] = useState('')
  const [newPhone, setNewPhone] = useState('')
  const [newGroup, setNewGroup] = useState('Enterprise')

  const handleAddClient = (e) => {
    e.preventDefault()
    if (!newClientName) return
    const newC = {
      id: Date.now(),
      name: newClientName,
      contact: newContact || 'Primary Contact',
      email: newEmail || 'contact@client.com',
      phone: newPhone || '+1 (555) 000-0000',
      group: newGroup,
      projectsCount: 0,
      dueAmount: '$0.00',
      paidAmount: '$0.00',
      status: 'Active',
    }
    setClients([newC, ...clients])
    setShowModal(false)
    setNewClientName('')
    setNewContact('')
    setNewEmail('')
    setNewPhone('')
  }

  const filteredClients = clients.filter((c) => {
    const matchesGroup = selectedGroup === 'All' || c.group === selectedGroup
    const matchesQuery =
      c.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      c.contact.toLowerCase().includes(searchQuery.toLowerCase()) ||
      c.email.toLowerCase().includes(searchQuery.toLowerCase())
    return matchesGroup && matchesQuery
  })

  return (
    <div className="space-y-6">
      {/* Title & Action */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <Users className="w-5 h-5 text-indigo-400" />
            <span>Clients Directory</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Manage corporate client accounts, billing records, and active engagements
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Add Client</span>
        </button>
      </div>

      {/* KPI Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Total Accounts</span>
          <p className="text-xl font-bold text-white mt-1">{clients.length}</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Active Engagements</span>
          <p className="text-xl font-bold text-indigo-400 mt-1">10 Projects</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Total Outstanding</span>
          <p className="text-xl font-bold text-rose-400 mt-1">$14,150.00</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Revenue Received</span>
          <p className="text-xl font-bold text-emerald-400 mt-1">$101,800.00</p>
        </div>
      </div>

      {/* Filter and Table Container */}
      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
        {/* Table Controls */}
        <div className="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div className="relative flex-1 max-w-sm">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search clients by name, contact or email..."
              className="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-slate-800 border border-slate-700/60 text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <div className="flex items-center gap-2">
            <Filter className="w-3.5 h-3.5 text-slate-400" />
            <select
              value={selectedGroup}
              onChange={(e) => setSelectedGroup(e.target.value)}
              className="px-3 py-1.5 text-xs rounded-xl bg-slate-800 border border-slate-700/60 text-slate-200 focus:outline-none"
            >
              <option value="All">All Customer Groups</option>
              <option value="Enterprise">Enterprise</option>
              <option value="VIP Corporate">VIP Corporate</option>
              <option value="Small Business">Small Business</option>
            </select>
          </div>
        </div>

        {/* Data Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Company Name</th>
                <th className="py-3 px-4">Primary Contact</th>
                <th className="py-3 px-4">Customer Group</th>
                <th className="py-3 px-4">Active Projects</th>
                <th className="py-3 px-4">Balance Due</th>
                <th className="py-3 px-4">Total Paid</th>
                <th className="py-3 px-4">Status</th>
                <th className="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredClients.map((c) => (
                <tr key={c.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-semibold text-white">
                    <div className="flex items-center gap-2.5">
                      <div className="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                        {c.name.charAt(0)}
                      </div>
                      <div>
                        <p className="font-semibold text-white">{c.name}</p>
                        <p className="text-[10px] text-slate-400">ID: #CL-{c.id}</p>
                      </div>
                    </div>
                  </td>
                  <td className="py-3 px-4">
                    <p className="text-slate-200 font-medium">{c.contact}</p>
                    <p className="text-[10px] text-slate-400">{c.email}</p>
                  </td>
                  <td className="py-3 px-4">
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-300">
                      {c.group}
                    </span>
                  </td>
                  <td className="py-3 px-4 font-mono font-medium text-slate-200">
                    {c.projectsCount} Projects
                  </td>
                  <td className="py-3 px-4 font-mono font-bold text-rose-400">
                    {c.dueAmount}
                  </td>
                  <td className="py-3 px-4 font-mono font-bold text-emerald-400">
                    {c.paidAmount}
                  </td>
                  <td className="py-3 px-4">
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                        c.status === 'Active'
                          ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                          : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                      }`}
                    >
                      {c.status}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <button className="px-2.5 py-1 text-[11px] font-semibold text-indigo-400 hover:text-white hover:bg-indigo-600/30 rounded-lg transition-colors cursor-pointer">
                      View Profile
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add Client Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Create Client Account</h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-slate-400 hover:text-white text-lg font-bold"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleAddClient} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Company / Client Name
                </label>
                <input
                  type="text"
                  required
                  value={newClientName}
                  onChange={(e) => setNewClientName(e.target.value)}
                  placeholder="e.g. Apex Global Retail"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Primary Contact Person
                </label>
                <input
                  type="text"
                  value={newContact}
                  onChange={(e) => setNewContact(e.target.value)}
                  placeholder="e.g. Marcus Vance"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Email
                  </label>
                  <input
                    type="email"
                    value={newEmail}
                    onChange={(e) => setNewEmail(e.target.value)}
                    placeholder="contact@company.com"
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Phone Number
                  </label>
                  <input
                    type="text"
                    value={newPhone}
                    onChange={(e) => setNewPhone(e.target.value)}
                    placeholder="+1 555-0192"
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Customer Group
                </label>
                <select
                  value={newGroup}
                  onChange={(e) => setNewGroup(e.target.value)}
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="Enterprise">Enterprise</option>
                  <option value="VIP Corporate">VIP Corporate</option>
                  <option value="Small Business">Small Business</option>
                </select>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white rounded-xl shadow-sm cursor-pointer"
                >
                  Save Account
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
