import { defineStore } from 'pinia'
import { authApi, api } from '../services/api'

export interface Department {
    id: string;
    name: string;
    parent: { id: string; name: string } | null;
    employees_count?: number;
}

export const useDepartmentStore = defineStore('department', {
    state: () => ({
        departments: [] as Department[], // List departemen untuk tabel/dropdown
        department: null as Department | null, // Detail departemen saat diedit/view
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
        departmentOptions: (state) => state.departments.map(d => ({
            label: d.name,
            id: d.id
        }))
    },

    actions: {
        // 1. GET ALL (List Departments)
        // Menggunakan endpoint ReferenceController yang sudah dibuat
        async fetchDepartments(params = {}) {
            this.loading = true
            this.error = null
            try {
                // params bisa berisi { search: 'budi' }
                const response = await api.get('/departments', { params })
                this.departments = response.data.data
                console.log('Fetched Departments:', this.departments)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil data departemen'
                console.error('Fetch Departments error:', error)
            } finally {
                this.loading = false
            }
        },

        // 2. GET SINGLE (Detail Department)
        // Menggunakan endpoint ReferenceController
        async fetchDepartment(id: string) {
            this.loading = true
            this.error = null
            try {
                const response = await api.get(`/departments/${id}`)
                this.department = response.data
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil detail departemen'
                console.error('Fetch Department Detail error:', error)
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
        async createDepartment(payload: any) {
            this.loading = true
            try {
                const response = await api.post('/departments', payload)
                // Tambahkan ke list state local agar tidak perlu fetch ulang
                this.departments.push(response.data)
                return response.data
            } catch (error: any) {
                console.error('Create Department error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 5. UPDATE (Manual)
        async updateDepartment(id: string, payload: any) {
            this.loading = true
            try {
                const response = await api.put(`/departments/${id}`, payload)

                // Update state local
                const index = this.departments.findIndex(p => p.id === id)
                if (index !== -1) {
                    this.departments[index] = response.data
                }
                this.department = response.data // Update current view juga

                return response.data
            } catch (error: any) {
                console.error('Update Department error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 6. DELETE (Manual)
        async deleteDepartment(id: string) {
            this.loading = true
            try {
                await api.delete(`/departments/${id}`)
                // Hapus dari state local
                this.departments = this.departments.filter(p => p.id !== id)
            } catch (error: any) {
                console.error('Delete Department error:', error)
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})