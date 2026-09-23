import { describe, it, expect } from 'vitest'
import {
  isValidUrl,
  isValidAlias,
  buildApiUrl,
  calculateStats,
} from '../utils/linkLogic'
import type { LinkItem } from '../types/link'

describe('Link Logic & Validation Utilities', () => {
  describe('isValidUrl', () => {
    it('mengembalikan true untuk URL http dan https yang valid', () => {
      expect(isValidUrl('https://laravel.com')).toBe(true)
      expect(isValidUrl('http://example.com/some/path?query=1#hash')).toBe(true)
      expect(isValidUrl('https://sub.domain.co.id/test-url')).toBe(true)
    })

    it('mengembalikan false untuk string kosong, tanpa protokol, atau URL tidak valid', () => {
      expect(isValidUrl('')).toBe(false)
      expect(isValidUrl('   ')).toBe(false)
      expect(isValidUrl('bukan-url')).toBe(false)
      expect(isValidUrl('ftp://ftp.example.com')).toBe(false)
      expect(isValidUrl('javascript:alert(1)')).toBe(false)
      expect(isValidUrl('www.google.com')).toBe(false) // tanpa http(s)://
    })
  })

  describe('isValidAlias', () => {
    it('mengembalikan true untuk alias alpha_dash yang valid', () => {
      expect(isValidAlias('my-link')).toBe(true)
      expect(isValidAlias('my_link_123')).toBe(true)
      expect(isValidAlias('Campaign2026')).toBe(true)
      expect(isValidAlias('')).toBe(true) // opsional
    })

    it('mengembalikan false jika mengandung spasi atau karakter khusus ilegal', () => {
      expect(isValidAlias('my link')).toBe(false)
      expect(isValidAlias('link/path')).toBe(false)
      expect(isValidAlias('link?query')).toBe(false)
      expect(isValidAlias('link@promo')).toBe(false)
      expect(isValidAlias('link#section')).toBe(false)
    })
  })

  describe('buildApiUrl', () => {
    it('menggabungkan base URL dan endpoint dengan benar tanpa double slash', () => {
      const url = buildApiUrl('http://localhost:8000/api/', '/links/')
      expect(url).toBe('http://localhost:8000/api/links')
    })

    it('menambahkan query parameter dengan benar', () => {
      const url = buildApiUrl('http://localhost:8000/api', 'links', {
        search: 'promo',
        status: 'active',
      })
      expect(url).toBe('http://localhost:8000/api/links?search=promo&status=active')
    })

    it('mengabaikan parameter kosong, undefined, atau null', () => {
      const url = buildApiUrl('http://localhost:8000/api', 'links', {
        search: '',
        status: undefined,
        limit: '10',
      })
      expect(url).toBe('http://localhost:8000/api/links?limit=10')
    })
  })

  describe('calculateStats', () => {
    it('menghitung jumlah link aktif dan total link dengan akurat', () => {
      const sampleLinks: LinkItem[] = [
        {
          id: 1,
          name: 'Link 1',
          target_url: 'https://example1.com',
          custom_alias: 'link1',
          short_url: 'http://localhost/link1',
          is_active: true,
        },
        {
          id: 2,
          name: 'Link 2',
          target_url: 'https://example2.com',
          custom_alias: 'link2',
          short_url: 'http://localhost/link2',
          is_active: false,
        },
        {
          id: 3,
          name: 'Link 3',
          target_url: 'https://example3.com',
          custom_alias: 'link3',
          short_url: 'http://localhost/link3',
          is_active: true,
        },
      ]

      const stats = calculateStats(sampleLinks)
      expect(stats.total_links_count).toBe(3)
      expect(stats.active_links_count).toBe(2)
    })

    it('menangani array kosong dengan aman', () => {
      const stats = calculateStats([])
      expect(stats.total_links_count).toBe(0)
      expect(stats.active_links_count).toBe(0)
    })
  })
})
