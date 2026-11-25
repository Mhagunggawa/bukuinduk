export interface SiswaIdentifier {
    id: number;
    nis: string;
    nisn: string;
    nama_lengkap: string;
    kelas_saat_ini?: string | null;
    pasfoto_path?: string | null;
    pasfoto_url?: string | null;
}

export interface ZipRawEntry {
    filename: string;
    basename: string;
    identifier: string;
    ext: string;
    compressionMethod: number;
    compressedSize: number;
    uncompressedSize: number;
    localHeaderOffset: number;
}

export interface MatchedZipPhoto {
    entry: ZipRawEntry;
    siswa: SiswaIdentifier;
    matchedBy: 'NIS' | 'NISN';
    hasExistingPhoto: boolean;
    existingPhotoUrl: string | null;
    newPhotoPreviewUrl: string | null;
}

export interface UnmatchedZipPhoto {
    filename: string;
    basename: string;
    identifier: string;
    uncompressedSize: number;
    message: string;
}

export interface InvalidTypeZipFile {
    filename: string;
    basename: string;
    identifier: string;
    ext: string;
    reason: string;
}

export interface ZipInspectionResult {
    matched: MatchedZipPhoto[];
    newPhotos: MatchedZipPhoto[];
    existingPhotos: MatchedZipPhoto[];
    unmatched: UnmatchedZipPhoto[];
    invalidTypeFiles: InvalidTypeZipFile[];
    totalFiles: number;
    totalImages: number;
    totalValidOriginalBytes: number;
}

const ALLOWED_EXTENSIONS = new Set(['jpg', 'jpeg', 'png', 'webp']);

// Precomputed CRC32 table for building valid ZIP archives in memory
const CRC32_TABLE = (() => {
    const table = new Uint32Array(256);
    for (let i = 0; i < 256; i++) {
        let c = i;
        for (let k = 0; k < 8; k++) {
            c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        }
        table[i] = c >>> 0;
    }
    return table;
})();

function crc32(data: Uint8Array): number {
    let crc = 0xffffffff;
    for (let i = 0; i < data.length; i++) {
        crc = CRC32_TABLE[(crc ^ data[i]) & 0xff] ^ (crc >>> 8);
    }
    return (crc ^ 0xffffffff) >>> 0;
}

/**
 * Checks whether binary bytes match known valid image signatures (JPEG, PNG, WEBP, GIF).
 */
export function isValidImageSignature(bytes: Uint8Array): boolean {
    if (bytes.length < 4) return false;

    // JPEG: FF D8 FF
    if (bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff) {
        return true;
    }

    // PNG: 89 50 4E 47
    if (bytes[0] === 0x89 && bytes[1] === 0x50 && bytes[2] === 0x4e && bytes[3] === 0x47) {
        return true;
    }

    // GIF: 47 49 46 38 ("GIF8")
    if (bytes[0] === 0x47 && bytes[1] === 0x49 && bytes[2] === 0x46 && bytes[3] === 0x38) {
        return true;
    }

    // WEBP: "RIFF" .... "WEBP"
    if (
        bytes.length >= 12 &&
        bytes[0] === 0x52 &&
        bytes[1] === 0x49 &&
        bytes[2] === 0x46 &&
        bytes[3] === 0x46 &&
        bytes[8] === 0x57 &&
        bytes[9] === 0x45 &&
        bytes[10] === 0x42 &&
        bytes[11] === 0x50
    ) {
        return true;
    }

    return false;
}

/**
 * Parses ALL non-directory, non-system file entries from a ZIP Central Directory.
 */
export function parseAllZipEntries(buffer: ArrayBuffer): ZipRawEntry[] {
    const view = new DataView(buffer);
    const bytes = new Uint8Array(buffer);
    const len = bytes.length;

    // Locate End of Central Directory (EOCD) signature 0x06054b50
    let eocdOffset = -1;
    const minEocdSearch = Math.max(0, len - 65557);
    for (let i = len - 22; i >= minEocdSearch; i--) {
        if (view.getUint32(i, true) === 0x06054b50) {
            eocdOffset = i;
            break;
        }
    }

    if (eocdOffset === -1) {
        throw new Error('Tipe berkas tidak sesuai atau rusak! Pastikan Anda mengunggah berkas arsip .ZIP yang valid.');
    }

    const totalEntries = view.getUint16(eocdOffset + 10, true);
    const cdOffset = view.getUint32(eocdOffset + 16, true);

    const decoder = new TextDecoder('utf-8');
    const entries: ZipRawEntry[] = [];
    let ptr = cdOffset;

    for (let i = 0; i < totalEntries && ptr + 46 <= len; i++) {
        const sig = view.getUint32(ptr, true);
        if (sig !== 0x02014b50) {
            break;
        }

        const compressionMethod = view.getUint16(ptr + 10, true);
        const compressedSize = view.getUint32(ptr + 20, true);
        const uncompressedSize = view.getUint32(ptr + 24, true);
        const fileNameLen = view.getUint16(ptr + 28, true);
        const extraLen = view.getUint16(ptr + 30, true);
        const commentLen = view.getUint16(ptr + 32, true);
        const localHeaderOffset = view.getUint32(ptr + 42, true);

        const filenameBytes = bytes.subarray(ptr + 46, ptr + 46 + fileNameLen);
        const filename = decoder.decode(filenameBytes);

        ptr += 46 + fileNameLen + extraLen + commentLen;

        // Skip directories and macOS / Windows metadata files
        const basename = filename.split(/[/\\]/).pop() || filename;
        if (
            filename.endsWith('/') ||
            filename.includes('__MACOSX') ||
            basename.startsWith('.') ||
            basename.toLowerCase() === 'thumbs.db' ||
            basename.toLowerCase() === 'desktop.ini'
        ) {
            continue;
        }

        const dotIdx = basename.lastIndexOf('.');
        const identifier = dotIdx > 0 ? basename.substring(0, dotIdx).trim() : basename.trim();
        const ext = dotIdx > 0 ? basename.substring(dotIdx + 1).toLowerCase().trim() : '';

        entries.push({
            filename,
            basename,
            identifier,
            ext,
            compressionMethod,
            compressedSize,
            uncompressedSize,
            localHeaderOffset,
        });
    }

    return entries;
}

/**
 * Extracts a single file's raw bytes from the ZIP ArrayBuffer.
 */
async function extractEntryBytes(buffer: ArrayBuffer, entry: ZipRawEntry): Promise<Uint8Array> {
    const view = new DataView(buffer);
    const offset = entry.localHeaderOffset;

    if (view.getUint32(offset, true) !== 0x04034b50) {
        throw new Error(`Header lokal ZIP tidak valid untuk ${entry.basename}`);
    }

    const fileNameLen = view.getUint16(offset + 26, true);
    const extraLen = view.getUint16(offset + 28, true);
    const dataStart = offset + 30 + fileNameLen + extraLen;
    const compressedSlice = new Uint8Array(buffer, dataStart, entry.compressedSize);

    if (entry.compressionMethod === 0) {
        return new Uint8Array(compressedSlice);
    }

    if (entry.compressionMethod === 8 && typeof DecompressionStream !== 'undefined') {
        const ds = new DecompressionStream('deflate-raw');
        const writer = ds.writable.getWriter();
        writer.write(compressedSlice);
        writer.close();

        const response = new Response(ds.readable);
        const ab = await response.arrayBuffer();
        return new Uint8Array(ab);
    }

    throw new Error(`Metode kompresi ZIP (${entry.compressionMethod}) tidak didukung di browser.`);
}

/**
 * Revokes any temporary Object URLs created during ZIP inspection.
 */
export function revokeInspectionPreviews(result: ZipInspectionResult | null): void {
    if (!result) return;
    for (const item of result.matched) {
        if (item.newPhotoPreviewUrl) {
            try {
                URL.revokeObjectURL(item.newPhotoPreviewUrl);
            } catch {
                // ignore
            }
        }
    }
}

/**
 * Inspects a ZIP file:
 * 1) Checks every file's extension and actual image binary header (magic bytes) to detect wrong file types.
 * 2) Checks valid image filenames against registered student NIS and NISN.
 * 3) Identifies students who ALREADY have an existing photo and generates side-by-side preview URLs.
 */
export async function inspectZipPhotos(
    file: File,
    siswaList: SiswaIdentifier[]
): Promise<ZipInspectionResult> {
    const buffer = await file.arrayBuffer();
    const allEntries = parseAllZipEntries(buffer);

    const byNis = new Map<string, SiswaIdentifier>();
    const byNisn = new Map<string, SiswaIdentifier>();

    for (const s of siswaList) {
        if (s.nis) byNis.set(String(s.nis).trim().toUpperCase(), s);
        if (s.nisn) byNisn.set(String(s.nisn).trim().toUpperCase(), s);
    }

    const matched: MatchedZipPhoto[] = [];
    const newPhotos: MatchedZipPhoto[] = [];
    const existingPhotos: MatchedZipPhoto[] = [];
    const unmatched: UnmatchedZipPhoto[] = [];
    const invalidTypeFiles: InvalidTypeZipFile[] = [];
    let totalImages = 0;
    let totalValidOriginalBytes = 0;

    for (const entry of allEntries) {
        // 1. Check extension
        if (!entry.ext || !ALLOWED_EXTENSIONS.has(entry.ext)) {
            const extLabel = entry.ext ? `.${entry.ext.toUpperCase()}` : 'Tanpa Ekstensi';
            invalidTypeFiles.push({
                filename: entry.filename,
                basename: entry.basename,
                identifier: entry.identifier || '-',
                ext: extLabel,
                reason: `Tipe file "${extLabel}" tidak sesuai. Hanya menerima format foto .JPG, .JPEG, .PNG, atau .WEBP.`,
            });
            continue;
        }

        // 2. Check actual binary content (magic bytes) to catch non-image files renamed to .jpg/.png
        let rawBytes: Uint8Array;
        try {
            rawBytes = await extractEntryBytes(buffer, entry);
            if (!isValidImageSignature(rawBytes)) {
                invalidTypeFiles.push({
                    filename: entry.filename,
                    basename: entry.basename,
                    identifier: entry.identifier || '-',
                    ext: `.${entry.ext.toUpperCase()} (Isi Bukan Gambar)`,
                    reason: `Berkas "${entry.basename}" memiliki ekstensi .${entry.ext} tetapi isi datanya bukan gambar yang valid atau rusak.`,
                });
                continue;
            }
        } catch {
            invalidTypeFiles.push({
                filename: entry.filename,
                basename: entry.basename,
                identifier: entry.identifier || '-',
                ext: `.${entry.ext.toUpperCase()} (Rusak)`,
                reason: `Berkas "${entry.basename}" tidak dapat dibaca atau rusak di dalam arsip ZIP.`,
            });
            continue;
        }

        totalImages++;

        // 3. Check NIS / NISN against database
        const key = entry.identifier.toUpperCase();
        const matchedNis = byNis.get(key);
        const matchedNisn = byNisn.get(key);
        const targetSiswa = matchedNis || matchedNisn;

        if (targetSiswa) {
            const matchedBy: 'NIS' | 'NISN' = matchedNis ? 'NIS' : 'NISN';
            const hasExisting = Boolean(targetSiswa.pasfoto_path || targetSiswa.pasfoto_url);
            const existingUrl = targetSiswa.pasfoto_url || (targetSiswa.pasfoto_path ? `/storage/${targetSiswa.pasfoto_path}` : null);

            let newPreviewUrl: string | null = null;
            if (hasExisting && typeof URL !== 'undefined' && typeof URL.createObjectURL === 'function') {
                const mimeType =
                    entry.ext === 'png'
                        ? 'image/png'
                        : entry.ext === 'webp'
                          ? 'image/webp'
                          : 'image/jpeg';
                const blob = new Blob([rawBytes], { type: mimeType });
                newPreviewUrl = URL.createObjectURL(blob);
            }

            const matchedItem: MatchedZipPhoto = {
                entry,
                siswa: targetSiswa,
                matchedBy,
                hasExistingPhoto: hasExisting,
                existingPhotoUrl: existingUrl,
                newPhotoPreviewUrl: newPreviewUrl,
            };

            matched.push(matchedItem);
            if (hasExisting) {
                existingPhotos.push(matchedItem);
            } else {
                newPhotos.push(matchedItem);
            }
            totalValidOriginalBytes += entry.uncompressedSize;
        } else {
            unmatched.push({
                filename: entry.filename,
                basename: entry.basename,
                identifier: entry.identifier,
                uncompressedSize: entry.uncompressedSize,
                message: `NIS / NISN "${entry.identifier}" tidak ditemukan di database siswa.`,
            });
        }
    }

    return {
        matched,
        newPhotos,
        existingPhotos,
        unmatched,
        invalidTypeFiles,
        totalFiles: allEntries.length,
        totalImages,
        totalValidOriginalBytes,
    };
}

/**
 * Compresses an image byte array in the browser using Canvas (max 450x600px, JPEG 80% quality).
 */
async function compressImageBytes(
    rawBytes: Uint8Array,
    ext: string,
    maxWidth = 450,
    maxHeight = 600,
    quality = 0.8
): Promise<{ bytes: Uint8Array; ext: string }> {
    try {
        const mimeType =
            ext === 'png'
                ? 'image/png'
                : ext === 'webp'
                  ? 'image/webp'
                  : 'image/jpeg';

        const blob = new Blob([rawBytes], { type: mimeType });
        const bitmap = await createImageBitmap(blob);

        let targetW = bitmap.width;
        let targetH = bitmap.height;

        if (targetW > maxWidth || targetH > maxHeight) {
            const ratio = Math.min(maxWidth / targetW, maxHeight / targetH);
            targetW = Math.max(1, Math.round(targetW * ratio));
            targetH = Math.max(1, Math.round(targetH * ratio));
        }

        const canvas = document.createElement('canvas');
        canvas.width = targetW;
        canvas.height = targetH;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            bitmap.close();
            return { bytes: rawBytes, ext };
        }

        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, targetW, targetH);
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(bitmap, 0, 0, targetW, targetH);
        bitmap.close();

        const compressedBlob = await new Promise<Blob | null>((resolve) =>
            canvas.toBlob((b) => resolve(b), 'image/jpeg', quality)
        );

        if (!compressedBlob) {
            return { bytes: rawBytes, ext };
        }

        const compressedAb = await compressedBlob.arrayBuffer();
        const compressedUint8 = new Uint8Array(compressedAb);

        if (compressedUint8.length < rawBytes.length || ext !== 'jpg') {
            return { bytes: compressedUint8, ext: 'jpg' };
        }

        return { bytes: rawBytes, ext };
    } catch {
        return { bytes: rawBytes, ext };
    }
}

/**
 * Packs a list of named byte arrays into a standard ZIP File (Store method 0).
 */
function createZipFileFromEntries(
    files: { name: string; data: Uint8Array }[],
    zipFilename: string
): File {
    const encoder = new TextEncoder();
    const localParts: Uint8Array[] = [];
    const centralParts: Uint8Array[] = [];
    let currentOffset = 0;

    for (const file of files) {
        const nameBytes = encoder.encode(file.name);
        const crc = crc32(file.data);
        const size = file.data.length;

        const localHeader = new Uint8Array(30 + nameBytes.length);
        const lv = new DataView(localHeader.buffer);
        lv.setUint32(0, 0x04034b50, true);
        lv.setUint16(4, 20, true);
        lv.setUint16(6, 0x0800, true);
        lv.setUint16(8, 0, true);
        lv.setUint16(10, 0, true);
        lv.setUint16(12, 0, true);
        lv.setUint32(14, crc, true);
        lv.setUint32(18, size, true);
        lv.setUint32(22, size, true);
        lv.setUint16(26, nameBytes.length, true);
        lv.setUint16(28, 0, true);
        localHeader.set(nameBytes, 30);

        localParts.push(localHeader, file.data);

        const centralHeader = new Uint8Array(46 + nameBytes.length);
        const cv = new DataView(centralHeader.buffer);
        cv.setUint32(0, 0x02014b50, true);
        cv.setUint16(4, 20, true);
        cv.setUint16(6, 20, true);
        cv.setUint16(8, 0x0800, true);
        cv.setUint16(10, 0, true);
        cv.setUint16(12, 0, true);
        cv.setUint16(14, 0, true);
        cv.setUint32(16, crc, true);
        cv.setUint32(20, size, true);
        cv.setUint32(24, size, true);
        cv.setUint16(28, nameBytes.length, true);
        cv.setUint16(30, 0, true);
        cv.setUint16(32, 0, true);
        cv.setUint16(34, 0, true);
        cv.setUint16(36, 0, true);
        cv.setUint32(38, 0, true);
        cv.setUint32(42, currentOffset, true);
        centralHeader.set(nameBytes, 46);

        centralParts.push(centralHeader);
        currentOffset += localHeader.length + size;
    }

    const centralDirSize = centralParts.reduce((acc, part) => acc + part.length, 0);

    const eocd = new Uint8Array(22);
    const ev = new DataView(eocd.buffer);
    ev.setUint32(0, 0x06054b50, true);
    ev.setUint16(4, 0, true);
    ev.setUint16(6, 0, true);
    ev.setUint16(8, files.length, true);
    ev.setUint16(10, files.length, true);
    ev.setUint32(12, centralDirSize, true);
    ev.setUint32(16, currentOffset, true);
    ev.setUint16(20, 0, true);

    return new File([...localParts, ...centralParts, eocd], zipFilename, {
        type: 'application/zip',
    });
}

/**
 * Extracts ONLY the selected matched student photos from the original ZIP, compresses each image
 * in the browser (max 450x600px @ 80% JPEG), and repacks them into a clean ZIP File.
 */
export async function buildCompressedValidZip(
    originalZip: File,
    photosToProcess: MatchedZipPhoto[],
    onProgress?: (processed: number, total: number) => void
): Promise<{
    zipFile: File;
    originalBytes: number;
    compressedBytes: number;
}> {
    const buffer = await originalZip.arrayBuffer();
    const outputEntries: { name: string; data: Uint8Array }[] = [];
    let originalBytes = 0;
    let compressedBytes = 0;
    const total = photosToProcess.length;

    for (let i = 0; i < total; i++) {
        const item = photosToProcess[i];
        const rawBytes = await extractEntryBytes(buffer, item.entry);
        originalBytes += rawBytes.length;

        const compressed = await compressImageBytes(rawBytes, item.entry.ext);
        compressedBytes += compressed.bytes.length;

        const cleanIdentifier = item.siswa.nisn || item.siswa.nis || item.entry.identifier;
        outputEntries.push({
            name: `${cleanIdentifier}.${compressed.ext}`,
            data: compressed.bytes,
        });

        if (onProgress) {
            onProgress(i + 1, total);
        }
    }

    const zipFile = createZipFileFromEntries(outputEntries, `compressed_${originalZip.name}`);
    return {
        zipFile,
        originalBytes,
        compressedBytes,
    };
}

export function formatFileSize(bytes: number): string {
    if (bytes <= 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
}
