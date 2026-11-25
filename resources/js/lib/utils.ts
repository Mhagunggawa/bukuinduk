import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function formatJurusanName(nama?: string | null, kode?: string | null): string {
    if (!nama) return '';
    if (!kode) return nama.trim();
    const cleanKode = kode.trim().toUpperCase();
    const cleanNama = nama.trim();
    if (cleanNama.toUpperCase().includes(`(${cleanKode})`)) {
        return cleanNama;
    }
    return `${cleanNama} (${cleanKode})`;
}
