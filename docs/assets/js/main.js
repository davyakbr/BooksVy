/**
 * BooksVy - JavaScript Helper & Interactive Enhancements
 */

// Global Helpers for Cart & Notifications
window.togglePasswordVisibility = function (inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
};

function showCartToast(message, isSuccess = true) {
    const toastEl = document.getElementById('cartToast');
    const toastMsg = document.getElementById('cartToastMsg');
    if (toastEl && toastMsg) {
        toastMsg.textContent = message;

        const iconEl = toastEl.querySelector('.toast-body i');
        if (iconEl) {
            if (isSuccess) {
                iconEl.className = 'bi bi-check-circle-fill text-warning fs-5';
            } else {
                iconEl.className = 'bi bi-exclamation-triangle-fill text-danger fs-5';
            }
        }

        const btnLihat = toastEl.querySelector('a.btn-warning');
        if (btnLihat) {
            if (window.location.pathname.includes('/cart')) {
                btnLihat.classList.add('d-none');
            } else {
                btnLihat.classList.remove('d-none');
            }
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toastInstance = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4000 });
            toastInstance.show();
        } else {
            alert(message);
        }
    }
}

function updateNavCartBadge(count) {
    const badges = document.querySelectorAll('.nav-cart-badge');
    badges.forEach(function (badge) {
        badge.textContent = ''; // Tidak menampilkan angka, murni titik indikator notifikasi
        if (count > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
        // Trigger smooth bouncy pop animation
        badge.classList.remove('badge-pop');
        void badge.offsetWidth; // Force reflow
        badge.classList.add('badge-pop');
        setTimeout(function () {
            badge.classList.remove('badge-pop');
        }, 600);
    });
}

// Quick Add to Cart Handler (Direct Click, No Form Submit, No Page Reload)
window.quickAddToCart = function (bookId, btn) {
    if (!bookId) return false;

    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menambahkan...';
    }

    const formData = new FormData();
    formData.append('action', 'quick_add');
    formData.append('book_id', bookId);
    formData.append('format', 'json');

    const baseUrl = window.BASE_URL || '';
    const postUrl = baseUrl ? (baseUrl + '/cart/quick-add') : '/cart/quick-add';
    const csrfToken = window.CSRF_TOKEN || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

    fetch(postUrl, {
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
        if (btn) {
            btn.disabled = false;
        }

        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.success) {
            if (data.cartCount !== undefined) {
                updateNavCartBadge(data.cartCount);
            }
            showCartToast(data.message || 'Buku berhasil ditambahkan ke keranjang!', true);

            // Efek visual lembut pada kartu buku
            const bookCard = btn ? btn.closest('.book-card') : null;
            if (bookCard) {
                bookCard.classList.add('added-highlight');
                setTimeout(function () {
                    bookCard.classList.remove('added-highlight');
                }, 800);
            }

            // Feedback visual pada tombol
            if (btn) {
                const originalClasses = btn.className;
                btn.classList.add('btn-added-success');
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Ditambahkan!';
                setTimeout(function () {
                    btn.className = originalClasses;
                    btn.innerHTML = originalHtml;
                }, 1300);
            }
        } else {
            if (btn) {
                btn.innerHTML = originalHtml;
            }
            showCartToast(data.message || 'Gagal menambahkan buku.', false);
        }
    })
    .catch(function (err) {
        console.error('Quick Add Error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        showCartToast('Terjadi kendala saat menambahkan buku ke keranjang.', false);
    });

    return false;
};

// Form Add to Cart Handler (Detail Buku)
window.handleAddToCart = function (event, form) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    const originalHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menambahkan...';
    }

    const formData = new FormData(form);
    formData.append('format', 'json');
    const actionUrl = form.getAttribute('action') || (window.BASE_URL ? window.BASE_URL + '/cart/add' : '/cart/add');
    const csrfToken = window.CSRF_TOKEN || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

    fetch(actionUrl, {
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
        if (submitBtn) {
            submitBtn.disabled = false;
        }

        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.success) {
            if (data.cartCount !== undefined) {
                updateNavCartBadge(data.cartCount);
            }
            showCartToast(data.message || 'Buku berhasil ditambahkan ke keranjang!', true);

            // Update kuota tersisa di halaman detail buku secara real-time
            const qtyInput = form.querySelector('input[name="quantity"]');
            const inCartNotice = document.getElementById('inCartNotice');

            if (data.remaining !== undefined && qtyInput) {
                qtyInput.setAttribute('max', data.remaining);
                qtyInput.value = Math.min(parseInt(qtyInput.value) || 1, Math.max(1, data.remaining));

                if (inCartNotice && data.currentInCart !== undefined) {
                    inCartNotice.innerHTML = '<i class="bi bi-bag-check-fill text-success me-1"></i> Sudah ada <strong class="text-dark">' + data.currentInCart + ' unit</strong> di keranjang Anda. Sisa yang dapat ditambahkan: <strong class="text-primary">' + data.remaining + ' unit</strong>.';
                    inCartNotice.classList.remove('d-none');
                }

                if (data.remaining <= 0) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1400);
                }
            }

            if (submitBtn) {
                const originalClasses = submitBtn.className;
                submitBtn.classList.add('btn-added-success');
                submitBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Ditambahkan!';
                setTimeout(function () {
                    submitBtn.className = originalClasses;
                    submitBtn.innerHTML = originalHtml;
                }, 1300);
            }
        } else {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
            showCartToast(data.message || 'Gagal menambahkan buku.', false);

            const qtyInput = form.querySelector('input[name="quantity"]');
            if (data.remaining !== undefined && qtyInput) {
                qtyInput.setAttribute('max', data.remaining);
                qtyInput.value = Math.max(1, data.remaining);
            }

            if (data.alreadyMax) {
                setTimeout(function () {
                    window.location.reload();
                }, 1800);
            }
        }
    })
    .catch(function (err) {
        console.error('Add to Cart Form Error:', err);
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
        showCartToast('Terjadi kendala koneksi dengan server.', false);
    });

    return false;
};

document.addEventListener('DOMContentLoaded', function () {
    // 1. Image Preview saat upload cover buku
    const coverInput = document.getElementById('cover_image_input');
    const coverPreview = document.getElementById('cover_image_preview');

    if (coverInput && coverPreview) {
        coverInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    coverPreview.src = event.target.result;
                    coverPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Quantity Counter di Cart / Detail Buku
    const qtyInputs = document.querySelectorAll('.qty-input');
    qtyInputs.forEach(function (input) {
        const btnMinus = input.parentElement.querySelector('.btn-qty-minus');
        const btnPlus = input.parentElement.querySelector('.btn-qty-plus');
        const max = parseInt(input.getAttribute('max')) || 999;
        const min = parseInt(input.getAttribute('min')) || 1;

        if (btnMinus) {
            btnMinus.addEventListener('click', function () {
                let val = parseInt(input.value) || 1;
                if (val > min) {
                    input.value = val - 1;
                    if (input.form && input.classList.contains('auto-submit')) {
                        input.form.submit();
                    }
                }
            });
        }

        if (btnPlus) {
            btnPlus.addEventListener('click', function () {
                let val = parseInt(input.value) || 1;
                if (val < max) {
                    input.value = val + 1;
                    if (input.form && input.classList.contains('auto-submit')) {
                        input.form.submit();
                    }
                } else {
                    if (typeof showCartToast === 'function') {
                        showCartToast('Stok tidak mencukupi! Jumlah pesanan telah mencapai batas stok maksimal (' + max + ' unit).', false);
                    } else {
                        alert('Jumlah pesanan telah mencapai batas stok maksimal yang tersedia (' + max + ').');
                    }
                }
            });
        }
    });

    // 3. Konfirmasi Pop-Up Modern (Logout & Hapus Data) Menggunakan SweetAlert2
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form) return;

        // A. Konfirmasi Logout Pop-Up
        const isLogout = form.id === 'logoutForm' || 
                         form.id === 'adminLogoutForm' || 
                         form.classList.contains('form-confirm-logout') ||
                         (form.action && form.action.includes('/logout'));

        if (isLogout && !form.dataset.confirmed) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Keluar (Logout)',
                    text: 'Apakah Anda yakin ingin keluar dari akun Anda?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0'
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin keluar dari akun Anda?')) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            }
            return;
        }

        // B. Konfirmasi Hapus Data Pop-Up
        const methodInput = form.querySelector('input[name="_method"]');
        const isDelete = (methodInput && methodInput.value.toUpperCase() === 'DELETE') ||
                         form.classList.contains('form-confirm-delete') ||
                         (form.action && (form.action.includes('destroy') || form.action.includes('cart/remove') || form.action.includes('cart/clear')));

        if (isDelete && !form.dataset.confirmed) {
            e.preventDefault();
            const itemName = form.dataset.itemName || 'data ini';
            const confirmMsg = `Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Data',
                    text: confirmMsg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0'
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            } else {
                if (confirm(confirmMsg)) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            }
            return;
        }
    });

    // 4. Auto dismiss flash alerts after 6 seconds
    const flashAlerts = document.querySelectorAll('.alert.alert-dismissible');
    flashAlerts.forEach(function (alert) {
        setTimeout(function () {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }
        }, 6000);
    });

    // 5. Intercept submit untuk form-add-cart
    const addCartForms = document.querySelectorAll('.form-add-cart');
    addCartForms.forEach(function (form) {
        if (!form.dataset.boundSubmit) {
            form.dataset.boundSubmit = 'true';
            form.addEventListener('submit', function (e) {
                handleAddToCart(e, form);
            });
        }
    });
});
