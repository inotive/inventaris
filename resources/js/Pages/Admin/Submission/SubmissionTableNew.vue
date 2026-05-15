<template>
  <Head title="Daftar Pengajuan" />
  <Toast />

  <div class="flex flex-col h-full gap-6 px-6 py-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center text-sm text-gray-500">
      <span class="cursor-pointer hover:text-gray-700">Daftar Pengajuan</span>
      <ChevronRight class="w-4 h-4 mx-2" />
      <span class="text-[#002875] font-semibold">{{
        props.types[props.type]
      }}</span>
    </nav>

    <!-- Conditional rendering based on type -->
    <SubmissionsTable
      v-if="props.type === 'submissions'"
      :submissions="submissions.data"
      :types="props.types"
      :current-type="props.type"
      :branches="props.branches"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :stats-cards="statsCards"
      @open-modal="openModal"
      @filtered-data-updated="updateFilteredSubmissions"
    />

    <!-- Card: Daftar Pengajuan (Stats + Table combined) -->
    <div v-else class="bg-white rounded-xl border border-[#DBDFE9]">
      <!-- Stats Section -->
      <div class="px-6 py-4 border-b border-[#DBDFE9]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="(stat, index) in statsCards"
            :key="index"
            class="flex items-center gap-4 p-4 border border-gray-100 rounded-xl"
          >
            <div
              :class="[
                'w-11 h-11 rounded-xl flex items-center justify-center',
                stat.bgClass,
              ]"
            >
              <component
                :is="stat.icon"
                class="w-7 h-7"
                :class="stat.iconClass"
              />
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">
                {{ stat.value }}
              </div>
              <div class="text-sm text-gray-500">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Header -->
      <div class="border-b border-gray-200">
        <nav class="flex overflow-x-auto scrollbar-hide">
          <template v-for="label in props.submission_types" :key="label?.name">
            <Link
              v-if="label?.permission && can(label.permission)"
              :href="`/${label.name}`"
              :class="[
                'inline-flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors',
                label.name === props.type
                  ? 'border-[#002875] text-[#002875]'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              {{ label.label }}
              <span
                v-if="
                  (label.value == 0
                    ? pendingCounts.total
                    : pendingCounts.by_type[label.value]) > 0
                "
                class="inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 text-xs font-medium text-white bg-[#002875] rounded-full"
              >
                {{
                  label.value == 0
                    ? pendingCounts.total
                    : pendingCounts.by_type[label.value]
                }}
              </span>
            </Link>
          </template>
        </nav>
      </div>

      <div class="m-0">
        <div class="overflow-x-auto">
          <table class="w-full text-sm" style="min-width: 1000px">
            <thead>
              <tr class="border-y border-[#DBDFE9] bg-gray-50/50">
                <th
                  class="px-4 py-3 font-medium text-left text-gray-700 whitespace-nowrap"
                  style="min-width: 50px; width: 50px"
                >
                  <div class="flex items-center gap-1">
                    No
                    <ArrowUpDown class="w-3 h-3 text-gray-400" />
                  </div>
                </th>
                <th
                  class="px-4 py-3 font-medium text-left text-gray-700 whitespace-nowrap"
                  style="min-width: 140px"
                >
                  <div class="flex items-center gap-1">
                    Tanggal Pengajuan
                    <ArrowUpDown class="w-3 h-3 text-gray-400" />
                  </div>
                </th>
                <th
                  class="px-4 py-3 font-medium text-left text-gray-700 whitespace-nowrap"
                  style="min-width: 150px"
                >
                  <div class="flex items-center gap-1">
                    Karyawan
                    <ArrowUpDown class="w-3 h-3 text-gray-400" />
                  </div>
                </th>
                <th
                  class="px-4 py-3 font-medium text-left text-gray-700 whitespace-nowrap"
                  style="min-width: 180px"
                >
                  <div class="flex items-center gap-1">
                    Cabang
                    <ArrowUpDown class="w-3 h-3 text-gray-400" />
                  </div>
                </th>
                <!-- Kolom khusus Izin Lainnya -->
                <th
                  v-if="props.type === 'others'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Kategori Izin
                </th>
                <!-- Kolom khusus Cuti (Izin Sakit, Izin Cuti, Izin Lainnya) -->
                <th
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Tanggal Mulai
                </th>
                <th
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Tanggal Akhir
                </th>
                <th
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Total Hari
                </th>
                <!-- Kolom khusus Lembur -->
                <th
                  v-if="props.type === 'overtime'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Waktu Mulai
                </th>
                <th
                  v-if="props.type === 'overtime'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Waktu Selesai
                </th>

                <!-- Kolom khusus Attendance Correction -->
                <th
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Tanggal Absen
                </th>
                <th
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Waktu Masuk
                </th>
                <th
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Waktu Keluar
                </th>

                <!-- Kolom khusus Reimbursement -->
                <th
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Judul Reimbursement
                </th>
                <th
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Tanggal
                </th>
                <th
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Jumlah
                </th>
                <!-- Kolom khusus Submission General -->
                <th
                  v-if="props.type === 'general'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Judul
                </th>
                <th
                  v-if="props.type === 'general'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Tag
                </th>

                <!-- Kolom khusus Piutang -->
                <th
                  v-if="props.type === 'debt'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Tenor
                </th>
                <th
                  v-if="props.type === 'debt'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Jumlah Piutang
                </th>
                <th
                  v-if="props.type === 'debt'"
                  class="px-4 py-3 font-medium text-left text-gray-700"
                >
                  Jumlah yang Dibayar
                </th>
                <th
                  class="px-4 py-3 font-medium text-left text-gray-700 whitespace-nowrap"
                  style="min-width: 100px"
                >
                  <div class="flex items-center gap-1">
                    Status
                    <ArrowUpDown class="w-3 h-3 text-gray-400" />
                  </div>
                </th>
                <th
                  class="px-4 py-3 font-medium text-center text-gray-700 whitespace-nowrap"
                  style="min-width: 80px"
                >
                  Aksi
                </th>
              </tr>
              <!-- Filter Row -->
              <tr class="bg-gray-50 border-b border-[#DBDFE9]">
                <th class="px-4 py-3"></th>
                <th class="px-4 py-3">
                  <div class="relative">
                    <input
                      type="date"
                      v-model="filters.start_date"
                      @change="applyFilters"
                      class="w-full px-3 py-2 text-sm bg-[#fbfbfb] border border-[#DBDFE9] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                      placeholder="dd/mm/yyyy"
                    />
                    <Calendar
                      class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2"
                    />
                  </div>
                </th>
                <th class="px-4 py-3">
                  <div class="relative">
                    <input
                      type="text"
                      v-model="filters.name"
                      @input="debounceSearch"
                      class="w-full px-3 py-2 pr-10 text-sm bg-[#fbfbfb] border border-[#DBDFE9] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                      placeholder="Cari Nama"
                    />
                    <Search
                      class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2"
                    />
                  </div>
                </th>
                <th class="px-4 py-3">
                  <div class="relative">
                    <select
                      v-model="filters.branch"
                      @change="applyFilters"
                      class="w-full px-3 py-2 pr-10 text-sm bg-[#fbfbfb] border border-[#DBDFE9] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875] appearance-none"
                    >
                      <option value="">Pilih Cabang</option>
                      <option
                        v-for="branch in props.branches"
                        :key="branch.id"
                        :value="branch.id"
                      >
                        {{ branch.name }}
                      </option>
                    </select>
                    <Filter
                      class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2"
                    />
                  </div>
                </th>
                <!-- Empty filter untuk kolom Izin Lainnya -->
                <th v-if="props.type === 'others'" class="px-4 py-3"></th>
                <!-- Empty filter untuk kolom Cuti -->
                <th
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-3"
                ></th>
                <th
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-3"
                ></th>
                <th
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-3"
                ></th>
                <!-- Empty filter untuk kolom Lembur -->
                <th v-if="props.type === 'overtime'" class="px-4 py-3"></th>
                <th v-if="props.type === 'overtime'" class="px-4 py-3"></th>
                <!-- Empty filter untuk kolom Attendance Correction -->
                <th
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-3"
                ></th>
                <th
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-3"
                ></th>
                <th
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-3"
                ></th>
                <!-- Filter untuk kolom Reimbursement -->
                <th v-if="props.type === 'reimbursement'" class="px-4 py-3">
                  <div class="relative">
                    <input
                      type="text"
                      v-model="filters.title"
                      @input="debounceSearch"
                      class="w-full px-3 py-2 pr-10 text-sm bg-[#fbfbfb] border border-[#DBDFE9] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                      placeholder="Cari judul..."
                    />
                    <Search
                      class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2"
                    />
                  </div>
                </th>
                <th v-if="props.type === 'reimbursement'" class="px-4 py-3">
                  <div class="relative">
                    <input
                      type="date"
                      v-model="filters.event_start_date"
                      @change="applyFilters"
                      class="w-full px-3 py-2 text-sm bg-[#fbfbfb] border border-[#DBDFE9] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                      placeholder="dd/mm/yyyy"
                    />
                    <Calendar
                      class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2"
                    />
                  </div>
                </th>
                <th
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-3"
                ></th>
                <!-- Empty filter untuk kolom Piutang -->
                <th v-if="props.type === 'debt'" class="px-4 py-3"></th>
                <th v-if="props.type === 'debt'" class="px-4 py-3"></th>
                <th v-if="props.type === 'debt'" class="px-4 py-3"></th>
                <!-- Empty filter untuk kolom General -->
                <th v-if="props.type === 'general'" class="px-4 py-3"></th>
                <th v-if="props.type === 'general'" class="px-4 py-3"></th>
                <th class="px-4 py-3">
                  <div class="relative">
                    <select
                      v-model="filters.status"
                      @change="applyFilters"
                      class="w-full px-3 py-2 pr-10 text-sm bg-[#fbfbfb] border border-[#DBDFE9] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875] appearance-none"
                    >
                      <option value="">Pilih Status</option>
                      <option
                        v-for="status in props.submission_statuses"
                        :key="status.value"
                        :value="status.value"
                      >
                        {{ status.label }}
                      </option>
                    </select>
                    <Filter
                      class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 pointer-events-none right-3 top-1/2"
                    />
                  </div>
                </th>
                <th class="px-4 py-3 text-center">
                  <button
                    @click="clearFilters"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#002875] rounded-lg hover:bg-[#001d5c] transition-colors"
                  >
                    <RotateCcw class="w-4 h-4" />
                    Clear
                  </button>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white">
              <!-- Empty state -->
              <tr v-if="datatableConfig.data.length === 0">
                <td :colspan="getTotalColumns()" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-40 h-40 mb-6">
                      <img
                        src="/images/empty-state.svg"
                        alt="No Data"
                        class="object-contain w-full h-full"
                        onerror="
                          this.style.display = 'none';
                          this.nextElementSibling.style.display = 'flex';
                        "
                      />
                      <div
                        class="items-center justify-center hidden w-24 h-24 mx-auto bg-gray-100 rounded-full"
                      >
                        <FileText class="w-10 h-10 text-gray-400" />
                      </div>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">
                      Tidak ada data
                    </h3>
                    <p class="text-sm text-gray-500">
                      Belum ada pengajuan yang tersedia
                    </p>
                  </div>
                </td>
              </tr>
              <!-- Data rows -->
              <tr
                v-for="(context, index) in datatableConfig.data"
                v-else
                :key="context.id"
                @click="openModal(context, props.type)"
                class="border-b border-gray-100 hover:bg-[#f5f8ff] transition-colors cursor-pointer"
              >
                <td class="px-4 py-4 text-sm text-center text-gray-900">
                  {{
                    index +
                    (datatableConfig.currentPage - 1) *
                      datatableConfig.perPage +
                    1
                  }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ formatDateTime(context?.tanggal_pengajuan) }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{
                    props.type === "employee"
                      ? context?.name || "-"
                      : context?.employee?.name || "-"
                  }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ context?.employee?.branch_name || "-" }}
                </td>
                <!-- Kolom khusus Izin Lainnya -->
                <td
                  v-if="props.type === 'others'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.leave_type_name || "-" }}
                </td>
                <!-- Kolom khusus Cuti (Izin Sakit, Izin Cuti, Izin Lainnya) -->
                <td
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatDateWithDay(context?.start_date) }}
                </td>
                <td
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatDateWithDay(context?.end_date) }}
                </td>
                <td
                  v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                  class="px-4 py-4 text-sm text-center text-gray-900"
                >
                  {{ context?.total_days ? context.total_days + " hari" : "-" }}
                </td>
                <!-- Kolom khusus Lembur -->
                <td
                  v-if="props.type === 'overtime'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.start_time || "-" }}
                </td>
                <td
                  v-if="props.type === 'overtime'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.end_time || "-" }}
                </td>
                <!-- Kolom khusus Attendance Correction -->
                <td
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatDate(context?.date) }}
                </td>
                <td
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.time_in || "-" }}
                </td>
                <td
                  v-if="props.type === 'attendance-correction'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.time_out || "-" }}
                </td>
                <!-- Kolom khusus Reimbursement -->
                <td
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.title || "-" }}
                </td>
                <td
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatDate(context?.event_date) }}
                </td>
                <td
                  v-if="props.type === 'reimbursement'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatCurrency(context?.amount) || "-" }}
                </td>
                <!-- Kolom khusus General Submission -->
                <td
                  v-if="props.type === 'general'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.title || "-" }}
                </td>
                <td
                  v-if="props.type === 'general'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  <div class="flex flex-wrap gap-1">
                    <template v-if="Array.isArray(context?.tag)">
                      <span
                        v-for="(tag, i) in context.tag"
                        :key="i"
                        class="px-2 py-1 text-xs bg-gray-100 rounded"
                      >
                        {{ tag }}
                      </span>
                    </template>
                    <template v-else>
                      <span class="px-2 py-1 text-xs bg-gray-100 rounded">{{
                        context?.tag || "-"
                      }}</span>
                    </template>
                  </div>
                </td>

                <!-- Kolom khusus Piutang -->
                <td
                  v-if="props.type === 'debt'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ context?.tenor ? context.tenor + " bulan" : "-" }}
                </td>
                <td
                  v-if="props.type === 'debt'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatCurrency(context?.amount) || "-" }}
                </td>
                <td
                  v-if="props.type === 'debt'"
                  class="px-4 py-4 text-sm text-gray-900"
                >
                  {{ formatCurrency(context?.remaining_amount) }}
                </td>
                <td class="px-4 py-4">
                  <div
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap"
                    :class="getStatusClass(context.status)"
                  >
                    <span
                      class="flex-shrink-0 w-2 h-2 rounded-full"
                      :class="getStatusDotClass(context.status)"
                    ></span>
                    <span class="truncate">{{
                      getStatusLabel(context.status)
                    }}</span>
                  </div>
                </td>
                <td class="px-2 py-2 text-center align-middle" @click.stop>
                  <div class="flex justify-center gap-2">
                    <button
                      @click.stop="openModal(context, props.type)"
                      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 transition border border-blue-100 rounded-md bg-blue-50 hover:bg-blue-100"
                    >
                      <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M11.9992 15.2094C13.77 15.2094 15.2055 13.7739 15.2055 12.0031C15.2055 10.2324 13.77 8.79688 11.9992 8.79688C10.2285 8.79688 8.79297 10.2324 8.79297 12.0031C8.79297 13.7739 10.2285 15.2094 11.9992 15.2094Z"
                          fill="#1B84FF"
                        />
                        <path
                          opacity="0.3"
                          d="M12 2.36719C6.67875 2.36719 0.75 7.56469 0.75 11.9972C0.75 16.2272 6.67875 21.6272 12 21.6272C17.625 21.6272 23.25 16.4634 23.25 11.9972C23.25 7.69969 17.3213 2.36719 12 2.36719ZM12 16.8909C11.0321 16.8909 10.086 16.6039 9.28118 16.0662C8.4764 15.5285 7.84916 14.7642 7.47876 13.8699C7.10837 12.9757 7.01146 11.9918 7.20028 11.0425C7.38911 10.0932 7.85519 9.22119 8.5396 8.53678C9.22401 7.85238 10.0961 7.3863 11.0453 7.19747C11.9946 7.00864 12.9785 7.10556 13.8728 7.47595C14.7671 7.84635 15.5313 8.47359 16.0691 9.27837C16.6068 10.0831 16.8938 11.0293 16.8938 11.9972C16.8938 13.2951 16.3782 14.5398 15.4604 15.4576C14.5426 16.3753 13.2979 16.8909 12 16.8909Z"
                          fill="#1B84FF"
                        />
                      </svg>
                      <span>Lihat</span>
                    </button>

                    <!-- Button Pembayaran untuk type debt - hanya muncul jika status disetujui -->
                    <button
                      v-if="props.type === 'debt' && isDebtApproved(context)"
                      @click.stop="openPaymentModal(context)"
                      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-green-700 transition border border-green-100 rounded-md bg-green-50 hover:bg-green-100"
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
                          d="M12 4v16m8-8H4"
                        ></path>
                      </svg>
                      <span>Pembayaran</span>
                    </button>

                    <!-- Edit and Delete buttons for employee type -->
                    <template v-if="props.type === 'employee'">
                      <button
                        v-if="can('submission_daily.edit')"
                        @click="
                          context?.status !== 'approved' &&
                            context?.status !== 'rejected' &&
                            openEditModal(context);
                          $event.stopPropagation();
                        "
                        :disabled="
                          context?.status === 'approved' ||
                          context?.status === 'rejected'
                        "
                        :class="[
                          'inline-flex gap-1 items-center px-2 py-1 text-xs font-medium rounded-md border transition',
                          context?.status === 'approved' ||
                          context?.status === 'rejected'
                            ? 'text-gray-500 bg-gray-100 border-gray-200 cursor-not-allowed opacity-60'
                            : 'text-yellow-700 bg-yellow-50 border-yellow-100 hover:bg-yellow-100',
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
                          ></path>
                        </svg>
                        <span>Edit</span>
                      </button>
                      <button
                        v-if="can('submission_daily.delete')"
                        @click="
                          confirmDelete(context.id);
                          $event.stopPropagation();
                        "
                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-700 transition border border-red-100 rounded-md bg-red-50 hover:bg-red-100"
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          ></path>
                        </svg>
                        <span>Hapus</span>
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Card Footer: Pagination -->
      <div class="px-6 py-3 border-t border-[#DBDFE9]">
        <Pagination
          :pagination="submissions.meta"
          @page-change="changePage"
          @per-page-change="perPageChanged"
        />
      </div>
    </div>

    <!-- Modal Components -->
    <DetailSick
      v-if="modalState.type === 'sick'"
      :is-open="modalState.isOpen && modalState.type === 'sick'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailOvertime
      v-if="modalState.type === 'overtime'"
      :is-open="modalState.isOpen && modalState.type === 'overtime'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailOther
      v-if="modalState.type === 'others'"
      :is-open="modalState.isOpen && modalState.type === 'others'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailDebt
      v-if="modalState.type === 'debt'"
      :is-open="modalState.isOpen && modalState.type === 'debt'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailDailyReport
      v-if="modalState.type === 'employee'"
      :is-open="modalState.isOpen && modalState.type === 'employee'"
      :submission="modalState.submission"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailAnnualLeave
      v-if="modalState.type === 'annual-leave'"
      :is-open="modalState.isOpen && modalState.type === 'annual-leave'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailReimbursement
      v-if="modalState.type === 'reimbursement'"
      :is-open="modalState.isOpen && modalState.type === 'reimbursement'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <DetailGeneral
      v-if="modalState.type === 'general'"
      :is-open="modalState.isOpen && modalState.type === 'general'"
      :submission="modalState.submission"
      :type="modalState.type"
      :submission-types="props.submission_types"
      :submission-statuses="props.submission_statuses"
      :loading="modalState.loading"
      :error="modalState.error"
      @close="closeModal"
      @update-success="handleModalUpdateSuccess"
    />

    <!-- Create/Edit Daily Report Modal -->
    <div
      v-if="props.type === 'employee'"
      class="overflow-y-auto fixed inset-0 z-[9999] w-full h-full bg-gray-600 bg-opacity-50"
      :class="{ hidden: !formModalState.isOpen }"
    >
      <div
        class="relative w-11/12 p-5 mx-auto bg-white border rounded-md shadow-lg top-20 md:w-2/3 lg:w-1/2"
      >
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{
                formModalState.isEdit
                  ? "Edit Pengajuan karyawan Harian"
                  : "Tambah Pengajuan karyawan Harian"
              }}
            </h3>
            <button
              @click="closeFormModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                ></path>
              </svg>
            </button>
          </div>

          <!-- Loading State -->
          <div
            v-if="formModalState.loading"
            class="flex items-center justify-center p-8"
          >
            <div class="text-center">
              <div
                class="w-12 h-12 mx-auto mb-4 border-b-2 border-blue-600 rounded-full animate-spin"
              ></div>
              <p class="text-gray-600">Memuat data pengajuan...</p>
            </div>
          </div>

          <!-- Error State -->
          <div
            v-else-if="formModalState.error"
            class="p-4 mb-4 border border-red-200 rounded-md bg-red-50"
          >
            <p class="text-sm text-red-600">{{ formModalState.error }}</p>
            <button
              @click="closeFormModal"
              class="px-4 py-2 mt-2 text-sm text-white bg-red-600 rounded hover:bg-red-700"
            >
              Tutup
            </button>
          </div>

          <form v-else @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- Start Date -->
              <div>
                <label class="block mb-1 text-sm font-medium text-gray-700"
                  >Tanggal Mulai *</label
                >
                <input
                  type="date"
                  v-model="formData.start_date"
                  required
                  class="px-3 py-2 w-full rounded-md bg-[#fbfbfb] border border-[#DBDFE9] focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                />
                <div
                  v-if="formErrors.start_date"
                  class="mt-1 text-sm text-red-500"
                >
                  {{ formErrors.start_date }}
                </div>
              </div>

              <!-- End Date -->
              <div>
                <label class="block mb-1 text-sm font-medium text-gray-700"
                  >Tanggal Akhir *</label
                >
                <input
                  type="date"
                  v-model="formData.end_date"
                  required
                  class="px-3 py-2 w-full rounded-md bg-[#fbfbfb] border border-[#DBDFE9] focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                />
                <div
                  v-if="formErrors.end_date"
                  class="mt-1 text-sm text-red-500"
                >
                  {{ formErrors.end_date }}
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- Name -->
              <div>
                <label class="block mb-1 text-sm font-medium text-gray-700"
                  >Nama Karyawan Harian *</label
                >
                <input
                  type="text"
                  v-model="formData.name"
                  required
                  class="px-3 py-2 w-full rounded-md bg-[#fbfbfb] border border-[#DBDFE9] focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                  placeholder="Masukkan nama karyawan harian"
                />
                <div v-if="formErrors.name" class="mt-1 text-sm text-red-500">
                  {{ formErrors.name }}
                </div>
              </div>

              <!-- Salary -->
              <div>
                <label class="block mb-1 text-sm font-medium text-gray-700"
                  >Gaji *</label
                >
                <input
                  type="text"
                  v-model="salaryDisplay"
                  @input="handleSalaryInput"
                  @blur="handleSalaryBlur"
                  required
                  class="px-3 py-2 w-full rounded-md bg-[#fbfbfb] border border-[#DBDFE9] focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                  placeholder="Masukkan gaji"
                />
                <div v-if="formErrors.salary" class="mt-1 text-sm text-red-500">
                  {{ formErrors.salary }}
                </div>
              </div>
            </div>

            <!-- Reason -->
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Alasan *</label
              >
              <textarea
                v-model="formData.reason"
                required
                rows="3"
                class="px-3 py-2 w-full rounded-md bg-[#fbfbfb] border border-[#DBDFE9] focus:outline-none focus:ring-2 focus:ring-[#002875]/20 focus:border-[#002875]"
                placeholder="Masukkan alasan pengajuan..."
              ></textarea>
              <div v-if="formErrors.reason" class="mt-1 text-sm text-red-500">
                {{ formErrors.reason }}
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="closeFormModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="formModalState.loading"
                class="px-4 py-2 text-sm font-medium text-white bg-[#002875] rounded-md border border-transparent hover:bg-[#002875] focus:outline-none focus:ring-2 focus:ring-[#002875] disabled:opacity-50"
              >
                <span v-if="formModalState.loading">Menyimpan...</span>
                <span v-else>{{
                  formModalState.isEdit ? "Update" : "Simpan"
                }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Payment Modal for Debt -->
    <div
      v-if="paymentModalState.isOpen"
      @click="closePaymentModal"
      class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center p-4"
    >
      <div
        @click.stop
        class="w-full max-w-4xl bg-white rounded-lg shadow-xl"
        style="max-height: 88vh; overflow-y: auto"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200"
        >
          <h2 class="text-xl font-bold text-gray-900">
            Proses Pembayaran Piutang
          </h2>
          <button
            @click="closePaymentModal"
            class="p-2 text-gray-400 transition-colors rounded-full hover:text-gray-600 hover:bg-gray-100"
            title="Tutup"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div
          v-if="paymentModalState.loading"
          class="flex items-center justify-center p-8"
        >
          <div class="text-center">
            <div
              class="w-12 h-12 mx-auto mb-4 border-b-2 border-blue-600 rounded-full animate-spin"
            ></div>
            <p class="text-gray-600">Memuat data pembayaran...</p>
          </div>
        </div>

        <div
          v-else-if="paymentModalState.error"
          class="flex items-center justify-center p-8"
        >
          <div class="text-center">
            <div class="mb-4 text-red-500">
              <svg
                class="w-12 h-12 mx-auto"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
            </div>
            <p class="mb-4 text-red-600">{{ paymentModalState.error }}</p>
            <button
              @click="closePaymentModal"
              class="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-600"
            >
              Tutup
            </button>
          </div>
        </div>

        <div v-else class="p-6 space-y-4">
          <!-- Info Piutang -->
          <div
            class="grid grid-cols-1 gap-4 p-4 border rounded-lg md:grid-cols-2 bg-gray-50"
          >
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Nama</label
              >
              <p class="text-sm text-gray-900">
                {{ paymentModalState.submission?.employee?.name || "-" }}
              </p>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Jumlah Piutang</label
              >
              <p class="text-sm font-semibold text-gray-900">
                {{
                  formatCurrency(paymentModalState.submission?.amount) || "-"
                }}
              </p>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Total Dibayar</label
              >
              <p class="text-sm font-semibold text-green-600">
                {{ formatCurrency(totalPaid) }}
              </p>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Sisa Piutang</label
              >
              <p class="text-sm font-semibold text-red-600">
                {{ formatCurrency(remainingDebt) }}
              </p>
            </div>
          </div>

          <!-- Header untuk Form dan Riwayat -->
          <div
            class="flex items-center justify-between pb-3 border-b border-gray-200"
          >
            <h3 class="text-lg font-semibold text-gray-900">
              Riwayat Pembayaran
            </h3>
            <button
              @click="showPaymentForm = !showPaymentForm"
              class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium text-white bg-[#002875] rounded-md hover:bg-[#002875] focus:outline-none focus:ring-2 focus:ring-[#002875] transition-colors"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                ></path>
              </svg>
              <span>Tambah Pembayaran</span>
            </button>
          </div>

          <!-- Form Pembayaran -->
          <div
            v-if="showPaymentForm"
            class="p-4 border border-blue-200 rounded-lg bg-blue-50"
          >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="block mb-1 text-sm font-medium text-gray-700"
                  >Tanggal Pembayaran *</label
                >
                <input
                  type="date"
                  v-model="paymentForm.paid_date"
                  required
                  class="w-full p-3 rounded border border-[#DBDFE9] bg-[#fbfbfb] text-sm focus:border-[#002875] focus:outline-none focus:ring-2 focus:ring-[#002875]/20"
                  :class="{ 'border-red-300': paymentFormErrors.paid_date }"
                />
                <p
                  v-if="paymentFormErrors.paid_date"
                  class="mt-1 text-xs text-red-500"
                >
                  {{ paymentFormErrors.paid_date }}
                </p>
              </div>
              <div>
                <label class="block mb-1 text-sm font-medium text-gray-700"
                  >Jumlah Pembayaran *</label
                >
                <input
                  type="text"
                  v-model="paymentFormDisplay.amount"
                  @input="handlePaymentAmountInput"
                  @blur="handlePaymentAmountBlur"
                  required
                  class="w-full p-3 rounded border border-[#DBDFE9] bg-[#fbfbfb] text-sm focus:border-[#002875] focus:outline-none focus:ring-2 focus:ring-[#002875]/20"
                  :class="{ 'border-red-300': paymentFormErrors.amount }"
                  placeholder="Masukkan jumlah pembayaran"
                />
                <p
                  v-if="paymentFormErrors.amount"
                  class="mt-1 text-xs text-red-500"
                >
                  {{ paymentFormErrors.amount }}
                </p>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
              <button
                @click="cancelPaymentForm"
                class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Batal
              </button>
              <button
                @click="submitPayment"
                :disabled="paymentModalState.processing"
                class="px-4 py-2 text-sm font-medium text-white bg-[#002875] rounded-md border border-transparent hover:bg-[#002875] focus:outline-none focus:ring-2 focus:ring-[#002875] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="paymentModalState.processing">Menyimpan...</span>
                <span v-else>Simpan Pembayaran</span>
              </button>
            </div>
          </div>

          <!-- Riwayat Pembayaran -->
          <div class="space-y-2">
            <div
              v-if="paymentHistory.length === 0"
              class="py-8 text-center text-gray-500"
            >
              <p>Belum ada riwayat pembayaran</p>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase"
                    >
                      No
                    </th>
                    <th
                      class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase"
                    >
                      Tanggal Pembayaran
                    </th>
                    <th
                      class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase"
                    >
                      Jumlah
                    </th>
                    <th
                      class="px-4 py-3 text-xs font-semibold text-left text-gray-600 uppercase"
                    >
                      Aksi
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="(payment, index) in paymentHistory"
                    :key="payment.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-4 py-3 text-gray-900">{{ index + 1 }}</td>
                    <td class="px-4 py-3 text-gray-900">
                      {{ payment.paid_date }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-900">
                      {{ formatCurrency(payment.amount) }}
                    </td>
                    <td class="px-4 py-3">
                      <button
                        @click="deletePayment(payment.id)"
                        class="text-xs font-medium text-red-600 hover:text-red-800"
                      >
                        Hapus
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="deleteModalState.isOpen"
      class="overflow-y-auto fixed inset-0 z-[9999] w-full h-full bg-gray-600 bg-opacity-50"
    >
      <div
        class="relative p-5 mx-auto bg-white border rounded-md shadow-lg top-20 w-96"
      >
        <div class="mt-3 text-center">
          <div
            class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full"
          >
            <svg
              class="w-6 h-6 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
              ></path>
            </svg>
          </div>
          <h3 class="mt-2 text-lg font-medium text-gray-900">
            Konfirmasi Hapus
          </h3>
          <div class="py-3 mt-2 px-7">
            <p class="text-sm text-gray-500">
              Apakah Anda yakin ingin menghapus pengajuan laporan harian ini?
              Tindakan ini tidak dapat dibatalkan.
            </p>
          </div>
          <div class="flex justify-center gap-3 mt-4">
            <button
              @click="closeDeleteModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Batal
            </button>
            <button
              @click="deleteSubmission"
              :disabled="deleteModalState.loading"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
            >
              <span v-if="deleteModalState.loading">Menghapus...</span>
              <span v-else>Hapus</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/PaginationResource.vue";
import SubmissionsTable from "./components/SubmissionsTable.vue";
import DetailSick from "./components/DetailSick.vue";
import DetailOvertime from "./components/DetailOvertime.vue";
import DetailOther from "./components/DetailOther.vue";
import DetailDebt from "./components/DetailDebt.vue";
import DetailDailyReport from "./components/DetailDailyReport.vue";
import DetailAnnualLeave from "./components/DetailAnnualLeave.vue";
import DetailReimbursement from "./components/DetailReimbursement.vue";
import DetailGeneral from "./components/DetailGeneral.vue";
import Select from "@/Components/form/SelectBrng.vue";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import { ref, watch, computed, reactive } from "vue";
import { useForm, router, usePage, Head, Link } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

import {
  ChevronRight,
  Plus,
  Timer,
  CheckCircle2,
  XCircle,
  Undo2,
  ArrowUpDown,
  Calendar,
  Search,
  Filter,
  RotateCcw,
} from "lucide-vue-next";

function formatDate(value) {
  if (!value) return "-";

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;

  return parsed.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function formatDateTime(value) {
  if (!value) return "-";

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;

  return parsed.toLocaleString("id-ID", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatDateWithDay(value) {
  if (!value) return "-";

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;

  return parsed.toLocaleDateString("id-ID", {
    weekday: "long",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

const props = defineProps({
  submissions: Object,
  sort_by: String,
  sort_direction: String,
  search: String,
  start_date: String,
  end_date: String,
  name_filter: String,
  branch_filter: String,
  type_filter: String,
  status_filter: String,
  title_filter: String,
  event_start_date: String,
  event_end_date: String,
  branches: Array,
  employees: Array,
  submission_types: Array,
  submission_statuses: Array,
  statistics: Object,
  type: String,
  types: Array,
  auth: Object,
});

const { user, is, can } = useAuth();
const toast = useToast();

// Get pending counts from page props
const page = usePage();
const pendingCounts = computed(
  () => page.props.pendingSubmissionsCount || { total: 0, by_type: {} },
);
console.log("=== PENDING COUNTS ===", pendingCounts.value);
console.log("=== Label ===", props.submission_types);

const datatableConfig = reactive({
  showHeader: true,
  perPage: props.submissions?.meta?.per_page ?? 10,
  search: props.search ?? "",
  title: "Daftar Pengajuan",
  columns: [
    {
      label: "No",
      key: "id",
      sortable: true,
      style: "width: 8%; text-align: center;",
    },
    {
      label: "Tanggal",
      key: "submission_date",
      sortable: true,
      style: "width: 15%; text-align: left;",
    },
    {
      label: "Nama",
      key: "employee_id",
      sortable: true,
      style: "width: 25%; text-align: left;",
    },
    {
      label: "Cabang",
      key: "branch_id",
      sortable: true,
      style: "width: 25%; text-align: left;",
    },
    {
      label: "Tipe",
      key: "type",
      sortable: true,
      style: "width: 15%; text-align: left;",
    },
    {
      label: "Status",
      key: "status",
      sortable: true,
      style: "width: 8%; text-align: center;",
    },
    {
      label: "Aksi",
      key: "actions",
      sortable: false,
      style: "width: 5%; text-align: center;",
    },
  ],
  data: props.submissions?.data ?? [],
  loading: false,
  totalItems: props.submissions?.meta?.total ?? 0,
  currentPage: props.submissions?.meta?.current_page ?? 1,
  sortBy: props.sort_by ?? "id",
  sortDirection: props.sort_direction ?? "desc",
});

// Filter states
const filters = reactive({
  start_date: props.start_date ?? "",
  end_date: props.end_date ?? "",
  name: props.name_filter ?? "",
  branch: props.branch_filter ?? "",
  type: props.type_filter ?? "",
  status: props.status_filter ?? "",
  title: props.title_filter ?? "",
  event_start_date: props.event_start_date ?? "",
  event_end_date: props.event_end_date ?? "",
});

const breadcrumbs = [{ label: "Absensi" }, { label: "Daftar Pengajuan" }];

// Modal state management
const modalState = reactive({
  isOpen: false,
  type: null,
  submission: null,
  loading: false,
  error: null,
});

// Form modal state management
const formModalState = reactive({
  isOpen: false,
  isEdit: false,
  loading: false,
  error: null,
});

// Delete modal state management
const deleteModalState = reactive({
  isOpen: false,
  loading: false,
  submissionId: null,
});

// Payment modal state management
const paymentModalState = reactive({
  isOpen: false,
  loading: false,
  error: null,
  processing: false,
  submission: null,
  receivableId: null,
});

// Payment form state
const showPaymentForm = ref(false);
const paymentForm = reactive({
  paid_date: "",
  amount: 0,
});

const paymentFormDisplay = reactive({
  amount: "",
});

const paymentFormErrors = reactive({});
const paymentHistory = ref([]);

// Form data
const formData = reactive({
  employee_id: "",
  start_date: "",
  end_date: "",
  name: "",
  salary: 0,
  reason: "",
  status: "pending",
});

// Form errors
const formErrors = reactive({});

// Salary display (formatted as rupiah)
const salaryDisplay = ref("");

// Employees list for dropdown
const employees = ref(props.employees || []);

// Format employees for Select component with display label
const formattedEmployees = computed(() => {
  return (props.employees || []).map((employee) => ({
    ...employee,
    displayLabel: `${employee.name} - ${employee.branch_name || "N/A"}`,
  }));
});

// Reactive reference to track filtered submissions data from SubmissionsTable
const filteredSubmissions = ref(props.submissions?.data || []);

// Function to calculate statistics from submissions data
function calculateStatisticsFromData(submissions) {
  const stats = {
    pending: 0,
    approved: 0,
    rejected: 0,
    cancelled: 0,
  };

  submissions.forEach((submission) => {
    const status = submission.status;
    if (stats.hasOwnProperty(status)) {
      stats[status]++;
    }
  });

  return stats;
}

// Handle statistics - use filtered data when SubmissionsTable is active, otherwise use server-side statistics
const statsCards = computed(() => {
  let statistics;

  if (props.type === "submissions") {
    // Calculate statistics from filtered submissions data
    statistics = calculateStatisticsFromData(filteredSubmissions.value);
  } else {
    // Use server-side statistics for other types
    statistics = props.statistics || {};
  }

  return [
    {
      icon: Timer,
      value: statistics.pending ?? 0,
      label: "Menunggu Direspon",
      bgClass: "bg-amber-50",
      iconClass: "text-amber-600",
    },
    {
      icon: CheckCircle2,
      value: statistics.approved ?? 0,
      label: "Disetujui",
      bgClass: "bg-green-50",
      iconClass: "text-green-600",
    },
    {
      icon: XCircle,
      value: statistics.rejected ?? 0,
      label: "Ditolak",
      bgClass: "bg-red-50",
      iconClass: "text-red-600",
    },
    {
      icon: Undo2,
      value: statistics.cancelled ?? 0,
      label: "Dibatalkan",
      bgClass: "bg-gray-50",
      iconClass: "text-gray-600",
    },
  ];
});

// Status styling functions
function getStatusLabel(status) {
  console.log(status);
  const statusMap = {
    pending: "Menunggu",
    approved: "Disetujui",
    rejected: "Ditolak",
    cancelled: "Dibatalkan",
  };
  return statusMap[status] || "Menunggu";
}

function getStatusClass(status) {
  const statusMap = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
    cancelled: "bg-gray-100 text-gray-800",
  };
  return statusMap[status] || "bg-gray-100 text-gray-800";
}

function getStatusDotClass(status) {
  const dotMap = {
    pending: "bg-yellow-500",
    approved: "bg-green-500",
    rejected: "bg-red-500",
    cancelled: "bg-gray-500",
  };
  return dotMap[status] || "bg-gray-500";
}

// Check if debt/receivable status is approved
function isDebtApproved(context) {
  if (!context || !context.status) return false;

  const status = context.status;

  // Handle string status
  if (typeof status === "string") {
    return status === "approved" || status === "disetujui";
  }

  // Handle object with value property
  if (typeof status === "object" && status !== null) {
    const statusValue = status.value || status.status || status;
    if (typeof statusValue === "string") {
      return statusValue === "approved" || statusValue === "disetujui";
    }
  }

  return false;
}

const dateFormat = (date) => {
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(date).toLocaleDateString(undefined, options);
};

// Format date for HTML date input (YYYY-MM-DD)
const formatDateForInput = (date) => {
  if (!date) return "";
  // Handle both date string formats: "Y-m-d H:i" or "Y-m-d" or "d-m-Y"
  let dateStr = date;

  // If date contains time, extract only date part
  if (dateStr.includes(" ")) {
    dateStr = dateStr.split(" ")[0];
  }

  // If date is in d-m-Y format, convert to Y-m-d
  if (dateStr.includes("-") && dateStr.split("-")[0].length === 2) {
    const parts = dateStr.split("-");
    dateStr = `${parts[2]}-${parts[1]}-${parts[0]}`;
  }

  // Parse and format
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) {
    return "";
  }
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

// Format currency to Indonesian Rupiah
const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return "-";
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// Format number to rupiah string (without currency symbol)
const formatRupiah = (amount) => {
  if (!amount && amount !== 0) return "";
  return new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// Parse rupiah string to number
const parseRupiah = (rupiahString) => {
  if (!rupiahString) return 0;
  // Remove all non-digit characters
  const cleaned = rupiahString.toString().replace(/\D/g, "");
  return cleaned ? parseInt(cleaned, 10) : 0;
};

// Handle salary input
function handleSalaryInput(event) {
  const value = event.target.value;
  // Remove all non-digit characters
  const numericValue = parseRupiah(value);
  // Update formData with numeric value
  formData.salary = numericValue;
  // Update display with formatted value
  salaryDisplay.value = numericValue > 0 ? formatRupiah(numericValue) : "";
}

// Handle salary blur (final format)
function handleSalaryBlur() {
  if (formData.salary > 0) {
    salaryDisplay.value = formatRupiah(formData.salary);
  } else {
    salaryDisplay.value = "";
  }
}

// Calculate total columns based on submission type
function getTotalColumns() {
  // Base columns that are always present
  let columns = 4; // No, Tanggal Pengajuan, Karyawan, Cabang

  // Add Kategori Izin for 'others' type
  if (props.type === "others") {
    columns += 1;
  }

  // Add date range columns for sick/annual-leave/others
  if (["sick", "annual-leave", "others"].includes(props.type)) {
    columns += 3; // Tanggal Mulai, Tanggal Akhir, Total Hari
  }

  // Add time columns for overtime
  if (props.type === "overtime") {
    columns += 2; // Waktu Mulai, Waktu Selesai
  }

  // Add title, date, and amount columns for reimbursement
  if (props.type === "reimbursement") {
    columns += 3; // Judul Reimbursement, Tanggal, Jumlah
  }

  // Add tenor, amount, and total paid columns for debt
  if (props.type === "debt") {
    columns += 3; // Tenor, Jumlah Piutang, Jumlah yang Dibayar
  }

  // Add columns for general
  if (props.type === "general") {
    columns += 2; // Title, Tag
  }

  // Add columns for attendance correction
  if (props.type === "attendance-correction") {
    columns += 3; // Tanggal Absen, Waktu Masuk, Waktu Keluar
  }

  // Add Status and Aksi (always at the end)
  columns += 2; // Status, Aksi

  return columns;
}

function fetchSubmissions({
  page = 1,
  perPage = datatableConfig.perPage,
  search = datatableConfig.search,
  sortBy = datatableConfig.sortBy,
  sortDirection = datatableConfig.sortDirection,
  startDate = filters.start_date,
  endDate = filters.end_date,
  nameFilter = filters.name,
  branchFilter = filters.branch,
  typeFilter = filters.type,
  statusFilter = filters.status,
  titleFilter = filters.title,
  eventStartDate = filters.event_start_date,
  eventEndDate = filters.event_end_date,
  onSuccess,
  onFinish,
} = {}) {
  datatableConfig.loading = true;
  const params = {
    page,
    per_page: perPage,
    search,
    sort_by: sortBy,
    sort_direction: sortDirection,
    start_date: startDate,
    end_date: endDate,
    name_filter: nameFilter,
    branch_filter: branchFilter,
    type_filter: typeFilter,
    status_filter: statusFilter,
    type: props.type,
  };

  // Add reimbursement-specific filters
  if (props.type === "reimbursement") {
    params.title_filter = titleFilter;
    params.event_start_date = eventStartDate;
    params.event_end_date = eventEndDate;
  }

  router.get(`/${props.type}`, params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      datatableConfig.data = page.props.submissions.data;
      datatableConfig.totalItems = page.props.submissions.meta.total;
      datatableConfig.currentPage = page.props.submissions.meta.current_page;
      datatableConfig.loading = false;

      if (onSuccess) onSuccess(page);
    },
    onFinish: () => {
      datatableConfig.loading = false;
      if (onFinish) onFinish();
    },
  });
}

function perPageChanged(newPerPage) {
  console.log("perPageChanged called with:", newPerPage);
  datatableConfig.perPage = newPerPage;
  datatableConfig.currentPage = 1;
  fetchSubmissions({
    page: 1,
    perPage: newPerPage,
    startDate: filters.start_date,
    endDate: filters.end_date,
    nameFilter: filters.name,
    branchFilter: filters.branch,
    typeFilter: filters.type,
    statusFilter: filters.status,
    titleFilter: filters.title,
    eventStartDate: filters.event_start_date,
    eventEndDate: filters.event_end_date,
  });
}

function changeSort(col) {
  const key = typeof col === "string" ? col : col?.key;
  const direction =
    datatableConfig.sortBy === key && datatableConfig.sortDirection === "asc"
      ? "desc"
      : "asc";
  datatableConfig.sortBy = key;
  datatableConfig.sortDirection = direction;
  fetchSubmissions({
    page: datatableConfig.currentPage,
    sortBy: key,
    sortDirection: direction,
  });
}

function changeSearch(value) {
  datatableConfig.search = value;
  datatableConfig.currentPage = 1;
  fetchSubmissions({ page: 1, search: value });
}

function changePage(page) {
  datatableConfig.currentPage = page;
  fetchSubmissions({
    page,
    startDate: filters.start_date,
    endDate: filters.end_date,
    nameFilter: filters.name,
    branchFilter: filters.branch,
    typeFilter: filters.type,
    statusFilter: filters.status,
    titleFilter: filters.title,
    eventStartDate: filters.event_start_date,
    eventEndDate: filters.event_end_date,
  });
}

// Filter functions
function applyFilters() {
  datatableConfig.currentPage = 1;
  fetchSubmissions({
    page: 1,
    startDate: filters.start_date,
    endDate: filters.end_date,
    nameFilter: filters.name,
    branchFilter: filters.branch,
    typeFilter: filters.type,
    statusFilter: filters.status,
    titleFilter: filters.title,
    eventStartDate: filters.event_start_date,
    eventEndDate: filters.event_end_date,
  });
}

function clearFilters() {
  filters.start_date = "";
  filters.end_date = "";
  filters.name = "";
  filters.branch = "";
  filters.type = "";
  filters.status = "";
  filters.title = "";
  filters.event_start_date = "";
  filters.event_end_date = "";
  datatableConfig.currentPage = 1;
  fetchSubmissions({ page: 1 });
}

// Debounce search for name filter
let searchTimeout;
function debounceSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
}

// Modal functions
async function openModal(submission, type) {
  modalState.isOpen = true;
  modalState.type = type;
  modalState.loading = true;
  modalState.error = null;

  // Set initial submission data
  modalState.submission = submission;
  console.log(type);

  try {
    // Fetch detailed submission data using fetch API
    const response = await fetch(
      route(`submission.${type}.show`, submission.id),
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN":
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "",
        },
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success) {
      // Wrap the data in the expected structure for modal components
      modalState.submission = {
        data: data.data,
        submission_types: data.submission_types,
        submission_statuses: data.submission_statuses,
        type: data.type,
      };
      modalState.loading = false;
    } else {
      throw new Error(data.message || "Gagal memuat detail pengajuan");
    }
  } catch (error) {
    console.error("Error fetching submission details:", error);
    modalState.error = error.message || "Gagal memuat detail pengajuan";
    modalState.loading = false;
  }
}

function closeModal() {
  modalState.isOpen = false;
  modalState.submission = null;
  modalState.type = null;
  modalState.loading = false;
  modalState.error = null;
}

// Function to update filtered submissions data from SubmissionsTable
function updateFilteredSubmissions(filteredData) {
  filteredSubmissions.value = filteredData;
}

// Function to refresh table data after successful update
function refreshTableData() {
  console.log("Refreshing table data...");
  // Use router.reload to refresh the current page data
  router.reload({
    only: ["submissions", "statistics"],
    onSuccess: () => {
      datatableConfig.data = props.submissions?.data ?? [];
      // Also update filtered submissions for submissions type
      if (props.type === "submissions") {
        filteredSubmissions.value = props.submissions?.data ?? [];
      }
      console.log("Table data refreshed successfully");
    },
    onError: (errors) => {
      console.error("Error refreshing table data:", errors);
    },
  });
}

// Handle modal update success
function handleModalUpdateSuccess() {
  console.log("Modal update successful, refreshing table...");
  // Close modal first
  closeModal();
  // Then refresh table data
  refreshTableData();
}

// Form modal functions
function openCreateModal() {
  formModalState.isOpen = true;
  formModalState.isEdit = false;
  formModalState.loading = false;
  formModalState.error = null;

  // Reset form data
  Object.assign(formData, {
    employee_id: user.employee.id,
    start_date: "",
    end_date: "",
    name: "",
    salary: 0,
    reason: "",
    status: "pending",
  });

  // Reset salary display
  salaryDisplay.value = "";

  // Clear errors
  Object.keys(formErrors).forEach((key) => delete formErrors[key]);

  // Employees are already loaded from props
}

async function openEditModal(submission) {
  formModalState.isOpen = true;
  formModalState.isEdit = true;
  formModalState.loading = true;
  formModalState.error = null;

  // Store submission ID for update
  modalState.submission = submission;

  try {
    // Fetch detailed submission data using fetch API
    const response = await fetch(
      route("submission.employee.show", submission.id),
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN":
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "",
        },
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success && data.data) {
      const submissionData = data.data;

      // Populate form data with detailed data
      Object.assign(formData, {
        employee_id:
          submissionData.employee_id || submissionData.employee?.id || "",
        start_date: submissionData.start_date
          ? formatDateForInput(submissionData.start_date)
          : "",
        end_date: submissionData.end_date
          ? formatDateForInput(submissionData.end_date)
          : "",
        name: submissionData.name || "",
        salary: submissionData.salary || 0,
        reason: submissionData.reason || "",
      });

      // Format salary display
      salaryDisplay.value =
        formData.salary > 0 ? formatRupiah(formData.salary) : "";

      formModalState.loading = false;
    } else {
      throw new Error(data.message || "Gagal memuat detail pengajuan");
    }
  } catch (error) {
    console.error("Error fetching submission details:", error);
    formModalState.error = error.message || "Gagal memuat detail pengajuan";
    formModalState.loading = false;
  }

  // Clear errors
  Object.keys(formErrors).forEach((key) => delete formErrors[key]);
}

function closeFormModal() {
  formModalState.isOpen = false;
  formModalState.isEdit = false;
  formModalState.loading = false;
  formModalState.error = null;

  // Clear form data
  Object.assign(formData, {
    employee_id: "",
    start_date: "",
    end_date: "",
    name: "",
    salary: 0,
    reason: "",
    status: "pending",
  });

  // Reset salary display
  salaryDisplay.value = "";

  // Clear errors
  Object.keys(formErrors).forEach((key) => delete formErrors[key]);
}

async function submitForm() {
  formModalState.loading = true;
  formModalState.error = null;

  // Clear previous errors
  Object.keys(formErrors).forEach((key) => delete formErrors[key]);

  // Ensure salary is numeric (parse from display if needed)
  if (typeof formData.salary === "string") {
    formData.salary = parseRupiah(formData.salary);
  }

  // Prepare data to submit
  const submitData = { ...formData };

  // For create, ensure status is "pending" and employee_id is from logged-in user
  if (!formModalState.isEdit) {
    submitData.status = "pending";
    submitData.employee_id = user.employee.id;
  }
  // For edit, don't send status (let backend keep existing status)
  else {
    delete submitData.status;
  }

  try {
    const url = formModalState.isEdit
      ? route("submission.employee.update", modalState.submission?.id)
      : route("submission.employee.store");

    const method = formModalState.isEdit ? "PUT" : "POST";

    const response = await fetch(url, {
      method: method,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN":
          document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "",
      },
      body: JSON.stringify(submitData),
    });

    const data = await response.json();

    if (response.ok && data.success) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail:
          data.message ||
          (formModalState.isEdit
            ? "Pengajuan karyawan Harian berhasil diperbarui"
            : "Pengajuan karyawan Harian berhasil dibuat"),
        life: 3000,
      });
      closeFormModal();
      refreshTableData();
    } else {
      // Handle validation errors
      if (data.errors) {
        Object.assign(formErrors, data.errors);
      } else {
        const errorMessage =
          data.message || "Terjadi kesalahan saat menyimpan data";
        formModalState.error = errorMessage;
        toast.add({
          severity: "error",
          summary: "Error",
          detail: errorMessage,
          life: 5000,
        });
      }
    }
  } catch (error) {
    console.error("Error submitting form:", error);
    const errorMessage = "Terjadi kesalahan saat menyimpan data";
    formModalState.error = errorMessage;
    toast.add({
      severity: "error",
      summary: "Error",
      detail: errorMessage,
      life: 5000,
    });
  } finally {
    formModalState.loading = false;
  }
}

// Delete modal functions
function confirmDelete(submissionId) {
  deleteModalState.isOpen = true;
  deleteModalState.submissionId = submissionId;
  deleteModalState.loading = false;
}

function closeDeleteModal() {
  deleteModalState.isOpen = false;
  deleteModalState.submissionId = null;
  deleteModalState.loading = false;
}

async function deleteSubmission() {
  if (!deleteModalState.submissionId) return;

  deleteModalState.loading = true;

  try {
    const response = await fetch(
      route("submission.employee.destroy", deleteModalState.submissionId),
      {
        method: "DELETE",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN":
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "",
        },
      },
    );

    const data = await response.json();

    if (response.ok && data.success) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail: data.message || "Pengajuan karyawan Harian berhasil dihapus",
        life: 3000,
      });
      closeDeleteModal();
      refreshTableData();
    } else {
      const errorMessage =
        data.message || "Terjadi kesalahan saat menghapus data";
      toast.add({
        severity: "error",
        summary: "Error",
        detail: errorMessage,
        life: 5000,
      });
    }
  } catch (error) {
    console.error("Error deleting submission:", error);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Terjadi kesalahan saat menghapus data",
      life: 5000,
    });
  } finally {
    deleteModalState.loading = false;
  }
}

// Computed for payment modal
const totalPaid = computed(() => {
  return paymentHistory.value.reduce(
    (sum, payment) => sum + (parseFloat(payment.amount) || 0),
    0,
  );
});

const remainingDebt = computed(() => {
  const totalAmount = parseFloat(paymentModalState.submission?.amount) || 0;
  return Math.max(0, totalAmount - totalPaid.value);
});

// Payment modal functions
async function openPaymentModal(submission) {
  paymentModalState.isOpen = true;
  paymentModalState.loading = true;
  paymentModalState.error = null;
  paymentModalState.submission = submission;
  paymentModalState.receivableId = submission.id;
  showPaymentForm.value = false;
  paymentHistory.value = [];

  // Reset form - set default date to today
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  paymentForm.paid_date = `${year}-${month}-${day}`;
  paymentForm.amount = 0;
  paymentFormDisplay.amount = "";
  Object.keys(paymentFormErrors).forEach(
    (key) => delete paymentFormErrors[key],
  );

  try {
    // Fetch payment history - using API endpoint
    const response = await fetch(
      `/api/v1/receivables/${submission.id}/payments`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN":
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "",
        },
        credentials: "include", // Include cookies for Sanctum authentication
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    if (data.success) {
      paymentHistory.value = data.data || [];
    } else {
      throw new Error(data.message || "Gagal memuat riwayat pembayaran");
    }
  } catch (error) {
    console.error("Error fetching payment history:", error);
    paymentModalState.error =
      error.message || "Gagal memuat riwayat pembayaran";
  } finally {
    paymentModalState.loading = false;
  }
}

function closePaymentModal() {
  paymentModalState.isOpen = false;
  paymentModalState.submission = null;
  paymentModalState.receivableId = null;
  paymentModalState.loading = false;
  paymentModalState.error = null;
  showPaymentForm.value = false;
  paymentHistory.value = [];

  // Reset form
  paymentForm.paid_date = "";
  paymentForm.amount = 0;
  paymentFormDisplay.amount = "";
  Object.keys(paymentFormErrors).forEach(
    (key) => delete paymentFormErrors[key],
  );
}

function cancelPaymentForm() {
  showPaymentForm.value = false;
  // Reset form - set default date to today
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  paymentForm.paid_date = `${year}-${month}-${day}`;
  paymentForm.amount = 0;
  paymentFormDisplay.amount = "";
  Object.keys(paymentFormErrors).forEach(
    (key) => delete paymentFormErrors[key],
  );
}

function handlePaymentAmountInput(event) {
  const value = event.target.value;
  const numericValue = parseRupiah(value);
  paymentForm.amount = numericValue;
  paymentFormDisplay.amount =
    numericValue > 0 ? formatRupiah(numericValue) : "";
}

function handlePaymentAmountBlur() {
  if (paymentForm.amount > 0) {
    paymentFormDisplay.amount = formatRupiah(paymentForm.amount);
  } else {
    paymentFormDisplay.amount = "";
  }
}

async function submitPayment() {
  paymentModalState.processing = true;
  Object.keys(paymentFormErrors).forEach(
    (key) => delete paymentFormErrors[key],
  );

  // Validation
  if (!paymentForm.paid_date) {
    paymentFormErrors.paid_date = "Tanggal pembayaran wajib diisi";
    paymentModalState.processing = false;
    return;
  }

  if (!paymentForm.amount || paymentForm.amount <= 0) {
    paymentFormErrors.amount =
      "Jumlah pembayaran wajib diisi dan harus lebih dari 0";
    paymentModalState.processing = false;
    return;
  }

  if (paymentForm.amount > remainingDebt.value) {
    paymentFormErrors.amount =
      "Jumlah pembayaran tidak boleh melebihi sisa piutang";
    paymentModalState.processing = false;
    return;
  }

  try {
    const response = await fetch(
      `/api/v1/receivables/${paymentModalState.receivableId}/payments`,
      {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN":
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "",
        },
        credentials: "include", // Include cookies for Sanctum authentication
        body: JSON.stringify({
          paid_date: paymentForm.paid_date,
          amount: paymentForm.amount,
        }),
      },
    );

    const data = await response.json();

    if (response.ok && data.success) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail: data.message || "Pembayaran berhasil ditambahkan",
        life: 3000,
      });

      // Refresh payment history
      await openPaymentModal(paymentModalState.submission);
      showPaymentForm.value = false;

      // Refresh table data to update the debt table
      refreshTableData();
    } else {
      if (data.errors) {
        Object.assign(paymentFormErrors, data.errors);
      } else {
        paymentModalState.error =
          data.message || "Terjadi kesalahan saat menyimpan pembayaran";
      }
      toast.add({
        severity: "error",
        summary: "Error",
        detail: data.message || "Terjadi kesalahan saat menyimpan pembayaran",
        life: 5000,
      });
    }
  } catch (error) {
    console.error("Error submitting payment:", error);
    paymentModalState.error = "Terjadi kesalahan saat menyimpan pembayaran";
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Terjadi kesalahan saat menyimpan pembayaran",
      life: 5000,
    });
  } finally {
    paymentModalState.processing = false;
  }
}

async function deletePayment(paymentId) {
  if (!confirm("Apakah Anda yakin ingin menghapus pembayaran ini?")) {
    return;
  }

  try {
    const response = await fetch(`/api/v1/receivables/payments/${paymentId}`, {
      method: "DELETE",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN":
          document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "",
      },
      credentials: "include", // Include cookies for Sanctum authentication
    });

    const data = await response.json();

    if (response.ok && data.success) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail: data.message || "Pembayaran berhasil dihapus",
        life: 3000,
      });

      // Refresh payment history
      await openPaymentModal(paymentModalState.submission);

      // Refresh table data to update the debt table
      refreshTableData();
    } else {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: data.message || "Terjadi kesalahan saat menghapus pembayaran",
        life: 5000,
      });
    }
  } catch (error) {
    console.error("Error deleting payment:", error);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Terjadi kesalahan saat menghapus pembayaran",
      life: 5000,
    });
  }
}

// Watch for submissions data changes and update filtered submissions
watch(
  () => props.submissions?.data,
  (newData) => {
    if (props.type === "submissions" && newData) {
      filteredSubmissions.value = newData;
    }
  },
  { deep: true, immediate: true },
);

defineOptions({
  layout: AppLayout,
});
</script>
