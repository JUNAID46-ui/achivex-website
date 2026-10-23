import { useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { Plus } from 'lucide-react'

export default function FAQ({ items }) {
  const [open, setOpen] = useState(0)
  return (
    <div className="divide-y divide-white/15 border-y border-white/15">
      {items.map((item, i) => {
        const isOpen = open === i
        return (
          <div key={item.q}>
            <h3>
              <button
                className="flex w-full items-center justify-between gap-6 py-6 text-left text-lg font-semibold text-white transition-colors hover:text-accent"
                aria-expanded={isOpen}
                onClick={() => setOpen(isOpen ? -1 : i)}
              >
                {item.q}
                <Plus className={`h-5 w-5 shrink-0 text-accent transition-transform duration-300 ${isOpen ? 'rotate-45' : ''}`} aria-hidden="true" />
              </button>
            </h3>
            <AnimatePresence initial={false}>
              {isOpen && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: 'auto', opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  transition={{ duration: 0.3 }}
                  className="overflow-hidden"
                >
                  <p className="max-w-3xl pb-6 text-[16px] leading-relaxed text-body/80">{item.a}</p>
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        )
      })}
    </div>
  )
}
