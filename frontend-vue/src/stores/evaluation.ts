import { defineStore } from 'pinia'
import { authApi, api } from '../services/api'
import {
  parseDate,
  DateFormatter
} from "@internationalized/date";
import { format } from 'date-fns/fp/format';

export interface Evaluation {
    id: string;
    period_id: string;
    employee_id: string;
    evaluator_id: string;
    period_start: string;
    period_end: string;
    end_contract_date: string;
    evaluation_purpose: string;
    question_1: number;
    question_2: number;
    question_3: number;
    question_4: number;
    question_5: number;
    question_6: number;
    question_7: number;
    question_8: number;
    question_9: number;
    question_10: number;
    comments?: string;
}

const formatDate = new DateFormatter("id-ID", { dateStyle: "medium" });

export const useEvaluationStore = defineStore('evaluation', {
    state: () => ({
        evaluations: [] as Evaluation[],
        evaluation: null as Evaluation | null,
        loading: false,
        error: null as string | null,

        pagination: {
            page: 1,
            total: 0,
            perPage: 10
        }
    }),

    getters: {
        // Helper untuk dropdown (format { label, id })
        evaluationOptions: (state) => state.evaluations.map(e => ({
            label: `${formatDate.format(new Date(e.period_start))} - ${formatDate.format(new Date(e.period_end))}`,
            id: e.id
        }))
    },

    actions: {
        // 1. GET ALL (List Evaluations)
        // Menggunakan endpoint ReferenceController yang sudah dibuat
        async fetchEvaluations(params = {}) {
            this.loading = true
            this.error = null
            try {
                // params bisa berisi { search: 'budi' }
                const response = await api.get('/evaluations', { params })
                this.evaluations = response.data.data
                console.log('Fetched Evaluations:', this.evaluations)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil data evaluasi'
                console.error('Fetch Evaluations error:', error)
            } finally {
                this.loading = false
            }
        },

        // 2. GET SINGLE (Detail Evaluation)
        // Menggunakan endpoint ReferenceController
        async fetchEvaluation(id: string) {
            this.loading = true
            this.error = null
            try {
                const response = await api.get(`/evaluations/${id}`)
                this.evaluation = response.data.data
                return response.data.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Gagal mengambil detail evaluasi'
                console.error('Fetch Evaluation Detail error:', error)
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
        async createEvaluation(payload: any) {
            this.loading = true
            try {
                const response = await api.post('/evaluations', payload)
                // Tambahkan ke list state local agar tidak perlu fetch ulang
                this.evaluations.push(response.data)
                return response.data
            } catch (error: any) {
                console.error('Create Evaluation error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 5. UPDATE (Manual)
        async updateEvaluation(id: string, payload: any) {
            this.loading = true
            try {
                const response = await api.put(`/evaluations/${id}`, payload)

                // Update state local
                const index = this.evaluations.findIndex(e => e.id === id)
                if (index !== -1) {
                    this.evaluations[index] = response.data
                }
                this.evaluation = response.data // Update current view juga

                return response.data
            } catch (error: any) {
                console.error('Update Evaluation error:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        // 6. DELETE (Manual)
        async deleteEvaluation(id: string) {
            this.loading = true
            try {
                await api.delete(`/evaluations/${id}`)
                // Hapus dari state local
                this.evaluations = this.evaluations.filter(e => e.id !== id)
            } catch (error: any) {
                console.error('Delete Evaluation error:', error)
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})