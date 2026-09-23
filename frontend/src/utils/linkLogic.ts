import type { LinkItem, LinkStats } from '../types/link'

/**
 * Validates whether a given string is a valid HTTP/HTTPS URL.
 */
export function isValidUrl(url: string): boolean {
  if (!url || typeof url !== 'string') return false
  const trimmed = url.trim()
  try {
    const parsed = new URL(trimmed)
    return parsed.protocol === 'http:' || parsed.protocol === 'https:'
  } catch {
    return false
  }
}

/**
 * Validates whether a custom alias matches the alpha_dash pattern (letters, numbers, dashes, underscores).
 */
export function isValidAlias(alias: string): boolean {
  if (!alias) return true // optional
  const regex = /^[a-zA-Z0-9_-]+$/
  return regex.test(alias.trim())
}

/**
 * Builds an API URL by cleanly combining the base URL, endpoint path, and query parameters.
 */
export function buildApiUrl(
  base: string,
  endpoint: string,
  params?: Record<string, string | undefined>
): string {
  const cleanBase = (base || '').replace(/\/+$/, '')
  const cleanEndpoint = (endpoint || '').replace(/^\/+|\/+$/g, '')
  const fullUrl = cleanEndpoint ? `${cleanBase}/${cleanEndpoint}` : cleanBase

  if (!params) return fullUrl

  const searchParams = new URLSearchParams()
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== null && value !== '') {
      searchParams.append(key, value)
    }
  }

  const queryString = searchParams.toString()
  return queryString ? `${fullUrl}?${queryString}` : fullUrl
}

/**
 * Calculates summary statistics (active links count and total links count) from an array of links.
 */
export function calculateStats(links: LinkItem[]): LinkStats {
  const safeLinks = Array.isArray(links) ? links : []
  const activeCount = safeLinks.filter((item) => item && item.is_active === true).length

  return {
    active_links_count: activeCount,
    total_links_count: safeLinks.length,
  }
}

/**
 * Validates link input payload before submission.
 */
export function validateLinkInput(data: {
  name: string
  target_url: string
  custom_alias?: string
}): { isValid: boolean; errors: Record<string, string> } {
  const errors: Record<string, string> = {}

  if (!data.name || !data.name.trim()) {
    errors.name = 'Nama link wajib diisi.'
  } else if (data.name.trim().length > 255) {
    errors.name = 'Nama link maksimal 255 karakter.'
  }

  if (!data.target_url || !data.target_url.trim()) {
    errors.target_url = 'URL tujuan wajib diisi.'
  } else if (!isValidUrl(data.target_url)) {
    errors.target_url = 'Format URL tujuan tidak valid (harus dimulai dengan http:// atau https://).'
  }

  if (data.custom_alias && !isValidAlias(data.custom_alias)) {
    errors.custom_alias = 'Alias khusus hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).'
  }

  return {
    isValid: Object.keys(errors).length === 0,
    errors,
  }
}

/**
 * Filters an array of links based on search query and status.
 */
export function filterLinks(
  links: LinkItem[],
  search?: string,
  status?: string
): LinkItem[] {
  if (!Array.isArray(links)) return []

  let filtered = [...links]

  if (search && search.trim()) {
    const q = search.trim().toLowerCase()
    filtered = filtered.filter(
      (link) =>
        link.name.toLowerCase().includes(q) ||
        link.custom_alias.toLowerCase().includes(q) ||
        link.target_url.toLowerCase().includes(q)
    )
  }

  if (status && status !== 'all') {
    if (status === 'active') {
      filtered = filtered.filter((link) => link.is_active === true)
    } else if (status === 'inactive') {
      filtered = filtered.filter((link) => link.is_active === false)
    }
  }

  return filtered
}

/**
 * Truncates text if it exceeds a specified maximum length.
 */
export function truncateText(text: string, maxLength = 50): string {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}
