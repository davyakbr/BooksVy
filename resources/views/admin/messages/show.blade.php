@extends('layouts.admin')

@section('title', 'Tanggapi Tiket #' . ($message->ticket_number ?: 'PSN-' . $message->id) . ' - BooksVy Admin')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary rounded-pill btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pesan
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary rounded-pill px-3 py-1">
                    #{{ $message->ticket_number ?: 'PSN-' . $message->id }}
                </span>
                <span class="badge rounded-pill {{ $message->status === 'Sudah Dibalas' ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $message->status }}
                </span>
            </div>

            <h4 class="fw-bold mb-2">{{ $message->subject }}</h4>
            <div class="small text-muted mb-4">
                <i class="bi bi-clock me-1"></i> Dikirim: {{ date('d F Y, H:i', strtotime($message->created_at)) }} WIB
            </div>

            <div class="card bg-light border-0 rounded-3 p-3 mb-4">
                <h6 class="fw-bold text-muted small mb-2 text-uppercase">Informasi Pengirim</h6>
                <div class="row g-2 small">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nama Lengkap:</span>
                        <strong class="text-dark">{{ $message->name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Email:</span>
                        <strong class="text-dark">{{ $message->email }}</strong>
                    </div>
                    <div class="col-sm-6 mt-2">
                        <span class="text-muted d-block">Topik Pertanyaan:</span>
                        <span class="badge bg-white text-dark border">{{ $message->topic }}</span>
                    </div>
                    @if ($message->order_number)
                        <div class="col-sm-6 mt-2">
                            <span class="text-muted d-block">Nomor Transaksi Terkait:</span>
                            <span class="badge bg-primary text-white">{{ $message->order_number }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-2">Isi Pesan Pelanggan:</h6>
            <div class="p-3 rounded-3 bg-light border text-dark mb-4" style="line-height: 1.6; white-space: pre-line;">
                {{ $message->message }}
            </div>

            @if ($message->reply)
                <div class="alert alert-success border-0 rounded-4 p-3 mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-success"><i class="bi bi-check2-circle me-1"></i> Tanggapan Terkirim Sebelumnya</strong>
                        <small class="text-muted">{{ $message->replied_at ? date('d/m/Y H:i', strtotime($message->replied_at)) : '' }}</small>
                    </div>
                    <div class="text-dark small" style="white-space: pre-line;">{{ $message->reply }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-reply-fill text-primary me-2"></i>Form Balasan Admin</h5>
            <p class="text-muted small mb-4">
                Tanggapan Anda akan langsung dapat dibaca oleh pelanggan saat mereka mengecek tiket atau menu Bantuan & Kontak.
            </p>

            <form action="{{ route('admin.messages.reply', $message->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Isi Tanggapan / Solusi <span class="text-danger">*</span></label>
                    <textarea name="reply" rows="6" class="form-control @error('reply') is-invalid @enderror" 
                              placeholder="Tuliskan jawaban yang ramah, jelas, dan membantu bagi pelanggan..." required>{{ old('reply', $message->reply) }}</textarea>
                    @error('reply')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Kirim & Perbarui Tanggapan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
