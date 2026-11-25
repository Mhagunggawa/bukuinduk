import { ref } from 'vue';

export interface ToastOptions {
    type?: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;
    duration?: number;
}

export interface ToastState {
    open: boolean;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    message: string;
    duration: number;
}

const toastState = ref<ToastState>({
    open: false,
    type: 'success',
    title: '',
    message: '',
    duration: 5000,
});

export function useToast() {
    const show = (options: ToastOptions) => {
        // Reset dulu agar transition / timer re-trigger jika pesan baru datang berurutan
        toastState.value.open = false;
        setTimeout(() => {
            toastState.value = {
                open: true,
                type: options.type || 'success',
                title: options.title || '',
                message: options.message,
                duration: options.duration ?? 5000,
            };
        }, 30);
    };

    const success = (message: string, title = 'Berhasil') => {
        show({ type: 'success', title, message });
    };

    const error = (message: string, title = 'Pemberitahuan') => {
        show({ type: 'error', title, message });
    };

    const warning = (message: string, title = 'Peringatan') => {
        show({ type: 'warning', title, message });
    };

    const info = (message: string, title = 'Informasi') => {
        show({ type: 'info', title, message });
    };

    const close = () => {
        toastState.value.open = false;
    };

    return {
        toastState,
        show,
        success,
        error,
        warning,
        info,
        close,
    };
}
