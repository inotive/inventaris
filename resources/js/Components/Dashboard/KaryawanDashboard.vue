<template>
    <div class="space-y-6">
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Total Karyawan -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Users class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ karyawanMetrics.total_karyawan }}</h2>
                    <p class="text-gray-500">Total Karyawan</p>
                </div>
            </div>

            <!-- Karyawan Aktif -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <UserCheck class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ karyawanMetrics.active_karyawan }}</h2>
                    <p class="text-gray-500">Karyawan Aktif</p>
                </div>
            </div>

            <!-- Karyawan Telat -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Clock class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ karyawanMetrics.late_karyawan }}</h2>
                    <p class="text-gray-500">Karyawan Telat</p>
                </div>
            </div>

            <!-- Karyawan Cuti -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Calendar class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ karyawanMetrics.on_leave }}</h2>
                    <p class="text-gray-500">Karyawan Cuti</p>
                </div>
            </div>

            <!-- Kinerja Karyawan -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <TrendingUp class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ karyawanMetrics.performance }}</h2>
                    <p class="text-gray-500">Kinerja Karyawan</p>
                </div>
            </div>
        </div>

        <!-- Middle Section - Activity Log, Exemplary Employees, KPI, Birthdays -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Log Aktivitas Hari ini -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Log Aktivitas Hari ini</h3>
                </div>
                <div class="space-y-4">
                    <div v-for="activity in displayedTodayActivities" :key="activity.id"
                        class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600">{{ activity.name.charAt(0) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ activity.name }}</p>
                            <p class="text-xs text-gray-500">Absen: {{ activity.time }}</p>
                        </div>
                        <span :class="[
                            'px-2 py-1 text-xs rounded-full',
                            activity.status === 'Hadir' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'
                        ]">
                            {{ activity.status }}
                        </span>
                    </div>
                    <div v-if="karyawanTodayActivities.length === 0" class="text-center text-gray-500 py-4">
                        Tidak ada aktivitas hari ini
                    </div>
                </div>
                <div class="mt-4" v-if="karyawanTodayActivities.length > 5">
                    <button @click="toggleShowAllTodayActivities" class="text-blue-600 text-sm hover:underline">
                        {{ showAllTodayActivities ? 'Tampilkan Lebih Sedikit' : 'Lihat Selengkapnya' }}
                    </button>
                </div>
            </div>

            <!-- Karyawan Tepat Waktu Hari Ini -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Karyawan Tepat Waktu Hari Ini</h3>
                </div>
                <div class="space-y-4">
                    <div v-for="employee in displayedExemplaryEmployees" :key="employee.id"
                        class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600">{{ employee.name.charAt(0) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ employee.name }}</p>
                            <p class="text-xs text-gray-500">Tepat waktu: {{ employee.time }}</p>
                        </div>
                    </div>
                    <div v-if="displayedExemplaryEmployees.length === 0" class="text-center text-gray-500 py-4">
                        Tidak ada karyawan yang tepat waktu hari ini
                    </div>
                </div>
                <div class="mt-4" v-if="exemplaryEmployees.length > 5">
                    <button @click="toggleShowAllExemplaryEmployees" class="text-blue-600 text-sm hover:underline">
                        {{ showAllExemplaryEmployees ? 'Tampilkan Lebih Sedikit' : 'Lihat Selengkapnya' }}
                    </button>
                </div>
            </div>

            <!-- Top 3 KPI Performance -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Top 3 KPI Performance</h3>
                    <div class="flex items-center gap-2">
                        <select class="text-sm border border-gray-300 rounded-lg px-3 py-1" v-model="selectedBranchKPI"
                            @change="handleBranchChangeKPI" :disabled="isLoadingKPI">
                            <option value="">Semua Cabang</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}
                            </option>
                        </select>
                        <select class="text-sm border border-gray-300 rounded-lg px-3 py-1"
                            v-model="selectedDepartmentKPI" @change="handleDepartmentChangeKPI"
                            :disabled="isLoadingKPI">
                            <option value="">Semua Departemen</option>
                            <option v-for="department in filteredDepartments" :key="department.id"
                                :value="department.id">{{
                                    department.name }}
                            </option>
                        </select>
                        <div v-if="isLoadingKPI"
                            class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin">
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div v-for="performer in filteredTopPerformers" :key="performer.id" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600">{{ performer.name.charAt(0) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ performer.name }}</p>
                            <p class="text-xs text-gray-500">Score: {{ performer.display_score }}</p>
                        </div>
                    </div>
                    <div v-if="filteredTopPerformers.length === 0" class="text-center text-gray-500 py-4">
                        Tidak ada data KPI Performance
                    </div>
                </div>
            </div>

            <!-- Ulang Tahun -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Ulang Tahun</h3>
                </div>
                <div class="space-y-4">
                    <div v-for="birthday in displayedUpcomingBirthdays" :key="birthday.id"
                        class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600">{{ birthday.name.charAt(0) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ birthday.name }}</p>
                            <p class="text-xs text-gray-500">{{ birthday.date }}</p>
                        </div>
                    </div>
                    <div v-if="upcomingBirthdays.length === 0" class="text-center text-gray-500 py-4">
                        Tidak ada ulang tahun mendatang
                    </div>
                </div>
                <div class="mt-4" v-if="upcomingBirthdays.length > 5">
                    <button @click="toggleShowAllUpcomingBirthdays" class="text-blue-600 text-sm hover:underline">
                        {{ showAllUpcomingBirthdays ? 'Tampilkan Lebih Sedikit' : 'Lihat Selengkapnya' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Proporsi Karyawan -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Proporsi Karyawan</h3>
                    <div class="flex items-center gap-2">
                        <select class="text-sm border border-gray-300 rounded-lg px-3 py-1" v-model="selectedBranch"
                            @change="handleBranchChange" :disabled="isLoadingChart">
                            <option value="">Semua</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}
                            </option>
                        </select>
                        <div v-if="isLoadingChart"
                            class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin">
                        </div>
                    </div>
                </div>
                <apexchart type="bar" height="300" :options="employeeProportionOptions"
                    :series="employeeProportionSeries" />
            </div>

            <!-- Proporsi Gender -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Proporsi Gender</h3>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z">
                            </path>
                        </svg>
                    </button>
                </div>
                <apexchart type="donut" height="300" :options="genderProportionOptions"
                    :series="genderProportionSeries" />
            </div>
        </div>

        <!-- Pertumbuhan Karyawan Chart -->
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Pertumbuhan Karyawan</h3>
            </div>
            <apexchart type="line" height="400" :options="employeeGrowthOptions" :series="employeeGrowthSeries" />
        </div>
    </div>
</template>

<script setup>
import { Users, UserCheck, Clock, Calendar, TrendingUp } from "lucide-vue-next";
import { ref, computed, onMounted } from "vue";

// Props
const props = defineProps({
    karyawanMetrics: {
        type: Object,
        default: () => ({
            total_karyawan: 0,
            active_karyawan: 0,
            late_karyawan: 0,
            on_leave: 0,
            performance: '0%'
        })
    },
    branches: {
        type: Array,
        default: () => []
    },
    departments: {
        type: Array,
        default: () => []
    },
    karyawanTodayActivities: {
        type: Array,
        default: () => []
    },
    exemplaryEmployees: {
        type: Array,
        default: () => []
    },
    topPerformers: {
        type: Array,
        default: () => []
    },
    upcomingBirthdays: {
        type: Array,
        default: () => []
    },
    employeeProportionData: {
        type: Object,
        default: () => ({
            categories: ['Balikpapan', 'Samarinda', 'Kantor Pusat', 'Bontang', 'IKN'],
            data: [0, 0, 0, 0, 0]
        })
    },
    genderProportionData: {
        type: Array,
        default: () => [0, 0]
    },
    employeeGrowthData: {
        type: Object,
        default: () => ({
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        })
    }
});

console.log(props.karyawanMetrics);

// Reactive data for show more functionality
const showAllTodayActivities = ref(false);
const showAllExemplaryEmployees = ref(false);
const showAllUpcomingBirthdays = ref(false);
const selectedBranch = ref("");
const selectedBranchKPI = ref("");
const selectedDepartmentKPI = ref("");
const isLoadingChart = ref(false);
const isLoadingKPI = ref(false);

// Computed properties for limited display
const displayedTodayActivities = computed(() => {
    if (showAllTodayActivities.value) {
        return props.karyawanTodayActivities;
    }
    return props.karyawanTodayActivities.slice(0, 5);
});

const displayedExemplaryEmployees = computed(() => {
    if (showAllExemplaryEmployees.value) {
        return props.exemplaryEmployees;
    }
    return props.exemplaryEmployees.slice(0, 5);
});

const displayedUpcomingBirthdays = computed(() => {
    if (showAllUpcomingBirthdays.value) {
        return props.upcomingBirthdays;
    }
    return props.upcomingBirthdays.slice(0, 5);
});

// Methods for toggling show more
const toggleShowAllTodayActivities = () => {
    showAllTodayActivities.value = !showAllTodayActivities.value;
};

const toggleShowAllExemplaryEmployees = () => {
    showAllExemplaryEmployees.value = !showAllExemplaryEmployees.value;
};

const toggleShowAllUpcomingBirthdays = () => {
    showAllUpcomingBirthdays.value = !showAllUpcomingBirthdays.value;
};

// Chart Options
const employeeProportionOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    xaxis: {
        categories: filteredEmployeeProportionData.value.categories,
    },
    colors: ["#1B84FF"],
    dataLabels: { enabled: false },
    plotOptions: {
        bar: {
            borderRadius: 4,
        }
    }
}));

const employeeProportionSeries = computed(() => [
    {
        name: "Jumlah Karyawan",
        data: filteredEmployeeProportionData.value.data
    }
]);

// Reactive data for filtered employee proportion
const filteredEmployeeProportionData = ref(props.employeeProportionData);
const filteredTopPerformers = ref(props.topPerformers);

// Function to fetch top performers by branch and department
const fetchTopPerformers = async () => {
    isLoadingKPI.value = true;
    const branchId = selectedBranchKPI.value;
    const departmentId = selectedDepartmentKPI.value;

    try {
        // If no filter selected, use all data
        if ((!branchId || branchId === "") && (!departmentId || departmentId === "")) {
            filteredTopPerformers.value = props.topPerformers;
            isLoadingKPI.value = false;
            return;
        }

        const queryParams = new URLSearchParams();
        if (branchId) queryParams.append('branch_id', branchId);
        if (departmentId) queryParams.append('department_id', departmentId);

        const response = await fetch(`/api/dashboard/top-performers-by-branch?${queryParams.toString()}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.data) {
            filteredTopPerformers.value = data.data;
        } else {
            console.error('API returned error:', data.message);
            filteredTopPerformers.value = props.topPerformers;
        }
    } catch (error) {
        console.error('Error fetching top performers:', error);
        filteredTopPerformers.value = props.topPerformers;
    } finally {
        isLoadingKPI.value = false;
    }
};

const filteredDepartments = computed(() => {
    if (!selectedBranchKPI.value) {
        return props.departments;
    }
    return props.departments.filter(dept => dept.branch_id == selectedBranchKPI.value);
});

const handleBranchChangeKPI = () => {
    // When branch changes, check if selected department is still valid
    if (selectedDepartmentKPI.value) {
        const selectedDept = props.departments.find(d => d.id === selectedDepartmentKPI.value);
        if (selectedDept && selectedDept.branch_id != selectedBranchKPI.value) {
            selectedDepartmentKPI.value = "";
        }
    }
    fetchTopPerformers();
};

const handleDepartmentChangeKPI = () => {
    // When department changes, auto-select its branch if not already selected correctly
    if (selectedDepartmentKPI.value) {
        const selectedDept = props.departments.find(d => d.id === selectedDepartmentKPI.value);
        if (selectedDept && selectedDept.branch_id) {
            selectedBranchKPI.value = selectedDept.branch_id;
        }
    }
    fetchTopPerformers();
};

// Function to fetch employee proportion data by branch
const fetchEmployeeProportionByBranch = async (branchId) => {
    isLoadingChart.value = true;

    try {
        // If no branch selected, use all data
        if (!branchId || branchId === "") {
            filteredEmployeeProportionData.value = props.employeeProportionData;
            isLoadingChart.value = false;
            return;
        }

        // Make API call to get data for specific branch
        const response = await fetch(`/api/dashboard/employee-proportion-by-branch?branch_id=${branchId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.data) {
            filteredEmployeeProportionData.value = {
                categories: data.data.categories || [],
                data: data.data.data || []
            };
        } else {
            console.error('API returned error:', data.message);
            // Fallback to original data
            filteredEmployeeProportionData.value = props.employeeProportionData;
        }
    } catch (error) {
        console.error('Error fetching employee proportion data:', error);
        // Fallback to original data
        filteredEmployeeProportionData.value = props.employeeProportionData;
    } finally {
        isLoadingChart.value = false;
    }
};

// Handle branch change
const handleBranchChange = () => {
    fetchEmployeeProportionByBranch(selectedBranch.value);
};

// Initialize with all data on mount
onMounted(() => {
    filteredEmployeeProportionData.value = props.employeeProportionData;
    filteredTopPerformers.value = props.topPerformers;
});

const genderProportionOptions = {
    chart: { toolbar: { show: false } },
    labels: ["Laki Laki", "Perempuan"],
    colors: ["#1B84FF", "#F59E0B"],
    legend: { position: "bottom" },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '70%'
            }
        }
    }
};

const genderProportionSeries = computed(() => props.genderProportionData);

const employeeGrowthOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    xaxis: {
        categories: props.employeeGrowthData.categories,
    },
    colors: ["#1B84FF"],
    stroke: { curve: "smooth", width: 3 },
    dataLabels: { enabled: false },
    markers: {
        size: 6,
        hover: {
            size: 8
        }
    }
}));

const employeeGrowthSeries = computed(() => [
    {
        name: "Karyawan Terdaftar",
        data: props.employeeGrowthData.data
    }
]);
</script>
