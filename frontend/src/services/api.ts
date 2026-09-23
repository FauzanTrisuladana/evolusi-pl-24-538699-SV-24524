import type {
  ApiResponse,
  CreateLinkPayload,
  LinkItem,
  LinkStats,
  ShortenLinkPayload,
  UpdateLinkPayload,
} from '../types/link'
import { buildApiUrl } from '../utils/linkLogic'

export class ApiError extends Error {
  public status: number
  public errors?: Record<string, string[]>

  constructor(message: string, status: number, errors?: Record<string, string[]>) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

export function getBaseApiUrl(): string {
  const envUrl = import.meta.env.VITE_API_URL
  if (!envUrl) {
    return 'http://localhost:8000/api'
  }
  return envUrl.replace(/\/+$/, '')
}

async function request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const baseUrl = getBaseApiUrl()
  const cleanEndpoint = endpoint.replace(/^\/+/, '')
  const url = `${baseUrl}/${cleanEndpoint}`

  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  }

  if (options.body && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
  }

  const response = await fetch(url, {
    ...options,
    headers,
  })

  let payload: Record<string, unknown> | null = null
  const contentType = response.headers.get('content-type')
  if (contentType && contentType.includes('application/json')) {
    try {
      payload = (await response.json()) as Record<string, unknown>
    } catch {
      payload = null
    }
  }

  if (!response.ok) {
    const errorMessage =
      (typeof payload?.message === 'string' && payload.message) ||
      `Permintaan gagal dengan status ${response.status} (${response.statusText})`
    const errors =
      payload?.errors && typeof payload.errors === 'object'
        ? (payload.errors as Record<string, string[]>)
        : undefined
    throw new ApiError(errorMessage, response.status, errors)
  }

  return payload as T
}

export const linkApiService = {
  /**
   * Get filtered links list.
   */
  async getLinks(params?: { search?: string; status?: string }): Promise<ApiResponse<LinkItem[]>> {
    const endpoint = buildApiUrl('', 'links', params)
    return request<ApiResponse<LinkItem[]>>(endpoint, { method: 'GET' })
  },

  /**
   * Get single link details by ID.
   */
  async getLink(id: number | string): Promise<ApiResponse<LinkItem>> {
    return request<ApiResponse<LinkItem>>(`links/${id}`, { method: 'GET' })
  },

  /**
   * Create a new link with full attributes.
   */
  async createLink(payload: CreateLinkPayload): Promise<ApiResponse<LinkItem>> {
    return request<ApiResponse<LinkItem>>('links', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  },

  /**
   * Update an existing link.
   */
  async updateLink(id: number | string, payload: UpdateLinkPayload): Promise<ApiResponse<LinkItem>> {
    return request<ApiResponse<LinkItem>>(`links/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
  },

  /**
   * Delete a link.
   */
  async deleteLink(id: number | string): Promise<{ status: string; message: string }> {
    return request<{ status: string; message: string }>(`links/${id}`, {
      method: 'DELETE',
    })
  },

  /**
   * Quick shorten for public/guest users.
   */
  async shortenLink(payload: ShortenLinkPayload): Promise<ApiResponse<LinkItem>> {
    return request<ApiResponse<LinkItem>>('links/shorten', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  },

  /**
   * Get links statistics (active links count & total links count).
   */
  async getStats(): Promise<ApiResponse<LinkStats>> {
    return request<ApiResponse<LinkStats>>('links/stats/count', {
      method: 'GET',
    })
  },
}
