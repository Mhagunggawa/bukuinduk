<script setup lang="ts">
import { Form, Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, Save } from '@lucide/vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseBadge from '@/components/base/BaseBadge.vue';
import InputError from '@/components/InputError.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profil Pengguna',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const userInitials = computed(() => {
    const name = user.value?.name || 'User';
    return name
        .split(' ')
        .map((n: string) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});
</script>

<template>
    <Head title="Profil Pengguna" />

    <div class="max-w-3xl mx-auto space-y-6 pb-12">
        <!-- Bar Navigasi Atas: Tombol Kembali & Info Halaman -->
        <div class="flex items-center justify-between gap-4 pb-1">
            <Link
                href="/dashboard"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[8px] text-xs font-semibold text-[#0F3A5F] bg-white border border-[#E2E8F0] hover:bg-[#EAF4FC] hover:text-[#2B7CC2] hover:border-[#BFE3FA] transition-all shadow-2xs group cursor-pointer select-none"
                title="Kembali ke Dashboard"
            >
                <ArrowLeft class="w-4 h-4 text-[#64748B] group-hover:text-[#2B7CC2] group-hover:-translate-x-0.5 transition-transform shrink-0" />
                <span>Kembali ke Dashboard</span>
            </Link>
        </div>

        <!-- Kartu 1: Informasi Profil Pengguna -->
        <BaseCard
            title="Informasi Profil"
            subtitle="Perbarui data nama lengkap dan alamat email akun Anda."
        >
            <!-- Banner Ringkasan Identitas Akun -->
            <div class="flex items-center gap-3.5 p-3.5 rounded-[10px] bg-[#F8FAFC] border border-[#E2E8F0] mb-5">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2B7CC2] to-[#0F3A5F] text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-xs ring-2 ring-white select-none">
                    {{ userInitials }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-[#0F3A5F] uppercase truncate">
                            {{ user?.name }}
                        </h4>
                        <BaseBadge :variant="user?.role === 'admin' ? 'primary' : 'neutral'" size="sm">
                            {{ user?.role === 'admin' ? 'Administrator' : 'Tata Usaha' }}
                        </BaseBadge>
                    </div>
                    <p class="text-xs text-[#64748B] truncate mt-0.5">
                        {{ user?.email }}
                    </p>
                </div>
            </div>

            <!-- Form Edit Profil -->
            <Form
                v-bind="ProfileController.update.form()"
                class="space-y-4"
                v-slot="{ errors, processing }"
            >
                <!-- Nama Lengkap -->
                <div class="space-y-1.5 text-left">
                    <label for="name" class="block text-xs font-semibold text-[#0F3A5F]">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        :defaultValue="user?.name"
                        required
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap"
                        class="w-full h-9.5 px-3 py-2 rounded-[8px] border border-[#E2E8F0] bg-white text-xs text-[#0F3A5F] focus:outline-none focus:border-[#2B7CC2] focus:ring-3 focus:ring-[#2B7CC2]/15 transition-all shadow-2xs"
                    />
                    <InputError :message="errors.name" />
                </div>

                <!-- Alamat Email -->
                <div class="space-y-1.5 text-left">
                    <label for="email" class="block text-xs font-semibold text-[#0F3A5F]">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        :defaultValue="user?.email"
                        required
                        autocomplete="username"
                        placeholder="nama@smk.sch.id"
                        class="w-full h-9.5 px-3 py-2 rounded-[8px] border border-[#E2E8F0] bg-white text-xs text-[#0F3A5F] focus:outline-none focus:border-[#2B7CC2] focus:ring-3 focus:ring-[#2B7CC2]/15 transition-all shadow-2xs"
                    />
                    <InputError :message="errors.email" />
                </div>

                <!-- Verifikasi Email jika belum diverifikasi -->
                <div v-if="page.props.mustVerifyEmail && !user?.email_verified_at" class="p-3 bg-amber-50 rounded-[8px] border border-amber-200 text-xs text-amber-800">
                    <p>
                        Alamat email Anda belum terverifikasi.
                        <Link
                            :href="send()"
                            as="button"
                            class="font-semibold text-amber-900 underline ml-1 hover:text-amber-950 cursor-pointer"
                        >
                            Kirim ulang email verifikasi.
                        </Link>
                    </p>
                    <div
                        v-if="page.props.status === 'verification-link-sent'"
                        class="mt-2 text-xs font-medium text-emerald-600"
                    >
                        Tautan verifikasi baru telah dikirim ke alamat email Anda.
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="pt-2 flex justify-end">
                    <BaseButton
                        type="submit"
                        variant="primary"
                        size="md"
                        :loading="processing"
                        :disabled="processing"
                        data-test="update-profile-button"
                    >
                        <Save class="w-4 h-4 mr-1.5 shrink-0" />
                        <span>{{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                    </BaseButton>
                </div>
            </Form>
        </BaseCard>
    </div>
</template>
