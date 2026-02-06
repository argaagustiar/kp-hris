import { defineStore } from 'pinia'
import { authApi, api } from '../services/api'

export interface Employee {
    id: string;
    name: string;
    employee_code: string;
    email: string;
    position_id: string;
    join_date: string;
    end_contract_date: string;
    is_active: boolean;
    position?: { id: string; title: string };
    departments?: any[];
    managers?: any[];
}

export const useEmployeeStore = defineStore('employee', {
    state: () => ({
        employees: [] as Employee[], // List karyawan untuk tabel/dropdown
        employee: null as Employee | null, // Detail karyawan saat diedit/view
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
        // Helper untuk mengambil karyawan yang aktif saja
        activeEmployees: (state) => state.employees.filter(e => e.is_active),

        // Helper untuk dropdown (format { label, value })
        employeeOptions: (state) => state.employees.map(e => ({
            label: `${e.name} - ${e.employee_code}`,
            value: e.id
        }))
    },

    actions: {
        // 1. GET ALL (List Karyawan)
        // Menggunakan endpoint ReferenceController yang sudah dibuat
        async fetchEmployees(params = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await api.get('/employees', { params })
                this.employees = response.data.data

                if (response.data.meta) {
                    this.pagination.page = response.data.meta.current_page
                    this.pagination.perPage = response.data.meta.per_page
                    this.pagination.total = response.data.meta.total
                }
            
                console.log('Fetched Employees:', this.employees)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil data karyawan'
                console.error('Fetch Employees error:', error)
            } finally {
                this.loading = false
            }
        },

        // 2. GET SINGLE (Detail Karyawan)
        // Menggunakan endpoint ReferenceController
        async fetchEmployee(id: string) {
            this.loading = true
            this.error = null
            try {
                const response = await api.get(`/employees/${id}`)
                this.employee = response.data.data
                return response.data.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil detail karyawan'
                console.error('Fetch Employee Detail error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 3. SYNC FROM 3RD PARTY (Fitur Tambahan)
        // Jika Anda membuat endpoint khusus untuk memicu command sync:employees
        async syncEmployees() {
            this.loading = true
            try {
                // Asumsi Anda membuat route: Route::post('sync/employees', ...)
                const response = await api.post('/sync/employees')

                // Refresh list setelah sync selesai
                await this.fetchEmployees()

                return response.data
            } catch (error: any) {
                console.error('Sync error:', error)
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
        async createEmployee(payload: any) {
            this.loading = true
            try {
                const response = await api.post('/employees', payload)
                // Tambahkan ke list state local agar tidak perlu fetch ulang
                this.employees.push(response.data)
                return response.data
            } catch (error: any) {
                console.error('Create Employee error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 5. UPDATE (Manual)
        async updateEmployee(id: string, payload: any) {
            this.loading = true
            try {
                const response = await api.put(`/employees/${id}`, payload)

                // Update state local
                const index = this.employees.findIndex(e => e.id === id)
                if (index !== -1) {
                    this.employees[index] = response.data
                }
                this.employee = response.data // Update current view juga

                return response.data
            } catch (error: any) {
                console.error('Update Employee error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 6. DELETE (Manual)
        async deleteEmployee(id: string) {
            this.loading = true
            try {
                await api.delete(`/employees/${id}`)
                // Hapus dari state local
                this.employees = this.employees.filter(e => e.id !== id)
                toast.add({
                    title: 'Customer deleted',
                    description: 'The customer has been deleted.'
                })
            } catch (error: any) {
                console.error('Delete Employee error:', error)
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})