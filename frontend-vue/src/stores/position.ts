import { defineStore } from 'pinia'
import { authApi, api } from '../services/api'

export interface Position {
    id: string;
    title: string;
    level: number;
    employees_count?: number;
}

export const usePositionStore = defineStore('position', {
    state: () => ({
        positions: [] as Position[], // List posisi untuk tabel/dropdown
        position: null as Position | null, // Detail posisi saat diedit/view
        loading: false,
        error: null as string | null,

        // Pagination (Jika nanti backend support pagination)
        pagination: {
            page: 1,
            total: 0,
            perPage: 10
        }
    }),

    getters: {
        // Helper untuk dropdown (format { label, id })
        positionOptions: (state) => state.positions.map(p => ({
            label: `${p.title} (Level ${p.level})`,
            id: p.id
        }))
    },

    actions: {
        // 1. GET ALL (List Positions)
        // Menggunakan endpoint ReferenceController yang sudah dibuat
        async fetchPositions(params = {}) {
            this.loading = true
            this.error = null
            try {
                // params bisa berisi { search: 'budi' }
                const response = await api.get('/positions', { params })
                this.positions = response.data.data
                console.log('Fetched Positions:', this.positions)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil data posisi'
                console.error('Fetch Positions error:', error)
            } finally {
                this.loading = false
            }
        },

        // 2. GET SINGLE (Detail Position)
        // Menggunakan endpoint ReferenceController
        async fetchPosition(id: string) {
            this.loading = true
            this.error = null
            try {
                const response = await api.get(`/positions/${id}`)
                this.position = response.data
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil detail posisi'
                console.error('Fetch Position Detail error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        /**
         * NOTE: Action Create/Update/Delete di bawah ini OPTIONAL.
         * Karena strategi kita adalah "Sync dari Pihak Ketiga", biasanya 
         * kita tidak mengedit data master karyawan secara manual di aplikasi ini.
         * Tapi jika butuh edit manual, berikut implementasinya:
         */

        // 4. CREATE (Manual)
        async createPosition(payload: any) {
            this.loading = true
            try {
                const response = await api.post('/positions', payload)
                // Tambahkan ke list state local agar tidak perlu fetch ulang
                this.positions.push(response.data)
                return response.data
            } catch (error: any) {
                console.error('Create Position error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 5. UPDATE (Manual)
        async updatePosition(id: string, payload: any) {
            this.loading = true
            try {
                const response = await api.put(`/positions/${id}`, payload)

                // Update state local
                const index = this.positions.findIndex(p => p.id === id)
                if (index !== -1) {
                    this.positions[index] = response.data
                }
                this.position = response.data // Update current view juga

                return response.data
            } catch (error: any) {
                console.error('Update Position error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 6. DELETE (Manual)
        async deletePosition(id: string) {
            this.loading = true
            try {
                await api.delete(`/positions/${id}`)
                // Hapus dari state local
                this.positions = this.positions.filter(p => p.id !== id)
            } catch (error: any) {
                console.error('Delete Position error:', error)
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})