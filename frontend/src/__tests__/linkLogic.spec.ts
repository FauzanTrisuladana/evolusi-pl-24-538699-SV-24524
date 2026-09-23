import { describe, it, expect } from 'vitest'
import {
  isValidUrl,
  isValidAlias,
  buildApiUrl,
  calculateStats,
  validateLinkInput,
  filterLinks,
  truncateText,
} from '../utils/linkLogic'
import type { LinkItem } from '../types/link'

describe('Logika Aplikasi Frontend Shortener (Unit Test Mandiri Tanpa Panggil API)', () => {
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
      expect(isValidUrl('www.google.com')).toBe(false)
    })
  })

  describe('isValidAlias', () => {
    it('mengembalikan true untuk alias alpha_dash yang valid', () => {
      expect(isValidAlias('my-link')).toBe(true)
      expect(isValidAlias('my_link_123')).toBe(true)
      expect(isValidAlias('Campaign2026')).toBe(true)
      expect(isValidAlias('')).toBe(true)
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

  describe('validateLinkInput', () => {
    it('mengembalikan isValid: true jika input lengkap dan valid', () => {
      const result = validateLinkInput({
        name: 'My Valid Link',
        target_url: 'https://vuejs.org',
        custom_alias: 'vue-valid',
      })
      expect(result.isValid).toBe(true)
      expect(Object.keys(result.errors)).toHaveLength(0)
    })

    it('mendeteksi error jika nama kosong atau melebihi 255 karakter', () => {
      const emptyName = validateLinkInput({
        name: '',
        target_url: 'https://vuejs.org',
      })
      expect(emptyName.isValid).toBe(false)
      expect(emptyName.errors.name).toBe('Nama link wajib diisi.')

      const longName = validateLinkInput({
        name: 'a'.repeat(256),
        target_url: 'https://vuejs.org',
      })
      expect(longName.isValid).toBe(false)
      expect(longName.errors.name).toBe('Nama link maksimal 255 karakter.')
    })

    it('mendeteksi error jika URL tujuan kosong atau format tidak valid', () => {
      const emptyUrl = validateLinkInput({
        name: 'Valid Name',
        target_url: '',
      })
      expect(emptyUrl.isValid).toBe(false)
      expect(emptyUrl.errors.target_url).toBe('URL tujuan wajib diisi.')

      const invalidUrl = validateLinkInput({
        name: 'Valid Name',
        target_url: 'not-a-valid-url',
      })
      expect(invalidUrl.isValid).toBe(false)
      expect(invalidUrl.errors.target_url).toContain('harus dimulai dengan http:// atau https://')
    })

    it('mendeteksi error jika custom alias mengandung karakter terlarang', () => {
      const invalidAlias = validateLinkInput({
        name: 'Valid Name',
        target_url: 'https://vuejs.org',
        custom_alias: 'alias with space',
      })
      expect(invalidAlias.isValid).toBe(false)
      expect(invalidAlias.errors.custom_alias).toContain('hanya boleh berisi huruf, angka')
    })
  })

  describe('filterLinks', () => {
    const mockLinks: LinkItem[] = [
      {
        id: 1,
        name: 'Laravel Documentation',
        target_url: 'https://laravel.com/docs',
        custom_alias: 'laravel-docs',
        short_url: 'http://localhost/laravel-docs',
        is_active: true,
      },
      {
        id: 2,
        name: 'Vue Guide',
        target_url: 'https://vuejs.org/guide',
        custom_alias: 'vue-guide',
        short_url: 'http://localhost/vue-guide',
        is_active: false,
      },
      {
        id: 3,
        name: 'PHP Manual',
        target_url: 'https://php.net/manual',
        custom_alias: 'php-man',
        short_url: 'http://localhost/php-man',
        is_active: true,
      },
    ]

    it('memfilter link berdasarkan query pencarian (nama, alias, atau URL)', () => {
      const searchByName = filterLinks(mockLinks, 'Laravel')
      expect(searchByName).toHaveLength(1)
      expect(searchByName[0].name).toBe('Laravel Documentation')

      const searchByAlias = filterLinks(mockLinks, 'vue-guide')
      expect(searchByAlias).toHaveLength(1)
      expect(searchByAlias[0].custom_alias).toBe('vue-guide')

      const searchByUrl = filterLinks(mockLinks, 'php.net')
      expect(searchByUrl).toHaveLength(1)
      expect(searchByUrl[0].name).toBe('PHP Manual')
    })

    it('memfilter link berdasarkan status aktif / tidak aktif', () => {
      const activeOnly = filterLinks(mockLinks, undefined, 'active')
      expect(activeOnly).toHaveLength(2)
      expect(activeOnly.every((l) => l.is_active)).toBe(true)

      const inactiveOnly = filterLinks(mockLinks, undefined, 'inactive')
      expect(inactiveOnly).toHaveLength(1)
      expect(inactiveOnly[0].name).toBe('Vue Guide')
    })

    it('mengembalikan semua link jika pencarian kosong dan status "all"', () => {
      const allLinks = filterLinks(mockLinks, '', 'all')
      expect(allLinks).toHaveLength(3)
    })
  })

  describe('truncateText', () => {
    it('tidak memotong teks jika panjangnya kurang dari batas maksimal', () => {
      expect(truncateText('Short text', 50)).toBe('Short text')
    })

    it('memotong teks dan menambahkan ellipsis (...) jika melebihi batas maksimal', () => {
      const longText = 'https://example.com/very/long/destination/url/that/exceeds/the/limit'
      const truncated = truncateText(longText, 25)
      expect(truncated).toBe('https://example.com/very/...')
      expect(truncated.endsWith('...')).toBe(true)
    })

    it('mengembalikan string kosong jika input falsy', () => {
      expect(truncateText('')).toBe('')
    })
  })
})
