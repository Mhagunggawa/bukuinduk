<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LogOut, Settings } from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import LogoutModal from '@/components/LogoutModal.vue';

type Props = {
    user: User;
};

const isLogoutOpen = ref(false);

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <button
            type="button"
            class="flex items-center w-full px-2 py-1.5 text-sm cursor-pointer text-rose-600 hover:bg-rose-50 rounded-sm"
            @click="isLogoutOpen = true"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Keluar
        </button>
    </DropdownMenuItem>

    <LogoutModal :open="isLogoutOpen" @close="isLogoutOpen = false" />
</template>
