<template>

    <Head title="Edit Karyawan" />
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
                                Edit Karyawan
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Formulir untuk mengedit data karyawan
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
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="form.password"
                                placeholder="Masukkan password baru (kosongkan jika tidak ingin mengubah)" minlength="8"
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
                    <!-- Row 1: Departemen, Jabatan -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                        <div>
                            <label for="jabatan_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jabatan
                                <span class="text-red-500">*</span></label>
                            <Select v-model="form.jabatan_id" :options="roleOptions" optionLabel="label"
                                optionValue="value" placeholder="Pilih Jabatan" :filter="showFilterRole"
                                filterPlaceholder="Cari jabatan..." :pt="selectPt" :required="true"
                                class="text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                :class="[
                                    !form.jabatan_id
                                        ? 'text-gray-400'
                                        : 'text-gray-900',
                                    {
                                        'p-invalid border-red-500':
                                            form.errors.jabatan_id,
                                    },
                                ]" @change="onJabatanChanged" />
                            <div v-if="form.errors.jabatan_id" class="mt-1 text-xs text-red-500">
                                {{ form.errors.jabatan_id[0] }}
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
                                Bekerja</label>
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
                                Karyawan</label>
                            <input ref="photoInput" type="file"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                                @change="handleFileChange($event, 'photo')" accept="image/*"
                                :class="{ 'border-red-500': form.errors.photo }" />
                            <div v-if="form.errors.photo" class="mt-1 text-xs text-red-500">
                                {{ form.errors.photo[0] }}
                            </div>
                            <!-- Preview Image -->
                            <div v-if="previewUrls.photo" class="mt-3 relative inline-block">
                                <div class="relative group">
                                    <img :src="previewUrls.photo" alt="Preview Foto Karyawan"
                                        class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-cover" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Tanda
                                Tangan</label>
                            <input ref="signatureInput" type="file"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                                accept="image/*" @change="handleFileChange($event, 'signature')" :class="{
                                    'border-red-500': form.errors.signature,
                                }" />
                            <div v-if="form.errors.signature" class="mt-1 text-xs text-red-500">
                                {{ form.errors.signature[0] }}
                            </div>
                            <!-- Preview Image -->
                            <div v-if="previewUrls.signature" class="mt-3 relative inline-block">
                                <div class="relative group">
                                    <img :src="previewUrls.signature" alt="Preview Tanda Tangan"
                                        class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                                </div>
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
                        <div class="px-3 py-2 w-full h-auto text-sm bg-white rounded-lg border border-gray-300"
                            :class="{ 'border-red-500': form.errors.gender }">
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
                        <div class="px-3 py-2 w-full h-auto text-sm bg-white rounded-lg border border-gray-300"
                            :class="{ 'border-red-500': form.errors.status }">
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
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Foto
                            KTP</label>
                        <input ref="ktpInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="handleFileChange($event, 'ktp')" :class="{
                                'border-red-500': form.errors.ktp_file,
                            }" />
                        <div v-if="form.errors.ktp_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.ktp_file[0] }}
                        </div>
                        <!-- Preview Image -->
                        <div v-if="previewUrls.ktp" class="mt-3 relative inline-block">
                            <div class="relative group">
                                <img :src="previewUrls.ktp" alt="Preview Foto KTP"
                                    class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                            </div>
                        </div>
                        <!-- Preview Document -->
                        <div v-if="fileInfo.ktp && !previewUrls.ktp" class="mt-3">
                            <div v-if="fileInfo.ktp.isPdf"
                                class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 overflow-hidden">
                                <iframe :src="fileInfo.ktp.url" class="w-full h-64" frameborder="0"></iframe>
                            </div>
                            <div v-else class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ fileInfo.ktp.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                fileInfo.ktp.isDoc
                                                    ? "Microsoft Word Document"
                                                    : "Document"
                                            }}
                                        </p>
                                    </div>
                                    <a :href="fileInfo.ktp.url" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kartu BPJS
                            Kesehatan</label>
                        <input ref="bpjsKesehatanInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="handleFileChange($event, 'bpjs_kesehatan')"
                            :class="{
                                'border-red-500':
                                    form.errors.bpjs_kesehatan_file,
                            }" />
                        <div v-if="form.errors.bpjs_kesehatan_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.bpjs_kesehatan_file[0] }}
                        </div>
                        <!-- Preview Image -->
                        <div v-if="previewUrls.bpjs_kesehatan" class="mt-3 relative inline-block">
                            <div class="relative group">
                                <img :src="previewUrls.bpjs_kesehatan" alt="Preview BPJS Kesehatan"
                                    class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                            </div>
                        </div>
                        <!-- Preview Document -->
                        <div v-if="
                            fileInfo.bpjs_kesehatan &&
                            !previewUrls.bpjs_kesehatan
                        " class="mt-3">
                            <div v-if="fileInfo.bpjs_kesehatan.isPdf"
                                class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 overflow-hidden">
                                <iframe :src="fileInfo.bpjs_kesehatan.url" class="w-full h-64" frameborder="0"></iframe>
                            </div>
                            <div v-else class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ fileInfo.bpjs_kesehatan.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                fileInfo.bpjs_kesehatan.isDoc
                                                    ? "Microsoft Word Document"
                                                    : "Document"
                                            }}
                                        </p>
                                    </div>
                                    <a :href="fileInfo.bpjs_kesehatan.url" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kartu BPJS
                            Ketenagakerjaan</label>
                        <input ref="bpjsKetenagakerjaanInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="
                                handleFileChange($event, 'bpjs_ketenagakerjaan')
                                " :class="{
                                    'border-red-500':
                                        form.errors.bpjs_ketenagakerjaan_file,
                                }" />
                        <div v-if="form.errors.bpjs_ketenagakerjaan_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.bpjs_ketenagakerjaan_file[0] }}
                        </div>
                        <!-- Preview Image -->
                        <div v-if="previewUrls.bpjs_ketenagakerjaan" class="mt-3 relative inline-block">
                            <div class="relative group">
                                <img :src="previewUrls.bpjs_ketenagakerjaan" alt="Preview BPJS Ketenagakerjaan"
                                    class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                            </div>
                        </div>
                        <!-- Preview Document -->
                        <div v-if="
                            fileInfo.bpjs_ketenagakerjaan &&
                            !previewUrls.bpjs_ketenagakerjaan
                        " class="mt-3">
                            <div v-if="fileInfo.bpjs_ketenagakerjaan.isPdf"
                                class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 overflow-hidden">
                                <iframe :src="fileInfo.bpjs_ketenagakerjaan.url" class="w-full h-64"
                                    frameborder="0"></iframe>
                            </div>
                            <div v-else class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{
                                                fileInfo.bpjs_ketenagakerjaan
                                                    .name
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                fileInfo.bpjs_ketenagakerjaan
                                                    .isDoc
                                                    ? "Microsoft Word Document"
                                                    : "Document"
                                            }}
                                        </p>
                                    </div>
                                    <a :href="fileInfo.bpjs_ketenagakerjaan.url
                                        " target="_blank"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Sertifikat
                            (opsional)</label>
                        <input ref="certificateInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf,.doc,.docx"
                            @change="handleFileChange($event, 'certificate')" :class="{
                                'border-red-500': form.errors.certificate_file,
                            }" />
                        <div v-if="form.errors.certificate_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.certificate_file[0] }}
                        </div>
                        <!-- Preview Image -->
                        <div v-if="previewUrls.certificate" class="mt-3 relative inline-block">
                            <div class="relative group">
                                <img :src="previewUrls.certificate" alt="Preview Sertifikat"
                                    class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                            </div>
                        </div>
                        <!-- Preview Document -->
                        <div v-if="
                            fileInfo.certificate && !previewUrls.certificate
                        " class="mt-3">
                            <div v-if="fileInfo.certificate.isPdf"
                                class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 overflow-hidden">
                                <iframe :src="fileInfo.certificate.url" class="w-full h-64" frameborder="0"></iframe>
                            </div>
                            <div v-else class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ fileInfo.certificate.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                fileInfo.certificate.isDoc
                                                    ? "Microsoft Word Document"
                                                    : "Document"
                                            }}
                                        </p>
                                    </div>
                                    <a :href="fileInfo.certificate.url" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kontrak
                            (opsional)</label>
                        <input ref="contractInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf,.doc,.docx" @change="handleFileChange($event, 'contract')"
                            :class="{
                                'border-red-500': form.errors.contract_file,
                            }" />
                        <div v-if="form.errors.contract_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.contract_file[0] }}
                        </div>
                        <!-- Preview Image -->
                        <div v-if="previewUrls.contract" class="mt-3 relative inline-block">
                            <div class="relative group">
                                <img :src="previewUrls.contract" alt="Preview Kontrak"
                                    class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                            </div>
                        </div>
                        <!-- Preview Document -->
                        <div v-if="fileInfo.contract && !previewUrls.contract" class="mt-3">
                            <div v-if="fileInfo.contract.isPdf"
                                class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 overflow-hidden">
                                <iframe :src="fileInfo.contract.url" class="w-full h-64" frameborder="0"></iframe>
                            </div>
                            <div v-else class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ fileInfo.contract.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                fileInfo.contract.isDoc
                                                    ? "Microsoft Word Document"
                                                    : "Document"
                                            }}
                                        </p>
                                    </div>
                                    <a :href="fileInfo.contract.url" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Kartu
                            Keluarga (opsional)</label>
                        <input ref="kkInput" type="file"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300"
                            accept="image/*,application/pdf" @change="handleFileChange($event, 'kk')" :class="{
                                'border-red-500': form.errors.kk_file,
                            }" />
                        <div v-if="form.errors.kk_file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.kk_file[0] }}
                        </div>
                        <!-- Preview Image -->
                        <div v-if="previewUrls.kk" class="mt-3 relative inline-block">
                            <div class="relative group">
                                <img :src="previewUrls.kk" alt="Preview Kartu Keluarga"
                                    class="max-w-full h-auto max-h-64 rounded-lg border-2 border-gray-300 shadow-md object-contain bg-gray-50" />
                            </div>
                        </div>
                        <!-- Preview Document -->
                        <div v-if="fileInfo.kk && !previewUrls.kk" class="mt-3">
                            <div v-if="fileInfo.kk.isPdf"
                                class="rounded-lg border-2 border-gray-300 shadow-md bg-gray-50 overflow-hidden">
                                <iframe :src="fileInfo.kk.url" class="w-full h-64" frameborder="0"></iframe>
                            </div>
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
                <Button title="Simpan" class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600" type="button"
                    @click="openConfirm">Simpan</Button>
            </div>
        </div>

        <Dialog v-model:visible="confirmVisible" modal dismissableMask
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }" :style="{ width: '560px' }" :pt="dialogPt">
            <!-- Header -->
            <template #header>
                <div class="flex items-center justify-between w-full">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Simpan Perubahan
                    </h3>
                </div>
            </template>

            <!-- Content -->
            <div class="flex flex-col items-center text-center">
                <svg width="250" height="250" viewBox="0 0 250 250" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M109.144 134.282C111.529 126.073 114.372 119.194 117.72 113.553C121.068 107.912 125.471 101.446 130.882 94.1536C134.826 88.742 137.945 84.2476 140.192 80.7621C142.439 77.2766 144.32 73.3784 145.833 69.0674C147.301 64.7564 148.034 60.1703 148.034 55.2173V55.0797C148.034 46.5036 145.833 39.8537 141.43 35.0841C137.028 30.3603 130.882 27.9755 122.994 27.9755C115.014 27.9755 108.823 30.9106 104.42 36.7809C100.017 42.6512 97.7702 50.9521 97.6785 61.7296V61.8672H67.043V61.7296C67.4557 48.7049 69.8864 37.5605 74.4267 28.3424C78.967 19.1242 85.3417 12.0615 93.505 7.24608C101.714 2.43063 111.437 0 122.673 0C134.414 0 144.503 2.15547 152.85 6.51231C161.243 10.8692 167.617 17.1522 172.02 25.4073C176.423 33.6624 178.624 43.5684 178.624 55.2173V55.3548C178.624 62.3716 177.707 68.6547 175.872 74.2956C174.038 79.9366 171.837 84.7979 169.222 88.9713C166.608 93.0989 163.031 98.1436 158.491 104.06C153.538 110.389 149.548 115.984 146.521 120.845C143.448 125.752 140.88 131.623 138.816 138.456C136.752 145.335 135.698 153.223 135.698 162.212H105.521C105.613 151.802 106.759 142.492 109.144 134.282ZM104.833 185.831H136.523V217.521H104.833V185.831Z"
                        fill="#1B84FF" />
                    <path
                        d="M215.086 209.678C210.959 198.396 213.252 193.627 211.647 181.886C209.904 169.274 205.96 156.341 200.732 144.968C198.943 141.07 195.87 136.392 192.935 133.044C180.323 118.735 168.72 105.894 150.284 97.5928V97.6845C150.055 96.4004 149.825 95.1163 149.55 93.878C151.201 92.2729 153.219 90.8512 155.237 89.4753C158.631 87.1364 161.979 84.7975 165.372 82.5044C166.198 81.954 167.023 81.3578 167.482 80.624C167.941 79.8902 167.986 78.9272 167.299 78.4227C166.794 78.0558 166.014 78.01 165.281 77.9182C164.18 77.8265 163.033 77.7348 161.933 77.9641C160.832 78.1475 159.777 78.6061 158.814 79.1106C154.824 81.0368 151.247 83.3299 147.762 85.7605C146.661 81.0827 145.423 76.1754 144.138 70.3052C144.138 70.2135 144.138 70.1676 144.184 70.0759C146.523 70.6721 149.137 70.3052 151.064 68.8376C154.136 66.5446 155.145 61.8667 153.265 58.5188C151.751 55.813 148.495 54.2078 145.423 54.483C145.744 39.3945 142.487 30.4515 133.728 30.8643C129.784 30.7726 125.84 32.5153 122.584 34.8084C112.815 41.6876 103.23 48.842 94.5622 54.8957C94.5622 54.8957 104.698 60.9494 109.696 63.8846C109.696 63.8846 105.431 79.7068 101.992 90.5301C101.854 90.5301 101.762 90.4842 101.625 90.4842C99.6069 90.3925 97.3597 91.4015 96.8094 93.0525C96.3966 94.3367 97.0387 95.7583 98.0018 96.8132C98.2769 97.1342 98.598 97.4094 98.919 97.6845C79.6113 111.947 66.5867 119.285 55.3047 138.089C51.1772 144.922 42.2801 158.222 39.7118 165.605C36.5932 174.64 33.1995 183.354 37.8774 193.122C36.3181 193.352 33.9332 194.177 33.6581 195.553C33.337 197.066 33.7957 198.626 34.2543 200.093C34.7588 201.79 35.4467 203.67 37.2353 204.496C35.3091 205.551 34.2084 207.615 34.4836 209.587C34.7588 211.559 36.4556 213.301 38.6111 213.898C36.1346 215.365 36.6391 218.942 38.8404 220.685C41.0876 222.428 44.298 222.795 47.279 223.024C50.26 223.253 53.4244 223.391 55.9468 224.858L56.2678 224.446C56.3137 226.693 58.2399 228.573 60.3495 229.903C70.6225 236.461 87.8664 234.535 98.2769 240.91L99.3318 242.057C102.68 245.955 107.449 249.578 113.044 249.532C116.943 249.486 120.52 247.698 123.73 245.771C129.004 242.561 133.866 238.663 137.58 234.123C147.807 237.012 156.383 232.701 166.977 235.223C183.946 239.259 208.069 234.26 211.509 228.115C214.857 222.382 217.288 215.686 215.086 209.678Z"
                        fill="white" />
                    <path
                        d="M70.5306 42.0545C70.9892 45.219 71.0809 48.4751 71.2644 51.6854C71.3102 52.5568 69.9344 52.5568 69.8885 51.6854C69.7051 48.7044 69.6134 45.6776 69.2465 42.7424C68.9713 40.4952 68.3751 38.0646 66.9075 36.276C65.6693 34.8084 63.789 33.7078 61.8169 33.6619C59.8907 33.6619 58.0563 34.9002 57.7811 36.8722C57.6435 37.7894 57.9646 38.7525 58.7442 39.3028C59.4321 39.8073 60.3952 40.0366 61.1748 39.578C61.9545 39.1652 62.6424 40.3577 61.8628 40.7704C59.3862 42.1004 56.3594 39.9907 56.3594 37.2849C56.3594 34.7625 58.4232 32.6529 60.8997 32.2861C63.2386 31.965 65.6693 33.0198 67.3203 34.625C69.2923 36.597 70.1637 39.3946 70.5306 42.0545ZM78.6481 61.4081C78.6023 61.4081 78.6481 61.4081 78.6481 61.4081C78.6023 61.4081 78.6023 61.4081 78.6481 61.4081C78.6023 61.3623 78.6022 61.3622 78.6022 61.3622C78.6022 61.4081 78.6023 61.4081 78.6481 61.4081ZM78.4647 62.0043C78.4647 61.9584 78.4647 61.9584 78.4647 62.0043V62.0043ZM80.024 61.5457C79.9781 62.417 79.4277 63.0591 78.7398 63.5636C78.6022 63.6553 78.4646 63.747 78.327 63.8387C78.1436 63.9763 77.9143 64.0222 77.685 63.9764C77.5933 63.9305 77.5015 63.8846 77.4098 63.8387C77.0888 63.6094 76.8595 63.2884 76.7219 62.8757C76.5385 62.3253 76.6302 61.5915 77.043 61.1329C76.9971 60.9953 76.9971 60.8578 77.043 60.7202C77.1805 60.3074 77.5015 59.9864 77.9143 59.8488C78.2812 59.7571 78.6481 59.8029 78.9691 59.9405C79.5653 60.2615 80.0698 60.8577 80.024 61.5457ZM78.006 62.5547C78.006 62.5088 78.006 62.5547 78.006 62.5547C78.006 62.5088 78.006 62.5088 78.006 62.5547ZM78.0519 62.0043C78.0978 61.9584 78.1436 61.9126 78.0519 62.0043V62.0043ZM78.327 61.0871C78.327 61.1329 78.327 61.1329 78.327 61.0871C78.2812 61.1329 78.2812 61.1329 78.327 61.0871C78.327 61.1329 78.327 61.1329 78.327 61.0871ZM78.5564 61.8667C78.5564 61.8209 78.6022 61.775 78.6022 61.775C78.6022 61.7291 78.6481 61.6832 78.6481 61.5915C78.6481 61.5457 78.6481 61.4998 78.6481 61.454L78.6022 61.4081V61.454C78.5563 61.4081 78.5564 61.4081 78.5105 61.3622C78.5105 61.3622 78.5105 61.3622 78.4647 61.3622C78.4188 61.3622 78.3729 61.3164 78.3729 61.3164H78.327C78.327 61.3164 78.3271 61.3164 78.2812 61.3164C78.2812 61.4081 78.327 61.4539 78.327 61.5457C78.327 61.6833 78.2353 61.9126 78.1436 62.0501C78.0977 62.096 78.0519 62.1419 78.006 62.1419L77.9602 62.1877V62.2336C77.9602 62.2795 77.9602 62.2795 77.9602 62.3253C77.9602 62.3712 77.9602 62.4629 77.9602 62.5088C77.9602 62.5088 77.9602 62.5088 77.9602 62.5547C78.0061 62.5088 78.1436 62.4171 77.9602 62.5547C78.0061 62.5088 78.006 62.5088 78.0519 62.4629C78.1436 62.417 78.1895 62.3253 78.2812 62.2794C78.3271 62.2336 78.327 62.2336 78.3729 62.1877L78.4188 62.1419C78.4188 62.1419 78.4188 62.1877 78.3729 62.1877C78.4646 62.0043 78.5105 61.9126 78.5564 61.8667ZM213.16 82.7337C212.289 82.5044 211.922 83.8344 212.793 84.0637C215.086 84.6599 216.049 87.3199 215.407 89.4295C214.673 91.8602 212.334 93.6029 209.995 94.3366C207.473 95.1163 204.676 94.887 202.337 93.6029C200.135 92.4105 198.026 90.5301 195.366 90.4384C193.118 90.3467 190.55 91.6767 190.413 94.1532C190.367 95.0246 191.743 95.0246 191.788 94.1532C191.926 91.9518 194.907 91.5391 196.558 92.0436C197.934 92.4563 199.126 93.3277 200.319 94.0615C201.557 94.7953 202.795 95.4832 204.217 95.8501C206.877 96.538 209.766 96.217 212.197 94.9329C214.444 93.7863 216.462 91.6767 216.921 89.0626C217.379 86.4026 215.912 83.4216 213.16 82.7337ZM183.35 97.2259C183.35 97.1801 183.35 97.2259 183.35 97.2259V97.2259ZM184.588 96.3546C184.818 96.859 184.863 97.4552 184.542 97.9597C184.313 98.3266 183.946 98.5559 183.488 98.6476C183.075 98.7394 182.616 98.6935 182.249 98.4642L182.203 98.4183C181.607 98.6476 180.828 98.4642 180.461 97.868C180.415 97.7763 180.323 97.6387 180.323 97.547C180.277 97.3635 180.323 97.1801 180.415 96.9966C180.506 96.7673 180.598 96.538 180.782 96.3546C181.103 95.896 181.515 95.5749 182.066 95.3456C182.937 94.9329 184.13 95.3915 184.588 96.3546ZM182.616 96.538C182.57 96.538 182.524 96.5839 182.479 96.5839H182.433H182.387C182.433 96.5839 182.479 96.6297 182.57 96.6297C182.845 96.6756 183.029 96.9508 183.075 97.2259H183.121H183.166H183.212H183.258C183.258 97.2259 183.304 97.226 183.35 97.1801C183.35 97.1801 183.35 97.1801 183.35 97.1342V97.0883C183.35 97.0883 183.35 97.0884 183.35 97.0425V96.9966V97.0425V96.9966C183.35 96.9966 183.35 96.9967 183.35 96.9508V96.9049C183.304 96.859 183.304 96.8132 183.258 96.7673C183.258 96.7673 183.258 96.7673 183.212 96.7215C183.166 96.6756 183.121 96.6297 183.075 96.6297C183.075 96.6297 183.075 96.6297 183.029 96.6297C182.983 96.6297 182.937 96.5839 182.891 96.538C182.937 96.538 182.983 96.538 182.891 96.538H182.845H182.8C182.754 96.538 182.57 96.4922 182.708 96.4922C182.754 96.4922 182.8 96.538 182.708 96.4922C182.662 96.4922 182.662 96.4922 182.616 96.4922C182.662 96.538 182.662 96.538 182.616 96.538C182.616 96.538 182.57 96.538 182.616 96.538C182.616 96.538 182.57 96.538 182.616 96.538ZM183.396 97.1342V97.0883C183.35 97.0883 183.35 97.1342 183.396 97.1342ZM183.35 97.0425C183.35 96.9966 183.35 97.0425 183.35 97.0425V96.9966V97.0425ZM182.662 96.538H182.616C182.662 96.5839 182.662 96.538 182.662 96.538ZM204.171 30.4974C203.988 28.7547 203.346 27.012 202.107 25.7278C201.007 24.6272 199.539 23.8934 198.026 23.6182C196.375 23.2972 194.54 23.4806 193.073 24.3978C191.788 25.1775 189.771 27.6082 191.605 28.8464C192.339 29.3509 193.027 28.1585 192.293 27.654C191.926 27.4247 192.889 26.2782 193.118 26.0488C193.577 25.6361 194.127 25.3151 194.724 25.0858C196.008 24.673 197.384 24.7189 198.622 25.1316C199.906 25.5444 201.052 26.3241 201.786 27.4706C202.703 28.9382 202.979 30.7267 202.749 32.3778C202.245 36.1384 199.677 39.1194 196.558 40.9997C193.027 43.1552 189.037 43.9348 185.184 45.3107C183.121 46.0445 181.057 47.0993 179.727 48.8879C178.489 50.5389 177.801 52.6485 178.443 54.6664C178.718 55.492 180.048 55.1251 179.773 54.2996C179.131 52.3275 180.094 50.3096 181.515 48.9338C183.121 47.3745 185.368 46.6407 187.432 46.0445C191.605 44.7604 195.87 43.5221 199.31 40.7245C202.337 38.3856 204.538 34.4874 204.171 30.4974ZM177.434 62.0043C177.159 62.0043 176.883 62.0043 176.608 62.0043C176.517 62.0043 176.425 62.0043 176.333 62.0501C176.241 62.096 176.196 62.1419 176.104 62.1877C176.058 62.2336 176.012 62.3253 175.966 62.417C175.92 62.5088 175.92 62.6005 175.92 62.6922C175.92 62.8757 175.966 63.0591 176.104 63.1967C176.241 63.3343 176.425 63.3801 176.608 63.3801C176.883 63.3801 177.159 63.3801 177.434 63.3801C177.525 63.3801 177.617 63.3801 177.709 63.3343C177.801 63.2884 177.847 63.2425 177.938 63.1967C177.984 63.1508 178.03 63.0591 178.076 62.9674C178.122 62.8756 178.122 62.7839 178.122 62.6922C178.122 62.5088 178.076 62.3253 177.938 62.1877C177.801 62.0502 177.617 62.0043 177.434 62.0043Z"
                        fill="#FFE11B" />
                    <path
                        d="M110.521 127.998C109.742 127.586 110.43 126.393 111.209 126.806C117.263 130.016 124.509 130.933 131.205 129.603C132.076 129.42 132.443 130.75 131.572 130.933C124.509 132.355 116.942 131.346 110.521 127.998ZM216.553 217.382C216.37 220.318 215.544 223.161 214.031 225.683C212.563 228.068 210.545 230.04 208.252 231.554C202.749 235.223 195.915 236.553 189.449 237.057C185.597 237.378 181.698 237.332 177.846 237.103C175.782 236.965 173.718 236.782 171.655 236.507C169.453 236.231 167.344 235.819 165.188 235.452C160.464 234.672 155.741 235.177 151.017 235.544C146.706 235.865 142.441 235.91 138.222 234.856C136.25 237.378 134.002 239.625 131.48 241.597C130.288 242.514 129.095 243.386 127.811 244.211C126.389 245.129 124.922 245.908 123.546 246.825C120.978 248.568 117.951 249.669 114.832 249.944C108.733 250.449 102.908 247.422 99.1476 242.698C98.8725 242.331 98.5514 241.964 98.2763 241.551C98.2304 241.551 98.1387 241.506 98.047 241.46C94.7449 239.442 90.9843 238.341 87.2236 237.561C80.161 236.048 72.8231 235.544 65.9439 233.205C62.7336 232.104 59.1564 230.545 57.0009 227.747C56.4506 227.059 56.0837 226.28 55.8544 225.454C55.8085 225.454 55.7627 225.408 55.7168 225.408C53.6989 224.262 51.36 223.986 49.0669 223.803C45.4438 223.528 41.0869 223.528 38.1977 220.914C36.2715 219.171 35.7212 216.052 37.3722 214.126C35.6753 213.301 34.2077 211.787 33.8867 209.861C33.5198 207.797 34.4829 205.825 36.0422 204.541C35.2626 203.945 34.7122 203.119 34.2994 202.202C33.7032 200.872 33.2447 199.359 33.0154 197.937C32.786 196.47 32.9236 194.956 33.8408 193.718C34.5746 192.755 35.5836 192.113 36.776 191.929C32.6943 179.822 37.4181 166.843 43.1049 155.974C44.8476 152.672 46.7279 149.416 48.6541 146.205C50.4886 143.178 52.323 140.152 54.2951 137.216C57.9181 131.897 61.9998 126.852 66.4484 122.266C75.8958 112.497 86.123 104.976 97.5425 97.7297C96.4418 96.5373 95.6163 95.0239 96.1666 93.3728C96.8087 91.4008 99.0559 90.3919 100.982 90.3002C101.12 90.3002 101.257 90.3002 101.349 90.3002C101.441 90.025 101.532 89.7039 101.624 89.4288C103.367 83.8337 105.018 78.1927 106.577 72.5517C107.357 69.8 108.136 67.0483 108.87 64.2508C107.724 63.5629 106.577 62.9208 105.431 62.2329C101.67 60.0315 97.9094 57.7843 94.1487 55.5371C93.6901 55.2619 93.736 54.6199 94.1487 54.3447C96.9004 52.4185 99.6521 50.4465 102.404 48.4744C107.54 44.8055 112.631 41.0449 117.767 37.376C120.244 35.5874 122.72 33.6612 125.426 32.2853C127.031 31.4598 128.728 30.8636 130.471 30.4967C129.37 29.9005 128.224 29.1209 127.169 28.0202C124.463 25.1768 123.408 16.1879 129.187 11.8311C133.452 24.1219 142.166 18.5727 151.2 26.5067C158.171 32.698 153.494 47.6948 147.807 53.9778C150.237 54.4823 152.53 55.9957 153.723 58.1971C155.741 61.866 154.686 66.9107 151.292 69.3873C149.825 70.4879 147.99 71.0383 146.156 71.0383C145.697 71.0383 145.284 70.9924 144.826 70.9466C145.238 72.9186 145.697 74.8448 146.156 76.8169C146.798 79.5227 147.486 82.2743 148.128 84.9802C148.862 84.4757 149.595 83.9713 150.375 83.4668C152.53 82.0909 154.686 80.7609 156.979 79.6144C158.126 79.0182 159.272 78.422 160.51 78.0092C161.749 77.5965 163.033 77.5506 164.317 77.5965C165.418 77.6423 166.748 77.6423 167.665 78.3303C168.49 79.0182 168.582 80.1647 168.123 81.0819C167.619 82.1368 166.518 82.8247 165.601 83.5126C164.409 84.3381 163.216 85.1637 162.024 85.9892C159.685 87.6402 157.3 89.2453 154.961 90.8963C152.989 92.2722 151.017 93.648 149.366 95.4366C149.641 95.9411 149.825 96.4456 149.916 96.9959C150.1 96.9501 150.283 96.9501 150.467 97.0418C152.072 97.9131 153.631 98.7845 155.236 99.7018C161.519 103.371 167.619 107.452 173.306 112.038C178.993 116.579 184.267 121.624 188.945 127.219C193.76 132.951 197.933 139.143 201.327 145.793C204.996 152.993 207.794 160.606 209.72 168.448C210.775 172.713 211.6 177.07 212.196 181.473C212.792 185.784 212.884 190.095 212.976 194.452C213.068 198.396 213.435 202.294 214.535 206.1C214.902 207.293 215.407 208.485 215.728 209.723C215.957 210.595 216.094 211.512 216.186 212.429C216.553 214.034 216.645 215.686 216.553 217.382ZM105.293 82.3661C104.513 85.0719 103.688 87.7777 102.862 90.4377C106.898 91.1715 109.742 95.1615 113.135 97.2252C116.804 99.4724 121.528 101.123 125.839 100.206C128.27 99.7018 130.333 98.1425 132.351 96.7666C134.415 95.3449 136.433 93.8314 138.451 92.318C141.34 90.1625 144.184 87.9612 147.119 85.8974C145.926 80.8068 144.642 75.762 143.542 70.6714C142.212 70.2586 141.019 69.5707 140.056 68.5618C139.781 68.2866 139.781 67.8739 140.056 67.5987C140.331 67.3235 140.744 67.3235 141.019 67.5987C143.45 70.0752 147.853 70.4421 150.65 68.3325C153.448 66.2687 154.365 61.9577 152.668 58.885C150.971 55.8582 146.844 54.3447 143.587 55.6288C143.221 55.7664 142.854 55.583 142.716 55.2161C142.578 54.8492 142.762 54.4823 143.129 54.3447C143.404 54.253 143.679 54.1613 144 54.0696L142.533 53.6568C138.222 48.8872 140.561 43.98 140.285 39.7607C139.919 33.5236 136.8 32.8815 133.131 31.5515C130.104 31.6432 127.261 32.7898 124.692 34.3032C122.078 35.8625 119.648 37.7887 117.125 39.5773C114.603 41.4117 112.081 43.2003 109.558 45.0348C105.018 48.3368 100.478 51.593 95.8915 54.8492C96.0291 54.9409 96.1666 54.9868 96.2583 55.0785C100.844 57.8302 105.477 60.5819 110.109 63.2877C110.384 63.4253 110.521 63.7922 110.43 64.0673C110.154 65.1221 109.879 66.1311 109.558 67.1859C108.182 72.2766 106.761 77.3213 105.293 82.3661ZM98.5973 96.8583C99.4228 97.7297 100.432 98.4176 101.395 99.1514C102.266 99.7934 103.046 100.527 103.825 101.307C108.091 105.847 109.696 111.993 112.677 117.313C115.612 116.212 119.006 117.129 121.941 117.817C124.005 118.322 126.114 118.78 128.178 119.285C130.058 119.743 132.03 120.064 133.865 120.706C135.011 121.119 136.25 121.761 136.754 122.908C138.955 121.027 140.377 118.276 142.624 116.395C144 115.203 145.835 114.286 146.844 112.772C147.486 111.855 147.532 110.617 146.66 110.066C146.523 110.158 146.431 110.25 146.293 110.342C145.972 110.617 145.651 110.892 145.33 111.167C144.688 111.763 144.138 112.36 143.542 113.002C143.312 113.277 142.808 113.231 142.578 113.002C142.303 112.726 142.349 112.314 142.578 112.038C143.496 110.984 144.505 110.021 145.559 109.149C145.559 108.92 145.651 108.645 145.881 108.553C146.889 108.14 147.761 107.315 147.99 106.214C148.219 105.251 147.853 104.334 147.348 103.508C146.202 104.563 145.101 105.618 143.954 106.673C143.679 106.948 143.266 106.948 142.991 106.673C142.716 106.443 142.716 105.939 142.991 105.71C144.229 104.563 145.468 103.417 146.706 102.224C146.706 102.087 146.798 101.903 146.889 101.811C148.403 100.481 149.274 98.4635 148.54 96.629C147.532 97.6838 146.477 98.6928 145.468 99.7476C145.193 100.023 144.78 99.9769 144.505 99.7476C144.23 99.4724 144.23 99.0138 144.505 98.7845C145.697 97.5921 146.844 96.3997 148.036 95.2073C148.082 95.1156 148.128 95.0239 148.174 94.9322C148.953 94.0149 149.825 93.2353 150.788 92.4556C152.76 90.8505 154.915 89.4288 157.025 88.0071C159.272 86.4478 161.519 84.8885 163.767 83.3292C164.775 82.6412 165.922 81.9992 166.748 81.0819C167.023 80.7609 167.344 80.2565 167.206 79.7979C167.069 79.3851 166.518 79.2934 166.105 79.2016C164.913 79.0182 163.629 78.9265 162.437 79.1099C161.152 79.2934 160.006 79.8437 158.859 80.394C150.237 84.6591 142.9 90.9422 135.103 96.5373C133.131 97.959 131.113 99.4724 128.958 100.573C127.031 101.536 124.876 101.903 122.72 101.811C118.593 101.582 114.42 99.931 111.026 97.5004C109.1 96.1245 107.494 94.2442 105.477 93.0059C103.642 91.8594 101.349 91.2174 99.2852 92.1346C98.5056 92.5015 97.6801 93.0977 97.4966 93.9691C97.2673 95.0239 97.9094 96.1245 98.5973 96.8583ZM59.4774 217.933C59.5691 217.841 59.6609 217.795 59.7526 217.749C59.294 217.153 58.8812 216.557 58.4685 215.961C57.5054 214.585 56.4964 213.255 55.2582 212.062C54.0658 210.916 52.6899 209.953 51.3141 209.036C48.5624 207.201 45.6272 205.504 43.4259 203.028C41.0411 200.368 39.0232 197.341 37.6474 194.039C37.5098 193.764 37.418 193.489 37.3263 193.213H37.2804C36.2256 193.259 35.3084 193.901 34.8039 194.818C34.2077 195.919 34.2995 197.203 34.6205 198.35C35.125 200.368 35.7211 202.89 37.7391 203.899C38.1977 204.128 38.1518 204.862 37.7391 205.091C36.1798 205.963 34.9874 207.751 35.3543 209.632C35.7212 211.42 37.2805 212.75 38.9773 213.209C39.5277 213.392 39.6652 214.172 39.1608 214.447C37.8766 215.227 37.6015 216.649 38.0601 218.024C39.0691 221.189 43.1507 221.877 45.9941 222.152C49.2962 222.473 52.7816 222.381 55.8085 223.849C55.8544 223.436 55.9002 223.069 56.0378 222.656C56.5882 220.822 58.0557 219.309 59.4774 217.933ZM96.3959 239.075C95.4329 237.745 94.4698 236.461 93.3691 235.223C91.9015 233.526 90.3422 231.92 88.5995 230.545C85.8019 228.343 82.5916 227.059 79.1979 226.142C74.6576 224.904 70.0714 223.803 65.6229 222.335C64.8432 222.06 64.0177 221.877 63.3298 221.372C62.6877 220.914 62.0915 220.409 61.5412 219.859C61.2202 219.538 60.8991 219.171 60.5781 218.804C60.5323 218.85 60.5322 218.896 60.4864 218.942C59.0646 220.363 57.1385 222.106 57.1385 224.262C57.0926 226.234 58.7436 227.839 60.257 228.894C62.917 230.774 66.0356 231.921 69.2 232.746C72.5938 233.663 76.0334 234.214 79.473 234.81C85.0681 235.727 91.1219 236.552 96.3959 239.075ZM136.754 234.535C135.929 234.259 136.295 232.975 137.121 233.205C137.304 233.25 137.442 233.296 137.625 233.342C137.625 233.342 137.625 233.342 137.625 233.296C140.744 229.031 142.808 224.124 143.817 218.942C145.926 207.889 143.175 196.286 138.772 186.151C137.58 183.399 136.02 180.785 135.011 177.941C134.14 175.465 133.727 172.851 133.773 170.283C133.819 165.375 135.424 160.698 136.571 155.974C137.717 151.158 138.222 146.297 136.479 141.573C135.974 140.197 135.287 138.867 134.507 137.629C134.048 136.895 133.59 136.208 133.085 135.565C132.581 134.878 131.801 134.235 131.434 133.456C131.113 132.814 130.838 131.988 130.7 131.163H130.792C130.93 128.824 133.498 127.356 134.874 125.705C135.378 125.109 135.929 124.284 135.562 123.412C135.057 122.22 133.36 121.807 132.26 121.532C128.453 120.569 124.555 119.743 120.748 118.826C119.281 118.505 117.813 118.184 116.3 118.138C115.016 118.092 113.64 118.184 112.493 118.872C111.163 119.651 110.796 121.027 110.796 122.449C110.796 124.008 111.072 125.476 110.842 127.035C110.705 127.907 109.375 127.54 109.512 126.668C109.833 124.834 109.237 122.954 109.512 121.073C109.696 119.743 110.43 118.643 111.484 117.909C109.879 115.111 108.687 112.084 107.311 109.149C106.44 107.269 105.431 105.48 104.192 103.783C102.817 101.949 101.074 100.573 99.2852 99.1972C99.1018 99.0597 98.8725 98.8762 98.689 98.6928C95.7539 100.573 93.736 101.995 90.8467 104.013C80.0234 111.442 69.8421 119.835 61.5412 129.97C57.2761 135.153 53.7447 140.748 50.2593 146.435C46.4986 152.58 42.8755 158.909 40.2614 165.651C37.8766 171.704 36.2715 178.171 36.776 184.729C37.3263 191.425 40.3532 197.937 45.0769 202.707C47.4158 205.092 50.3968 206.697 53.1485 208.577C54.4785 209.494 55.8085 210.549 56.955 211.741C58.0557 212.888 58.9729 214.218 59.936 215.548C60.8991 216.878 61.8622 218.208 63.1005 219.309C63.7426 219.905 64.4304 220.409 65.3018 220.684C65.9897 220.914 66.6777 221.143 67.3656 221.372C69.7504 222.152 72.1811 222.84 74.6117 223.436C78.1889 224.353 81.8578 225.133 85.2057 226.738C88.8288 228.435 91.8098 231.187 94.4239 234.214C96.4418 236.507 98.2304 238.983 100.019 241.46C101.808 243.982 104.33 246 107.173 247.284C109.833 248.476 112.814 248.889 115.704 248.476C117.171 248.247 118.593 247.834 119.969 247.284C121.482 246.596 122.812 245.587 124.234 244.808C128.912 242.193 133.314 238.8 136.754 234.535ZM214.856 211.65C214.489 209.448 213.572 207.385 213.022 205.229C212.517 203.303 212.196 201.331 212.013 199.359C211.646 195.644 211.784 191.883 211.6 188.123C211.187 179.409 209.353 170.65 206.785 162.349C204.492 155.011 201.373 147.902 197.475 141.252C193.76 135.015 189.357 129.191 184.404 123.917C179.543 118.734 174.131 114.056 168.399 109.837C162.62 105.572 156.52 101.811 150.237 98.3718C150.1 99.8393 149.412 101.261 148.357 102.362C149.183 103.6 149.779 105.022 149.412 106.535C149.183 107.544 148.541 108.415 147.715 109.057C148.403 109.654 148.816 110.571 148.724 111.58C148.586 113.093 147.577 114.24 146.431 115.157C145.147 116.212 143.679 117.129 142.533 118.367C140.652 120.385 139.231 122.816 137.029 124.467C136.938 125.155 136.617 125.797 136.204 126.393C135.47 127.356 134.553 128.136 133.681 128.961C133.039 129.603 132.214 130.383 132.168 131.346C132.26 131.897 132.397 132.539 132.672 132.997C133.039 133.593 133.635 134.144 134.094 134.74C137.534 139.234 139.414 144.554 138.955 150.287C138.589 155.148 136.892 159.78 135.883 164.504C134.782 169.686 134.92 174.915 137.259 179.776C138.589 182.574 140.01 185.325 141.157 188.214C142.257 191.012 143.221 193.855 144 196.745C146.752 207.247 146.981 218.712 142.166 228.664C141.294 230.453 140.285 232.15 139.139 233.755C140.79 234.168 142.533 234.397 144.23 234.443C148.77 234.672 153.264 233.938 157.805 233.755C160.143 233.663 162.528 233.755 164.821 234.076C167.023 234.397 169.178 234.856 171.38 235.131C178.947 236.094 186.697 236.369 194.264 235.131C200.135 234.168 206.326 232.242 210.591 227.931C214.81 223.574 215.819 217.52 214.856 211.65ZM172.664 190.462C173.764 191.471 173.26 192.892 172.388 193.855C171.38 195.002 169.774 195.965 168.215 195.69C166.977 195.506 166.197 194.497 165.922 193.351C165.876 193.168 165.83 192.938 165.739 192.755C164.913 193.443 164.225 194.268 163.445 195.048C162.437 196.057 161.336 196.882 160.006 197.478C157.438 198.671 154.502 199.129 151.659 198.717C150.788 198.579 151.155 197.249 152.026 197.387C152.76 197.479 153.448 197.524 154.181 197.478C157.208 197.341 160.189 196.286 162.391 194.131C163.308 193.213 164.179 192.25 165.188 191.425C163.629 188.031 160.556 185.509 158.676 182.298C157.575 180.418 156.979 178.354 157.392 176.153C157.621 174.823 158.309 173.676 158.905 172.53C159.501 171.475 160.51 169.916 160.006 168.632C159.501 167.302 157.575 167.072 156.429 166.522C155.145 165.926 153.998 165.054 152.989 164.091C151.017 162.119 149.641 159.505 149.228 156.753C149.091 155.882 150.421 155.515 150.558 156.387C150.971 159.368 152.53 162.073 154.869 163.954C156.016 164.871 157.3 165.467 158.676 165.972C160.143 166.522 161.382 167.439 161.473 169.136C161.611 171.75 159.272 173.814 158.722 176.29C158.263 178.217 158.905 180.097 159.914 181.794C161.794 184.912 164.73 187.389 166.335 190.691C167.298 190.186 168.353 189.911 169.407 189.774C170.508 189.636 171.838 189.728 172.664 190.462ZM169.912 191.104C168.903 191.15 167.802 191.425 166.885 191.929C166.931 192.021 166.931 192.067 166.977 192.159C167.252 192.984 167.344 194.176 168.399 194.36C169.178 194.497 170.004 194.039 170.6 193.58C171.104 193.213 171.655 192.709 171.838 192.113C172.205 191.104 170.508 191.058 169.912 191.104ZM129.875 42.0538C128.774 43.2921 127.949 44.7138 127.398 46.2731C127.261 46.5941 127.536 47.0527 127.857 47.0986C128.224 47.1903 128.591 46.961 128.682 46.64C128.728 46.4565 128.82 46.3189 128.866 46.1355C128.866 46.0896 128.912 46.0437 128.912 45.9979V45.952C128.958 45.8603 129.003 45.7686 129.003 45.7227C129.141 45.4017 129.324 45.0348 129.508 44.7138C129.691 44.3928 129.875 44.1176 130.104 43.7966C130.15 43.7048 130.196 43.659 130.242 43.5673C130.242 43.5673 130.242 43.5673 130.242 43.5214C130.242 43.5214 130.242 43.5214 130.242 43.4755C130.288 43.4296 130.288 43.3838 130.333 43.3838C130.471 43.2462 130.563 43.1086 130.7 42.9252C130.93 42.65 130.976 42.1914 130.7 41.9621C130.563 41.8245 130.104 41.7786 129.875 42.0538ZM122.491 46.5941C122.858 46.7776 123.225 46.64 123.454 46.3648C124.326 45.1265 125.197 43.8883 126.068 42.65C126.298 42.3749 126.114 41.8704 125.839 41.6869C125.472 41.5035 125.105 41.6411 124.876 41.9163C124.005 43.1545 123.133 44.3928 122.262 45.631C122.033 45.9521 122.17 46.4565 122.491 46.5941ZM123.225 38.4766C124.371 38.7518 125.564 39.0269 126.71 39.3021C127.077 39.3938 127.49 39.2104 127.536 38.8435C127.628 38.4766 127.444 38.0639 127.077 38.018C125.931 37.7429 124.738 37.4677 123.592 37.1925C123.225 37.1008 122.812 37.2842 122.766 37.6511C122.629 37.9721 122.858 38.3849 123.225 38.4766ZM129.37 37.4218C129.691 37.6052 130.15 37.5135 130.333 37.1925C130.655 36.5504 131.113 36.046 131.755 35.7249C132.306 35.4498 132.902 35.3122 133.498 35.5415C133.498 35.5415 133.635 35.5873 133.635 35.6332C133.681 35.6791 133.727 35.6791 133.773 35.7249C133.819 35.7249 133.819 35.7708 133.865 35.7708C133.865 35.7708 133.865 35.7708 133.911 35.8167C133.957 35.8625 134.048 35.9084 134.094 36.0001C134.094 36.0001 134.186 36.1377 134.232 36.1377C134.278 36.1835 134.278 36.2294 134.323 36.2753C134.369 36.367 134.415 36.4128 134.461 36.5046C134.461 36.5504 134.507 36.5963 134.507 36.5963C134.553 36.7339 134.599 36.8715 134.644 37.009C134.644 37.009 134.644 37.009 134.644 37.0549C134.644 37.1007 134.644 37.1008 134.644 37.1466C134.644 37.2384 134.644 37.2842 134.644 37.376C134.644 37.376 134.644 37.3759 134.644 37.4218C134.644 37.5135 134.599 37.5594 134.599 37.6511C134.599 37.6511 134.599 37.6511 134.599 37.697C134.599 37.7428 134.553 37.7429 134.553 37.7887C134.553 37.8346 134.415 38.018 134.507 37.8804C134.278 38.1556 134.232 38.5683 134.507 38.8435C134.736 39.0728 135.241 39.1187 135.47 38.8435C135.929 38.2932 136.112 37.6053 136.02 36.8715C135.929 36.1836 135.608 35.5415 135.149 35.0829C134.048 33.9363 132.351 33.9363 131.067 34.716C130.288 35.1746 129.646 35.8625 129.279 36.6422C128.912 36.8256 129.004 37.2384 129.37 37.4218ZM113.181 87.8695C113.411 88.1446 113.915 88.0988 114.144 87.8695C114.42 87.5943 114.374 87.1816 114.144 86.9064C113.227 85.7598 112.585 84.4299 112.218 83.054C111.943 82.0451 111.668 80.5775 112.31 79.6144C112.998 78.6054 114.282 79.431 114.786 80.2106C115.199 80.8068 115.337 81.5406 115.52 82.2285C115.704 82.9623 115.933 83.6961 116.254 84.4299C116.667 85.2095 117.813 84.5216 117.446 83.7419C116.804 82.4578 116.804 80.9903 116.116 79.752C115.52 78.6972 114.42 77.7341 113.135 77.7799C111.897 77.8258 111.026 78.7889 110.751 79.9354C110.43 81.1737 110.659 82.5037 111.026 83.7419C111.439 85.2095 112.172 86.6312 113.181 87.8695ZM107.861 127.631C99.2852 131.117 93.1398 139.372 91.6722 148.453C91.3053 150.7 91.2136 153.039 91.4429 155.332C91.5805 156.478 91.7639 157.625 92.0391 158.726C92.406 160.239 92.9105 161.752 93.1398 163.266C93.5525 165.88 92.8646 168.265 91.3512 170.374C89.9295 172.392 88.0033 174.089 86.123 175.694C82.133 179.226 77.1341 184.775 79.8858 190.508C80.2527 191.287 81.4451 190.599 81.0782 189.82C78.7851 185.004 83.3713 180.051 86.6733 177.07C90.1129 173.997 94.1487 170.65 94.6073 165.742C94.8825 162.853 93.6901 160.056 93.0939 157.258C92.6353 155.102 92.5436 152.901 92.727 150.7C93.0939 146.297 94.6074 141.986 97.038 138.317C99.7439 134.19 103.596 130.796 108.182 128.916C109.008 128.594 108.687 127.264 107.861 127.631ZM143.725 62.8749C143.771 62.8291 143.817 62.7832 143.863 62.6915C143.954 62.5998 144 62.5081 144.092 62.4163C144.275 62.2329 144.413 62.0494 144.596 61.9119C144.963 61.5908 145.238 61.3615 145.651 61.0863C146.477 60.5819 147.211 60.215 148.128 59.9856C148.495 59.8939 148.724 59.4812 148.586 59.1602C148.495 58.7933 148.082 58.6098 147.761 58.7016C145.651 59.206 143.817 60.4901 142.533 62.187C142.303 62.4622 142.487 62.9667 142.762 63.1501C143.129 63.3335 143.496 63.1501 143.725 62.8749Z"
                        fill="#181818" />
                </svg>
                <h4 class="text-2xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
                    Simpan Perubahan Data ?
                </h4>
                <p class="text-gray-600 dark:text-gray-300 max-w-md">
                    Pastikan data yang Anda ubah untuk
                    <span class="text-blue-600 font-semibold">{{
                        form.name || "—"
                        }}</span>
                    sudah benar.
                </p>
            </div>

            <!-- Footer -->
            <template #footer>
                <div class="flex gap-3 w-full">
                    <Button label="Batalkan" severity="secondary" class="flex-1 h-11 rounded-lg bg-gray-300 text-white"
                        @click="confirmVisible = false" />
                    <Button label="Ya, Simpan" class="flex-1 h-11 rounded-lg bg-blue-500 text-white"
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
import { watch, ref, computed, onMounted, onBeforeUnmount } from "vue";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import FormSelect from "@/Components/form/SelectPemakaian.vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import Swal from "sweetalert2";

defineOptions({ layout: AppLayout });

const props = defineProps({
    employee: { type: Object, default: () => ({}) },
    branches: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
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
    return props.departments
        .filter((dept) => dept.branch_id == form.branch_id)
        .map((dept) => ({ label: dept.name, value: dept.id }));
});

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
const showFilterDept = computed(() => departementOptions.value.length > 8);
const showFilterRole = computed(() => roleOptions.length > 8);
const showFilterPosition = computed(() => positionOptions.length > 8);
const showFilterShift = computed(() => shiftOptions.length > 8);


console.log("props.employee", props.employee)

const form = useForm({
    name: props.employee?.name || "",
    branch_id: props.employee?.branch_id || null,
    username: props.employee?.user?.username || "",
    email: props.employee?.user?.email || "",
    password: "",
    department_id: props.employee?.department_id || null,
    jabatan_id: props.employee?.user?.roles?.[0]?.id || null,
    position_id: props.employee?.position_id || null,
    working_start_date: props.employee?.working_start_date || null,
    birthdate: props.employee?.birthdate || props.employee?.birthdate || null,
    shift_id: props.employee?.shift_id || null,
    salary: props.employee?.salary || null,
    tunjangan_1: props.employee?.tunjangan_1 || 0,
    tunjangan_2: props.employee?.tunjangan_2 || 0,
    // quotas (auto-filled by posisi/jabatan, editable)
    leave_quota_per_year: props.employee?.leave_quota_per_year ?? 0,
    loan_quota: props.employee?.loan_quota ?? 0,
    photo: null,
    contact: props.employee?.contact || "",
    address: props.employee?.address || "",
    provinsi: props.employee?.provinsi || "",
    kota: props.employee?.kota || "",
    kecamatan: props.employee?.kecamatan || "",
    kelurahan: props.employee?.kelurahan || "",
    gender: props.employee?.gender || null,
    status: props.employee?.status || null,
    birthplace: props.employee?.birthplace || "",
    religion: props.employee?.religion || "",
    nik: props.employee?.nik || "",
    ktp: props.employee?.ktp || "",
    bpjs_kesehatan: props.employee?.bpjs_kesehatan || "",
    bpjs_ketenagakerjaan: props.employee?.bpjs_ketenagakerjaan || "",
    // document URLs
    ktp_url: props.employee?.ktp_url || "",
    bpjs_kesehatan_url: props.employee?.bpjs_kesehatan_url || "",
    bpjs_ketenagakerjaan_url: props.employee?.bpjs_ketenagakerjaan_url || "",
    // document files
    ktp_file: null,
    bpjs_kesehatan_file: null,
    bpjs_ketenagakerjaan_file: null,
    certificate: props.employee?.certificate || "",
    contract: props.employee?.contract || "",
    signature: null,
    certificate_file: null,
    contract_file: null,
    kk_file: null,
    resign_date: props.employee?.resign_date || null,
});

console.log(form.resign_date);

// Preview URLs for images
const previewUrls = ref({
    photo: null,
    ktp: null,
    bpjs_kesehatan: null,
    bpjs_ketenagakerjaan: null,
    certificate: null,
    contract: null,
    kk: null,
    signature: null,
});

// File info for documents (name, type, url)
const fileInfo = ref({
    ktp: null,
    bpjs_kesehatan: null,
    bpjs_ketenagakerjaan: null,
    certificate: null,
    contract: null,
    kk: null,
});

// Helper function to get file URL (for existing files from server)
const getFileUrl = (filePath) => {
    if (!filePath) return null;
    // If it's already a full URL, return it
    if (filePath.startsWith("http://") || filePath.startsWith("https://")) {
        return filePath;
    }
    // Remove leading slash if exists (to normalize)
    const normalizedPath = filePath.replace(/^\/+/, "");

    // If it starts with 'storage/', use it directly with leading slash
    if (normalizedPath.startsWith("storage/")) {
        return `/${normalizedPath}`;
    }

    // If it's a path like 'uploads/...', prepend with '/storage/'
    if (normalizedPath.startsWith("uploads/")) {
        return `/storage/${normalizedPath}`;
    }

    // If it starts with /, assume it's already a full path
    if (filePath.startsWith("/")) {
        return filePath;
    }

    // Default: prepend with /storage/
    return `/storage/${normalizedPath}`;
};

// Initialize preview URLs from existing documents
const initializePreviews = () => {
    // Photo
    if (props.employee?.path) {
        const photoUrl = getFileUrl(props.employee.path);
        if (photoUrl) {
            previewUrls.value.photo = photoUrl;
        }
    }

    // KTP
    const ktpDoc = props.employee?.documents?.find(
        (doc) => doc.title === "KTP",
    );
    if (ktpDoc?.file_path) {
        const fileUrl = getFileUrl(ktpDoc.file_path);
        // Check if it's an image file
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.ktp = fileUrl;
        } else if (fileUrl) {
            // It's a document file
            const fileName = ktpDoc.file_path.split("/").pop() || "KTP";
            fileInfo.value.ktp = {
                name: fileName,
                type: fileUrl.match(/\.pdf$/i) ? "application/pdf" : "document",
                url: fileUrl,
                isPdf: fileUrl.match(/\.pdf$/i) ? true : false,
                isDoc: fileUrl.match(/\.(doc|docx)$/i) ? true : false,
            };
        }
    }

    // BPJS Kesehatan
    const bpjsKesehatanDoc = props.employee?.documents?.find(
        (doc) => doc.title === "BPJS Kesehatan",
    );
    if (bpjsKesehatanDoc?.file_path) {
        const fileUrl = getFileUrl(bpjsKesehatanDoc.file_path);
        // Check if it's an image file
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.bpjs_kesehatan = fileUrl;
        } else if (fileUrl) {
            // It's a document file
            const fileName =
                bpjsKesehatanDoc.file_path.split("/").pop() || "BPJS Kesehatan";
            fileInfo.value.bpjs_kesehatan = {
                name: fileName,
                type: fileUrl.match(/\.pdf$/i) ? "application/pdf" : "document",
                url: fileUrl,
                isPdf: fileUrl.match(/\.pdf$/i) ? true : false,
                isDoc: fileUrl.match(/\.(doc|docx)$/i) ? true : false,
            };
        }
    }

    // BPJS Ketenagakerjaan
    const bpjsKetenagakerjaanDoc = props.employee?.documents?.find(
        (doc) => doc.title === "BPJS Ketenagakerjaan",
    );
    if (bpjsKetenagakerjaanDoc?.file_path) {
        const fileUrl = getFileUrl(bpjsKetenagakerjaanDoc.file_path);
        // Check if it's an image file
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.bpjs_ketenagakerjaan = fileUrl;
        } else if (fileUrl) {
            // It's a document file
            const fileName =
                bpjsKetenagakerjaanDoc.file_path.split("/").pop() ||
                "BPJS Ketenagakerjaan";
            fileInfo.value.bpjs_ketenagakerjaan = {
                name: fileName,
                type: fileUrl.match(/\.pdf$/i) ? "application/pdf" : "document",
                url: fileUrl,
                isPdf: fileUrl.match(/\.pdf$/i) ? true : false,
                isDoc: fileUrl.match(/\.(doc|docx)$/i) ? true : false,
            };
        }
    }

    // Signature
    const signatureDoc = props.employee?.documents?.find(
        (doc) => doc.title === "Tanda Tangan",
    );
    if (signatureDoc?.file_path) {
        const fileUrl = getFileUrl(signatureDoc.file_path);
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.signature = fileUrl;
        }
    }

    // Certificate - check if there's a document for certificate
    const certificateDoc = props.employee?.documents?.find(
        (doc) => doc.title === "Sertifikat" || doc.title === "Certificate",
    );
    if (certificateDoc?.file_path) {
        const fileUrl = getFileUrl(certificateDoc.file_path);
        // Check if it's an image file
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.certificate = fileUrl;
        } else if (fileUrl) {
            // It's a document file
            const fileName =
                certificateDoc.file_path.split("/").pop() || "Sertifikat";
            fileInfo.value.certificate = {
                name: fileName,
                type: fileUrl.match(/\.pdf$/i) ? "application/pdf" : "document",
                url: fileUrl,
                isPdf: fileUrl.match(/\.pdf$/i) ? true : false,
                isDoc: fileUrl.match(/\.(doc|docx)$/i) ? true : false,
            };
        }
    }

    // Contract - check if there's a document for contract
    const contractDoc = props.employee?.documents?.find(
        (doc) => doc.title === "Kontrak" || doc.title === "Contract",
    );
    if (contractDoc?.file_path) {
        const fileUrl = getFileUrl(contractDoc.file_path);
        // Check if it's an image file
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.contract = fileUrl;
        } else if (fileUrl) {
            // It's a document file
            const fileName =
                contractDoc.file_path.split("/").pop() || "Kontrak";
            fileInfo.value.contract = {
                name: fileName,
                type: fileUrl.match(/\.pdf$/i) ? "application/pdf" : "document",
                url: fileUrl,
                isPdf: fileUrl.match(/\.pdf$/i) ? true : false,
                isDoc: fileUrl.match(/\.(doc|docx)$/i) ? true : false,
            };
        }
    }

    // Kartu Keluarga - check if there's a document for KK
    const kkDoc = props.employee?.documents?.find(
        (doc) =>
            doc.title === "Kartu Keluarga" ||
            doc.title === "KK" ||
            doc.title === "Family Card",
    );
    if (kkDoc?.file_path) {
        const fileUrl = getFileUrl(kkDoc.file_path);
        // Check if it's an image file
        if (fileUrl && fileUrl.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewUrls.value.kk = fileUrl;
        } else if (fileUrl) {
            // It's a document file
            const fileName =
                kkDoc.file_path.split("/").pop() || "Kartu Keluarga";
            fileInfo.value.kk = {
                name: fileName,
                type: fileUrl.match(/\.pdf$/i) ? "application/pdf" : "document",
                url: fileUrl,
                isPdf: fileUrl.match(/\.pdf$/i) ? true : false,
                isDoc: fileUrl.match(/\.(doc|docx)$/i) ? true : false,
            };
        }
    }
};

// Handle file change and create preview
const handleFileChange = (event, type) => {
    const file = event.target.files[0];
    if (!file) {
        // If no file selected, keep existing preview if available
        return;
    }

    // Revoke old object URL if exists
    const oldUrl = previewUrls.value[type];
    if (oldUrl && oldUrl.startsWith("blob:")) {
        URL.revokeObjectURL(oldUrl);
    }

    // Check if file is an image
    const isImage = file.type.startsWith("image/");

    if (isImage) {
        // Create object URL for preview
        const objectUrl = URL.createObjectURL(file);
        previewUrls.value[type] = objectUrl;
        // Clear file info for image types
        if (fileInfo.value[type]) {
            fileInfo.value[type] = null;
        }
    } else {
        // For non-image files (PDF, DOC, etc.)
        // Clear image preview
        previewUrls.value[type] = null;

        // Store file info for document preview
        const fileUrl = URL.createObjectURL(file);
        fileInfo.value[type] = {
            name: file.name,
            type: file.type,
            url: fileUrl,
            isPdf: file.type === "application/pdf",
            isDoc:
                file.type === "application/msword" ||
                file.type ===
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        };
    }

    // Update form with the file
    switch (type) {
        case "photo":
            form.photo = file;
            form.errors.photo = null;
            break;
        case "ktp":
            form.ktp_file = file;
            form.errors.ktp_file = null;
            break;
        case "bpjs_kesehatan":
            form.bpjs_kesehatan_file = file;
            form.errors.bpjs_kesehatan_file = null;
            break;
        case "bpjs_ketenagakerjaan":
            form.bpjs_ketenagakerjaan_file = file;
            form.errors.bpjs_ketenagakerjaan_file = null;
            break;
        case "certificate":
            form.certificate_file = file;
            form.errors.certificate_file = null;
            break;
        case "contract":
            form.contract_file = file;
            form.errors.contract_file = null;
            break;
        case "kk":
            form.kk_file = file;
            form.errors.kk_file = null;
            break;
        case "signature":
            form.signature = file;
            form.errors.signature = null;
            break;
    }
};

// File input refs
const photoInput = ref(null);
const ktpInput = ref(null);
const signatureInput = ref(null);
const bpjsKesehatanInput = ref(null);
const bpjsKetenagakerjaanInput = ref(null);
const certificateInput = ref(null);
const contractInput = ref(null);
const kkInput = ref(null);

// Password visibility toggle
const showPassword = ref(false);

// Remove preview function
const removePreview = (type) => {
    // Revoke object URL if it's a blob URL
    const oldUrl = previewUrls.value[type];
    if (oldUrl && oldUrl.startsWith("blob:")) {
        URL.revokeObjectURL(oldUrl);
    }

    // Revoke fileInfo URL if exists
    if (
        fileInfo.value[type] &&
        fileInfo.value[type].url &&
        fileInfo.value[type].url.startsWith("blob:")
    ) {
        URL.revokeObjectURL(fileInfo.value[type].url);
    }

    // Clear preview
    previewUrls.value[type] = null;
    fileInfo.value[type] = null;

    // Clear form file and reset file input
    switch (type) {
        case "photo":
            form.photo = null;
            if (photoInput.value) {
                photoInput.value.value = "";
            }
            break;
        case "ktp":
            form.ktp_file = null;
            if (ktpInput.value) {
                ktpInput.value.value = "";
            }
            break;
        case "bpjs_kesehatan":
            form.bpjs_kesehatan_file = null;
            if (bpjsKesehatanInput.value) {
                bpjsKesehatanInput.value.value = "";
            }
            break;
        case "bpjs_ketenagakerjaan":
            form.bpjs_ketenagakerjaan_file = null;
            if (bpjsKetenagakerjaanInput.value) {
                bpjsKetenagakerjaanInput.value.value = "";
            }
            break;
        case "certificate":
            form.certificate_file = null;
            if (certificateInput.value) {
                certificateInput.value.value = "";
            }
            break;
        case "contract":
            form.contract_file = null;
            if (contractInput.value) {
                contractInput.value.value = "";
            }
            break;
        case "kk":
            form.kk_file = null;
            if (kkInput.value) {
                kkInput.value.value = "";
            }
            break;
        case "signature":
            form.signature = null;
            if (signatureInput.value) {
                signatureInput.value.value = "";
            }
            break;
    }

    // Clear errors
    if (form.errors[type] || form.errors[`${type}_file`]) {
        form.errors[type] = null;
        form.errors[`${type}_file`] = null;
    }
};

// Cleanup object URLs on unmount
onBeforeUnmount(() => {
    Object.values(previewUrls.value).forEach((url) => {
        if (url && url.startsWith("blob:")) {
            URL.revokeObjectURL(url);
        }
    });
    Object.values(fileInfo.value).forEach((info) => {
        if (info && info.url && info.url.startsWith("blob:")) {
            URL.revokeObjectURL(info.url);
        }
    });
});

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

// Load initial wilayah data based on existing employee data
const loadInitialWilayah = async () => {
    // Always load provinsi first
    await loadProvinsi();

    // If employee has provinsi, load the rest of the hierarchy
    if (props.employee?.provinsi) {
        // Wait a bit for provinsi to load
        await new Promise((resolve) => setTimeout(resolve, 100));

        const selectedProvinsi = provinsiOptions.value.find(
            (p) => p.name === props.employee.provinsi,
        );
        if (selectedProvinsi) {
            try {
                const response = await fetch(
                    `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProvinsi.id}.json`,
                );
                const data = await response.json();
                kotaOptions.value = data.map((k) => ({
                    id: k.id,
                    name: k.name,
                }));

                // If employee has kota, load kecamatan
                if (props.employee.kota) {
                    await new Promise((resolve) => setTimeout(resolve, 100));
                    const selectedKota = kotaOptions.value.find(
                        (k) => k.name === props.employee.kota,
                    );
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

                            // If employee has kecamatan, load kelurahan
                            if (props.employee.kecamatan) {
                                await new Promise((resolve) =>
                                    setTimeout(resolve, 100),
                                );
                                const selectedKecamatan =
                                    kecamatanOptions.value.find(
                                        (kec) =>
                                            kec.name ===
                                            props.employee.kecamatan,
                                    );
                                if (selectedKecamatan) {
                                    try {
                                        const response = await fetch(
                                            `https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedKecamatan.id}.json`,
                                        );
                                        const data = await response.json();
                                        kelurahanOptions.value = data.map(
                                            (kel) => ({
                                                id: kel.id,
                                                name: kel.name,
                                            }),
                                        );
                                    } catch (error) {
                                        console.error(
                                            "Error loading kelurahan:",
                                            error,
                                        );
                                    }
                                }
                            }
                        } catch (error) {
                            console.error("Error loading kecamatan:", error);
                        }
                    }
                }
            } catch (error) {
                console.error("Error loading kota:", error);
            }
        }
    }
};

// Initialize previews on mount
onMounted(() => {
    initializePreviews();
    loadInitialWilayah();
});

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

function onJabatanChanged() {
    form.errors.jabatan_id = null;
    const selected = props.roles.find((r) => r.id === form.jabatan_id);
    if (selected) {
        // Auto-fill quotas from role
        form.leave_quota_per_year = Number(selected.leave_quota_per_year ?? 0);
        form.loan_quota = Number(selected.loan_quota ?? 0);
    }
}

const creatingPosition = ref(false);

async function handlePositionChange(value) {
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

const openConfirm = () => {
    if (validateRequiredFields()) {
        confirmVisible.value = true;
    }
};

function validateRequiredFields() {
    const errs = {};

    // Basic fields
    if (!form.name) errs.name = ["Nama wajib diisi."];
    if (!form.username) errs.username = ["Username wajib diisi."];
    if (!form.email) errs.email = ["Email wajib diisi."];
    if (!form.branch_id) errs.branch_id = ["Cabang wajib dipilih."];
    if (!form.department_id) errs.department_id = ["Departemen wajib dipilih."];
    if (!form.position_id) errs.position_id = ["Jabatan wajib dipilih."];
    if (!form.shift_id) errs.shift_id = ["Shift kerja wajib dipilih."];
    // Tanggal mulai bekerja tidak perlu validasi di Edit (field readonly)
    if (!form.birthdate) errs.birthdate = ["Tanggal lahir wajib diisi."];

    // Salary validation
    if (form.salary !== null && form.salary !== undefined && form.salary <= 0) {
        errs.salary = ["Gaji pokok jika diisi harus > 0."];
    }

    // Quotas validation (only leave quota is required)
    if (
        form.leave_quota_per_year === null ||
        form.leave_quota_per_year === undefined ||
        form.leave_quota_per_year < 0
    ) {
        errs.leave_quota_per_year = [
            "Jatah cuti per tahun wajib diisi (minimal 0).",
        ];
    }
    // Jatah lembur optional - tidak perlu validasi required
    // Jatah piutang optional - hanya validasi jika diisi tidak boleh negatif
    if (
        form.loan_quota !== null &&
        form.loan_quota !== undefined &&
        form.loan_quota < 0
    ) {
        errs.loan_quota = ["Jatah piutang jika diisi tidak boleh negatif."];
    }

    // Gender & status required
    if (!form.gender) errs.gender = ["Jenis kelamin wajib dipilih."];
    if (!form.status) errs.status = ["Status kerja wajib dipilih."];

    // Set errors and show SweetAlert if there are errors
    if (Object.keys(errs).length > 0) {
        form.errors = errs;

        // Collect error messages for SweetAlert
        const errorMessages = Object.values(errs).flat();
        const errorList = errorMessages.map((msg) => `• ${msg}`).join("<br>");

        Swal.fire({
            icon: "error",
            title: "Validasi Gagal",
            html: `<div style="text-align: left;">${errorList}</div>`,
            confirmButtonText: "OK",
            confirmButtonColor: "#ef4444",
        });

        return false;
    }

    return true;
}

const dialogPt = {
    root: { class: "rounded-2xl" },
    header: { class: "px-6 pt-6 pb-2" },
    content: { class: "px-6 pb-2" },
    footer: { class: "px-6 pb-6" },
};

const confirmSave = () => {
    confirmVisible.value = false;
    form.transform((data) => ({
        ...data,
        _method: "PUT",
    })).post(route("employees.update", props.employee.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "Data karyawan berhasil diperbarui.",
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
                timer: 2000,
                timerProgressBar: true,
            }).then(() => {
                router.visit(route("employees.index"));
            });
        },
        onError: (errors) => {
            console.log("Validation errors received:", errors);
            console.log("Form errors:", form.errors);

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
                    resign_date: "Tanggal Resign",
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

            console.log("Processed error messages:", errorMessages);

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

// Ensure pressing Enter (form submit) also opens the confirmation dialog
const storeEmployee = () => {
    openConfirm();
};
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
