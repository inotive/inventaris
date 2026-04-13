<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import Modal from "@/Components/common/Modal.vue";
import FormSelect from "@/Components/form/Select.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { Head, useForm, router, usePage } from "@inertiajs/vue3";
import { computed, reactive, ref, watch, onMounted, nextTick } from "vue";

// Permission helper
const can = (permission) => {
    return usePage().props.auth?.permissions?.includes(permission) ?? false;
};

const breadcrumbs = [{ label: "Absensi" }, { label: "Jadwal Kerja & Libur" }];

const props = defineProps({
    month: { type: String, default: new Date().toISOString().slice(0, 7) },
    daysInMonth: { type: Number, default: 31 },
    employees: { type: [Array, Object], default: () => [] },
    shifts: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    isSuperadmin: { type: Boolean, default: false },
});

const days = computed(() => {
    // props.month format: YYYY-MM
    const [y, m] = props.month.split("-").map((n) => parseInt(n, 10));
    const last = new Date(y, m, 0);
    const arr = [];
    for (let d = 1; d <= last.getDate(); d++) {
        const date = new Date(y, m - 1, d);
        const dayIdx = date.getDay(); // 0 Sun ... 6 Sat
        const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        arr.push({
            d,
            label: dayNames[dayIdx],
            iso: `${props.month}-${String(d).padStart(2, "0")}`,
        });
    }

// Handlers for Pagination component
function handlePageChanged(newPage) {
    if (isServerPaginated.value) {
        page.value = newPage;
        fetchFromServer({ page: newPage });
    } else {
        page.value = newPage;
    }
}

function handlePerPageChanged(newPerPage) {
    perPage.value = newPerPage;
    if (isServerPaginated.value) {
        page.value = 1;
        fetchFromServer({ page: 1, per_page: newPerPage });
    } else {
        page.value = 1;
    }
}
    return arr;
});

defineOptions({ layout: AppLayout });

// Modal state and form for Tambah Libur
const showModal = ref(false);
const form = useForm({
    name: "",
    dates: "", // comma- or newline-separated list
    department_id: null,
    all_employees: false,
    employee_ids: [],
});

// Get employee data array from paginated object or array
const employeeData = computed(() => {
    if (Array.isArray(props.employees)) {
        return props.employees;
    }
    return props.employees?.data || [];
});

const employeeOptions = computed(() =>
    employeeData.value.map((e) => ({ value: e.id, label: e.name }))
);

// UI state: search, filter, grouping, pagination
const search = ref(props.filters?.search || "");
const showFilter = ref(false);
const showBranchFilter = ref(false);
const showStatusFilter = ref(false);
const showGroupMenu = ref(false);
const selectedDept = ref(props.filters?.department_id || null);
const selectedBranch = ref(props.filters?.branch_id || null);
const selectedStatus = ref(props.filters?.status || null);
const groupBy = ref(null); // null, 'department', 'position'
// If using server-side paginator, sync with backend current_page / per_page
const isServerPaginated = computed(
    () => !Array.isArray(props.employees) && !!props.employees?.data
);
const page = ref(
    isServerPaginated.value && props.employees.current_page
        ? props.employees.current_page
        : 1
);
const perPage = ref(
    props.filters?.per_page ||
        (isServerPaginated.value && props.employees.per_page)
        || 50
);

// Base index for "No" column (so numbering continues across pages)
const baseRowIndex = computed(() => {
    if (isServerPaginated.value && props.employees?.current_page && props.employees?.per_page) {
        return (props.employees.current_page - 1) * props.employees.per_page;
    }
    return (page.value - 1) * perPage.value;
});

// Helper function to close all filter dropdowns except the one being opened
function closeAllFilters(except = null) {
    if (except !== 'branch') showBranchFilter.value = false;
    if (except !== 'dept') showFilter.value = false;
    if (except !== 'status') showStatusFilter.value = false;
    if (except !== 'group') showGroupMenu.value = false;
}

// Filtered departments based on selected branch
const filteredDepartments = computed(() => {
    if (!selectedBranch.value) {
        return props.departments;
    }
    return props.departments.filter(dept => dept.branch_id === selectedBranch.value);
});

// Use server-side pagination data if available
const filteredEmployees = computed(() => {
    // If using server-side pagination, return data as-is (server sudah filter)
    if (isServerPaginated.value) {
        return employeeData.value;
    }

    // Otherwise, do client-side filtering
    const term = search.value.trim().toLowerCase();
    return employeeData.value.filter((e) => {
        const matchTerm =
            !term ||
            (e.name && e.name.toLowerCase().includes(term)) ||
            (e.department && e.department.toLowerCase().includes(term));
        const matchDept =
            !selectedDept.value || e.department_id === selectedDept.value;
        const matchBranch =
            !selectedBranch.value || e.branch_id === selectedBranch.value;
        const matchStatus =
            !selectedStatus.value || e.status === selectedStatus.value;
        return matchTerm && matchDept && matchBranch && matchStatus;
    });
});

// Use server-side pagination if available
const totalPages = computed(() => {
    if (isServerPaginated.value && props.employees.last_page) {
        return props.employees.last_page;
    }
    return Math.max(1, Math.ceil(filteredEmployees.value.length / perPage.value));
});

const startIdx = computed(() => (page.value - 1) * perPage.value);
const endIdx = computed(() =>
    Math.min(filteredEmployees.value.length, startIdx.value + perPage.value)
);

// Group employees based on groupBy
const groupedEmployees = computed(() => {
    if (!groupBy.value) {
        return null; // No grouping
    }

    const groups = {};

    filteredEmployees.value.forEach(emp => {
        let groupKey = '';
        let groupLabel = '';

        if (groupBy.value === 'department') {
            groupKey = emp.department || 'Tidak Ada Departemen';
            groupLabel = groupKey;
        } else if (groupBy.value === 'position') {
            groupKey = emp.role || 'Tidak Ada Jabatan';
            groupLabel = groupKey;
        }

        if (!groups[groupKey]) {
            groups[groupKey] = {
                label: groupLabel,
                employees: []
            };
        }

        groups[groupKey].employees.push(emp);
    });

    // Convert to array and sort
    return Object.keys(groups)
        .sort()
        .map(key => groups[key]);
});

const pagedEmployees = computed(() => {
    // If grouping is enabled, return grouped structure
    if (groupBy.value && groupedEmployees.value) {
        return groupedEmployees.value;
    }

    // If using server-side pagination, data is already paginated
    if (isServerPaginated.value) {
        return filteredEmployees.value;
    }
    // Otherwise, do client-side pagination
    return filteredEmployees.value.slice(startIdx.value, endIdx.value);
});

// Create pagination object compatible with Pagination component for client-side mode
const paginationData = computed(() => {
    const currentPage = page.value;
    const total = filteredEmployees.value.length;
    const lastPage = totalPages.value;
    const from = total > 0 ? startIdx.value + 1 : 0;
    const to = endIdx.value;

    // Build links array for pagination component
    const links = [];

    // Previous button
    links.push({
        url: currentPage > 1 ? `?page=${currentPage - 1}` : null,
        label: '&laquo; Previous',
        active: false,
    });

    // Page numbers
    const maxVisiblePages = 7;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(lastPage, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (startPage > 1) {
        links.push({ url: '?page=1', label: '1', active: false });
        if (startPage > 2) {
            links.push({ url: null, label: '...', active: false });
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        links.push({
            url: `?page=${i}`,
            label: String(i),
            active: i === currentPage,
        });
    }

    if (endPage < lastPage) {
        if (endPage < lastPage - 1) {
            links.push({ url: null, label: '...', active: false });
        }
        links.push({ url: `?page=${lastPage}`, label: String(lastPage), active: false });
    }

    // Next button
    links.push({
        url: currentPage < lastPage ? `?page=${currentPage + 1}` : null,
        label: 'Next &raquo;',
        active: false,
    });

    return {
        current_page: currentPage,
        data: pagedEmployees.value,
        first_page_url: '?page=1',
        from: from,
        last_page: lastPage,
        last_page_url: `?page=${lastPage}`,
        links: links,
        next_page_url: currentPage < lastPage ? `?page=${currentPage + 1}` : null,
        path: window.location.pathname,
        per_page: perPage.value,
        prev_page_url: currentPage > 1 ? `?page=${currentPage - 1}` : null,
        to: to,
        total: total,
    };
});

// Choose which pagination object to feed into Pagination component
const pagination = computed(() => {
    if (isServerPaginated.value) {
        return props.employees;
    }
    return paginationData.value;
});

// Check if dropdown should open upward (for last 2 rows)
const shouldDropUp = (empIndex) => {
    const totalEmployees = pagedEmployees.value.length;
    // If in last 2 rows, open upward
    return empIndex >= totalEmployees - 2;
};

// Get dropdown position for teleported dropdown
const getDropdownPosition = (empId, day, empIndex) => {
    // Find the button element
    const buttonId = `shift-btn-${empId}-${day}`;
    const button = document.getElementById(buttonId);

    if (!button) {
        return { top: '0px', left: '0px' };
    }

    const rect = button.getBoundingClientRect();
    const dropUp = shouldDropUp(empIndex);

    if (dropUp) {
        // Position above button
        return {
            top: `${rect.top - 200}px`, // 200px is approximate dropdown height
            left: `${rect.left}px`
        };
    } else {
        // Position below button
        return {
            top: `${rect.bottom + 4}px`,
            left: `${rect.left}px`
        };
    }
};

// Reactive position for filter dropdowns
const filterDropdownPositions = ref({
    branch: { top: '0px', right: '0px' },
    dept: { top: '0px', right: '0px' },
    status: { top: '0px', right: '0px' },
    group: { top: '0px', right: '0px' },
});

// Update filter dropdown positions
function updateFilterDropdownPosition(filterType) {
    nextTick(() => {
        const buttons = document.querySelectorAll('[data-filter-button]');
        let targetButton = null;

        buttons.forEach(btn => {
            const text = btn.textContent || '';
            if (filterType === 'branch' && text.includes('Cabang:')) {
                targetButton = btn;
            } else if (filterType === 'dept' && text.includes('Departemen:')) {
                targetButton = btn;
            } else if (filterType === 'status' && text.includes('Status:')) {
                targetButton = btn;
            } else if (filterType === 'group' && text.includes('Kelompokkan:')) {
                targetButton = btn;
            }
        });

        if (targetButton) {
            const rect = targetButton.getBoundingClientRect();
            filterDropdownPositions.value[filterType] = {
                top: `${rect.bottom + 4}px`,
                right: `${window.innerWidth - rect.right}px`
            };
        }
    });
}

// Watch for filter dropdown state changes to update positions
watch(showBranchFilter, (isOpen) => {
    if (isOpen) updateFilterDropdownPosition('branch');
});

watch(showFilter, (isOpen) => {
    if (isOpen) updateFilterDropdownPosition('dept');
});

watch(showStatusFilter, (isOpen) => {
    if (isOpen) updateFilterDropdownPosition('status');
});

watch(showGroupMenu, (isOpen) => {
    if (isOpen) updateFilterDropdownPosition('group');
});

// Flag to prevent circular updates
const isUpdatingFromDept = ref(false);
const isUpdatingFromBranch = ref(false);

// Watch for department selection to auto-select branch
watch(selectedDept, (newDeptId) => {
    if (isUpdatingFromBranch.value) return;

    if (newDeptId) {
        const dept = props.departments.find(d => d.id === newDeptId);
        if (dept && dept.branch_id && selectedBranch.value !== dept.branch_id) {
            isUpdatingFromDept.value = true;
            selectedBranch.value = dept.branch_id;
            nextTick(() => {
                isUpdatingFromDept.value = false;
            });
        }
    }
});

// Watch for branch selection to clear department if it doesn't belong to selected branch
watch(selectedBranch, (newBranchId) => {
    if (isUpdatingFromDept.value) return;

    if (newBranchId && selectedDept.value) {
        const dept = props.departments.find(d => d.id === selectedDept.value);
        if (dept && dept.branch_id !== newBranchId) {
            isUpdatingFromBranch.value = true;
            selectedDept.value = null;
            nextTick(() => {
                isUpdatingFromBranch.value = false;
            });
        }
    }
});

// Helper to (re)load data from server with current filters and page
function fetchFromServer(extra = {}) {
    router.get(
        route("calendar.index"),
        {
            month: props.month,
            search: search.value || undefined,
            department_id: selectedDept.value || undefined,
            branch_id: selectedBranch.value || undefined,
            status: selectedStatus.value || undefined,
            per_page: perPage.value,
            page: page.value,
            ...extra,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

// Watch for filter changes and reload from server if using server-side pagination
watch([search, selectedDept, selectedBranch, selectedStatus, perPage], () => {
    if (isServerPaginated.value) {
        page.value = 1;
        fetchFromServer({ page: 1 });
    } else {
        // Client-side: just reset page
        page.value = 1;
    }
});

// Group by label
const groupByLabel = computed(() => {
    if (!groupBy.value) return "Tidak Dikelompokkan";
    if (groupBy.value === "department") return "Departemen";
    if (groupBy.value === "position") return "Jabatan";
    return "Tidak Dikelompokkan";
});

function submitHoliday() {
    form.post(route("calendar.holidays.store"), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            router.reload({ only: ["employees", "month", "daysInMonth"] });
        },
    });
}

// Month and Year selection
const currentMonth = ref(props.month.split("-")[1]);
const currentYear = ref(props.month.split("-")[0]);

const months = [
    { value: "01", label: "Januari" },
    { value: "02", label: "Februari" },
    { value: "03", label: "Maret" },
    { value: "04", label: "April" },
    { value: "05", label: "Mei" },
    { value: "06", label: "Juni" },
    { value: "07", label: "Juli" },
    { value: "08", label: "Agustus" },
    { value: "09", label: "September" },
    { value: "10", label: "Oktober" },
    { value: "11", label: "November" },
    { value: "12", label: "Desember" },
];

const years = computed(() => {
    const currentYearNum = new Date().getFullYear();
    const yearsList = [];
    for (let i = currentYearNum - 2; i <= currentYearNum + 2; i++) {
        yearsList.push(i.toString());
    }
    return yearsList;
});

function changeMonth() {
    const newMonth = `${currentYear.value}-${currentMonth.value}`;
    router.get(
        route("calendar.index", { month: newMonth }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
}

watch([currentMonth, currentYear], () => {
    changeMonth();
});

// Initialize Bootstrap tooltips
onMounted(() => {
    nextTick(() => {
        // Initialize all tooltips
        const tooltipTriggerList = document.querySelectorAll(
            '[data-bs-toggle="tooltip"]'
        );
        tooltipTriggerList.forEach((tooltipTriggerEl) => {
            new window.bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                trigger: "hover",
            });
        });
    });
});

// Re-initialize tooltips when data changes
watch(
    [pagedEmployees],
    () => {
        nextTick(() => {
            // Dispose old tooltips
            const tooltipTriggerList = document.querySelectorAll(
                '[data-bs-toggle="tooltip"]'
            );
            tooltipTriggerList.forEach((tooltipTriggerEl) => {
                const tooltip =
                    window.bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (tooltip) {
                    tooltip.dispose();
                }
            });

            // Re-initialize tooltips
            tooltipTriggerList.forEach((tooltipTriggerEl) => {
                new window.bootstrap.Tooltip(tooltipTriggerEl, {
                    html: true,
                    trigger: "hover",
                });
            });
        });
    },
    { deep: true }
);

// Close dropdown when clicking outside
onMounted(() => {
    const handleClickOutside = (event) => {
        // Check if click is outside shift dropdown
        const dropdowns = document.querySelectorAll('.shift-dropdown-container');
        let clickedInside = false;

        dropdowns.forEach(dropdown => {
            if (dropdown.contains(event.target)) {
                clickedInside = true;
            }
        });

        if (!clickedInside) {
            activeDropdown.value = null;
            shiftSearch.value = '';
        }

        // Close filter dropdowns when clicking outside
        const filterButtons = document.querySelectorAll('[data-filter-button]');
        const filterDropdowns = document.querySelectorAll('[data-filter-dropdown]');

        let clickedOnFilter = false;

        filterButtons.forEach(button => {
            if (button.contains(event.target)) {
                clickedOnFilter = true;
            }
        });

        filterDropdowns.forEach(dropdown => {
            if (dropdown.contains(event.target)) {
                clickedOnFilter = true;
            }
        });

        if (!clickedOnFilter) {
            closeAllFilters();
        }
    };

    document.addEventListener('click', handleClickOutside);
});

// Tooltip function - returns HTML for Bootstrap tooltip
function getTooltipText(emp, day) {
    const dayValue = emp.days && emp.days[day.d];
    const dayName = day.label;
    const date = day.iso;

    // Get shift details if available
    const shiftDetails = emp.shift_details && emp.shift_details[day.d];

    if (dayValue === "L") {
        return `<div style="font-size: 14px; line-height: 1.6;">${dayName}, ${date}<br><strong>Status: Libur 🏖️</strong></div>`;
    } else if (dayValue) {
        // Check if it's a number (work day count)
        const isNumber = !isNaN(dayValue);

        if (isNumber) {
            return `<div style="font-size: 14px; line-height: 1.6;">${dayName}, ${date}<br><strong>Hari Kerja ke-${dayValue} 💼</strong></div>`;
        } else {
            // Assume it's a shift code (like S1, S2, S3)
            let tooltip = `<div style="font-size: 14px; line-height: 1.6;">${dayName}, ${date}<br><strong>Shift: ${dayValue}</strong>`;

            // Add shift details if available
            if (shiftDetails) {
                if (shiftDetails.name) {
                    tooltip += `<br>Nama: ${shiftDetails.name}`;
                }
                if (shiftDetails.start_time && shiftDetails.end_time) {
                    tooltip += `<br>Jam: ${shiftDetails.start_time} - ${shiftDetails.end_time}`;
                }
            }

            tooltip += `</div>`;
            return tooltip;
        }
    } else {
        return `<div style="font-size: 14px; line-height: 1.6;">${dayName}, ${date}<br><strong>Status: Libur (Default) 🏖️</strong></div>`;
    }
}

// Avatar preview modal
const showAvatar = ref(false);
const avatarTarget = ref(null);
function openAvatar(emp) {
    avatarTarget.value = emp || null;
    showAvatar.value = true;
}
function closeAvatar() {
    showAvatar.value = false;
    avatarTarget.value = null;
}

// Edit mode state
const editMode = ref(false);

// Dropdown state
const activeDropdown = ref(null);
const shiftSearch = ref("");

// Sidebar state for shift detail
const showShiftSidebar = ref(false);
const selectedShiftDetail = ref(null);

// Shift options with "Libur" option
const shiftOptions = computed(() => {
    const options = [{ id: "L", code: "L", name: "Libur", label: "Libur" }];
    (props.shifts || []).forEach((shift) => {
        // Format: S6 - Shift 1 (08:00 - 17:00)
        const label = `${shift.code} - ${shift.name} (${
            shift.start_time?.substring(0, 5) || ""
        } - ${shift.end_time?.substring(0, 5) || ""})`;
        options.push({
            id: shift.id,
            code: shift.code,
            name: shift.name,
            label: label,
        });
    });
    return options;
});

// Helper function to get shift ID from code
function getShiftIdFromCode(code) {
    if (code === "L") return "L";
    const shift = props.shifts.find((s) => s.code === code);
    return shift ? shift.id : "L";
}

// Helper function to get current shift value for select
function getCurrentShiftValue(emp, day) {
    const dayValue = emp.days && emp.days[day.d];
    if (!dayValue || dayValue === "L") return "L";
    // dayValue is code (S1, S2, etc), need to convert to id
    return getShiftIdFromCode(dayValue);
}

// Helper function to get shift code from ID (for display)
function getShiftCodeFromId(shiftId) {
    if (shiftId === "L") return "L";
    const shift = props.shifts.find((s) => s.id === shiftId);
    return shift ? shift.code : "L";
}

// Get tooltip text for shift button
function getShiftTooltip(emp, day) {
    const shiftId = getCurrentShiftValue(emp, day);
    if (shiftId === 'L') return 'Libur';

    const shift = props.shifts.find(s => s.id === shiftId);
    if (!shift) return 'Libur';

    return `${shift.code} - ${shift.name}\n${shift.start_time?.substring(0, 5)} - ${shift.end_time?.substring(0, 5)}`;
}

// Filtered shift options based on search
const filteredShiftOptions = computed(() => {
    const query = shiftSearch.value.toLowerCase();
    if (!query) return shiftOptions.value;
    return shiftOptions.value.filter(
        (shift) =>
            shift.code.toLowerCase().includes(query) ||
            shift.name.toLowerCase().includes(query) ||
            shift.label.toLowerCase().includes(query)
    );
});

// Toggle dropdown
function toggleShiftDropdown(empId, day) {
    const key = `${empId}-${day}`;
    if (activeDropdown.value === key) {
        activeDropdown.value = null;
        shiftSearch.value = "";
    } else {
        activeDropdown.value = key;
        shiftSearch.value = "";
    }
}

// Open shift detail sidebar
function openShiftDetail(emp, day) {
    const shiftId = getCurrentShiftValue(emp, day);
    const shift = props.shifts.find(s => s.id === shiftId);

    selectedShiftDetail.value = {
        employee: emp,
        day: day,
        shift: shift || { code: 'L', name: 'Libur' },
        shiftId: shiftId
    };

    showShiftSidebar.value = true;
}

// Select shift and update
function selectShift(employeeId, date, shiftId) {
    updateShift(employeeId, date, shiftId);
    activeDropdown.value = null;
    shiftSearch.value = "";
}

// Function to update shift for a specific employee and date
function updateShift(employeeId, date, shiftId) {
    router.post(
        route("calendar.update-shift"),
        {
            employee_id: employeeId,
            date: date,
            shift_id: shiftId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Reload data
                router.reload({ only: ["employees"] });
            },
        }
    );
}
</script>

<template>
    <Head title="Kalender Libur" />
    <div class="px-6 py-6">
        <!-- Header: Breadcrumb + actions -->
        <div class="flex justify-between items-center mb-4">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2">
                <!-- Hidden for now as requested -->
                <button
                    v-if="false"
                    class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md"
                    @click="showModal = true"
                >
                    Tambah Libur
                </button>
                <button
                    v-if="false"
                    class="px-3 py-2 text-sm rounded-md border"
                >
                    Tambah Libur Serentak
                </button>
            </div>
        </div>

        <div
            class="overflow-hidden bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700"
        >
            <div
                class="flex justify-between items-center px-4 py-3 border-b dark:border-gray-700"
            >
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    Kalender Libur
                </h2>
                <div class="flex gap-3 items-center">
                    <label class="text-sm text-gray-600 dark:text-gray-400"
                        >Bulan:</label
                    >
                    <select
                        v-model="currentMonth"
                        class="px-3 py-1.5 text-sm bg-white rounded-md border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="m in months"
                            :key="m.value"
                            :value="m.value"
                        >
                            {{ m.label }}
                        </option>
                    </select>
                    <select
                        v-model="currentYear"
                        class="px-3 py-1.5 text-sm bg-white rounded-md border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option v-for="y in years" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Toolbar: Search + Filter -->
            <div
                class="flex flex-wrap gap-3 justify-between items-center px-4 py-3 border-b"
            >
                <div class="relative flex-1 min-w-[240px] max-w-md">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama/departemen..."
                        class="pr-8 pl-3 w-full h-9 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400"
                    />
                    <span class="absolute top-2 right-2 text-gray-400">🔍</span>
                </div>
                <div class="flex gap-2">
                    <!-- Mode Edit Button -->
                    <button
                        v-if="can('calendar.edit')"
                        type="button"
                        @click="editMode = !editMode"
                        :class="[
                            'inline-flex gap-2 items-center px-4 py-2 text-sm font-medium rounded-lg border transition-colors whitespace-nowrap',
                            editMode
                                ? 'bg-green-600 text-white border-green-600 hover:bg-green-700'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                        ]"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        <span>{{
                            editMode ? "Selesai Edit" : "Mode Edit"
                        }}</span>
                    </button>

                    <!-- Filter Departemen -->

                    <!-- Filter Cabang -->
                    <div class="relative">
                        <button
                            type="button"
                            data-filter-button
                            @click="closeAllFilters('branch'); showBranchFilter = !showBranchFilter"
                            class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium text-gray-700 whitespace-nowrap bg-white rounded-lg border border-gray-300 transition-colors hover:bg-gray-50"
                        >
                            <span>Cabang:</span>
                            <span class="font-semibold text-blue-600">{{
                                props.branches.find(b => b.id === selectedBranch)?.name || "Semua"
                            }}</span>
                        </button>
                        <teleport to="body">
                        <div
                            v-if="showBranchFilter"
                            data-filter-dropdown
                            class="fixed z-[100] w-56 bg-white rounded-lg border border-gray-200 shadow-lg"
                            :style="filterDropdownPositions.branch"
                        >
                            <div
                                class="px-3 py-2 text-xs font-medium text-gray-500 border-b"
                            >
                                Pilih Cabang
                            </div>
                            <div class="py-1">
                                <button
                                    v-if="isSuperadmin"
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            !selectedBranch,
                                    }"
                                    @click="
                                        selectedBranch = null;
                                        showBranchFilter = false;
                                    "
                                >
                                    Semua Cabang
                                </button>
                                <button
                                    v-for="b in props.branches"
                                    :key="b.id"
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            selectedBranch === b.id,
                                    }"
                                    @click="
                                        selectedBranch = b.id;
                                        showBranchFilter = false;
                                    "
                                >
                                    {{ b.name }}
                                </button>
                            </div>
                        </div>
                        </teleport>
                    </div>
                    <div class="relative">
                        <button
                            type="button"
                            data-filter-button
                            @click="closeAllFilters('dept'); showFilter = !showFilter"
                            class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium text-gray-700 whitespace-nowrap bg-white rounded-lg border border-gray-300 transition-colors hover:bg-gray-50"
                        >
                            <span>Departemen:</span>
                            <span class="font-semibold text-blue-600">{{
                                props.departments.find(d => d.id === selectedDept)?.name || "Semua"
                            }}</span>
                        </button>
                        <teleport to="body">
                        <div
                            v-if="showFilter"
                            data-filter-dropdown
                            class="fixed z-[100] w-56 bg-white rounded-lg border border-gray-200 shadow-lg"
                            :style="filterDropdownPositions.dept"
                        >
                            <div
                                class="px-3 py-2 text-xs font-medium text-gray-500 border-b"
                            >
                                Pilih Departemen
                            </div>
                            <div class="py-1">
                                <button
                                    v-if="isSuperadmin"
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            !selectedDept,
                                    }"
                                    @click="
                                        selectedDept = null;
                                        showFilter = false;
                                    "
                                >
                                    Semua Departemen
                                </button>
                                <button
                                    v-for="d in filteredDepartments"
                                    :key="d.id"
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            selectedDept === d.id,
                                    }"
                                    @click="
                                        selectedDept = d.id;
                                        showFilter = false;
                                    "
                                >
                                    {{ d.name }}
                                </button>
                            </div>
                        </div>
                        </teleport>
                    </div>


                    <!-- Filter Status -->
                    <div class="relative">
                        <button
                            type="button"
                            data-filter-button
                            @click="closeAllFilters('status'); showStatusFilter = !showStatusFilter"
                            class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium text-gray-700 whitespace-nowrap bg-white rounded-lg border border-gray-300 transition-colors hover:bg-gray-50"
                        >
                            <span>Status:</span>
                            <span class="font-semibold text-blue-600">{{
                                selectedStatus === 'active' ? 'Active' :
                                selectedStatus === 'inactive' ? 'Inactive' :
                                selectedStatus === 'pending' ? 'Pending' :
                                "Semua"
                            }}</span>
                        </button>
                        <teleport to="body">
                        <div
                            v-if="showStatusFilter"
                            data-filter-dropdown
                            class="fixed z-[100] w-56 bg-white rounded-lg border border-gray-200 shadow-lg"
                            :style="filterDropdownPositions.status"
                        >
                            <div
                                class="px-3 py-2 text-xs font-medium text-gray-500 border-b"
                            >
                                Pilih Status
                            </div>
                            <div class="py-1">
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            !selectedStatus,
                                    }"
                                    @click="
                                        selectedStatus = null;
                                        showStatusFilter = false;
                                    "
                                >
                                    Semua Status
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            selectedStatus === 'active',
                                    }"
                                    @click="
                                        selectedStatus = 'active';
                                        showStatusFilter = false;
                                    "
                                >
                                    Active
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            selectedStatus === 'inactive',
                                    }"
                                    @click="
                                        selectedStatus = 'inactive';
                                        showStatusFilter = false;
                                    "
                                >
                                    Inactive
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            selectedStatus === 'pending',
                                    }"
                                    @click="
                                        selectedStatus = 'pending';
                                        showStatusFilter = false;
                                    "
                                >
                                    Pending
                                </button>
                            </div>
                        </div>
                        </teleport>
                    </div>

                    <!-- Group By -->
                    <div class="relative">
                        <button
                            type="button"
                            data-filter-button
                            @click="closeAllFilters('group'); showGroupMenu = !showGroupMenu"
                            class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium text-gray-700 whitespace-nowrap bg-white rounded-lg border border-gray-300 transition-colors hover:bg-gray-50"
                        >
                            <span>Kelompokkan:</span>
                            <span class="font-semibold text-blue-600">{{
                                groupByLabel
                            }}</span>
                        </button>
                        <teleport to="body">
                        <div
                            v-if="showGroupMenu"
                            data-filter-dropdown
                            class="fixed z-[100] w-56 bg-white rounded-lg border border-gray-200 shadow-lg"
                            :style="filterDropdownPositions.group"
                        >
                            <div
                                class="px-3 py-2 text-xs font-medium text-gray-500 border-b"
                            >
                                Group By
                            </div>
                            <div class="py-1">
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            !groupBy,
                                    }"
                                    @click="
                                        groupBy = null;
                                        showGroupMenu = false;
                                    "
                                >
                                    Tidak Dikelompokkan
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            groupBy === 'department',
                                    }"
                                    @click="
                                        groupBy = 'department';
                                        showGroupMenu = false;
                                    "
                                >
                                    Departemen
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2 w-full text-sm text-left transition-colors hover:bg-gray-50"
                                    :class="{
                                        'bg-blue-50 text-blue-600 font-medium':
                                            groupBy === 'position',
                                    }"
                                    @click="
                                        groupBy = 'position';
                                        showGroupMenu = false;
                                    "
                                >
                                    Jabatan
                                </button>
                            </div>
                        </div>
                        </teleport>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="(groupBy && (!groupedEmployees || groupedEmployees.length === 0)) || (!groupBy && (!pagedEmployees || pagedEmployees.length === 0))" class="flex flex-col items-center justify-center py-16 px-4">
                <div class="text-gray-400 mb-4">
                    <svg class="w-24 h-24 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Data Karyawan</h3>
                <p class="text-sm text-gray-500 text-center max-w-md">
                    <span v-if="search || selectedDept || selectedBranch || selectedStatus">
                        Tidak ditemukan karyawan yang sesuai dengan filter pencarian.
                        <button @click="search = ''; selectedDept = null; selectedBranch = null; selectedStatus = null;" class="text-blue-600 hover:underline">
                            Hapus filter
                        </button>
                    </span>
                    <span v-else>
                        Belum ada data karyawan untuk bulan ini. Silakan tambahkan jadwal kerja terlebih dahulu.
                    </span>
                </p>
            </div>

            <!-- Grid table -->
            <div v-else class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <colgroup>
                        <col style="width: 56px; min-width: 56px" />
                        <col style="width: 320px; min-width: 280px" />
                        <col style="width: 180px; min-width: 150px" />
                        <col style="width: 120px; min-width: 120px" />
                        <col style="width: 120px; min-width: 120px" />
                        <col
                            v-for="day in days"
                            :key="day.iso"
                            style="width: 72px; min-width: 72px"
                        />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y whitespace-nowrap min-w-[56px] sticky left-0 z-20"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600"
                                >
                                    No
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y whitespace-nowrap min-w-[280px] sticky left-[56px] z-20"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600"
                                >
                                    Nama & Jabatan Pekerja
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y whitespace-nowrap min-w-[150px]"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600"
                                >
                                    Nama Departemen
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y whitespace-nowrap min-w-[120px]"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600"
                                >
                                    Hari Kerja
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y whitespace-nowrap min-w-[120px]"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600"
                                >
                                    Libur
                                </div>
                            </th>
                            <th
                                v-for="d in days"
                                :key="d.iso"
                                class="py-2.5 bg-gray-100 border-gray-200 border-y text-center whitespace-nowrap min-w-[72px]"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    <div>{{ d.label }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ String(d.d).padStart(2, "0") }}
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Grouped View -->
                        <template v-if="groupBy && groupedEmployees">
                            <template v-for="(group, groupIdx) in groupedEmployees" :key="'group-' + groupIdx">
                                <!-- Group Header -->
                                <tr class="bg-gray-50 border-b-2 border-gray-300">
                                    <td
                                        colspan="100%"
                                        class="px-4 py-3 font-semibold text-gray-700 sticky left-0 bg-gray-50 z-10"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span>{{ groupBy === 'department' ? '📁' : '👔' }}</span>
                                            <span>{{ group.label }}</span>
                                            <span class="text-sm font-normal text-gray-500">
                                                ({{ group.employees.length }} karyawan)
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Group Employees -->
                                <tr
                                    v-for="(emp, empIdx) in group.employees"
                                    :key="emp.id"
                                    class="border-b border-gray-200"
                                >
                                    <td
                                        class="px-3 py-2 sticky left-0 min-w-[56px] bg-white z-10 border-r border-gray-100"
                                    >
                                        {{ baseRowIndex + empIdx + 1 }}
                                    </td>
                                    <td
                                        class="px-3 py-2 min-w-[280px] sticky left-[56px] bg-white z-10 border-r border-gray-100"
                                    >
                                        <div class="flex gap-3 items-center">
                                            <template v-if="emp.photo_url">
                                                <img
                                                    :src="
                                                        emp.photo_url
                                                            ? `/storage/${emp.photo_url}`
                                                            : ''
                                                    "
                                                    alt="avatar"
                                                    class="object-cover w-8 h-8 rounded-full cursor-zoom-in"
                                                    @click="openAvatar(emp)"
                                                />
                                            </template>
                                            <template v-else>
                                                <button
                                                    type="button"
                                                    @click="openAvatar(emp)"
                                                    class="inline-flex"
                                                >
                                                    <AvatarInitials
                                                        :name="emp.name"
                                                        :gender="emp?.gender || ''"
                                                        :size="32"
                                                    />
                                                </button>
                                            </template>
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-medium text-gray-800"
                                                    >{{ emp.name }}</span
                                                >
                                                <span class="text-xs text-gray-500">{{
                                                    emp.role || "Staff"
                                                }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 min-w-[150px]">
                                        {{ emp.department || "-" }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-center min-w-[120px] font-semibold text-gray-700"
                                    >
                                        {{ emp.work_days_count || 0 }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-center min-w-[120px] font-semibold text-gray-700"
                                    >
                                        {{ emp.off_days_count || 0 }}
                                    </td>
                                    <td
                                        v-for="d in days"
                                        :key="emp.id + '-' + d.iso"
                                        class="px-1 py-2 text-center min-w-[72px] relative"
                                    >
                                        <!-- Edit Mode: Show Custom Select -->
                                        <template v-if="editMode">
                                            <div class="relative inline-block w-full">
                                                <!-- Display Button (shows code only) -->
                                                <button
                                                    :id="'shift-btn-' + emp.id + '-' + d.d"
                                                    @click.stop="
                                                        toggleShiftDropdown(emp.id, d.d)
                                                    "
                                                    :class="[
                                                        'w-full px-2 py-1 text-xs font-semibold rounded border transition-colors',
                                                        getCurrentShiftValue(emp, d) ===
                                                        'L'
                                                            ? 'bg-red-50 text-red-700 border-red-300 hover:bg-red-100'
                                                            : 'bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100',
                                                    ]"
                                                    :title="getShiftTooltip(emp, d)"
                                                >
                                                    {{
                                                        getShiftCodeFromId(
                                                            getCurrentShiftValue(emp, d)
                                                        )
                                                    }}
                                                </button>

                                                <!-- Dropdown (shows full details) -->
                                                <teleport to="body">
                                                <div
                                                    v-if="
                                                        activeDropdown ===
                                                        `${emp.id}-${d.d}`
                                                    "
                                                    :ref="'dropdown-' + emp.id + '-' + d.d"
                                                    class="fixed z-[9999] w-64 bg-white rounded-lg border border-gray-200 shadow-lg"
                                                    :style="getDropdownPosition(emp.id, d.d, empIdx)"
                                                    @click.stop
                                                >
                                                    <div class="p-2">
                                                        <input
                                                            v-model="shiftSearch"
                                                            type="text"
                                                            placeholder="Cari shift..."
                                                            class="px-2 py-1 w-full text-xs rounded border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                            @click.stop
                                                        />
                                                    </div>
                                                    <ul
                                                        class="overflow-y-auto max-h-48"
                                                    >
                                                        <li
                                                            v-for="shift in filteredShiftOptions"
                                                            :key="shift.id"
                                                            @click="
                                                                selectShift(
                                                                    emp.id,
                                                                    d.iso,
                                                                    shift.id
                                                                )
                                                            "
                                                            :class="[
                                                                'px-3 py-2 text-xs cursor-pointer hover:bg-gray-100',
                                                                getCurrentShiftValue(
                                                                    emp,
                                                                    d
                                                                ) === shift.id
                                                                    ? 'bg-blue-50 font-semibold'
                                                                    : '',
                                                            ]"
                                                        >
                                                            <div class="font-medium">
                                                                {{ shift.code }}
                                                            </div>
                                                            <div
                                                                class="text-gray-600 text-[10px]"
                                                            >
                                                                {{ shift.label }}
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                                </teleport>
                                            </div>
                                        </template>

                                        <!-- View Mode: Show Badge -->
                                        <template v-else>
                                            <template v-if="emp.days && emp.days[d.d]">
                                                <span
                                                    :class="[
                                                        'inline-flex justify-center items-center w-6 h-6 text-xs font-semibold rounded cursor-help transition-all hover:scale-110',
                                                        emp.days[d.d] === 'L'
                                                            ? 'text-red-600 bg-red-50 hover:bg-red-100'
                                                            : 'text-blue-600 bg-blue-50 hover:bg-blue-100',
                                                    ]"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    data-bs-html="true"
                                                    :data-bs-title="
                                                        getTooltipText(emp, d)
                                                    "
                                                >
                                                    {{ emp.days[d.d] }}
                                                </span>
                                            </template>
                                            <template v-else>
                                                <span
                                                    class="inline-flex justify-center items-center w-6 h-6 text-xs font-semibold text-red-600 bg-red-50 rounded transition-all cursor-help hover:scale-110 hover:bg-red-100"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    data-bs-html="true"
                                                    :data-bs-title="
                                                        getTooltipText(emp, d)
                                                    "
                                                >
                                                    L
                                                </span>
                                            </template>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <!-- Normal View (No Grouping) -->
                        <template v-else>
                            <tr
                                v-for="(emp, idx) in pagedEmployees"
                                :key="emp.id"
                                class="border-b border-gray-200"
                            >
                                <td
                                    class="px-3 py-2 sticky left-0 min-w-[56px] bg-white z-10 border-r border-gray-100"
                                >
                                    {{ baseRowIndex + idx + 1 }}
                                </td>
                            <td
                                class="px-3 py-2 min-w-[280px] sticky left-[56px] bg-white z-10 border-r border-gray-100"
                            >
                                <div class="flex gap-3 items-center">
                                    <template v-if="emp.photo_url">
                                        <img
                                            :src="
                                                emp.photo_url
                                                    ? `/storage/${emp.photo_url}`
                                                    : ''
                                            "
                                            alt="avatar"
                                            class="object-cover w-8 h-8 rounded-full cursor-zoom-in"
                                            @click="openAvatar(emp)"
                                        />
                                    </template>
                                    <template v-else>
                                        <button
                                            type="button"
                                            @click="openAvatar(emp)"
                                            class="inline-flex"
                                        >
                                            <AvatarInitials
                                                :name="emp.name"
                                                :gender="emp?.gender || ''"
                                                :size="32"
                                            />
                                        </button>
                                    </template>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-gray-800"
                                            >{{ emp.name }}</span
                                        >
                                        <span class="text-xs text-gray-500">{{
                                            emp.role || "Staff"
                                        }}</span>
                                    </div>
                                </div>

                                <!-- <div class="flex gap-3 items-center">
                                    <template v-if="emp.photo_url">
                                        <img
                                            :src="emp.photo_url"
                                            alt="avatar"
                                            class="object-cover w-8 h-8 rounded-full border"
                                        />
                                    </template>
                                    <template v-else>
                                        <AvatarInitials
                                            :name="emp.name"
                                            :gender="emp?.gender || ''"
                                            :size="32"
                                            :color-class="colorClass(emp?.name)"
                                        />
                                    </template>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-gray-800"
                                            >{{ emp.name }}</span
                                        >
                                        <span class="text-xs text-gray-500">{{
                                            emp?.role
                                        }}</span>
                                    </div>
                                </div> -->
                            </td>
                            <td class="px-3 py-2 min-w-[150px]">
                                {{ emp.department || "-" }}
                            </td>
                            <td
                                class="px-3 py-2 text-center min-w-[120px] font-semibold text-gray-700"
                            >
                                {{ emp.work_days_count || 0 }}
                            </td>
                            <td
                                class="px-3 py-2 text-center min-w-[120px] font-semibold text-gray-700"
                            >
                                {{ emp.off_days_count || 0 }}
                            </td>
                            <td
                                v-for="d in days"
                                :key="emp.id + '-' + d.iso"
                                class="px-1 py-2 text-center min-w-[72px] relative"
                            >
                                <!-- Edit Mode: Show Custom Select -->
                                <template v-if="editMode">
                                    <div class="relative inline-block w-full">
                                        <!-- Display Button (shows code only) -->
                                        <button
                                            :id="'shift-btn-' + emp.id + '-' + d.d"
                                            @click.stop="
                                                toggleShiftDropdown(emp.id, d.d)
                                            "
                                            :class="[
                                                'w-full px-2 py-1 text-xs font-semibold rounded border transition-colors',
                                                getCurrentShiftValue(emp, d) ===
                                                'L'
                                                    ? 'bg-red-50 text-red-700 border-red-300 hover:bg-red-100'
                                                    : 'bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100',
                                            ]"
                                            :title="getShiftTooltip(emp, d)"
                                        >
                                            {{
                                                getShiftCodeFromId(
                                                    getCurrentShiftValue(emp, d)
                                                )
                                            }}
                                        </button>

                                        <!-- Dropdown (shows full details) -->
                                        <teleport to="body">
                                        <div
                                            v-if="
                                                activeDropdown ===
                                                `${emp.id}-${d.d}`
                                            "
                                            :ref="'dropdown-' + emp.id + '-' + d.d"
                                            class="fixed z-[9999] w-64 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            :style="getDropdownPosition(emp.id, d.d, idx)"
                                            @click.stop
                                        >
                                            <div class="p-2">
                                                <input
                                                    v-model="shiftSearch"
                                                    type="text"
                                                    placeholder="Cari shift..."
                                                    class="px-2 py-1 w-full text-xs rounded border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    @click.stop
                                                />
                                            </div>
                                            <ul
                                                class="overflow-y-auto max-h-48"
                                            >
                                                <li
                                                    v-for="shift in filteredShiftOptions"
                                                    :key="shift.id"
                                                    @click="
                                                        selectShift(
                                                            emp.id,
                                                            d.iso,
                                                            shift.id
                                                        )
                                                    "
                                                    :class="[
                                                        'px-3 py-2 text-xs cursor-pointer hover:bg-gray-100',
                                                        getCurrentShiftValue(
                                                            emp,
                                                            d
                                                        ) === shift.id
                                                            ? 'bg-blue-50 font-semibold'
                                                            : '',
                                                    ]"
                                                >
                                                    <div class="font-medium">
                                                        {{ shift.code }}
                                                    </div>
                                                    <div
                                                        class="text-gray-600 text-[10px]"
                                                    >
                                                        {{ shift.label }}
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        </teleport>
                                    </div>
                                </template>

                                <!-- View Mode: Show Badge -->
                                <template v-else>
                                    <template v-if="emp.days && emp.days[d.d]">
                                        <span
                                            :class="[
                                                'inline-flex justify-center items-center w-6 h-6 text-xs font-semibold rounded cursor-help transition-all hover:scale-110',
                                                emp.days[d.d] === 'L'
                                                    ? 'text-red-600 bg-red-50 hover:bg-red-100'
                                                    : 'text-blue-600 bg-blue-50 hover:bg-blue-100',
                                            ]"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-bs-html="true"
                                            :data-bs-title="
                                                getTooltipText(emp, d)
                                            "
                                        >
                                            {{ emp.days[d.d] }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span
                                            class="inline-flex justify-center items-center w-6 h-6 text-xs font-semibold text-red-600 bg-red-50 rounded transition-all cursor-help hover:scale-110 hover:bg-red-100"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-bs-html="true"
                                            :data-bs-title="
                                                getTooltipText(emp, d)
                                            "
                                        >
                                            L
                                        </span>
                                    </template>
                                </template>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer: pagination -->
            <Pagination
                :pagination="pagination"
                @page-changed="handlePageChanged"
                @per-page-changed="handlePerPageChanged"
            />
        </div>
        <!-- Tambah Libur Modal -->
        <div
            v-if="showModal"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black/40"
        >
            <div class="w-full max-w-xl bg-white rounded-lg shadow">
                <div
                    class="flex justify-between items-center px-4 py-3 border-b"
                >
                    <h3 class="text-lg font-semibold">Tambah Libur</h3>
                    <button class="text-gray-500" @click="showModal = false">
                        ✕
                    </button>
                </div>
                <div class="px-4 py-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Nama Libur</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="px-3 w-full h-9 rounded-md border"
                            placeholder="Libur Bersama"
                        />
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Tanggal Libur</label
                        >
                        <textarea
                            v-model="form.dates"
                            rows="2"
                            class="px-3 py-2 w-full rounded-md border"
                            placeholder="28/06/2025, 30/07/2025, 01/08/2025"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Pisahkan dengan koma atau baris baru. Format d/m/Y
                            atau Y-m-d.
                        </p>
                    </div>
                    <div class="flex gap-2 items-center">
                        <input
                            id="allEmp"
                            type="checkbox"
                            v-model="form.all_employees"
                        />
                        <label for="allEmp" class="text-sm"
                            >Pilih semua karyawan (sesuai
                            filter/organisasi)</label
                        >
                    </div>
                    <div v-if="!form.all_employees">
                        <label class="block mb-1 text-sm text-gray-600"
                            >Nama Karyawan</label
                        >
                        <select
                            v-model="form.employee_ids"
                            multiple
                            class="px-3 py-2 w-full h-28 rounded-md border"
                        >
                            <option
                                v-for="opt in employeeOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 justify-end px-4 py-3 border-t">
                    <button
                        class="px-3 py-2 rounded-md border"
                        @click="showModal = false"
                    >
                        Batal
                    </button>
                    <button
                        class="px-3 py-2 text-white bg-blue-600 rounded-md"
                        @click="submitHoliday"
                        :disabled="form.processing"
                    >
                        Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Preview Modal -->
    <Modal
        :show="showAvatar"
        title="Foto Karyawan"
        closeText="Tutup"
        maxWidth="sm"
        @close="closeAvatar"
    >
        <div class="flex flex-col justify-center items-center py-4">
            <template v-if="avatarTarget?.photo_url">
                <img
                    :src="
                        avatarTarget?.photo_url
                            ? `/storage/${avatarTarget.photo_url}`
                            : ''
                    "
                    alt="avatar"
                    class="object-cover w-64 h-64 rounded-xl shadow-md"
                />
            </template>
            <template v-else>
                <AvatarInitials
                    :name="avatarTarget?.name"
                    :gender="avatarTarget?.gender || ''"
                    :size="160"
                />
            </template>
            <div class="mt-3 text-sm text-gray-600">
                {{ avatarTarget?.name }}
            </div>
        </div>
    </Modal>
</template>
