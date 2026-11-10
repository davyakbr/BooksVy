@extends('layouts.app')

@section('title', 'Hubungi Admin & Pusat Bantuan - BooksVy')

@section('content')

<!-- Header Pusat Bantuan -->
<div class="mb-5 text-center">
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold mb-2">
        <i class="bi bi-envelope-heart me-1"></i> Layanan Bantuan & Kontak Admin
    </span>
    <h2 class="fw-bold text-dark mb-2">Hubungi Tim Administrator BooksVy</h2>
    <p class="text-secondary mx-auto" style="max-width: 680px;">
        Punya pertanyaan seputar ketersediaan buku, status pembayaran <em>Payment at Delivery</em>, atau konfirmasi pengiriman? Sampaikan langsung kepada kami.
    </p>
</div>

<div class="row g-4 mb-5">
    <!-- Kolom Utama: Tab Form Pesan Baru & Riwayat Balasan -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <!-- Navigation Tabs -->
            <div class="card-header bg-white border-bottom p-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="contactTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 fw-semibold {{ $activeTab !== 'my_messages' ? 'active' : '' }}" id="new-message-tab" data-bs-toggle="pill" data-bs-target="#new-message-pane" type="button" role="tab" aria-controls="new-message-pane" aria-selected="{{ $activeTab !== 'my_messages' ? 'true' : 'false' }}">
                            <i class="bi bi-pencil-square me-1"></i> Kirim Pesan Baru
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 fw-semibold {{ $activeTab === 'my_messages' ? 'active' : '' }}" id="my-messages-tab" data-bs-toggle="pill" data-bs-target="#my-messages-pane" type="button" role="tab" aria-controls="my-messages-pane" aria-selected="{{ $activeTab === 'my_messages' ? 'true' : 'false' }}">
                            <i class="bi bi-inbox me-1"></i> Riwayat Pesan Saya
                            @if(!empty($myMessages) && count($myMessages) > 0)
                                <span class="badge bg-danger rounded-pill ms-1">{{ count($myMessages) }}</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="contactTabsContent">
                    <!-- Tab 1: Formulir Kirim Pesan Baru -->
                    <div class="tab-pane fade {{ $activeTab !== 'my_messages' ? 'show active' : '' }}" id="new-message-pane" role="tabpanel" aria-labelledby="new-message-tab" tabindex="0">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="bi bi-send text-primary me-2"></i>Formulir Kirim Pesan</h4>
                                <p class="text-muted small mb-0">Pilih topik pertanyaan agar Admin BooksVy dapat memberikan tanggapan dengan tepat</p>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3 mb-4 shadow-sm">
                                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon lengkapi formulir:</div>
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.store') }}">
                            @csrf

                            <!-- Pilihan Kategori Topik (Pills Radio) -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark d-block mb-2">Pilih Topik Pesan <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer bg-light h-100">
                                            <input type="radio" name="topic" value="Pertanyaan Stok & Rekomendasi Buku" {{ old('topic', 'Pertanyaan Stok & Rekomendasi Buku') === 'Pertanyaan Stok & Rekomendasi Buku' ? 'checked' : '' }} class="form-check-input mt-0" required>
                                            <div class="small">
                                                <strong class="d-block text-dark"><i class="bi bi-book me-1 text-primary"></i> Info Stok & Buku</strong>
                                                <span class="text-muted">Ketersediaan judul & edisi buku</span>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer bg-light h-100">
                                            <input type="radio" name="topic" value="Lainnya" {{ old('topic') === 'Lainnya' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <div class="small">
                                                <strong class="d-block text-dark"><i class="bi bi-three-dots me-1 text-success"></i> Lainnya</strong>
                                                <span class="text-muted">Pertanyaan umum atau kendala lainnya</span>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer bg-light h-100">
                                            <input type="radio" name="topic" value="Status Pengiriman & Cek Resi" {{ old('topic') === 'Status Pengiriman & Cek Resi' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <div class="small">
                                                <strong class="d-block text-dark"><i class="bi bi-truck me-1 text-warning"></i> Cek Pengiriman & Resi</strong>
                                                <span class="text-muted">Pelacakan paket pesanan</span>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer bg-light h-100">
                                            <input type="radio" name="topic" value="Kritik & Saran" {{ old('topic') === 'Kritik & Saran' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <div class="small">
                                                <strong class="d-block text-dark"><i class="bi bi-chat-heart me-1 text-info"></i> Kritik & Saran</strong>
                                                <span class="text-muted">Umpan balik & masukan layanan</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Nomor Pesanan Terkait (Opsional) -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-muted">Kaitkan Nomor Pesanan (Opsional)</label>
                                    @if (!empty($userOrders) && count($userOrders) > 0)
                                        <select name="order_number" class="form-select bg-light">
                                            <option value="">-- Tidak Terkait Pesanan Tertentu --</option>
                                            @foreach ($userOrders as $uo)
                                                <option value="{{ $uo->order_number }}" {{ old('order_number') === $uo->order_number ? 'selected' : '' }}>
                                                    Pesanan #{{ $uo->order_number }} ({{ formatRupiah($uo->total_amount) }} - {{ $uo->order_status }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Pilih nomor transaksi Anda jika ingin menanyakan status pesanan atau kendala terkait.</small>
                                    @else
                                        <input type="text" name="order_number" class="form-control bg-light" placeholder="Contoh: BVY-20261106-ABCDE" value="{{ old('order_number') }}">
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Nama Pengirim (Akun Anda)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-circle"></i></span>
                                        <input type="text" name="name" class="form-control bg-light border-start-0" value="{{ $user->name }}" required readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Alamat Email Terdaftar</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control bg-light border-start-0" value="{{ $user->email }}" required readonly>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-muted">Subjek / Inti Pesan <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control bg-light" placeholder="Contoh: Kapan buku Clean Code restock kembali?" value="{{ old('subject') }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-muted">Rincian Pertanyaan / Pesan <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control bg-light" rows="5" placeholder="Tuliskan pertanyaan atau kendala yang Anda hadapi secara rinci..." required>{{ old('message') }}</textarea>
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 w-100 fw-semibold shadow-sm">
                                        <i class="bi bi-send-fill me-1"></i> Kirim Pesan ke Admin
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Riwayat Pesan Saya -->
                    <div class="tab-pane fade {{ $activeTab === 'my_messages' ? 'show active' : '' }}" id="my-messages-pane" role="tabpanel" aria-labelledby="my-messages-tab" tabindex="0">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="bi bi-chat-square-text text-primary me-2"></i>Riwayat Pesan & Balasan</h4>
                                <p class="text-muted small mb-0">Pantau status tanggapan dan baca pesan balasan langsung dari Administrator</p>
                            </div>
                        </div>

                        @if (empty($myMessages) || count($myMessages) === 0)
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-chat-left-dots display-4 d-block mb-3 text-secondary"></i>
                                <h5 class="fw-bold">Belum Ada Riwayat Pesan</h5>
                                <p class="small text-muted mb-0">Anda belum pernah mengirimkan pesan pertanyaan ke administrator.</p>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach ($myMessages as $t)
                                    <div class="card border rounded-3 p-3 bg-light">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold">
                                                    #{{ $t->ticket_number ?: 'PSN-' . $t->id }}
                                                </span>
                                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1 small">
                                                    {{ $t->topic }}
                                                </span>
                                                @if (!empty($t->order_number))
                                                    <span class="badge bg-light text-dark border rounded-pill">
                                                        <i class="bi bi-box-seam me-1 text-primary"></i>Pesanan: {{ $t->order_number }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div>
                                                @if ($t->status === 'Sudah Dibalas')
                                                    <span class="badge bg-success rounded-pill px-3 py-1">
                                                        <i class="bi bi-check2-circle me-1"></i> Sudah Dibalas
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Tanggapan
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-1">{{ $t->subject }}</h6>
                                        <p class="small text-secondary mb-2" style="line-height: 1.6; white-space: pre-line;">{{ $t->message }}</p>
                                        <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                                            Dikirim pada: {{ date('d M Y, H:i', strtotime($t->created_at)) }} WIB
                                        </small>

                                        <!-- Kotak Balasan Admin -->
                                        @if (!empty($t->reply))
                                            <div class="bg-white p-3 rounded-3 border-start border-success border-4 shadow-sm mt-2">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <strong class="text-success small d-flex align-items-center gap-1">
                                                        <i class="bi bi-shield-check fs-5"></i> Balasan Resmi Administrator BooksVy
                                                    </strong>
                                                    <small class="text-muted" style="font-size: 0.72rem;">
                                                        {{ $t->replied_at ? date('d M Y, H:i', strtotime($t->replied_at)) . ' WIB' : '' }}
                                                    </small>
                                                </div>
                                                <p class="small text-dark mb-0" style="line-height: 1.7; white-space: pre-line;">{{ $t->reply }}</p>
                                            </div>
                                        @else
                                            <div class="p-2 bg-white rounded-2 small text-muted border border-dashed">
                                                <i class="bi bi-info-circle me-1 text-warning"></i>
                                                Pesan Anda sedang ditinjau oleh Administrator. Balasan akan segera muncul di sini.
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Samping: Quick WhatsApp, FAQ Kilat, & Kontak Toko -->
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-3">
            <!-- Card WhatsApp Quick Chat -->
            <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="p-3 bg-white text-success rounded-circle shadow-sm">
                        <i class="bi bi-whatsapp fs-3"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0">Respon Cepat via WhatsApp</h6>
                        <small class="text-white-50">Chat langsung dengan staf toko</small>
                    </div>
                </div>
                <p class="small text-white-50 mb-3" style="line-height: 1.6;">
                    Butuh konfirmasi instan pembayaran atau pengecekan stok darurat? Hubungi nomor resmi customer service kami.
                </p>
                <a href="https://wa.me/6285781880279?text=Halo%20Admin%20BooksVy,%20saya%20ingin%20berkonsultasi%20seputar%20pesanan%20buku." target="_blank" class="btn btn-light text-success fw-bold rounded-pill w-100 shadow-sm">
                    <i class="bi bi-chat-dots-fill me-1"></i> Mulai Chat WhatsApp
                </a>
            </div>

            <!-- Card Pertanyaan Sering Diajukan (FAQ Kilat) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-question-circle text-primary"></i> Pertanyaan Populer (FAQ)
                </h6>

                <div class="accordion accordion-flush small" id="faqQuickAccordion">
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed px-0 py-2 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#fq1">
                                Kapan pesanan saya dikirim?
                            </button>
                        </h2>
                        <div id="fq1" class="accordion-collapse collapse" data-bs-parent="#faqQuickAccordion">
                            <div class="accordion-body px-0 text-muted">
                                Pesanan diproses setelah transfer pembayaran terverifikasi. Paket dikirim dalam 1x24 jam kerja.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed px-0 py-2 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#fq2">
                                Bagaimana cara bayar Payment at Delivery?
                            </button>
                        </h2>
                        <div id="fq2" class="accordion-collapse collapse" data-bs-parent="#faqQuickAccordion">
                            <div class="accordion-body px-0 text-muted">
                                Transfer tagihan sesuai invoice ke rekening resmi BCA/Mandiri/BRI atau scan QRIS BooksVy, lalu tekan tombol konfirmasi transfer.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed px-0 py-2 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#fq3">
                                Apakah buku dijamin original?
                            </button>
                        </h2>
                        <div id="fq3" class="accordion-collapse collapse" data-bs-parent="#faqQuickAccordion">
                            <div class="accordion-body px-0 text-muted">
                                Seluruh buku di BooksVy 100% asli dari penerbit resmi dengan garansi penggantian jika cacat produksi.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Info Jam Kerja & Lokasi -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-warning"></i> Jam Operasional & Alamat
                </h6>
                <div class="small text-secondary d-flex flex-column gap-2 mb-0">
                    <div>
                        <strong>Senin - Jumat:</strong> 08.00 - 17.00 WIB
                    </div>
                    <div>
                        <strong>Sabtu:</strong> 08.00 - 14.00 WIB
                    </div>
                    <div class="text-danger">
                        <strong>Minggu & Hari Libur:</strong> Tutup
                    </div>
                    <hr class="my-2">
                    <div class="text-muted">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Jl. Thamrin No. 88, Menteng, Jakarta Pusat
                    </div>
                    <div class="text-muted">
                        <i class="bi bi-envelope-fill text-info me-1"></i> support@booksvy.com
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
