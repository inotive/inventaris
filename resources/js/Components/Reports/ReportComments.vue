<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                History Komentar
            </h3>
            <span class="text-sm text-gray-500">
                {{ comments.length }} komentar
            </span>
        </div>

        <!-- Comments List (Read Only) -->
        <div class="space-y-4 max-h-96 overflow-y-auto">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4"
            >
                <!-- Comment Header -->
                <div class="flex items-center space-x-2 mb-2">
                    <div
                        class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold"
                    >
                        {{ getInitials(comment.user.name) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ comment.user.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ formatDate(comment.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Comment Content -->
                <div>
                    <p
                        class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap"
                    >
                        {{ comment.content }}
                    </p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="comments.length === 0" class="text-center py-8">
                <div class="text-gray-400 mb-2">
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
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        ></path>
                    </svg>
                </div>
                <p class="text-gray-500">Belum ada komentar</p>
                <p class="text-sm text-gray-400">
                    Komentar dapat ditambahkan melalui mobile app
                </p>
            </div>
        </div>

        <!-- Info Footer -->
        <div class="text-center pt-2 border-t">
            <p class="text-xs text-gray-400">
                💡 Untuk menambah, edit, atau hapus komentar, gunakan mobile app
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    reportId: {
        type: Number,
        required: true,
    },
});

const comments = ref([]);

// Fetch comments (read only)
const fetchComments = async () => {
    try {
        const response = await axios.get(
            `/api/v1/reports/${props.reportId}/comments`,
        );
        if (response.data.success) {
            comments.value = response.data.data.comments;
        }
    } catch (error) {
        console.error("Error fetching comments:", error);
    }
};

// Get user initials
const getInitials = (name) => {
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
};

// Format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);

    if (diffInHours < 1) {
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        return `${diffInMinutes} menit yang lalu`;
    } else if (diffInHours < 24) {
        return `${Math.floor(diffInHours)} jam yang lalu`;
    } else {
        return date.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }
};

// Load comments on mount
onMounted(() => {
    fetchComments();
});
</script>
