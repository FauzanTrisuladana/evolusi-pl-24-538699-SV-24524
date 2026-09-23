import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { linkApiService, ApiError, getBaseApiUrl } from '../services/api'

describe('Link API Service (Mocked Fetch - Standalone without Laravel running)', () => {
  const originalFetch = global.fetch

  beforeEach(() => {
    vi.restoreAllMocks()
  })

  afterEach(() => {
    global.fetch = originalFetch
  })

  it('getLinks: berhasil mengambil daftar link dari API dengan header Accept application/json', async () => {
    const mockResponseData = {
      status: 'success',
      message: 'Daftar link berhasil diambil.',
      data: [
        {
          id: 1,
          name: 'Promo Link',
          target_url: 'https://example.com/promo',
          custom_alias: 'promo',
          short_url: 'http://localhost/promo',
          is_active: true,
        },
      ],
    }

    const mockFetch = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => mockResponseData,
    })
    global.fetch = mockFetch

    const result = await linkApiService.getLinks({ search: 'promo', status: 'active' })

    expect(mockFetch).toHaveBeenCalledTimes(1)
    const [calledUrl, calledOptions] = mockFetch.mock.calls[0]
    expect(calledUrl).toContain('/links?search=promo&status=active')
    expect(calledOptions.method).toBe('GET')
    expect(calledOptions.headers.Accept).toBe('application/json')
    expect(result.data).toHaveLength(1)
    expect(result.data[0].name).toBe('Promo Link')
  })

  it('createLink: mengirim data payload JSON dan mengembalikan link baru yang dibuat', async () => {
    const mockPayload = {
      name: 'New Campaign',
      target_url: 'https://campaign.com',
      custom_alias: 'campaign-alias',
      is_active: true,
    }

    const mockResponseData = {
      status: 'success',
      message: 'URL berhasil diperpendek!',
      data: {
        id: 10,
        ...mockPayload,
        short_url: 'http://localhost/campaign-alias',
      },
    }

    const mockFetch = vi.fn().mockResolvedValue({
      ok: true,
      status: 201,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => mockResponseData,
    })
    global.fetch = mockFetch

    const result = await linkApiService.createLink(mockPayload)

    expect(mockFetch).toHaveBeenCalledTimes(1)
    const [calledUrl, calledOptions] = mockFetch.mock.calls[0]
    expect(calledUrl).toContain('/links')
    expect(calledOptions.method).toBe('POST')
    expect(calledOptions.headers['Content-Type']).toBe('application/json')
    expect(JSON.parse(calledOptions.body)).toEqual(mockPayload)
    expect(result.data.id).toBe(10)
    expect(result.data.custom_alias).toBe('campaign-alias')
  })

  it('createLink: melempar ApiError dengan pesan dan dictionary errors jika backend mengembalikan HTTP 422', async () => {
    const errorPayload = {
      message: 'Data tidak valid.',
      errors: {
        name: ['Nama link wajib diisi.'],
        target_url: ['URL tujuan wajib diisi.'],
      },
    }

    const mockFetch = vi.fn().mockResolvedValue({
      ok: false,
      status: 422,
      statusText: 'Unprocessable Content',
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => errorPayload,
    })
    global.fetch = mockFetch

    await expect(
      linkApiService.createLink({ name: '', target_url: '' })
    ).rejects.toThrow(ApiError)

    try {
      await linkApiService.createLink({ name: '', target_url: '' })
    } catch (err: unknown) {
      expect(err).toBeInstanceOf(ApiError)
      const apiErr = err as ApiError
      expect(apiErr.status).toBe(422)
      expect(apiErr.errors?.name).toContain('Nama link wajib diisi.')
    }
  })

  it('getStats: berhasil mengambil statistik total dan link aktif', async () => {
    const mockStatsResponse = {
      status: 'success',
      message: 'Statistik link berhasil diambil.',
      data: {
        active_links_count: 5,
        total_links_count: 8,
      },
    }

    const mockFetch = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => mockStatsResponse,
    })
    global.fetch = mockFetch

    const result = await linkApiService.getStats()
    expect(result.data.active_links_count).toBe(5)
    expect(result.data.total_links_count).toBe(8)
  })

  it('deleteLink: berhasil menghapus link dengan metode HTTP DELETE', async () => {
    const mockDeleteResponse = {
      status: 'success',
      message: 'Link berhasil dihapus!',
    }

    const mockFetch = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => mockDeleteResponse,
    })
    global.fetch = mockFetch

    const result = await linkApiService.deleteLink(5)
    expect(mockFetch).toHaveBeenCalledTimes(1)
    const [calledUrl, calledOptions] = mockFetch.mock.calls[0]
    expect(calledUrl).toContain('/links/5')
    expect(calledOptions.method).toBe('DELETE')
    expect(result.message).toBe('Link berhasil dihapus!')
  })

  it('getBaseApiUrl: melempar Error jika VITE_API_URL kosong atau tidak didefinisikan (tanpa fallback hardcoded link)', () => {
    const originalEnv = import.meta.env.VITE_API_URL
    try {
      // @ts-expect-error testing missing environment
      import.meta.env.VITE_API_URL = ''
      expect(() => getBaseApiUrl()).toThrow('VITE_API_URL tidak ditemukan')
    } finally {
      // @ts-expect-error restoring environment
      import.meta.env.VITE_API_URL = originalEnv
    }
  })

  it('getBaseApiUrl: mengembalikan URL dari VITE_API_URL dan memotong trailing slash', () => {
    const originalEnv = import.meta.env.VITE_API_URL
    try {
      // @ts-expect-error testing custom environment
      import.meta.env.VITE_API_URL = 'https://custom-api.example.com/api/'
      expect(getBaseApiUrl()).toBe('https://custom-api.example.com/api')
    } finally {
      // @ts-expect-error restoring environment
      import.meta.env.VITE_API_URL = originalEnv
    }
  })
})
