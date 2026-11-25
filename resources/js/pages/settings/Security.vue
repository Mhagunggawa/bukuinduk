<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Shield, KeyRound, ArrowLeft } from '@lucide/vue';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';

// oxfmt-ignore
type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Keamanan & Sandi',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Keamanan & Sandi" />

    <div class="max-w-3xl mx-auto space-y-6 pb-12">
        <!-- Bar Navigasi Atas: Tombol Kembali -->
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

        <!-- Kartu Ubah Kata Sandi -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 sm:p-7 shadow-2xs space-y-6">
            <div class="flex items-start gap-3.5 pb-4 border-b border-[#E2E8F0]/80">
                <div class="w-10 h-10 rounded-xl bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center shrink-0 border border-[#BFE3FA]/60">
                    <Shield class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-[#0F3A5F]">
                        Perbarui Kata Sandi
                    </h2>
                    <p class="text-xs text-[#64748B] mt-0.5">
                        Pastikan akun Anda menggunakan kata sandi yang aman untuk menjaga keamanan akses sistem.
                    </p>
                </div>
            </div>

            <Form
                v-bind="SecurityController.update.form()"
                :options="{
                    preserveScroll: true,
                }"
                reset-on-success
                :reset-on-error="[
                    'password',
                    'password_confirmation',
                    'current_password',
                ]"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <div class="space-y-1.5">
                    <Label for="current_password" class="text-xs font-semibold text-[#0F3A5F]">
                        Kata Sandi Saat Ini
                    </Label>
                    <PasswordInput
                        id="current_password"
                        name="current_password"
                        class="h-10 text-xs rounded-xl border-[#E2E8F0] focus:border-[#2B7CC2] focus:ring-3 focus:ring-[#2B7CC2]/15 bg-white text-[#0F3A5F]"
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi saat ini"
                    />
                    <InputError :message="errors.current_password" />
                </div>

                <div class="space-y-1.5">
                    <Label for="password" class="text-xs font-semibold text-[#0F3A5F]">
                        Kata Sandi Baru
                    </Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        class="h-10 text-xs rounded-xl border-[#E2E8F0] focus:border-[#2B7CC2] focus:ring-3 focus:ring-[#2B7CC2]/15 bg-white text-[#0F3A5F]"
                        autocomplete="new-password"
                        placeholder="Masukkan kata sandi baru"
                        :passwordrules="props.passwordRules"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-1.5">
                    <Label for="password_confirmation" class="text-xs font-semibold text-[#0F3A5F]">
                        Konfirmasi Kata Sandi Baru
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        class="h-10 text-xs rounded-xl border-[#E2E8F0] focus:border-[#2B7CC2] focus:ring-3 focus:ring-[#2B7CC2]/15 bg-white text-[#0F3A5F]"
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                        :passwordrules="props.passwordRules"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <Button
                        :disabled="processing"
                        data-test="update-password-button"
                        class="bg-[#2B7CC2] hover:bg-[#1E6BB0] text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center gap-2"
                    >
                        <KeyRound class="w-4 h-4" />
                        <span>{{ processing ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}</span>
                    </Button>
                </div>
            </Form>
        </div>

        <ManageTwoFactor
            :canManageTwoFactor="canManageTwoFactor"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />

        <ManagePasskeys
            :canManagePasskeys="canManagePasskeys"
            :passkeys="passkeys"
        />
    </div>
</template>
