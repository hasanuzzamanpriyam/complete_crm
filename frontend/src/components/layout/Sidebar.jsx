import React, { useState, useMemo } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import {
  Search,
  ChevronDown,
  ChevronRight,
  LogOut,
  Building,
  Sparkles,
  Layers,
} from 'lucide-react'
import { NAVIGATION_SECTIONS } from '../../config/navigation'
import { useAuthStore } from '../../store/useAuthStore'

export default function Sidebar({ isOpen, onClose }) {
  const [searchQuery, setSearchQuery] = useState('')
  const [expandedSections, setExpandedSections] = useState({})
  const { user, logout } = useAuthStore()
  const location = useLocation()

  const toggleSubMenu = (title) => {
    setExpandedSections((prev) => ({
      ...prev,
      [title]: !prev[title],
    }))
  }

  // Filter navigation items by search query
  const filteredSections = useMemo(() => {
    if (!searchQuery.trim()) return NAVIGATION_SECTIONS
    const q = searchQuery.toLowerCase()

    return NAVIGATION_SECTIONS.map((section) => {
      const filteredItems = section.items
        .map((item) => {
          if (item.children) {
            const matchedChildren = item.children.filter((c) =>
              c.title.toLowerCase().includes(q)
            )
            if (matchedChildren.length > 0 || item.title.toLowerCase().includes(q)) {
              return { ...item, children: matchedChildren.length ? matchedChildren : item.children }
            }
            return null
          }
          if (item.title.toLowerCase().includes(q)) {
            return item
          }
          return null
        })
        .filter(Boolean)

      return { ...section, items: filteredItems }
    }).filter((section) => section.items.length > 0)
  }, [searchQuery])

  return (
    <>
      {/* Mobile backdrop */}
      {isOpen && (
        <div
          className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
          onClick={onClose}
        />
      )}

      <aside
        className={`fixed top-0 bottom-0 left-0 z-50 flex flex-col w-72 bg-slate-900 border-r border-slate-800 transition-transform duration-300 ease-in-out lg:translate-x-0 ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        {/* Brand Header */}
        <div className="flex items-center justify-between h-16 px-6 border-b border-slate-800 bg-slate-900/50 backdrop-blur">
          <div className="flex items-center gap-3">
            <div className="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 shadow-lg shadow-indigo-500/25">
              <Layers className="w-5 h-5 text-white" />
            </div>
            <div>
              <span className="font-bold text-lg tracking-tight text-white flex items-center gap-1.5">
                TIC CRM
                <span className="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                  React
                </span>
              </span>
              <p className="text-xs text-slate-400">Enterprise Edition</p>
            </div>
          </div>
        </div>

        {/* Search Bar */}
        <div className="p-4 border-b border-slate-800/80">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Quick search menu..."
              className="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg bg-slate-800/80 border border-slate-700/60 text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery('')}
                className="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-white"
              >
                ×
              </button>
            )}
          </div>
        </div>

        {/* Navigation Menu List */}
        <div className="flex-1 overflow-y-auto px-3 py-4 space-y-6">
          {filteredSections.map((section, sIdx) => (
            <div key={section.title || sIdx} className="space-y-1">
              <div className="px-3 pb-1 text-[11px] font-bold tracking-wider text-slate-400 uppercase">
                {section.title}
              </div>

              {section.items.map((item) => {
                const Icon = item.icon
                const isSubMenu = !!item.children
                const isExpanded = expandedSections[item.title] || searchQuery.length > 0
                const isChildActive = item.children?.some(
                  (c) => location.pathname === c.path
                )

                if (isSubMenu) {
                  return (
                    <div key={item.title} className="space-y-0.5">
                      <button
                        onClick={() => toggleSubMenu(item.title)}
                        className={`w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors group ${
                          isChildActive
                            ? 'text-indigo-400 bg-indigo-500/10'
                            : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                        }`}
                      >
                        <div className="flex items-center gap-3">
                          {Icon && (
                            <Icon
                              className={`w-4 h-4 transition-colors ${
                                isChildActive
                                  ? 'text-indigo-400'
                                  : 'text-slate-400 group-hover:text-slate-200'
                              }`}
                            />
                          )}
                          <span>{item.title}</span>
                        </div>
                        {isExpanded ? (
                          <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
                        ) : (
                          <ChevronRight className="w-3.5 h-3.5 text-slate-400" />
                        )}
                      </button>

                      {isExpanded && (
                        <div className="pl-9 pr-2 py-1 space-y-0.5 border-l border-slate-800 ml-5">
                          {item.children.map((child) => (
                            <NavLink
                              key={child.path}
                              to={child.path}
                              className={({ isActive }) =>
                                `block px-2.5 py-1.5 text-xs rounded-md transition-all ${
                                  isActive
                                    ? 'font-medium text-white bg-indigo-600 shadow-sm shadow-indigo-600/30'
                                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
                                }`
                              }
                            >
                              {child.title}
                            </NavLink>
                          ))}
                        </div>
                      )}
                    </div>
                  )
                }

                return (
                  <NavLink
                    key={item.path}
                    to={item.path}
                    end={item.path === '/'}
                    className={({ isActive }) =>
                      `flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-all group ${
                        isActive
                          ? 'text-white bg-gradient-to-r from-indigo-600 to-indigo-700 shadow-md shadow-indigo-600/20 font-semibold'
                          : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                      }`
                    }
                  >
                    <div className="flex items-center gap-3">
                      {Icon && (
                        <Icon
                          className={`w-4 h-4 transition-colors ${
                            location.pathname === item.path
                              ? 'text-white'
                              : 'text-slate-400 group-hover:text-slate-200'
                          }`}
                        />
                      )}
                      <span>{item.title}</span>
                    </div>
                    {item.badge && (
                      <span className="text-[10px] px-1.5 py-0.5 rounded-full font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {item.badge}
                      </span>
                    )}
                  </NavLink>
                )
              })}
            </div>
          ))}
        </div>

        {/* User Profile & Logout Footer */}
        <div className="p-3 border-t border-slate-800 bg-slate-900/60">
          <div className="flex items-center justify-between p-2 rounded-xl bg-slate-800/60 border border-slate-700/40">
            <div className="flex items-center gap-2.5 overflow-hidden">
              <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs shrink-0">
                {user?.full_name ? user.full_name.charAt(0).toUpperCase() : 'U'}
              </div>
              <div className="truncate">
                <p className="text-xs font-semibold text-slate-100 truncate">
                  {user?.full_name || user?.username || 'Administrator'}
                </p>
                <p className="text-[10px] text-slate-400 capitalize truncate">
                  {user?.role || 'Staff User'}
                </p>
              </div>
            </div>

            <button
              onClick={logout}
              title="Logout"
              className="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
            >
              <LogOut className="w-4 h-4" />
            </button>
          </div>
        </div>
      </aside>
    </>
  )
}
