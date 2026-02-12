<script setup lang="ts">
import { useTemplateRef, h, ref, watch, resolveComponent, computed } from "vue";
import { DateFormatter } from "@internationalized/date";
import { titleCase } from "scule";
import type { TableColumn } from "@nuxt/ui";
import { usePeriodStore } from "../stores/period"; // Pastikan store ini ada dan sesuai
import { useAuthStore } from "../stores/auth";
import { api } from "../services/api";
import { useRoute, useRouter } from "vue-router";
import AttendanceUploadModal from "../components/periods/AttendanceUploadModal.vue";

// --- STORES ---
const periodStore = usePeriodStore();
const authStore = useAuthStore();

// --- COMPONENTS ---
const UButton = resolveComponent("UButton");
const UBadge = resolveComponent("UBadge");
const UDropdownMenu = resolveComponent("UDropdownMenu");
const UCheckbox = resolveComponent("UCheckbox");

// --- UTILS & REFS ---
const toast = useToast();
const table = useTemplateRef("table");
const formatDate = new DateFormatter("id-ID", { dateStyle: "medium" });

// --- STATE ---
const page = ref(1);
const pageCount = ref(10);
const sorting = ref([]);
const search = ref("");
const userRole = authStore.user?.role || "guest";
const periodDesc = ref("");
const showUploadModal = ref(false);

// Attendance state
const route = useRoute();
const router = useRouter();
const periodId = ref<string | null>((route.query.period_id as string) || null);
const attendanceRecords = ref<any[]>([]);
const loading = ref(false);

// --- COMPUTED ---
// Format data attendance untuk tampilan tabel
const rows = computed(() => {
  return attendanceRecords.value.map((r) => ({
    id: r.id,
    employee_name: r.employee?.name || "-",
    sick: Number(r.sick) || 0,
    work_accident: Number(r.work_accident) || 0,
    permit: Number(r.permit) || 0,
    awol: Number(r.awol) || 0,
    late_permit: Number(r.late_permit) || 0,
    early_leave: Number(r.early_leave) || 0,
    annual_leave: Number(r.annual_leave) || 0,
    late: Number(r.late) || 0,
    warning_letter_1: Number(r.warning_letter_1) || 0,
    warning_letter_2: Number(r.warning_letter_2) || 0,
    warning_letter_3: Number(r.warning_letter_3) || 0,
    subordinate_late: Number(r.subordinate_late) || 0,
    subordinate_awol: Number(r.subordinate_awol) || 0,
  }));
});

// --- ACTIONS ---
function openCreateModal() {
  // noop for attendance page
}

function openEditModal(period: any) {
  // noop for attendance page
}

async function handleDelete(period: any) {
  // not applicable here
}

function openUploadModal() {
  showUploadModal.value = true;
}

// --- TABLE CONFIG ---
const columnFilters = ref([]);
const columnVisibility = ref();
const rowSelection = ref({});

const attendancePagination = ref({ page: 1, total: 0, per_page: 10 });

// Menu Dropdown per Baris
function getRowItems(row: any) {
  const items: any[] = [{ type: "label", label: "Actions" }];

  // Hanya Admin/HR yang bisa edit/delete periode
  if (["admin", "hr", "hr_manager"].includes(userRole)) {
    items.push(
      {
        label: "Attendance",
        icon: "i-lucide-list-check",
        class: "cursor-pointer",
        onSelect: () => {
          router.push(`/attendance?period_id=${row.original.id}`);
        },
      },

      {
        label: "Edit",
        icon: "i-lucide-edit-2",
        class: "cursor-pointer",
        onSelect: () => openEditModal(row.original),
      },
      {
        label: "Delete",
        icon: "i-lucide-trash",
        class: "cursor-pointer",
        color: "error",
        onSelect: () => handleDelete(row.original),
      }
    );
  }

  return items;
}

// Definisi Kolom Tabel (Attendance per employee)
const columns: TableColumn<any>[] = [
  {
    accessorKey: "employee_name",
    header: "Employee",
    cell: ({ row }) =>
      h("span", { class: "font-medium" }, row.original.employee_name),
  },
  { accessorKey: "sick", header: "Sick", cell: ({ row }) => row.original.sick },
  {
    accessorKey: "work_accident",
    header: "Work Accident",
    cell: ({ row }) => row.original.work_accident,
  },
  {
    accessorKey: "permit",
    header: "Permit",
    cell: ({ row }) => row.original.permit,
  },
  { accessorKey: "awol", header: "AWOL", cell: ({ row }) => row.original.awol },
  {
    accessorKey: "late_permit",
    header: "Late Permit",
    cell: ({ row }) => row.original.late_permit,
  },
  {
    accessorKey: "early_leave",
    header: "Early Leave",
    cell: ({ row }) => row.original.early_leave,
  },
  {
    accessorKey: "annual_leave",
    header: "Annual Leave",
    cell: ({ row }) => row.original.annual_leave,
  },
  { accessorKey: "late", header: "Late", cell: ({ row }) => row.original.late },
  {
    accessorKey: "warning_letter_1",
    header: "Warning Letter 1",
    cell: ({ row }) => row.original.warning_letter_1,
  },
  {
    accessorKey: "warning_letter_2",
    header: "Warning Letter 2",
    cell: ({ row }) => row.original.warning_letter_2,
  },
  {
    accessorKey: "warning_letter_3",
    header: "Warning Letter 3",
    cell: ({ row }) => row.original.warning_letter_3,
  },
  {
    accessorKey: "subordinate_late",
    header: "Subordinate Late",
    cell: ({ row }) => row.original.subordinate_late,
  },
  {
    accessorKey: "subordinate_awol",
    header: "Subordinate AWOL",
    cell: ({ row }) => row.original.subordinate_awol,
  },
];

// --- LOAD DATA ---
async function loadData() {
  const sort = sorting.value[0];

  console.log("Loading attendance for period:", periodId.value);
  if (!periodId.value) {
    attendanceRecords.value = [];
    attendancePagination.value.total = 0;
    return;
  }

  loading.value = true;
  try {
    await periodStore.fetchPeriod(periodId.value);
    periodDesc.value = periodStore.period.description || "";
    console.log("Loaded period:", periodDesc.value);

    const response = await api.get("/attendance-records", {
      params: {
        period_id: periodId.value,
        page: page.value,
        per_page: pageCount.value,
        search: search.value,
      },
    });

    attendanceRecords.value = response.data.data;
    attendancePagination.value.page =
      response.data.meta?.current_page || page.value;
    attendancePagination.value.total = response.data.meta?.total || 0;
    attendancePagination.value.per_page =
      response.data.meta?.per_page || pageCount.value;
  } catch (error) {
    console.error("Error fetching attendance records:", error);
    toast.add({
      title: "Error",
      description: "Failed to load attendance records",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
}

function onPageChange(newPage: number) {
  page.value = newPage;
  loadData();
}

// Watchers
watch(sorting, () => {
  page.value = 1;
  loadData();
});

watch(
  () => route.query.period_id,
  (p) => {
    periodId.value = (p as string) || null;
    loadData();
  }
);

// Initial Load
loadData();
</script>

<template>
  <UDashboardPanel id="periods">
    <template #header>
      <UDashboardNavbar>
        <template #title>
          <span class="font-bold">Attendance Records {{ periodDesc }}</span>
        </template>

        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <UButton
            v-if="['admin', 'hr', 'hr_manager'].includes(userRole)"
            label="Upload Attendance"
            icon="i-lucide-upload"
            class="cursor-pointer"
            @click="openUploadModal"
          />

          <UButton
            label="Back"
            icon="i-lucide-arrow-left"
            class="cursor-pointer"
            @click="router.back()"
          />

          <AttendanceUploadModal v-model="showUploadModal" :periodId="periodId" @saved="loadData" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="flex flex-wrap items-center justify-between gap-1.5 mb-4">
        <UInput
          v-model="search"
          class="max-w-sm"
          icon="i-lucide-search"
          placeholder="Search..."
          @change="loadData"
        />

        <div class="flex flex-wrap items-center gap-1.5">
          <div v-if="['admin', 'hr'].includes(userRole)"></div>

          <UDropdownMenu
            :items="table?.tableApi?.getAllColumns()
            .filter((c: any) => c.getCanHide())
            .map((c: any) => ({
                label: titleCase(c.id),
                type: 'checkbox',
                checked: c.getIsVisible(),
                onUpdateChecked: (val: boolean) => table?.tableApi?.getColumn(c.id)?.toggleVisibility(val)
            }))
            "
            :content="{ align: 'end' }"
          >
            <UButton
              label="Display"
              color="neutral"
              variant="outline"
              trailing-icon="i-lucide-settings-2"
              class="cursor-pointer"
            />
          </UDropdownMenu>
        </div>
      </div>

      <UTable
        ref="table"
        v-model:column-filters="columnFilters"
        v-model:column-visibility="columnVisibility"
        v-model:row-selection="rowSelection"
        v-model:sorting="sorting"
        class="shrink-0"
        :data="rows"
        :columns="columns"
        :loading="loading"
        :ui="{
          base: 'table-fixed border-separate border-spacing-0',
          thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
          tbody: '[&>tr]:last:[&>td]:border-b-0',
          th: 'py-2 first:rounded-l-lg last:rounded-r-lg border-y border-default first:border-l last:border-r',
          td: 'border-b border-default',
        }"
      />

      <div
        class="flex items-center justify-between gap-3 border-t border-default pt-4 mt-auto"
      >
        <div class="text-sm text-muted">
          Showing {{ (page - 1) * pageCount + 1 }} to
          {{ Math.min(page * pageCount, attendancePagination.total || 0) }}
          of {{ attendancePagination.total || 0 }} results
        </div>

        <div class="flex items-center gap-1.5">
          <UPagination
            v-model="page"
            :page-count="pageCount"
            :total="attendancePagination.total || 0"
            class="[&_button]:!cursor-pointer [&_button]:!pointer-events-auto"
            @update:page="onPageChange"
          />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>