<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka): string {
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

if (!function_exists('renderOrderStatusBadge')) {
    function renderOrderStatusBadge(string $status): string {
        $map = [
            'Menunggu Konfirmasi' => 'bg-warning text-dark',
            'Sedang Diproses'     => 'bg-info text-dark',
            'Sedang Dikirim'      => 'bg-primary text-white',
            'Selesai'             => 'bg-success text-white',
            'Dibatalkan'          => 'bg-danger text-white'
        ];
        $cls = $map[$status] ?? 'bg-secondary text-white';
        return "<span class='badge {$cls} px-2 py-1 rounded-pill fw-semibold'>{$status}</span>";
    }
}

if (!function_exists('renderPaymentStatusBadge')) {
    function renderPaymentStatusBadge(string $status): string {
        if ($status === 'Lunas') {
            return "<span class='badge bg-success text-white px-2 py-1 rounded-pill fw-semibold'><i class='bi bi-check-circle-fill me-1'></i> Lunas</span>";
        } elseif ($status === 'Dibatalkan' || $status === 'Batal') {
            return "<span class='badge bg-danger text-white px-2 py-1 rounded-pill fw-semibold'><i class='bi bi-x-circle-fill me-1'></i> Batal</span>";
        }

        $map = [
            'Menunggu Pembayaran'               => 'bg-warning text-dark',
            'Sudah Dibayar (Menunggu Verifikasi)' => 'bg-info text-dark',
        ];
        $cls = $map[$status] ?? 'bg-secondary text-white';
        return "<span class='badge {$cls} px-2 py-1 rounded-pill fw-semibold'>{$status}</span>";
    }
}

if (!function_exists('bookCoverHtml')) {
    function bookCoverHtml(
        ?string $coverFile,
        string  $altTitle   = 'Buku',
        string  $mode       = 'card',
        string  $extraClass = ''
    ): string {
        $isValid = !empty($coverFile) && $coverFile !== 'default_book.png';
        $uploadUrl = asset('uploads') . '/';

        if ($mode === 'card') {
            if ($isValid) {
                $src = $uploadUrl . htmlspecialchars($coverFile);
                return "<img src=\"{$src}\" alt=\"" . htmlspecialchars($altTitle) . "\" class=\"book-cover-img {$extraClass}\">";
            }
            $label = htmlspecialchars(mb_strimwidth($altTitle, 0, 45, '…'));
            return "<div class=\"book-no-cover\"><i class=\"bi bi-book-half\"></i><span>{$label}</span></div>";
        }

        if ($mode === 'thumb') {
            if ($isValid) {
                $src = $uploadUrl . htmlspecialchars($coverFile);
                return "<img src=\"{$src}\" alt=\"" . htmlspecialchars($altTitle) . "\" class=\"rounded-2 shadow-sm {$extraClass}\" style=\"width:52px;height:70px;object-fit:cover;\">";
            }
            return "<div class=\"rounded-2 shadow-sm {$extraClass}\" style=\"width:52px;height:70px;background:#f4f1eb;display:flex;align-items:center;justify-content:center;border:1px dashed #c8bfae;\"><i class=\"bi bi-book text-muted\" style=\"font-size:1.4rem;\"></i></div>";
        }

        if ($mode === 'detail') {
            if ($isValid) {
                $src = $uploadUrl . htmlspecialchars($coverFile);
                return "<img src=\"{$src}\" alt=\"" . htmlspecialchars($altTitle) . "\" class=\"img-fluid w-100 {$extraClass}\" style=\"object-fit:cover;aspect-ratio:3/4;\">";
            }
            $label = htmlspecialchars($altTitle);
            return "<div style=\"aspect-ratio:3/4;background:linear-gradient(145deg,#f4f1eb,#ede8de);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;color:#b0a48e;padding:2rem;border-radius:12px;\"><i class=\"bi bi-book-half\" style=\"font-size:4rem;opacity:.5;\"></i><span style=\"font-weight:700;font-size:.9rem;text-align:center;text-transform:uppercase;letter-spacing:.4px;opacity:.7;\">{$label}</span></div>";
        }

        return '';
    }
}
