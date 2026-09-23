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
