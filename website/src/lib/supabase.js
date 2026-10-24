import { createClient } from '@supabase/supabase-js'

// These are the public project URL and publishable key. They are safe to ship in the browser
// (the database only allows what its row level security policies permit), so the site works
// on any host without setting environment variables. Override them with NEXT_PUBLIC_SUPABASE_* if needed.
const url = process.env.NEXT_PUBLIC_SUPABASE_URL || 'https://pktnqerzatrdndhbtsqp.supabase.co'
const key = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY || 'sb_publishable_r9C0dMpa0Djj4Hf4tyn_rw_Nptd2QdD'

// When the env values are missing the site still works from the bundled data files.
export const supabase = url && key ? createClient(url, key, { auth: { persistSession: false } }) : null
