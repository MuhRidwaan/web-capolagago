<?php

if (! function_exists('upload_url')) {
    /**
     * Generate public URL untuk file yang disimpan di public/uploads/.
     * Kompatibel dengan shared hosting yang tidak support symlink.
     *
     * @param  string|null  $path  Path relatif dari public/uploads/ (e.g. "products/image.jpg")
     * @param  string|null  $fallback  URL fallback jika path kosong
     */
    function upload_url(?string $path, ?string $fallback = null): string
    {
        if (blank($path)) {
            return $fallback ?? asset('images/placeholder.png');
        }

        // Jika sudah berupa URL penuh, kembalikan apa adanya
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('uploads/' . ltrim($path, '/'));
    }
}

if (! function_exists('upload_store')) {
    /**
     * Simpan file ke public/uploads/{folder}/ dan return path relatifnya.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder  Sub-folder tujuan (e.g. "products", "mitra-logos")
     * @return string  Path relatif dari public/uploads/ (e.g. "products/abc123.jpg")
     */
    function upload_store(\Illuminate\Http\UploadedFile $file, string $folder): string
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/' . $folder), $filename);
        return $folder . '/' . $filename;
    }
}

if (! function_exists('upload_delete')) {
    /**
     * Hapus file dari public/uploads/.
     *
     * @param  string|null  $path  Path relatif dari public/uploads/
     */
    function upload_delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $fullPath = public_path('uploads/' . ltrim($path, '/'));
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
