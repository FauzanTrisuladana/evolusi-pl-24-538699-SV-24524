export interface LinkItem {
  id: number
  name: string
  target_url: string
  custom_alias: string
  short_url: string
  is_active: boolean
}

export interface ApiResponse<T> {
  status: 'success' | 'error' | string
  message: string
  data: T
}

export interface ApiErrorResponse {
  message: string
  errors?: Record<string, string[]>
}

export interface LinkStats {
  active_links_count: number
  total_links_count: number
}

export interface CreateLinkPayload {
  name: string
  target_url: string
  custom_alias?: string
  is_active?: boolean
}

export interface UpdateLinkPayload {
  name: string
  target_url: string
  custom_alias: string
  is_active?: boolean
}

export interface ShortenLinkPayload {
  target_url: string
  custom_alias?: string
}
