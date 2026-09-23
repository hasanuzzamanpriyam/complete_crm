import React, { useState, useMemo, useEffect } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import {
  Search,
  ChevronDown,
  ChevronRight,
  LogOut,
  Layers,
  Pin,
  PinOff,
  Sparkles,
  List,
  FolderTree,
} from 'lucide-react'
import { OLD_CRM_NAVIGATION, NAVIGATION_SECTIONS } from '../../config/navigation'
import { useAuthStore } from '../../store/useAuthStore'

export default function Sidebar({ isOpen, onClose }) {
  const [searchQuery, setSearchQuery] = useState('')
  const [expandedMenus, setExpandedMenus] = useState({})
  const [sidebarMode, setSidebarMode] = useState('categorized') // 'categorized' | 'classic'
  const [pinnedIds, setPinnedIds] = useState(() => {
    try {
      const saved = localStorage.getItem('tic_crm_pinned_menus')
      return saved ? JSON.parse(saved) : ['calendar', 'tasks', 'invoices', 'timesync']
    } catch {
      return ['calendar', 'tasks']
    }
  })

  const { user, logout } = useAuthStore()
  const location = useLocation()

  // Save pinned items to localStorage
  useEffect(() => {
    try {
      localStorage.setItem('tic_crm_pinned_menus', JSON.stringify(pinnedIds))
    } catch (e) {
      console.error(e)
    }
  }, [pinnedIds])

  const togglePin = (e, id) => {
    e.preventDefault()
    e.stopPropagation()
    setPinnedIds((prev) =>
      prev.includes(id) ? prev.filter((p) => p !== id) : [...prev, id]
    )
  }

  const toggleMenu = (title) => {
    setExpandedMenus((prev) => ({
      ...prev,
      [title]: !prev[title],
    }))
  }

  // Filter items in classic or categorized mode
  const filteredClassicItems = useMemo(() => {
    if (!searchQuery.trim()) return OLD_CRM_NAVIGATION
    const q = searchQuery.toLowerCase()

    return OLD_CRM_NAVIGATION.filter((item) => {
      if (item.title.toLowerCase().includes(q)) return true
      if (item.children) {
        return item.children.some(
          (c) =>
            c.title.toLowerCase().includes(q) ||
            (c.children && c.children.some((sc) => sc.title.toLowerCase().includes(q)))
        )
      }
      return false
    })
  }, [searchQuery])

  const filteredCategorizedSections = useMemo(() => {
    if (!searchQuery.trim()) return NAVIGATION_SECTIONS
    const q = searchQuery.toLowerCase()

    return NAVIGATION_SECTIONS.map((sec) => ({
      ...sec,
      items: sec.items.filter((item) => {
        if (item.title.toLowerCase().includes(q)) return true
        if (item.children) {
          return item.children.some(
            (c) =>
              c.title.toLowerCase().includes(q) ||
              (c.children && c.children.some((sc) => sc.title.toLowerCase().includes(q)))
          )
        }
        return false
      }),
    })).filter((sec) => sec.items.length > 0)
  }, [searchQuery])

  // Pinned items list
  const pinnedItems = useMemo(() => {
    return OLD_CRM_NAVIGATION.filter((item) => pinnedIds.includes(item.id))
  }, [pinnedIds])

  // Helper to render an item and recursive submenus
  const renderMenuItem = (item, level = 0) => {
    const Icon = item.icon
    const hasChildren = item.children && item.children.length > 0
    const isExpanded = expandedMenus[item.title] || searchQuery.length > 0
    const isPinned = pinnedIds.includes(item.id)

    // Check if any child is active
    const isAnyChildActive = hasChildren && (function checkActive(children) {
      return children.some((c) => {
        if (location.pathname === c.path) return true
        if (c.children) return checkActive(c.children)
        return false
      })
    })(item.children)

    if (hasChildren) {
      return (
        <div key={item.id || item.title} className="space-y-0.5">
          <button
            onClick={() => toggleMenu(item.title)}
            className={`w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-all group ${
              isAnyChildActive
                ? 'text-indigo-400 bg-indigo-500/10'
                : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
            }`}
          >
            <div className="flex items-center gap-2.5 overflow-hidden">
              {Icon && (
                <Icon
                  className={`w-4 h-4 shrink-0 transition-colors ${
                    isAnyChildActive ? 'text-indigo-400' : 'text-slate-400 group-hover:text-slate-200'
                  }`}
                />
              )}
              <span className="truncate">{item.title}</span>
            </div>

            <div className="flex items-center gap-1 shrink-0">
              {item.badge && (
                <span className="text-[10px] px-1.5 py-0.2 rounded-full font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                  {item.badge}
                </span>
              )}
              {level === 0 && (
                <span
                  onClick={(e) => togglePin(e, item.id)}
                  title={isPinned ? 'Unpin' : 'Pin to top'}
                  className={`p-1 rounded opacity-0 group-hover:opacity-100 hover:bg-slate-700/50 transition-opacity ${
                    isPinned ? 'opacity-100 text-amber-400' : 'text-slate-500'
                  }`}
                >
                  <Pin className="w-3 h-3" />
                </span>
              )}
              {isExpanded ? (
                <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
              ) : (
                <ChevronRight className="w-3.5 h-3.5 text-slate-400" />
              )}
            </div>
          </button>

          {isExpanded && (
            <div className="pl-6 pr-2 py-0.5 space-y-0.5 border-l border-slate-800/80 ml-4">
              {item.children.map((child) => renderMenuItem(child, level + 1))}
            </div>
          )}
        </div>
      )
    }

    return (
      <NavLink
        key={item.id || item.path || item.title}
        to={item.path}
        end={item.path === '/'}
        className={({ isActive }) =>
          `flex items-center justify-between px-3 py-1.5 text-xs font-medium rounded-lg transition-all group ${
            isActive
              ? 'text-white bg-gradient-to-r from-indigo-600 to-indigo-700 shadow-md shadow-indigo-600/20 font-semibold'
              : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
          }`
        }
      >
        <div className="flex items-center gap-2.5 overflow-hidden">
          {Icon && (
            <Icon
              className={`w-4 h-4 shrink-0 transition-colors ${
                location.pathname === item.path
                  ? 'text-white'
                  : 'text-slate-400 group-hover:text-slate-200'
              }`}
            />
          )}
          <span className="truncate">{item.title}</span>
        </div>

        <div className="flex items-center gap-1.5 shrink-0">
          {item.badge && (
            <span className="text-[10px] px-1.5 py-0.2 rounded-full font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
              {item.badge}
            </span>
          )}
          {level === 0 && (
            <span
              onClick={(e) => togglePin(e, item.id)}
              title={isPinned ? 'Unpin' : 'Pin to top'}
              className={`p-1 rounded opacity-0 group-hover:opacity-100 hover:bg-slate-700/50 transition-opacity ${
                isPinned ? 'opacity-100 text-amber-400' : 'text-slate-500'
              }`}
            >
              <Pin className="w-3 h-3" />
            </span>
          )}
        </div>
      </NavLink>
    )
  }

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
        <div className="flex items-center justify-between h-16 px-5 border-b border-slate-800 bg-slate-900/50 backdrop-blur">
          <div className="flex items-center gap-3">
            <div className="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 shadow-lg shadow-indigo-500/25">
              <Layers className="w-5 h-5 text-white" />
            </div>
            <div>
              <span className="font-bold text-base tracking-tight text-white flex items-center gap-1.5">
                TIC CRM
                <span className="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                  React
                </span>
              </span>
              <p className="text-[11px] text-slate-400">Complete ERP Suite</p>
            </div>
          </div>
        </div>

        {/* Search Bar & Mode Switcher */}
        <div className="p-3 border-b border-slate-800/80 space-y-2">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search 45+ CRM modules..."
              className="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-slate-800/80 border border-slate-700/60 text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all"
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

          <div className="flex items-center justify-between text-[11px] px-1 text-slate-400">
            <span className="font-medium">Sidebar Layout:</span>
            <div className="inline-flex rounded-lg bg-slate-800/80 p-0.5 border border-slate-700/60">
              <button
                onClick={() => setSidebarMode('categorized')}
                className={`px-2 py-0.5 rounded text-[10px] font-semibold transition-colors flex items-center gap-1 ${
                  sidebarMode === 'categorized'
                    ? 'bg-indigo-600 text-white'
                    : 'text-slate-400 hover:text-slate-200'
                }`}
              >
                <FolderTree className="w-3 h-3" />
                <span>Grouped</span>
              </button>
              <button
                onClick={() => setSidebarMode('classic')}
                className={`px-2 py-0.5 rounded text-[10px] font-semibold transition-colors flex items-center gap-1 ${
                  sidebarMode === 'classic'
                    ? 'bg-indigo-600 text-white'
                    : 'text-slate-400 hover:text-slate-200'
                }`}
              >
                <List className="w-3 h-3" />
                <span>Classic</span>
              </button>
            </div>
          </div>
        </div>

        {/* Navigation Scroll Area */}
        <div className="flex-1 overflow-y-auto px-3 py-3 space-y-4">
          {/* Pinned Shortcuts Section (Old Sidebar Feature) */}
          {pinnedItems.length > 0 && !searchQuery && (
            <div className="space-y-1 pb-2 border-b border-slate-800/60">
              <div className="px-3 pb-1 text-[10px] font-bold tracking-wider text-amber-400/90 uppercase flex items-center gap-1.5">
                <Pin className="w-3 h-3" />
                <span>Pinned Shortcuts</span>
              </div>
              {pinnedItems.map((item) => renderMenuItem(item))}
            </div>
          )}

          {/* Categorized Mode */}
          {sidebarMode === 'categorized' && (
            <div className="space-y-5">
              {filteredCategorizedSections.map((sec, idx) => (
                <div key={sec.title || idx} className="space-y-1">
                  <div className="px-3 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
                    {sec.title}
                  </div>
                  {sec.items.map((item) => renderMenuItem(item))}
                </div>
              ))}
            </div>
          )}

          {/* Classic Old Sidebar Mode (1-to-1 Sequence Match) */}
          {sidebarMode === 'classic' && (
            <div className="space-y-1">
              <div className="px-3 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
                All Modules (Old System Order)
              </div>
              {filteredClassicItems.map((item) => renderMenuItem(item))}
            </div>
          )}
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
