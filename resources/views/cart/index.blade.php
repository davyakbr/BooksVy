@extends('layouts.app')

@section('title', 'Keranjang Belanja - BooksVy')

@section('content')

<!-- Stepper Alur Belanja -->
<div class="order-stepper mb-4 d-none d-md-flex">
    <div class="step-item active">
        <div class="step-number">1</div>
        <span>1. Keranjang Belanja</span>
    </div>
    <div class="d-flex align-items-center text-muted"><i class="bi bi-chevron-right"></i></div>
    <div class="step-item">
        <div class="step-number">2</div>
        <span>2. Alamat & Pengiriman</span>
    </div>
    <div class="d-flex align-items-center text-muted"><i class="bi bi-chevron-right"></i></div>
    <div class="step-item">
        <div class="step-number">3</div>
        <span>3. Pembayaran (Payment at Delivery)</span>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja Anda</h3>
        <p class="text-muted small mb-0">Centang buku yang ingin dicheckout sebelum melangkah ke proses pengisian alamat</p>
    </div>
    @if(!empty($sortedCartItems) && count($sortedCartItems) > 0)
        <form method="POST" action="{{ route('cart.clear') }}" class="form-confirm-delete" data-item-name="seluruh isi keranjang belanja">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-trash3 me-1"></i> Kosongkan Keranjang
            </button>
        </form>
    @endif
</div>

@if(empty($sortedCartItems) || count($sortedCartItems) === 0)
    <div class="card border-0 shadow-sm text-center py-5 rounded-4 mb-5 bg-white">
        <div class="card-body">
            <div class="mb-3 text-muted">
                <i class="bi bi-cart-x" style="font-size: 5rem;"></i>
            </div>
            <h4 class="fw-bold">Keranjang Belanja Masih Kosong</h4>
            <p class="text-muted small mb-4">Anda belum menambahkan buku ke dalam keranjang. Jelajahi katalog buku terbaik kami sekarang!</p>
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-book me-1"></i> Jelajahi Katalog Buku
            </a>
        </div>
    </div>
@else
    <div class="row g-4 mb-5">
        <!-- Kolom Tabel Daftar Item Keranjang -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 custom-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">
                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input" title="Pilih Semua Produk Tersedia" style="cursor: pointer; width: 1.2rem; height: 1.2rem;">
                                </th>
                                <th style="width: 40%;">Buku</th>
                                <th class="text-center" style="width: 15%;">Harga</th>
                                <th class="text-center" style="width: 18%;">Jumlah</th>
                                <th class="text-end" style="width: 15%;">Subtotal</th>
                                <th class="text-center" style="width: 7%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $firstOutOfStockRendered = false; @endphp
                            @foreach ($sortedCartItems as $bookId => $item)
                                @php 
                                    $subtotal = $item['price'] * $item['qty'];
                                    $stk = $stockData[$bookId] ?? ['stock' => 0, 'is_out' => false, 'is_exceeded' => false];
                                    $curStock = (int)$stk['stock'];
                                    $isOut = (bool)$stk['is_out'];
                                    $isExceeded = (bool)$stk['is_exceeded'];
                                @endphp

                                @if ($isOut && !$firstOutOfStockRendered && !empty($availableItems))
                                    @php $firstOutOfStockRendered = true; @endphp
                                    <tr>
                                        <td colspan="6" class="py-2 px-3 text-secondary small fw-semibold bg-light border-top border-bottom">
                                            <i class="bi bi-dash-circle me-1"></i> Produk Stok Habis (Tidak Dapat Dipilih)
                                        </td>
                                    </tr>
                                @endif

                                <tr class="{{ $isOut ? 'table-light text-muted' : ($isExceeded ? 'table-warning' : '') }}" id="cart-row-{{ $bookId }}" style="{{ $isOut ? 'background-color: #fbfbfb;' : '' }}">
                                    <td class="text-center">
                                        @if ($isOut)
                                            <input type="checkbox" 
                                                   class="form-check-input bg-light border-secondary-subtle" 
                                                   disabled 
                                                   title="Produk ini stoknya habis dan tidak dapat dicentang untuk checkout" 
                                                   style="cursor: not-allowed; width: 1.2rem; height: 1.2rem; opacity: 0.35;">
                                        @else
                                            <input type="checkbox" 
                                                   class="form-check-input item-checkbox" 
                                                   value="{{ $bookId }}" 
                                                   data-book-id="{{ $bookId }}" 
                                                   data-price="{{ $item['price'] }}" 
                                                   data-qty="{{ $item['qty'] }}" 
                                                   checked 
                                                   title="Centang untuk menyertakan produk ini ke checkout"
                                                   style="cursor: pointer; width: 1.2rem; height: 1.2rem;">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="position-relative flex-shrink-0" style="{{ $isOut ? 'opacity: 0.55; filter: grayscale(85%);' : '' }}">
                                                {!! bookCoverHtml($item['cover_image'] ?? null, $item['title'], 'thumb') !!}
                                                @if ($isOut)
                                                    <span class="position-absolute top-0 start-0 badge bg-secondary rounded-pill m-1 shadow-sm" style="font-size: 0.65rem;">Habis</span>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1">
                                                    <a href="{{ route('books.show', $bookId) }}" class="text-decoration-none {{ $isOut ? 'text-secondary' : 'text-dark' }}">
                                                        {{ $item['title'] }}
                                                    </a>
                                                </h6>
                                                @if ($isOut)
                                                    <div class="mt-1">
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-medium"><i class="bi bi-dash-circle me-1"></i>Stok Habis</span>
                                                    </div>
                                                    <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                                        Produk saat ini tidak tersedia
                                                    </div>
                                                @elseif ($isExceeded)
                                                    <div class="mt-1">
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>Sisa Stok: {{ $curStock }} unit</span>
                                                    </div>
                                                    <div class="text-danger small mt-1" style="font-size: 0.78rem;">
                                                        Pesanan melebihi stok yang tersedia
                                                    </div>
                                                @else
                                                    <small class="text-muted">Sisa Stok: {{ $curStock }} unit</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap {{ $isOut ? 'text-muted' : '' }}">
                                        {{ formatRupiah($item['price']) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($isOut)
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-light text-secondary border border-secondary-subtle px-3 py-1">
                                                    0 unit
                                                </span>
                                                <small class="text-muted mt-1" style="font-size: 0.72rem;">Habis</small>
                                            </div>
                                        @else
                                            <div class="d-inline-flex flex-column align-items-center justify-content-center">
                                                <div class="input-group input-group-sm" style="max-width: 120px;">
                                                    <button class="btn btn-outline-secondary btn-cart-minus" type="button" data-book-id="{{ $bookId }}" {{ $item['qty'] <= 1 ? 'disabled' : '' }}>
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                    <input type="number" 
                                                           name="qty" 
                                                           class="form-control text-center cart-qty-input {{ $isExceeded ? 'border-warning text-warning-emphasis fw-bold' : '' }}" 
                                                           id="cart-qty-{{ $bookId }}"
                                                           data-book-id="{{ $bookId }}" 
                                                           data-price="{{ $item['price'] }}"
                                                           value="{{ $item['qty'] }}" 
                                                           min="1" 
                                                           max="{{ $curStock }}">
                                                    <button class="btn btn-outline-secondary btn-cart-plus" type="button" data-book-id="{{ $bookId }}" title="Tambah jumlah">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                                @if ($isExceeded)
                                                    <small class="text-danger mt-1" style="font-size: 0.72rem;">Maks: {{ $curStock }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $isOut ? 'text-muted text-decoration-line-through' : 'text-primary' }} text-nowrap" id="subtotal-{{ $bookId }}">
                                        {{ formatRupiah($subtotal) }}
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('cart.remove') }}" class="form-confirm-delete" data-item-name="buku &quot;{{ addslashes($item['title'] ?? 'ini') }}&quot; dari keranjang">
                                            @csrf
                                            <input type="hidden" name="book_id" value="{{ $bookId }}">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm border-0 rounded-circle" title="Hapus Item">
                                                <i class="bi bi-trash3 text-secondary"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Tambah Buku Lainnya
                    </a>
                    <span class="small text-muted">
                        Total di keranjang: <strong id="totalCartItemsCount">{{ $totalItemsCount }}</strong> buku
                    </span>
                </div>
            </div>
        </div>

        <!-- Kolom Ringkasan Belanja -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3">Ringkasan Tagihan</h5>

                <form method="POST" action="{{ route('checkout.index') }}" id="checkoutForm">
                    @csrf
                    <!-- Container hidden input untuk item-item yang dicentang -->
                    <div id="selectedItemsContainer"></div>

                    <div class="d-flex justify-content-between text-muted mb-2 small">
                        <span>Subtotal Produk (<span id="summarySelectedCount" class="fw-bold text-dark">0</span> item terpilih)</span>
                        <span class="text-dark fw-semibold" id="summarySelectedSubtotal">Rp 0</span>
                    </div>

                    <div class="d-flex justify-content-between text-muted mb-3 small">
                        <span>Biaya Pengiriman</span>
                        <span class="text-success fw-semibold"><i class="bi bi-tag-fill me-1"></i>Gratis Promo</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold text-dark">Total Pembayaran</span>
                        <span class="fs-4 fw-bold text-primary" id="summarySelectedTotal">Rp 0</span>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 small mb-4 py-2 px-3">
                        <i class="bi bi-shield-check me-1"></i>
                        Sistem <strong>Payment at Delivery</strong>: Buku akan dikemas dan dikirim setelah verifikasi transfer pembayaran diselesaikan.
                    </div>

                    @auth
                        <button type="submit" id="btnCheckout" class="btn btn-primary rounded-pill py-2 fw-semibold w-100 shadow-sm">
                            Lanjut ke Pengiriman & Pembayaran <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        <div id="emptySelectionNotice" class="text-center mt-2 small text-muted fw-medium d-none">
                            <i class="bi bi-info-circle me-1"></i> Centang minimal 1 produk yang tersedia untuk checkout.
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill py-2 fw-semibold w-100 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk (Login) untuk Checkout
                        </a>
                        <div class="text-center mt-2 small text-muted">
                            Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-medium">Daftar sekarang</a>
                        </div>
                    @endauth
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    const summarySelectedCount = document.getElementById('summarySelectedCount');
    const summarySelectedSubtotal = document.getElementById('summarySelectedSubtotal');
    const summarySelectedTotal = document.getElementById('summarySelectedTotal');
    const btnCheckout = document.getElementById('btnCheckout');
    const emptySelectionNotice = document.getElementById('emptySelectionNotice');
    const selectedItemsContainer = document.getElementById('selectedItemsContainer');
    const checkoutForm = document.getElementById('checkoutForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function recalculateCart() {
        let totalQty = 0;
        let totalPrice = 0;
        let checkedItemsCount = 0;
        const totalAvailableCheckboxes = itemCheckboxes.length;

        if (selectedItemsContainer) {
            selectedItemsContainer.innerHTML = '';
        }

        itemCheckboxes.forEach(function (cb) {
            if (cb.checked) {
                checkedItemsCount++;
                const qty = parseInt(cb.getAttribute('data-qty')) || 1;
                const price = parseFloat(cb.getAttribute('data-price')) || 0;
                totalQty += qty;
                totalPrice += (qty * price);

                if (selectedItemsContainer) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_items[]';
                    hiddenInput.value = cb.value;
                    selectedItemsContainer.appendChild(hiddenInput);
                }
            }
        });

        if (summarySelectedCount) {
            summarySelectedCount.textContent = totalQty;
        }
        if (summarySelectedSubtotal) {
            summarySelectedSubtotal.textContent = formatRupiah(totalPrice);
        }
        if (summarySelectedTotal) {
            summarySelectedTotal.textContent = formatRupiah(totalPrice);
        }

        if (btnCheckout) {
            if (checkedItemsCount === 0 || totalPrice <= 0) {
                btnCheckout.disabled = true;
                btnCheckout.classList.add('disabled', 'opacity-75');
                btnCheckout.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Pilih Produk Terlebih Dahulu';
                if (emptySelectionNotice) emptySelectionNotice.classList.remove('d-none');
            } else {
                btnCheckout.disabled = false;
                btnCheckout.classList.remove('disabled', 'opacity-75');
                btnCheckout.innerHTML = 'Lanjut ke Pengiriman & Pembayaran <i class="bi bi-arrow-right ms-1"></i>';
                if (emptySelectionNotice) emptySelectionNotice.classList.add('d-none');
            }
        }

        if (selectAllCheckbox) {
            if (totalAvailableCheckboxes === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.disabled = true;
            } else {
                selectAllCheckbox.disabled = false;
                selectAllCheckbox.checked = (checkedItemsCount === totalAvailableCheckboxes);
            }
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checkedState = this.checked;
            itemCheckboxes.forEach(function (cb) {
                cb.checked = checkedState;
            });
            recalculateCart();
        });
    }

    itemCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
            recalculateCart();
        });
    });

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked:not(:disabled)').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 produk buku yang tersedia sebelum melanjutkan checkout.');
                return false;
            }
        });
    }

    // AJAX Handler untuk Tambah / Kurang Quantity tanpa Refresh Halaman
    function updateCartItemQty(bookId, newQty) {
        const input = document.getElementById('cart-qty-' + bookId);
        if (!input) return;

        const maxStock = parseInt(input.getAttribute('max')) || 999;
        const minStock = parseInt(input.getAttribute('min')) || 1;

        if (newQty < minStock) {
            newQty = minStock;
        }
        if (newQty > maxStock) {
            newQty = maxStock;
            const bookTitleEl = document.querySelector('#cart-row-' + bookId + ' h6 a');
            const bookTitle = bookTitleEl ? bookTitleEl.textContent.trim() : 'buku ini';
            const msg = 'Stok tidak mencukupi! Buku "' + bookTitle + '" maksimal hanya tersedia ' + maxStock + ' unit.';
            if (typeof showCartToast === 'function') {
                showCartToast(msg, false);
            } else {
                alert(msg);
            }
            return;
        }

        const btnMinus = input.parentElement.querySelector('.btn-cart-minus');
        const btnPlus = input.parentElement.querySelector('.btn-cart-plus');

        if (btnMinus) btnMinus.disabled = true;
        if (btnPlus) btnPlus.disabled = true;
        input.disabled = true;

        const formData = new FormData();
        formData.append('book_id', bookId);
        formData.append('qty', newQty);
        formData.append('format', 'json');

        fetch("{{ route('cart.update') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(function (res) {
            return res.json();
        })
        .then(function (data) {
            input.disabled = false;
            if (btnMinus) btnMinus.disabled = false;
            if (btnPlus) btnPlus.disabled = false;

            if (data.success) {
                input.value = data.qty;

                const checkbox = document.querySelector('.item-checkbox[data-book-id="' + bookId + '"]');
                if (checkbox) {
                    checkbox.setAttribute('data-qty', data.qty);
                }

                const subtotalEl = document.getElementById('subtotal-' + bookId);
                if (subtotalEl && data.itemSubtotalFormatted) {
                    subtotalEl.textContent = data.itemSubtotalFormatted;
                }

                if (btnMinus) {
                    btnMinus.disabled = (data.qty <= minStock);
                }

                const totalCartItemsCountEl = document.getElementById('totalCartItemsCount');
                if (totalCartItemsCountEl && data.cartCount !== undefined) {
                    totalCartItemsCountEl.textContent = data.cartCount;
                }

                if (typeof updateNavCartBadge === 'function' && data.cartCount !== undefined) {
                    updateNavCartBadge(data.cartCount);
                }

                recalculateCart();

                if (data.warning && typeof showCartToast === 'function') {
                    showCartToast(data.message, false);
                }
            } else {
                if (typeof showCartToast === 'function') {
                    showCartToast(data.message || 'Gagal memperbarui jumlah buku.', false);
                } else {
                    alert(data.message || 'Gagal memperbarui jumlah.');
                }
            }
        })
        .catch(function (err) {
            console.error('Cart Update Error:', err);
            input.disabled = false;
            if (btnMinus) btnMinus.disabled = false;
            if (btnPlus) btnPlus.disabled = false;
            if (typeof showCartToast === 'function') {
                showCartToast('Terjadi kendala koneksi dengan server.', false);
            }
        });
    }

    document.querySelectorAll('.btn-cart-minus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const bookId = this.getAttribute('data-book-id');
            const input = document.getElementById('cart-qty-' + bookId);
            if (!input) return;
            const currentVal = parseInt(input.value) || 1;
            if (currentVal > 1) {
                updateCartItemQty(bookId, currentVal - 1);
            }
        });
    });

    document.querySelectorAll('.btn-cart-plus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const bookId = this.getAttribute('data-book-id');
            const input = document.getElementById('cart-qty-' + bookId);
            if (!input) return;
            const currentVal = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 999;
            if (currentVal >= max) {
                const bookTitleEl = document.querySelector('#cart-row-' + bookId + ' h6 a');
                const bookTitle = bookTitleEl ? bookTitleEl.textContent.trim() : 'buku ini';
                const msg = 'Stok tidak mencukupi! Buku "' + bookTitle + '" maksimal hanya tersedia ' + max + ' unit.';
                if (typeof showCartToast === 'function') {
                    showCartToast(msg, false);
                } else {
                    alert(msg);
                }
                return;
            }
            updateCartItemQty(bookId, currentVal + 1);
        });
    });

    document.querySelectorAll('.cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const bookId = this.getAttribute('data-book-id');
            let val = parseInt(this.value) || 1;
            const max = parseInt(this.getAttribute('max')) || 999;
            const min = parseInt(this.getAttribute('min')) || 1;
            if (val > max) {
                val = max;
                this.value = max;
                const bookTitleEl = document.querySelector('#cart-row-' + bookId + ' h6 a');
                const bookTitle = bookTitleEl ? bookTitleEl.textContent.trim() : 'buku ini';
                const msg = 'Stok tidak mencukupi! Buku "' + bookTitle + '" maksimal hanya tersedia ' + max + ' unit.';
                if (typeof showCartToast === 'function') {
                    showCartToast(msg, false);
                } else {
                    alert(msg);
                }
            } else if (val < min) {
                val = min;
                this.value = min;
            }
            updateCartItemQty(bookId, val);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
            }
        });
    });

    recalculateCart();
});
</script>
@endsection
