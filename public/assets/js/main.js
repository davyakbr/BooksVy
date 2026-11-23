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

// Notifikasi real-time untuk input angka (telepon / NIK)
window.validateNumberInput = function (input, maxDigits, fieldLabel) {
    maxDigits = maxDigits || 15;
    fieldLabel = fieldLabel || 'Nomor';
    
    const originalVal = input.value;
    const cleanVal = originalVal.replace(/[^0-9]/g, '');

    // Jika user mengetik selain angka
    if (originalVal !== cleanVal) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'warning',
                title: fieldLabel + ' hanya boleh berisi angka!'
            });
        }
    } 
    // Jika mencapai batas digit maksimal
    else if (cleanVal.length >= maxDigits && originalVal.length >= maxDigits) {
        if (input._lastLen !== maxDigits && typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'info',
                title: fieldLabel + ' maksimal ' + maxDigits + ' digit angka!'
            });
        }
    }

    input._lastLen = cleanVal.length;
    input.value = cleanVal.slice(0, maxDigits);
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

    // 6. Instant Client-Side Category Filter (No Page Reload, Banner Stays on Current Slide)
    window.filterCatalogByCategory = function (categoryId, categoryName = '') {
        const bookItems = document.querySelectorAll('.book-item');
        let visibleCount = 0;

        // Update Category Chips Active State
        const chips = document.querySelectorAll('.category-chip');
        chips.forEach(function (chip) {
            const chipCat = chip.dataset.category || '';
            if (String(chipCat) === String(categoryId || '')) {
                chip.className = 'badge rounded-pill px-3 py-2 text-decoration-none category-chip bg-primary text-white';
                if (!categoryName && chip.dataset.categoryName) {
                    categoryName = chip.dataset.categoryName;
                }
            } else {
                chip.className = 'badge rounded-pill px-3 py-2 text-decoration-none category-chip bg-light text-secondary border';
            }
        });

        // Filter and animate book items
        bookItems.forEach(function (item) {
            const itemCat = item.dataset.category || '';
            if (!categoryId || String(itemCat) === String(categoryId)) {
                item.style.display = '';
                item.style.opacity = '0';
                item.style.transform = 'translateY(12px)';
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 20);
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Update Heading and Count
        const headingEl = document.getElementById('catalogHeading');
        const countEl = document.getElementById('catalogCount');
        if (headingEl) {
            headingEl.textContent = categoryId ? `Kategori: ${categoryName || 'Koleksi Terpilih'}` : 'Semua Koleksi Buku';
        }
        if (countEl) {
            countEl.textContent = `Menampilkan ${visibleCount} judul buku di BooksVy`;
        }

        // Toggle Empty State
        const noBooksEl = document.getElementById('noBooksFound');
        if (noBooksEl) {
            if (visibleCount === 0) {
                noBooksEl.classList.remove('d-none');
            } else {
                noBooksEl.classList.add('d-none');
            }
        }
    };

    // Helper to extract category parameter from href
    function getCategoryParam(href) {
        if (!href) return null;
        const match = href.match(/category=([0-9]+)/);
        return match ? match[1] : null;
    }

    // 7. Enhanced Banner Button Click & Category Interactivity (Pause Carousel, Direct Filter, Smooth Scroll)
    const bannerAndCategoryButtons = document.querySelectorAll(
        '.hero-banner-btn, .hero-carousel a, .hero-slide-img-box a, .category-chip'
    );

    bannerAndCategoryButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            // 1. Immediately pause carousel so it does NOT auto-slide away or reset to slide 1
            const carouselEl = document.getElementById('booksVyHeroCarousel');
            if (carouselEl && typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                const carouselInstance = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                carouselInstance.pause();
            }

            // 2. Ripple effect on button click
            if (btn.classList.contains('hero-banner-btn') || btn.classList.contains('btn')) {
                const rect = btn.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'hero-btn-ripple';
                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
                ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
                btn.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            }

            const href = btn.getAttribute('href') || '';
            const categoryFromParam = getCategoryParam(href);
            const categoryFromData = btn.dataset.category !== undefined ? btn.dataset.category : null;
            const targetCategory = categoryFromData !== null ? categoryFromData : categoryFromParam;

            // 3. Category Filter Action (from banner button or category chip)
            if (targetCategory !== null || href.includes('#katalog')) {
                e.preventDefault();

                // Apply category filter instantly
                const catId = targetCategory ? targetCategory : '';
                const catName = btn.dataset.categoryName || btn.textContent.trim();
                window.filterCatalogByCategory(catId, catId ? catName : '');

                // Smooth Scroll to #katalog without page reload
                const katalogEl = document.getElementById('katalog');
                if (katalogEl) {
                    const navbar = document.querySelector('.app-navbar');
                    const navHeight = navbar ? navbar.offsetHeight : 70;
                    const targetPosition = katalogEl.getBoundingClientRect().top + window.pageYOffset - (navHeight + 20);

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    // Pulse highlight catalog card
                    katalogEl.classList.remove('catalog-focus-highlight');
                    void katalogEl.offsetWidth; // force reflow
                    katalogEl.classList.add('catalog-focus-highlight');
                    setTimeout(function () {
                        katalogEl.classList.remove('catalog-focus-highlight');
                    }, 1800);
                }
                return;
            }

            // 4. Book Card Anchor Action (e.g. #book-2)
            if (href.includes('#book-')) {
                const targetId = href.split('#')[1];
                if (targetId) {
                    const targetEl = document.getElementById(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        const navbar = document.querySelector('.app-navbar');
                        const navHeight = navbar ? navbar.offsetHeight : 70;
                        const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - (navHeight + 25);

                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });

                        const bookCard = targetEl.querySelector('.book-card') || (targetEl.classList.contains('book-card') ? targetEl : null);
                        if (bookCard) {
                            document.querySelectorAll('.book-highlight-glow').forEach(el => el.classList.remove('book-highlight-glow'));
                            bookCard.classList.remove('book-highlight-glow');
                            void bookCard.offsetWidth; // force reflow
                            bookCard.classList.add('book-highlight-glow');
                            setTimeout(function () {
                                bookCard.classList.remove('book-highlight-glow');
                            }, 3200);
                        }
                    }
                }
            }
        });
    });
});
