<template>

    <Head title="Tambah Karyawan" />
    <Toast />

    <form @submit.prevent="storeEmployee" enctype="multipart/form-data">
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="space-y-6">
                <div class="flex justify-between items-center ps-4">
                    <div class="flex gap-3 items-center">
                        <Link :href="route('employees.index')" as="button"
                            class="inline-flex justify-center items-center w-10 h-10 text-white bg-blue-500 rounded-lg transition-colors duration-200 hover:bg-blue-700">
                        <i class="text-lg lni lni-chevron-left"></i>
                        </Link>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                Tambah Karyawan
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Formulir untuk menambahkan karyawan baru
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                            Lengkap
                            <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.name" placeholder="Masukkan nama lengkap karyawan" required
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-200':
                                    form.errors.name,
                            }" @input="form.errors.name = null" />

                        <div v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.name[0] }}
                        </div>
                    </div>
                    <div>
                        <label for="branch_id"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penempatan Cabang
                            <span class="text-red-500">*</span></label>

                        <Select id="branch_id" v-model="form.branch_id" :options="branchOptions" optionLabel="label"
                            optionValue="value" placeholder="Pilih cabang" :filter="showFilterBranch"
                            filterPlaceholder="Cari cabang..." :pt="selectPt" :required="true" class="w-full" :class="[
                                !form.branch_id
                                    ? 'text-gray-400'
                                    : 'text-gray-900',
                                {
                                    'p-invalid border-red-500':
                                        form.errors.branch_id,
                                },
                            ]" @change="form.errors.branch_id = null" />
                        <div v-if="form.errors.branch_id" class="mt-1 text-xs text-red-500">
                            {{ form.errors.branch_id[0] }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label for="username"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username <span
                                class="text-red-500">*</span></label>
                        <input type="text" v-model="form.username" placeholder="Masukkan username" required
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-200':
                                    form.errors.username,
                            }" @input="form.errors.username = null" />
                        <div v-if="form.errors.username" class="mt-1 text-xs text-red-500">
                            {{ form.errors.username[0] }}
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email
                            <span class="text-red-500">*</span></label>
                        <input type="email" v-model="form.email" placeholder="Masukkan email" required
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-200':
                                    form.errors.email,
                            }" @input="form.errors.email = null" />
                        <div v-if="form.errors.email" class="mt-1 text-xs text-red-500">
                            {{ form.errors.email[0] }}
                        </div>
                    </div>
                    <div>
                        <label for="password"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="form.password"
                                placeholder="Masukkan password" required minlength="8"
                                class="block p-2.5 pr-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-200':
                                        form.errors.password,
                                }" @input="form.errors.password = null" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-gray-700 focus:outline-none z-10">
                                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-1 text-xs text-red-500">
                            {{ form.errors.password[0] }}
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Informasi Tambahan
                </h3>
                <div class="space-y-6">
                    <!-- Row 1: Departemen only (Jabatan hidden, auto-set to Staff) -->
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="department_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Departemen
                                <span class="text-red-500">*</span></label>
                            <Select v-model="form.department_id" :options="departementOptions" optionLabel="label"
                                optionValue="value" :placeholder="form.branch_id
                                    ? 'Pilih departemen'
                                    : 'Pilih cabang terlebih dahulu'
                                    " :filter="showFilterDept" filterPlaceholder="Cari departemen..." :pt="selectPt"
                                :required="true" :disabled="!form.branch_id"
                                class="text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                :class="[
                                    !form.department_id
                                        ? 'text-gray-400'
                                        : 'text-gray-900',
                                    {
                                        'p-invalid border-red-500':
                                            form.errors.department_id,
                                    },
                                ]" @change="form.errors.department_id = null" />
                            <div v-if="form.errors.department_id" class="mt-1 text-xs text-red-500">
                                {{ form.errors.department_id[0] }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Position, Jatah Cuti, Jatah Piutang -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="position_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Position</label>
                            <FormSelect v-model="form.position_id" label="Pilih Position (opsional)"
                                :items="positionItems" :taggable="true" labelKey="name" searchKey="name"
                                @update:modelValue="handlePositionChange" />
                            <div v-if="form.errors.position_id" class="mt-1 text-xs text-red-500">
                                {{ form.errors.position_id[0] }}
                            </div>
                            <div v-if="creatingPosition" class="mt-1 text-xs text-blue-600">
                                Menyimpan position baru...
                            </div>
                        </div>
                        <div>
                            <label for="leave_quota_per_year"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jatah Cuti
                                (hari/tahun)
                                <span class="text-red-500">*</span></label>
                            <div class="flex items-stretch">
                                <InputNumber v-model="form.leave_quota_per_year" placeholder="Masukkan jatah cuti"
                                    class="flex-1"
                                    inputClass="w-full bg-gray-50 text-gray-900 border border-gray-300 rounded-l-lg border-r-0 p-2.5 focus:ring-blue-500 focus:border-blue-500"
                                    :min="0" :useGrouping="false" :minFractionDigits="0" :maxFractionDigits="0"
                                    :step="1" :required="true" />
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-600 bg-gray-100 rounded-r-lg border border-l-0 border-gray-300">hari/tahun</span>
                            </div>
                        </div>
                        <div>
                            <label for="loan_quota"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jatah Piutang
                                (Rp)</label>
                            <div class="flex items-stretch">
                                <InputNumber v-model="form.loan_quota" placeholder="Masukkan jatah piutang (opsional)"
                                    class="flex-1"
                                    inputClass="w-full bg-gray-50 text-gray-900 border border-gray-300 rounded-l-lg border-r-0 p-2.5 focus:ring-blue-500 focus:border-blue-500"
                                    :min="0" mode="currency" currency="IDR" locale="id-ID" />
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-600 bg-gray-100 rounded-r-lg border border-l-0 border-gray-300">/tahun</span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Shift Kerja, Tanggal Mulai Bekerja, Gaji Pokok -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="shift_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Shift Kerja
                                <span class="text-red-500">*</span></label>
                            <Select v-model="form.shift_id" :options="shiftOptions" optionLabel="label"
                                optionValue="value" placeholder="Pilih Shift Kerja" :filter="showFilterShift"
                                filterPlaceholder="Cari shift..." :pt="selectPt" :required="true"
                                class="text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                :class="[
                                    !form.shift_id
                                        ? 'text-gray-400'
                                        : 'text-gray-900',
                                    {
                                        'p-invalid border-red-500':
                                            form.errors.shift_id,
                                    },
                                ]" @change="form.errors.shift_id = null" />
                            <div v-if="form.errors.shift_id" class="mt-1 text-xs text-red-500">
                                {{ form.errors.shift_id[0] }}
                            </div>
                        </div>
                        <div>
                            <label for="working_start_date"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Mulai
                                Bekerja
                                <span class="text-red-500">*</span></label>
                            <input type="date" v-model="form.working_start_date"
                                :style="{ 'background-color': '#f9fafb' }"
                                class="block p-2.5 w-full text-sm placeholder-gray-400 text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Pilih Tanggal Mulai Bekerja" required :class="{
                                    'p-invalid border-red-500':
                                        form.errors.working_start_date,
                                }" @input="form.errors.working_start_date = null" />
                            <div v-if="form.errors.working_start_date" class="mt-1 text-xs text-red-500">
                                {{ form.errors.working_start_date[0] }}
                            </div>
                        </div>
                        <div>
                            <label for="salary"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gaji Pokok</label>
                            <InputNumber v-model="form.salary" placeholder="Masukkan Gaji Pokok (opsional)"
                                class="w-full"
                                inputClass="w-full bg-gray-50 text-gray-900 border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'p-invalid border-red-500':
                                        form.errors.salary,
                                }" :min="0" @input="form.errors.salary = null" />
                            <div v-if="form.errors.salary" class="mt-1 text-xs text-red-500">
                                {{ form.errors.salary[0] }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Upload Foto Karyawan, Upload Tanda Tangan -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Foto
                                Karyawan
                                <span class="text-red-500">*</span></label>
                            <input ref="photoInput" type="file"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                                @change="form.photo = $event.target.files[0]" accept="image/*"
                                :class="{ 'border-red-500': form.errors.photo }" />
                            <div v-if="form.errors.photo" class="mt-1 text-xs text-red-500">
                                {{ form.errors.photo[0] }}
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Tanda
                                Tangan</label>
                            <input ref="signatureInput" type="file"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                                accept="image/*" @change="
                                    form.signature = $event.target.files[0]
                                    " :class="{
                                        'border-red-500': form.errors.signature,
                                    }" />
                            <div v-if="form.errors.signature" class="mt-1 text-xs text-red-500">
                                {{ form.errors.signature[0] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Identitas & Kontak -->
        <div class="mt-6 bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-800">
                    Identitas & Kontak
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            for="contact-input">No. Telepon</label>
                        <div class="relative">
                            <input id="contact-input" v-model="form.contact" type="text"
                                placeholder="Masukkan nomor telepon"
                                class="w-full bg-gray-50 text-gray-900 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-200':
                                        form.errors.contact,
                                }" maxlength="20" @input="
                                    // Only allow numeric input, keep leading 0s
                                    $event.target.value =
                                    $event.target.value.replace(
                                        /[^0-9]/g,
                                        '',
                                    );
                                form.contact = $event.target.value;
                                form.errors.contact = null;
                                " inputmode="numeric" autocomplete="tel" />
                            <div v-if="form.errors.contact" class="mt-1 text-xs text-red-500">
                                {{ form.errors.contact[0] }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kelamin
                            <span class="text-red-500">*</span></label>
                        <div class="px-3 py-2 w-full h-auto text-sm bg-white rounded-lg border border-gray-300">
                            <div class="flex gap-6 items-center">
                                <label class="inline-flex gap-2 items-center">
                                    <input type="radio" name="gender" value="Laki-laki" v-model="form.gender" required
                                        @change="form.errors.gender = null" />
                                    <span>Laki-laki</span>
                                </label>
                                <label class="inline-flex gap-2 items-center">
                                    <input type="radio" name="gender" value="Perempuan" v-model="form.gender" required
                                        @change="form.errors.gender = null" />
                                    <span>Perempuan</span>
                                </label>
                            </div>
                        </div>
                        <div v-if="form.errors.gender" class="mt-1 text-xs text-red-500">
                            {{ form.errors.gender[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status Kerja
                            <span class="text-red-500">*</span></label>
                        <div class="px-3 py-2 w-full h-auto text-sm bg-white rounded-lg border border-gray-300">
                            <div class="flex flex-wrap gap-6 items-center">
                                <label class="inline-flex gap-2 items-center">
                                    <input type="radio" name="status" value="Tetap" v-model="form.status" required
                                        @change="form.errors.status = null" />
                                    <span>Tetap</span>
                                </label>
                                <label class="inline-flex gap-2 items-center">
                                    <input type="radio" name="status" value="Kontrak" v-model="form.status" required
                                        @change="form.errors.status = null" />
                                    <span>Kontrak</span>
                                </label>
                                <label class="inline-flex gap-2 items-center">
                                    <input type="radio" name="status" value="Magang" v-model="form.status" required
                                        @change="form.errors.status = null" />
                                    <span>Magang</span>
                                </label>
                            </div>
                        </div>
                        <div v-if="form.errors.status" class="mt-1 text-xs text-red-500">
                            {{ form.errors.status[0] }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tempat Lahir</label>
                        <InputText v-model="form.birthplace" placeholder="Masukkan tempat lahir"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'p-invalid border-red-500':
                                    form.errors.birthplace,
                            }" @input="form.errors.birthplace = null" />
                        <div v-if="form.errors.birthplace" class="mt-1 text-xs text-red-500">
                            {{ form.errors.birthplace[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Agama</label>
                        <Select v-model="form.religion" :options="religionOptions" optionLabel="label"
                            optionValue="value" placeholder="Pilih Agama" class="w-full" :pt="selectPt" :class="{
                                'p-invalid border-red-500':
                                    form.errors.religion,
                            }" @change="form.errors.religion = null" />
                        <div v-if="form.errors.religion" class="mt-1 text-xs text-red-500">
                            {{ form.errors.religion[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Lahir
                            <span class="text-red-500">*</span></label>

                        <input type="date" v-model="form.birthdate" :style="{ 'background-color': '#f9fafb' }"
                            class="block p-2.5 w-full text-sm placeholder-gray-400 text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Pilih Tanggal Lahir" required @input="form.errors.birthdate = null" />
                        <div v-if="form.errors.birthdate" class="mt-1 text-xs text-red-500">
                            {{ form.errors.birthdate[0] }}
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                    <textarea v-model="form.address" rows="3"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan alamat lengkap" :class="{ 'border-red-500': form.errors.address }"
                        @input="form.errors.address = null"></textarea>
                    <div v-if="form.errors.address" class="mt-1 text-xs text-red-500">
                        {{ form.errors.address[0] }}
                    </div>
                </div>

                <!-- Provinsi, Kota, Kecamatan, Kelurahan -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="provinsi"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Provinsi</label>
                        <Select v-model="form.provinsi" :options="provinsiOptions" optionLabel="name" optionValue="name"
                            placeholder="Pilih Provinsi" filter class="w-full" :pt="selectPt" :class="{
                                'p-invalid border-red-500':
                                    form.errors.provinsi,
                            }" @change="onProvinsiChange" />
                        <div v-if="form.errors.provinsi" class="mt-1 text-xs text-red-500">
                            {{ form.errors.provinsi[0] }}
                        </div>
                    </div>
                    <div>
                        <label for="kota"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kota</label>
                        <Select v-model="form.kota" :options="kotaOptions" optionLabel="name" optionValue="name"
                            placeholder="Pilih Kota" filter :disabled="!form.provinsi" class="w-full" :pt="selectPt"
                            :class="{
                                'p-invalid border-red-500': form.errors.kota,
                            }" @change="onKotaChange" />
                        <div v-if="form.errors.kota" class="mt-1 text-xs text-red-500">
                            {{ form.errors.kota[0] }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="kecamatan"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kecamatan</label>
                        <Select v-model="form.kecamatan" :options="kecamatanOptions" optionLabel="name"
                            optionValue="name" placeholder="Pilih Kecamatan" filter :disabled="!form.kota"
                            class="w-full" :pt="selectPt" :class="{
                                'p-invalid border-red-500':
                                    form.errors.kecamatan,
                            }" @change="onKecamatanChange" />
                        <div v-if="form.errors.kecamatan" class="mt-1 text-xs text-red-500">
                            {{ form.errors.kecamatan[0] }}
                        </div>
                    </div>
                    <div>
                        <label for="kelurahan"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kelurahan</label>
                        <Select v-model="form.kelurahan" :options="kelurahanOptions" optionLabel="name"
                            optionValue="name" placeholder="Pilih Kelurahan" filter :disabled="!form.kecamatan"
                            class="w-full" :pt="selectPt" :class="{
                                'p-invalid border-red-500':
                                    form.errors.kelurahan,
                            }" @change="form.errors.kelurahan = null" />
                        <div v-if="form.errors.kelurahan" class="mt-1 text-xs text-red-500">
                            {{ form.errors.kelurahan[0] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Data Legal & Administratif -->
        <div class="mt-6 bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-800">
                    Data Legal & Administratif
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            for="nik-input">NIK</label>
                        <input id="nik-input" v-model="form.nik" type="text" inputmode="numeric" pattern="[0-9]*"
                            maxlength="16" placeholder="Masukkan NIK"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-200':
                                    form.errors.nik,
                            }" @input="
                                $event.target.value =
                                $event.target.value.replace(/[^0-9]/g, '');
                            form.nik = $event.target.value;
                            form.errors.nik = null;
                            " autocomplete="off" />
                        <div v-if="form.errors.nik" class="mt-1 text-xs text-red-500">
                            {{ form.errors.nik[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="ktp-input">No.
                            KTP</label>
                        <input id="ktp-input" v-model="form.ktp" type="text" inputmode="numeric" pattern="[0-9]*"
                            maxlength="20" placeholder="Masukkan no KTP"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-200':
                                    form.errors.ktp,
                            }" @input="
                                $event.target.value =
                                $event.target.value.replace(/[^0-9]/g, '');
                            form.ktp = $event.target.value;
                            form.errors.ktp = null;
                            " autocomplete="off" />
                        <div v-if="form.errors.ktp" class="mt-1 text-xs text-red-500">
                            {{ form.errors.ktp[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">BPJS
                            Kesehatan</label>
                        <InputText v-model="form.bpjs_kesehatan" placeholder="No BPJS Kesehatan"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'p-invalid border-red-500':
                                    form.errors.bpjs_kesehatan,
                            }" @input="form.errors.bpjs_kesehatan = null" />
                        <div v-if="form.errors.bpjs_kesehatan" class="mt-1 text-xs text-red-500">
                            {{ form.errors.bpjs_kesehatan[0] }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">BPJS
                            Ketenagakerjaan</label>
                        <InputText v-model="form.bpjs_ketenagakerjaan" placeholder="No BPJS Ketenagakerjaan"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'p-invalid border-red-500':
                                    form.errors.bpjs_ketenagakerjaan,
                            }" @input="form.errors.bpjs_ketenagakerjaan = null" />
                        <div v-if="form.errors.bpjs_ketenagakerjaan" class="mt-1 text-xs text-red-500">
                            {{ form.errors.bpjs_ketenagakerjaan[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sertifikat</label>
                        <InputText v-model="form.certificate" placeholder="Nama/No Sertifikat (opsional)"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'p-invalid border-red-500':
                                    form.errors.certificate,
                            }" @input="form.errors.certificate = null" />
                        <div v-if="form.errors.certificate" class="mt-1 text-xs text-red-500">
                            {{ form.errors.certificate[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kontrak</label>
                        <InputText v-model="form.contract" placeholder="Info kontrak (opsional)"
                            class="w-full !bg-white !text-black !border !border-gray-300 rounded-lg" :class="{
                                'p-invalid border-red-500':
                                    form.errors.contract,
                            }" @input="form.errors.contract = null" />
                        <div v-if="form.errors.contract" class="mt-1 text-xs text-red-500">
                            {{ form.errors.contract[0] }}
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen Pendukung -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- <div>
                        <label
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Upload Foto Karyawan</label
                        >
                        <input
                            type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*"
                            @change="onPhotoChange"
                            :class="{ 'border-red-500': form.errors.photo }"
                        />
                        <div v-if="photoPreview" class="mt-2">
                            <img
                                :src="photoPreview"
                                alt="Preview Foto"
                                class="h-20 rounded border"
                            />
                        </div>
                        <div
                            v-if="form.errors.photo"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.photo[0] }}
                        </div>
                    </div> -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Foto
                            KTP</label>
                        <input ref="ktpInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="form.ktp_file = $event.target.files[0]" :class="{
                                'border-red-500': form.errors.ktp_file,
                            }" />
                        <div v-if="form.errors.ktp_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.ktp_file[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kartu BPJS
                            Kesehatan</label>
                        <input ref="bpjsKesehatanInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="
                                form.bpjs_kesehatan_file =
                                $event.target.files[0]
                                " :class="{
                                    'border-red-500':
                                        form.errors.bpjs_kesehatan_file,
                                }" />
                        <div v-if="form.errors.bpjs_kesehatan_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.bpjs_kesehatan_file[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kartu BPJS
                            Ketenagakerjaan</label>
                        <input ref="bpjsKetenagakerjaanInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="
                                form.bpjs_ketenagakerjaan_file =
                                $event.target.files[0]
                                " :class="{
                                    'border-red-500':
                                        form.errors.bpjs_ketenagakerjaan_file,
                                }" />
                        <div v-if="form.errors.bpjs_ketenagakerjaan_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.bpjs_ketenagakerjaan_file[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Sertifikat
                            (opsional)</label>
                        <input ref="certificateInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf,.doc,.docx" @change="
                                form.certificate_file = $event.target.files[0]
                                " :class="{
                                    'border-red-500': form.errors.certificate_file,
                                }" />
                        <div v-if="form.errors.certificate_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.certificate_file[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kontrak
                            (opsional)</label>
                        <input ref="contractInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf,.doc,.docx" @change="
                                form.contract_file = $event.target.files[0]
                                " :class="{
                                    'border-red-500': form.errors.contract_file,
                                }" />
                        <div v-if="form.errors.contract_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.contract_file[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kartu
                            Keluarga (opsional)</label>
                        <input ref="kkInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="form.kk_file = $event.target.files[0]" :class="{
                                'border-red-500': form.errors.kk_file,
                            }" />
                        <div v-if="form.errors.kk_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.kk_file[0] }}
                        </div>
                    </div>
                </div>
                <!-- Tanggal Resign -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Resign
                            (opsional)</label>
                        <input type="date" v-model="form.resign_date" :style="{ 'background-color': '#f9fafb' }"
                            class="block p-2.5 w-full text-sm placeholder-gray-400 text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Pilih Tanggal Resign" :class="{
                                'p-invalid border-red-500':
                                    form.errors.resign_date,
                            }" @input="form.errors.resign_date = null" />
                        <div v-if="form.errors.resign_date" class="mt-1 text-xs text-red-500">
                            {{ form.errors.resign_date[0] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-between items-center mt-4">
            <Button title="Kembali" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50"
                type="button" @click="goBack">← Kembali</Button>
            <div class="flex gap-2">
                <Button title="Simpan dan Tambah Lagi"
                    class="px-4 py-2 text-blue-600 bg-blue-50 border border-blue-300 hover:bg-blue-100" type="button"
                    @click="openConfirmAddMore()">Simpan dan Tambah Lagi</Button>
                <Button title="Simpan" class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600" type="button"
                    @click="openConfirm()">Simpan</Button>
            </div>
        </div>

        <Dialog v-model:visible="confirmVisible" modal dismissableMask
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }" :style="{ width: '560px' }" :pt="dialogPt">
            <!-- Header -->
            <template #header>
                <div class="flex justify-between items-center w-full">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Konfirmasi Data
                    </h3>
                </div>
            </template>

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <!-- SVG illustration omitted for brevity -->
                <h4 class="mb-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Simpan Data Karyawan ?
                </h4>
                <p class="max-w-md text-gray-600 dark:text-gray-300">
                    Anda akan menambahkan karyawan bernama
                    <span class="font-semibold text-blue-600">{{
                        form.name || "—"
                    }}</span>
                    sebagai karyawan baru. Lanjutkan?
                </p>
            </div>

            <!-- Footer -->
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Batalkan" severity="secondary" class="flex-1 h-11 text-white bg-gray-300 rounded-lg"
                        @click="confirmVisible = false" />
                    <Button label="Ya, Simpan" class="flex-1 h-11 text-white bg-blue-500 rounded-lg"
                        @click="confirmSave" />
                </div>
            </template>
        </Dialog>
    </form>
</template>

<script setup>
import { useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import { watch, ref, defineProps, computed, onMounted } from "vue";
import Select from "primevue/select";
import FormSelect from "@/Components/form/SelectPemakaian.vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import Swal from "sweetalert2";

defineOptions({ layout: AppLayout });

const props = defineProps({
    branches: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    // permissions removed
    shifts: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    positions: { type: Array, default: () => [] },
});

const branchOptions = props.branches.map((branch) => {
    return { label: branch.name, value: branch.id };
});

// Department options will be filtered based on selected branch
const departementOptions = computed(() => {
    if (!form.branch_id) {
        return [];
    }
    console.log(form.branch_id);
    console.log(props.departments);
    return props.departments
        .filter((dept) => dept.branch_id == form.branch_id)
        .map((dept) => ({ label: dept.name, value: dept.id }));
});
// permission options removed

const shiftOptions = props.shifts.map((shift) => {
    const fmt = (t) => (t ? String(t).slice(0, 5).replace(":", ".") : "--:--");
    const st = fmt(shift.start_time);
    const et = fmt(shift.end_time);
    return { label: `${shift.name} (${st} - ${et})`, value: shift.id };
});

const roleOptions = props.roles.map((role) => {
    return { label: role.name, value: role.id };
});

const positionOptions = props.positions.map((position) => {
    return { label: position.name, value: position.id };
});

const positionItems = computed(() => {
    return [
        ...props.positions.map((p) => ({ id: p.id, name: p.name })),
        ...localAddedPositions.value,
    ];
});

const localAddedPositions = ref([]);

// PrimeVue Select theming to mimic Flowbite inputs (polished)
const selectPt = {
    root: {
        class: "w-full bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-base focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500",
    },
    input: { class: "px-3 py-2.5 bg-gray-50 text-base" },
    label: { class: "px-3 py-2.5 text-gray-900 text-base" },
    panel: { class: "bg-white border border-gray-200 shadow-lg" },
    item: { class: "px-3 py-2 text-base" },
    filterContainer: { class: "mb-2" },
    filterIcon: { class: "hidden" },
    filterInput: {
        class: "w-full h-10 rounded-md bg-gray-50 border border-gray-300 px-3 text-base focus:ring-1 focus:ring-blue-100 focus:border-blue-300",
    },
};

// Show filter only when options are many
const showFilterBranch = computed(() => branchOptions.length > 8);
const showFilterRole = computed(() => roleOptions.length > 8);
const showFilterPosition = computed(() => positionOptions.length > 8);
const showFilterShift = computed(() => shiftOptions.length > 8);

const form = useForm({
    name: "",
    branch_id: null,
    username: "",
    email: "",
    password: "",
    // permission_id removed
    department_id: null,
    jabatan_id: null,
    position_id: null,
    working_start_date: null,
    birthdate: null,
    shift_id: null,
    salary: null,
    tunjangan_1: 0,
    tunjangan_2: 0,
    // quotas (auto-filled by posisi/jabatan, editable)
    leave_quota_per_year: 0,
    loan_quota: 0,
    photo: null,
    contact: "",
    address: "",
    provinsi: "",
    kota: "",
    kecamatan: "",
    kelurahan: "",
    gender: null,
    status: null,
    birthplace: "",
    religion: "",
    nik: "",
    ktp: "",
    bpjs_kesehatan: "",
    bpjs_ketenagakerjaan: "",
    // document URLs
    ktp_url: "",
    bpjs_kesehatan_url: "",
    bpjs_ketenagakerjaan_url: "",
    // document files
    ktp_file: null,
    bpjs_kesehatan_file: null,
    bpjs_ketenagakerjaan_file: null,
    certificate: "",
    contract: "",
    certificate_file: null,
    contract_file: null,
    kk_file: null,
    resign_date: null,
    signature: null,
    is_add_more: false,
});

// Image previews for photo and signature
const photoPreview = ref("");
const signaturePreview = ref("");

// Refs for file inputs
const photoInput = ref(null);
const signatureInput = ref(null);
const ktpInput = ref(null);
const bpjsKesehatanInput = ref(null);
const bpjsKetenagakerjaanInput = ref(null);
const certificateInput = ref(null);
const contractInput = ref(null);
const kkInput = ref(null);

// Password visibility toggle
const showPassword = ref(false);

const creatingPosition = ref(false);

watch(
    () => form.working_start_date,
    (val) => {
        if (val instanceof Date) {
            const yyyy = val.getFullYear();
            const mm = String(val.getMonth() + 1).padStart(2, "0");
            const dd = String(val.getDate()).padStart(2, "0");
            form.working_start_date = `${yyyy}-${mm}-${dd}`;
        }
    },
);

watch(
    () => form.birthdate,
    (val) => {
        if (val instanceof Date) {
            const yyyy = val.getFullYear();
            const mm = String(val.getMonth() + 1).padStart(2, "0");
            const dd = String(val.getDate()).padStart(2, "0");
            form.birthdate = `${yyyy}-${mm}-${dd}`;
        }
    },
);

watch(
    () => form.resign_date,
    (val) => {
        if (val instanceof Date) {
            const yyyy = val.getFullYear();
            const mm = String(val.getMonth() + 1).padStart(2, "0");
            const dd = String(val.getDate()).padStart(2, "0");
            form.resign_date = `${yyyy}-${mm}-${dd}`;
        }
    },
);

// Watch for branch changes and reset department selection
watch(
    () => form.branch_id,
    (newBranchId, oldBranchId) => {
        // Reset department selection when branch changes
        if (newBranchId !== oldBranchId) {
            form.department_id = null;
            form.errors.department_id = null;
        }
    },
);

const toast = useToast();
const confirmVisible = ref(false);

function validateRequiredFields() {
    const errs = {};
    if (!form.name) errs.name = ["Nama wajib diisi."];
    if (!form.branch_id) errs.branch_id = ["Cabang wajib dipilih."];
    if (!form.username) errs.username = ["Username wajib diisi."];
    if (!form.email) errs.email = ["Email wajib diisi."];
    if (!form.password || String(form.password).length < 8)
        errs.password = ["Password minimal 8 karakter."];
    if (!form.department_id) errs.department_id = ["Departemen wajib dipilih."];
    if (!form.jabatan_id) errs.jabatan_id = ["Jabatan wajib dipilih."];
    if (!form.shift_id) errs.shift_id = ["Shift wajib dipilih."];
    if (!form.working_start_date)
        errs.working_start_date = ["Tanggal mulai kerja wajib diisi."];
    // salary is optional, but if filled must be > 0
    if (
        form.salary !== null &&
        form.salary !== undefined &&
        Number(form.salary) <= 0
    ) {
        errs.salary = ["Gaji pokok jika diisi harus > 0."];
    }
    // quotas required (allow 0 but not null/negative)
    if (
        form.leave_quota_per_year === null ||
        form.leave_quota_per_year === undefined ||
        Number(form.leave_quota_per_year) < 0
    ) {
        errs.leave_quota_per_year = [
            "Jatah cuti wajib diisi (boleh 0) dan tidak boleh negatif.",
        ];
    }
    // loan_quota is optional, but if filled must be >= 0
    if (
        form.loan_quota !== null &&
        form.loan_quota !== undefined &&
        Number(form.loan_quota) < 0
    ) {
        errs.loan_quota = ["Jatah piutang jika diisi tidak boleh negatif."];
    }
    // photo required
    if (!form.photo) {
        errs.photo = ["Upload foto karyawan wajib."];
    }
    // gender & status required
    if (!form.gender) errs.gender = ["Jenis kelamin wajib dipilih."];
    if (!form.status) errs.status = ["Status kerja wajib dipilih."];
    form.setError(errs);
    return Object.keys(errs).length === 0;
}

// Ensure pressing Enter (form submit) also opens the confirmation dialog
const storeEmployee = () => {
    if (validateRequiredFields()) {
        openConfirm();
    }
};

function onJabatanChanged() {
    form.errors.jabatan_id = null;
    const selected = props.roles.find((r) => r.id === form.jabatan_id);
    if (selected) {
        // Auto-fill quotas from role
        form.leave_quota_per_year = Number(selected.leave_quota_per_year ?? 0);
        form.loan_quota = Number(selected.loan_quota ?? 0);
    }
}

async function handlePositionChange(value) {
    console.log(value);

    // Check if value is a string (new position that needs to be created)
    // If it's a string and not a number, it's a new position created via taggable
    if (
        typeof value === "string" &&
        value &&
        isNaN(value) &&
        value != "unsigned"
    ) {
        // Check if this exact name already exists

        const response = await fetch(
            route("positions.checkExist", { name: value.trim() }),
        );
        const data = await response.json();
        const exists = data.exists;

        if (!exists && value.trim()) {
            // This is a new position, create it
            await createNewPosition(value);
        } else {
            form.position_id = data.id;
        }
    } else {
        creatingPosition.value = false;
    }
}

async function createNewPosition(name) {
    creatingPosition.value = true;

    try {
        const response = await fetch(route("positions.store"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]')
                        ?.content || "",
            },
            body: JSON.stringify({ name: name }),
        });

        if (response.ok) {
            const data = await response.json();
            // Add to local added positions
            localAddedPositions.value.push({ id: data.id, name: data.name });
            // Update form value
            form.position_id = data.id;
            creatingPosition.value = false;

            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: `Position "${name}" berhasil dibuat`,
                life: 2000,
            });
        } else if (response.status === 422) {
            // Validation error - position might already exist
            const errorData = await response.json();
            creatingPosition.value = false;
            toast.add({
                severity: "warn",
                summary: "Peringatan",
                detail: errorData.message || "Position mungkin sudah ada",
                life: 3000,
            });
        } else {
            throw new Error("Failed to create position");
        }
    } catch (error) {
        console.error("Error creating position:", error);
        creatingPosition.value = false;
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Gagal membuat position baru",
            life: 3000,
        });
    }
}
const saveAction = ref("save"); // 'save' or 'add_more'
const openConfirm = () => {
    form.is_add_more = false;
    confirmVisible.value = true;
};

const openConfirmAddMore = () => {
    form.is_add_more = true;
    confirmVisible.value = true;
};

const dialogPt = {
    root: { class: "rounded-2xl" },
    header: { class: "px-6 pt-6 pb-2" },
    content: { class: "px-6 pb-2" },
    footer: { class: "px-6 pb-6" },
};

const confirmSave = () => {
    confirmVisible.value = false;
    const shouldAddMore = form.is_add_more;
    form.post(route("employees.store"), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            if (shouldAddMore) {
                // Reset form dan tetap di halaman create (backend sudah redirect back)
                form.reset();

                // Reset semua file inputs
                if (photoInput.value) photoInput.value.value = "";
                if (signatureInput.value) signatureInput.value.value = "";
                if (ktpInput.value) ktpInput.value.value = "";
                if (bpjsKesehatanInput.value)
                    bpjsKesehatanInput.value.value = "";
                if (bpjsKetenagakerjaanInput.value)
                    bpjsKetenagakerjaanInput.value.value = "";
                if (certificateInput.value) certificateInput.value.value = "";
                if (contractInput.value) contractInput.value.value = "";
                if (kkInput.value) kkInput.value.value = "";

                // Reset preview images
                photoPreview.value = "";
                signaturePreview.value = "";
            }
            // Jika tidak add_more, backend akan redirect ke index otomatis
        },
        onError: (errors) => {
            console.log("Validation errors received:", errors);

            // Helper function to format field names to Indonesian
            const formatFieldName = (field) => {
                const fieldNames = {
                    name: "Nama",
                    username: "Username",
                    email: "Email",
                    password: "Password",
                    branch_id: "Cabang",
                    department_id: "Departemen",
                    jabatan_id: "Jabatan",
                    position_id: "Position",
                    shift_id: "Shift",
                    working_start_date: "Tanggal Mulai Kerja",
                    birthdate: "Tanggal Lahir",
                    salary: "Gaji",
                    leave_quota_per_year: "Jatah Cuti per Tahun",
                    loan_quota: "Jatah Piutang",
                    contact: "No. Telepon",
                    gender: "Jenis Kelamin",
                    status: "Status Karyawan",
                    photo: "Foto",
                    signature: "Tanda Tangan",
                    address: "Alamat",
                    birthplace: "Tempat Lahir",
                    religion: "Agama",
                };
                return fieldNames[field] || field;
            };

            // Collect all validation error messages
            const errorMessages = [];

            // Process errors from Inertia (main source)
            if (errors && typeof errors === "object") {
                Object.keys(errors).forEach((field) => {
                    const fieldErrors = errors[field];
                    const fieldName = formatFieldName(field);

                    if (Array.isArray(fieldErrors)) {
                        fieldErrors.forEach((error) => {
                            errorMessages.push(`${fieldName}: ${error}`);
                        });
                    } else if (fieldErrors && typeof fieldErrors === "string") {
                        errorMessages.push(`${fieldName}: ${fieldErrors}`);
                    }
                });
            }

            // Fallback to form.errors if no errors from Inertia
            if (
                errorMessages.length === 0 &&
                form.errors &&
                typeof form.errors === "object"
            ) {
                Object.keys(form.errors).forEach((field) => {
                    const fieldErrors = form.errors[field];
                    const fieldName = formatFieldName(field);

                    if (Array.isArray(fieldErrors)) {
                        fieldErrors.forEach((error) => {
                            errorMessages.push(`${fieldName}: ${error}`);
                        });
                    } else if (fieldErrors && typeof fieldErrors === "string") {
                        errorMessages.push(`${fieldName}: ${fieldErrors}`);
                    }
                });
            }

            // Show SweetAlert2 with validation errors
            if (errorMessages.length > 0) {
                Swal.fire({
                    icon: "error",
                    title: "Validasi Gagal",
                    html: `
                        <div style="text-align: left; max-height: 300px; overflow-y: auto;">
                            <p style="margin-bottom: 15px; color: #666; font-size: 14px;">Mohon perbaiki field berikut:</p>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;">
                                ${errorMessages.map((msg) => `• ${msg}`).join("<br>")}
                            </div>
                        </div>
                    `,
                    confirmButtonText: "OK, Saya Mengerti",
                    confirmButtonColor: "#3085d6",
                    width: "500px",
                    customClass: {
                        popup: "swal2-popup-validation",
                        htmlContainer: "swal2-html-validation",
                    },
                });
            } else {
                // Fallback jika tidak ada error yang terdeteksi
                Swal.fire({
                    icon: "error",
                    title: "Gagal Menyimpan",
                    text: "Terjadi kesalahan saat menyimpan data. Silakan periksa kembali form Anda.",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#3085d6",
                });
            }
        },
    });
};

const goBack = () => {
    router.visit(route("employees.index"));
};

// Religion options
const religionOptions = [
    { label: "Islam", value: "Islam" },
    { label: "Kristen", value: "Kristen" },
    { label: "Katolik", value: "Katolik" },
    { label: "Hindu", value: "Hindu" },
    { label: "Buddha", value: "Buddha" },
    { label: "Konghucu", value: "Konghucu" },
];

// Wilayah options
const provinsiOptions = ref([]);
const kotaOptions = ref([]);
const kecamatanOptions = ref([]);
const kelurahanOptions = ref([]);

// Load provinsi on mount
const loadProvinsi = async () => {
    try {
        const response = await fetch(
            "https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json",
        );
        const data = await response.json();
        provinsiOptions.value = data.map((p) => ({ id: p.id, name: p.name }));
    } catch (error) {
        console.error("Error loading provinsi:", error);
    }
};

// Load kota based on provinsi
const onProvinsiChange = async () => {
    form.kota = "";
    form.kecamatan = "";
    form.kelurahan = "";
    kotaOptions.value = [];
    kecamatanOptions.value = [];
    kelurahanOptions.value = [];

    const selectedProvinsi = provinsiOptions.value.find(
        (p) => p.name === form.provinsi,
    );
    if (selectedProvinsi) {
        try {
            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProvinsi.id}.json`,
            );
            const data = await response.json();
            kotaOptions.value = data.map((k) => ({ id: k.id, name: k.name }));
        } catch (error) {
            console.error("Error loading kota:", error);
        }
    }
    form.errors.provinsi = null;
};

// Load kecamatan based on kota
const onKotaChange = async () => {
    form.kecamatan = "";
    form.kelurahan = "";
    kecamatanOptions.value = [];
    kelurahanOptions.value = [];

    const selectedKota = kotaOptions.value.find((k) => k.name === form.kota);
    if (selectedKota) {
        try {
            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedKota.id}.json`,
            );
            const data = await response.json();
            kecamatanOptions.value = data.map((kec) => ({
                id: kec.id,
                name: kec.name,
            }));
        } catch (error) {
            console.error("Error loading kecamatan:", error);
        }
    }
    form.errors.kota = null;
};

// Load kelurahan based on kecamatan
const onKecamatanChange = async () => {
    form.kelurahan = "";
    kelurahanOptions.value = [];

    const selectedKecamatan = kecamatanOptions.value.find(
        (kec) => kec.name === form.kecamatan,
    );
    if (selectedKecamatan) {
        try {
            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedKecamatan.id}.json`,
            );
            const data = await response.json();
            kelurahanOptions.value = data.map((kel) => ({
                id: kel.id,
                name: kel.name,
            }));
        } catch (error) {
            console.error("Error loading kelurahan:", error);
        }
    }
    form.errors.kecamatan = null;
};

// Load initial data
loadProvinsi();

// Auto-set jabatan_id to "Staff" on component mount
onMounted(() => {
    const staffRole = props.roles.find(role => role.name.toLowerCase() === 'staff');
    if (staffRole) {
        form.jabatan_id = staffRole.id;
        // Auto-fill quotas from Staff role
        form.leave_quota_per_year = Number(staffRole.leave_quota_per_year ?? 0);
        form.loan_quota = Number(staffRole.loan_quota ?? 0);
    }
});
</script>

<style scoped>
/* Custom styles untuk SweetAlert2 validation */
:global(.swal2-popup-validation) {
    font-family: "Inter", sans-serif;
}

:global(.swal2-html-validation) {
    margin: 0 !important;
    padding: 0 !important;
}

:global(.swal2-popup-validation .swal2-title) {
    color: #dc3545;
    font-size: 1.5rem;
    font-weight: 600;
}

:global(.swal2-popup-validation .swal2-html-container) {
    text-align: left;
    line-height: 1.6;
}
</style>
