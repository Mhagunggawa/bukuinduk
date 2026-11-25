<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans antialiased text-[#0F3A5F]">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Header & Maskot Logo Utuh -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-3">
                    <img
                        src="/images/logo-full.webp"
                        alt="Maskot SI KARANJI"
                        class="w-20 h-20 object-contain mx-auto"
                        loading="eager"
                    />
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0F3A5F] tracking-tight">
                    SI KARANJI
                </h1>
                <p class="text-xs text-[#64748B] mt-1 font-medium">
                    Sistem Informasi Buku Induk Siswa SMK
                </p>
            </div>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Card Putih Bersih di Tengah -->
            <div class="bg-white py-8 px-6 sm:px-8 rounded-[12px] border border-[#E2E8F0] shadow-[0_1px_3px_0_rgba(15,58,95,0.04)]">
                <div v-if="title || description" class="mb-6 text-center">
                    <h2 class="text-sm font-bold text-[#0F3A5F]">
                        {{ title || 'Masuk Akun' }}
                    </h2>
                    <p v-if="description" class="text-xs text-[#64748B] mt-1">
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>

            <!-- Footer Hak Cipta -->
            <div class="text-center mt-6 text-xs text-[#64748B]">
                &copy; {{ new Date().getFullYear() }} SI KARANJI. Sistem Buku Induk Siswa SMK.
            </div>
        </div>
    </div>
</template>
