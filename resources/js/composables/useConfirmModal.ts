import { ref } from 'vue';

export interface ConfirmModalOptions {
    title?: string;
    message?: string;
    itemInfo?: string;
    subInfo?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'primary' | 'success';
    onConfirm: () => void | Promise<void>;
}

export function useConfirmModal() {
    const isOpen = ref(false);
    const isLoading = ref(false);
    const options = ref<ConfirmModalOptions>({
        title: 'Konfirmasi Tindakan',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        itemInfo: undefined,
        subInfo: undefined,
        confirmText: 'Konfirmasi',
        cancelText: 'Batal',
        variant: 'danger',
        onConfirm: () => {},
    });

    const openConfirm = (opts: ConfirmModalOptions) => {
        options.value = {
            title: opts.title ?? 'Konfirmasi Tindakan',
            message: opts.message ?? 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            itemInfo: opts.itemInfo,
            subInfo: opts.subInfo,
            confirmText: opts.confirmText ?? 'Konfirmasi',
            cancelText: opts.cancelText ?? 'Batal',
            variant: opts.variant ?? 'danger',
            onConfirm: opts.onConfirm,
        };
        isLoading.value = false;
        isOpen.value = true;
    };

    const closeConfirm = () => {
        if (isLoading.value) return;
        isOpen.value = false;
    };

    const handleConfirm = async () => {
        try {
            isLoading.value = true;
            await options.value.onConfirm();
        } finally {
            isLoading.value = false;
            isOpen.value = false;
        }
    };

    return {
        isOpen,
        isLoading,
        options,
        openConfirm,
        closeConfirm,
        handleConfirm,
    };
}
