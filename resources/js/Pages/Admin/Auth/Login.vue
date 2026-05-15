<template>
    <FullScreenLayout>
        <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
            <div
                class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900"
            >
                <div class="flex flex-col flex-1 w-full lg:w-1/2">
                    <div
                        class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto"
                    >
                        <div>
                            <div class="mb-5 text-center sm:mb-8">
                                <div
                                    class="text-xl font-bold text-gray-800 dark:text-white/90"
                                >
                                    Masuk
                                </div>
                                <p
                                    class="font-semibold text-gray-500 dark:text-gray-400"
                                >
                                    Backoffice Sistem Penyimpanan
                                </p>
                            </div>
                            <div>
                                <form @submit.prevent="form.post('/login')">
                                    <div class="space-y-5">
                                        <!-- Username / Email -->
                                        <div>
                                            <label
                                                for="username"
                                                class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400"
                                            >
                                                Username / Email<span
                                                    class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="text"
                                                id="username"
                                                v-model="form.user"
                                                placeholder="Masukkan username / email Anda"
                                                class="px-4 py-2.5 w-full h-11 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-300 dark:bg-dark-900 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-indigo-800"
                                            />
                                            <p
                                                v-if="form.errors.user"
                                                class="mt-1 text-xs text-red-500"
                                            >
                                                {{
                                                    Array.isArray(
                                                        form.errors.user
                                                    )
                                                        ? form.errors.user[0]
                                                        : form.errors.user
                                                }}
                                            </p>
                                        </div>
                                        <!-- Password -->
                                        <div>
                                            <div
                                                class="flex justify-between items-center mb-1.5"
                                            >
                                                <label
                                                    for="password"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                                                >
                                                    Kata Sandi<span
                                                        class="text-red-500"
                                                        >*</span
                                                    >
                                                </label>
                                                <a
                                                    href="/forgot-password"
                                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                                                    >Lupa Kata Sandi ?</a
                                                >
                                            </div>
                                            <div class="relative">
                                                <input
                                                    v-model="form.password"
                                                    :type="
                                                        showPassword
                                                            ? 'text'
                                                            : 'password'
                                                    "
                                                    id="password"
                                                    placeholder="Masukkan kata sandi"
                                                    class="py-2.5 pr-11 pl-4 w-full h-11 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-300 dark:bg-dark-900 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-indigo-800"
                                                />
                                                <span
                                                    @click="
                                                        togglePasswordVisibility
                                                    "
                                                    class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-5 top-1/2 dark:text-gray-400"
                                                >
                                                    <svg
                                                        v-if="!showPassword"
                                                        class="fill-current"
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 20 20"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            clip-rule="evenodd"
                                                            d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z"
                                                            fill="#98A2B3"
                                                        />
                                                    </svg>
                                                    <svg
                                                        v-else
                                                        class="fill-current"
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 20 20"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            clip-rule="evenodd"
                                                            d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z"
                                                            fill="#98A2B3"
                                                        />
                                                    </svg>
                                                </span>
                                            </div>
                                            <p
                                                v-if="form.errors.password"
                                                class="mt-1 text-xs text-red-500"
                                            >
                                                {{
                                                    Array.isArray(
                                                        form.errors.password
                                                    )
                                                        ? form.errors
                                                              .password[0]
                                                        : form.errors.password
                                                }}
                                            </p>
                                        </div>

                                        <!-- Remember me -->
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <label
                                                class="inline-flex items-center gap-2 select-none"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="form.remember"
                                                    class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                />
                                                <span
                                                    class="text-sm text-gray-600"
                                                    >Ingat Saya</span
                                                >
                                            </label>
                                        </div>
                                        <!-- Button -->
                                        <div>
                                            <button
                                                type="submit"
                                                class="flex items-center justify-center w-full gap-2 px-4 py-3 text-sm font-medium text-white transition bg-indigo-500 rounded-lg shadow-theme-xs hover:bg-indigo-600"
                                            >
                                                Masuk Sekarang
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                        <div></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="relative items-center hidden w-full h-full bg-indigo-700 lg:w-1/2 dark:bg-white/5 lg:grid"
                >
                    <div
                        class="flex items-center justify-center px-8 text-white z-1"
                    >
                        <common-grid-shape />
                        <!-- Carousel Container -->
                        <div class="relative w-full max-w-2xl">
                            <!-- Carousel Items -->
                            <div class="relative overflow-hidden">
                                <div
                                    class="flex transition-transform duration-500 ease-in-out"
                                    :style="{
                                        transform: `translateX(-${
                                            currentSlide * 100
                                        }%)`,
                                    }"
                                >
                                    <div
                                        v-for="(slide, index) in slides"
                                        :key="index"
                                        class="flex flex-col justify-center items-center flex-shrink-0 space-y-6 w-full min-h-[500px] text-center"
                                    >
                                        <!-- Logo/Image -->
                                        <div
                                            class="flex items-center justify-center"
                                        >
                                            <img
                                                v-if="slide.img_url"
                                                :src="slide.img_url"
                                                alt="Logo"
                                                class="object-contain opacity-95 drop-shadow-2xl w-250 h-250"
                                            />
                                            <img
                                                v-else
                                                src="public/images/logo/bg-login.png"
                                                alt="Logo"
                                                class="object-contain opacity-95 drop-shadow-2xl w-250 h-250"
                                            />
                                        </div>

                                        <!-- Title -->
                                        <h3
                                            class="px-4 text-3xl font-bold leading-tight"
                                        >
                                            {{ slide.title }}
                                        </h3>

                                        <!-- Description -->
                                        <p
                                            class="px-8 text-lg leading-relaxed text-white/90"
                                        >
                                            {{ slide.description }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Navigation Dots -->
                                <div
                                    class="absolute bottom-0 flex justify-center gap-2 -translate-x-1/2 left-1/2 mt-36"
                                >
                                    <button
                                        v-for="(slide, index) in slides"
                                        :key="index"
                                        @click="goToSlide(index)"
                                        :class="[
                                            'w-2.5 h-2.5 rounded-full transition-all duration-300',
                                            currentSlide === index
                                                ? 'bg-white w-8'
                                                : 'bg-white/40 hover:bg-white/60',
                                        ]"
                                        :aria-label="`Go to slide ${index + 1}`"
                                    ></button>
                                </div>

                                <!-- Navigation Arrows -->
                                <button
                                    @click="prevSlide"
                                    class="absolute flex items-center justify-center w-10 h-10 text-white transition-all -translate-y-1/2 rounded-full left-4 top-1/2 backdrop-blur-sm bg-white/10 hover:bg-white/20"
                                    aria-label="Previous slide"
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
                                            d="M15 19l-7-7 7-7"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="nextSlide"
                                    class="absolute flex items-center justify-center w-10 h-10 text-white transition-all -translate-y-1/2 rounded-full right-4 top-1/2 backdrop-blur-sm bg-white/10 hover:bg-white/20"
                                    aria-label="Next slide"
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
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FullScreenLayout>
</template>

<script setup>
import FullScreenLayout from "@/Components/layout/FullScreenLayout.vue";
import CommonGridShape from "@/Components/common/CommonGridShape.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    googleLoginUrl: String,
    workPrinciples: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    user: "",
    password: "",
    remember: false,
});
const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

// Carousel state
const currentSlide = ref(0);
const slides = ref(
    props.workPrinciples.length > 0
        ? props.workPrinciples
        : [
              {
                  category: "Manajemen Inventaris",
                  title: "Kelola Stok",
                  description:
                      "Pantau dan kelola stok barang secara real-time. Sistem penyimpanan modern memastikan setiap barang tercatat dengan akurat dan mudah ditelusuri.",
                  img_url: "public/images/logo/carousel-inventory-1.jpg",
              },
              {
                  category: "Manajemen Inventaris",
                  title: "Efisiensi Gudang",
                  description:
                      "Optimalkan tata letak dan pengelolaan gudang untuk meningkatkan efisiensi operasional. Atur alur barang masuk dan keluar dengan sistematis.",
                  img_url: "public/images/logo/carousel-inventory-2.jpg",
              },
              {
                  category: "Manajemen Inventaris",
                  title: "Tercatat Rapi",
                  description:
                      "Setiap transaksi barang masuk dan keluar tercatat secara digital. Data inventaris selalu akurat dan siap untuk pelaporan kapan saja.",
                  img_url: "public/images/logo/carousel-inventory-3.jpg",
              },
              {
                  category: "Manajemen Inventaris",
                  title: "Laporan Cepat",
                  description:
                      "Hasilkan laporan inventaris secara otomatis dan cepat. Dapatkan wawasan mendalam tentang kondisi stok untuk pengambilan keputusan yang tepat.",
                  img_url: "public/images/logo/carousel-inventory-4.jpg",
              },
          ]
);

// Auto-play carousel
let carouselInterval = null;

const startCarousel = () => {
    carouselInterval = setInterval(() => {
        nextSlide();
    }, 5000); // Change slide every 5 seconds
};

const stopCarousel = () => {
    if (carouselInterval) {
        clearInterval(carouselInterval);
    }
};

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.value.length;
};

const prevSlide = () => {
    currentSlide.value =
        currentSlide.value === 0
            ? slides.value.length - 1
            : currentSlide.value - 1;
};

const goToSlide = (index) => {
    currentSlide.value = index;
    stopCarousel();
    startCarousel();
};

// Start carousel on mount
import { onMounted, onUnmounted } from "vue";

onMounted(() => {
    if (slides.value.length > 1) {
        startCarousel();
    }
});

onUnmounted(() => {
    stopCarousel();
});
</script>
