import React, { useState } from 'react'
import {
  FileSpreadsheet,
  Plus,
  Download,
  Trash2,
  Calculator,
  RefreshCw,
  Table,
  Save,
  Check,
} from 'lucide-react'

export default function SpreadsheetPage() {
  const initialRows = 10
  const initialCols = 6
  const colHeaders = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']

  const [data, setData] = useState([
    ['Item / Service', 'Quantity', 'Unit Rate ($)', 'Tax Rate', 'Subtotal ($)', 'Status'],
    ['Cloud Migration Sprint 1', '40', '150', '15%', '6000', 'Approved'],
    ['Dedicated Server Hosting', '12', '240', '15%', '2880', 'Active'],
    ['Database Optimization', '25', '120', '15%', '3000', 'Pending'],
    ['Security Audit & Pentest', '1', '4500', '0%', '4500', 'Completed'],
    ['TimeSync Pro Licenses', '50', '12', '15%', '600', 'Renewed'],
    ['', '', '', '', '', ''],
    ['', '', '', '', '', ''],
  ])

  const [selectedCell, setSelectedCell] = useState({ row: 0, col: 0 })
  const [formulaValue, setFormulaValue] = useState(data[0][0])
  const [savedNotification, setSavedNotification] = useState(false)

  const handleCellChange = (rowIndex, colIndex, value) => {
    const nextData = data.map((row, r) =>
      row.map((cell, c) => (r === rowIndex && c === colIndex ? value : cell))
    )
    setData(nextData)
    if (selectedCell.row === rowIndex && selectedCell.col === colIndex) {
      setFormulaValue(value)
    }
  }

  const handleSelect = (r, c) => {
    setSelectedCell({ row: r, col: c })
    setFormulaValue(data[r]?.[c] || '')
  }

  const handleFormulaChange = (val) => {
    setFormulaValue(val)
    handleCellChange(selectedCell.row, selectedCell.col, val)
  }

  const addRow = () => {
    const newRow = new Array(data[0].length).fill('')
    setData([...data, newRow])
  }

  const exportCSV = () => {
    const csvContent =
      'data:text/csv;charset=utf-8,' + data.map((e) => e.join(',')).join('\n')
    const encodedUri = encodeURI(csvContent)
    const link = document.createElement('a')
    link.setAttribute('href', encodedUri)
    link.setAttribute('download', 'crm_worksheet_export.csv')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  const handleSave = () => {
    setSavedNotification(true)
    setTimeout(() => setSavedNotification(false), 2500)
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <FileSpreadsheet className="w-6 h-6 text-emerald-400" />
            <span>Enterprise Spreadsheet & Worksheets</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Real-time interactive tabular grid: formulate cost breakdowns, budgets, and project financial records.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={addRow}
            className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 transition-colors flex items-center gap-2"
          >
            <Plus className="w-4 h-4" />
            <span>Add Row</span>
          </button>
          <button
            onClick={exportCSV}
            className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 transition-colors flex items-center gap-2"
          >
            <Download className="w-4 h-4" />
            <span>Export CSV</span>
          </button>
          <button
            onClick={handleSave}
            className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2"
          >
            {savedNotification ? <Check className="w-4 h-4" /> : <Save className="w-4 h-4" />}
            <span>{savedNotification ? 'Saved to DB' : 'Save Worksheet'}</span>
          </button>
        </div>
      </div>

      {/* Formula Bar */}
      <div className="p-3 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center gap-3">
        <div className="px-2.5 py-1 rounded bg-slate-800 border border-slate-700 text-xs font-mono font-bold text-indigo-400">
          {colHeaders[selectedCell.col] || 'A'}
          {selectedCell.row + 1}
        </div>
        <div className="flex items-center gap-1.5 text-xs text-slate-500 font-mono">
          <span>fx</span>
          <span>=</span>
        </div>
        <input
          type="text"
          value={formulaValue}
          onChange={(e) => handleFormulaChange(e.target.value)}
          placeholder="Enter formula or value..."
          className="flex-1 bg-transparent border-none text-xs text-slate-200 focus:outline-none font-mono"
        />
      </div>

      {/* Spreadsheet Grid */}
      <div className="rounded-xl bg-slate-900/80 border border-slate-800 overflow-x-auto shadow-2xl">
        <table className="w-full border-collapse text-xs">
          <thead>
            <tr className="bg-slate-800/80 text-slate-400 font-mono text-[11px]">
              <th className="w-10 p-2 border-r border-b border-slate-700/60 text-center">#</th>
              {data[0]?.map((_, c) => (
                <th
                  key={c}
                  className="p-2 border-r border-b border-slate-700/60 font-bold text-slate-300 min-w-[120px] text-center"
                >
                  {colHeaders[c] || `C${c}`}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.map((row, r) => (
              <tr key={r} className="hover:bg-slate-800/20">
                <td className="p-2 border-r border-b border-slate-800 bg-slate-800/40 text-center font-mono text-[10px] text-slate-500 select-none">
                  {r + 1}
                </td>
                {row.map((cell, c) => {
                  const isSelected = selectedCell.row === r && selectedCell.col === c
                  return (
                    <td
                      key={c}
                      onClick={() => handleSelect(r, c)}
                      className={`p-0 border-r border-b border-slate-800 transition-colors ${
                        isSelected
                          ? 'ring-2 ring-indigo-500 ring-inset bg-indigo-500/10'
                          : r === 0
                          ? 'bg-slate-800/20 font-bold text-white'
                          : ''
                      }`}
                    >
                      <input
                        type="text"
                        value={cell}
                        onChange={(e) => handleCellChange(r, c, e.target.value)}
                        className={`w-full px-2.5 py-1.5 bg-transparent border-none focus:outline-none text-xs ${
                          r === 0 ? 'font-bold text-slate-100' : 'text-slate-300'
                        }`}
                      />
                    </td>
                  )
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
