const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const toast = (message, tone = 'info') => {
    const host = document.querySelector('[data-toast-host]');

    if (! host || ! message) {
        return;
    }

    const item = document.createElement('p');
    item.className = tone === 'error'
        ? 'rounded-2xl border border-accent-hot/20 bg-accent-hot/5 px-4 py-2.5 text-sm text-accent-hot'
        : 'rounded-2xl border border-navy/15 bg-navy/5 px-4 py-2.5 text-sm text-navy';
    item.textContent = message;
    host.replaceChildren(item);

    window.setTimeout(() => {
        if (item.parentElement === host) {
            item.remove();
        }
    }, 3200);
};

const readJson = async (response) => {
    const contentType = response.headers.get('content-type') ?? '';

    if (! contentType.includes('application/json')) {
        return {};
    }

    return response.json();
};

const submitRemote = async (form, submitter) => {
    const body = new FormData(form);

    if (submitter?.name) {
        body.set(submitter.name, submitter.value);
    }

    const response = await fetch(form.action, {
        method: (form.getAttribute('method') || 'POST').toUpperCase(),
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body,
        credentials: 'same-origin',
    });

    const payload = await readJson(response);

    if (response.status === 422) {
        const errors = payload.errors ? Object.values(payload.errors).flat() : ['Data tidak valid.'];
        throw new Error(errors[0]);
    }

    if (response.status === 403) {
        throw new Error(payload.message || 'Aksi ini tidak diizinkan.');
    }

    if (! response.ok) {
        throw new Error(payload.message || 'Aksi gagal.');
    }

    return payload;
};

const applyLike = (form, payload) => {
    const button = form.querySelector('[data-like-button]');

    if (button) {
        button.textContent = `${payload.liked ? 'Disukai' : 'Suka'} · ${payload.likes_count}`;
        button.classList.toggle('bg-gold', payload.liked);
        button.classList.toggle('text-navy', payload.liked);
        button.classList.toggle('bg-card', ! payload.liked);
        button.classList.toggle('text-ink', ! payload.liked);
    }

    const list = document.querySelector('[data-like-list]');

    if (list && typeof payload.likes_html === 'string') {
        list.outerHTML = payload.likes_html;
    }
};

const applyComment = (form, payload) => {
    const list = document.querySelector('[data-comment-list]');
    const empty = document.querySelector('[data-comment-empty]');

    if (empty) {
        empty.remove();
    }

    if (list && payload.html) {
        list.insertAdjacentHTML('beforeend', payload.html);
    }

    form.reset();

    const count = document.querySelector('[data-comments-count]');

    if (count && typeof payload.comments_count === 'number') {
        count.textContent = String(payload.comments_count);
    }
};

const applyCommentDelete = (form, payload) => {
    form.closest('[data-comment]')?.remove();

    const count = document.querySelector('[data-comments-count]');

    if (count && typeof payload.comments_count === 'number') {
        count.textContent = String(payload.comments_count);
    }

    const list = document.querySelector('[data-comment-list]');

    if (list && list.querySelectorAll('[data-comment]').length === 0) {
        list.insertAdjacentHTML('beforeend', '<p data-comment-empty class="text-sm text-ink/55">Belum ada komentar.</p>');
    }
};

const applyReview = (form, payload) => {
    const cell = form.closest('[data-status-cell]');

    if (! cell) {
        return;
    }

    const label = cell.querySelector('[data-status-label]');

    if (label) {
        label.textContent = payload.label;
    }

    cell.querySelector('[data-review-actions]')?.remove();
    cell.closest('[data-filter-row]')?.setAttribute('data-status', payload.status);
};

const applyRole = (form, payload) => {
    const select = form.querySelector('select[name="role"]');

    if (select) {
        select.dataset.previous = payload.role;
        select.closest('[data-filter-row]')?.setAttribute('data-role', payload.role);
    }
};

const applyAttendanceMark = (form, payload) => {
    const row = form.closest('[data-attendance-row]');
    const select = form.querySelector('select[name="present"]');

    if (select) {
        select.dataset.previous = payload.present ? '1' : '0';
    }

    if (! row) {
        return;
    }

    const statusCell = row.querySelector('[data-attendance-status-cell]');
    const label = row.querySelector('[data-attendance-status-label]');
    const timeCell = row.querySelector('[data-attendance-time-cell]');

    if (label) {
        label.textContent = payload.present ? 'Hadir' : 'Tidak hadir';
        label.classList.toggle('text-navy', payload.present);
        label.classList.toggle('text-accent-hot', ! payload.present);
    }

    statusCell?.querySelector('[data-attendance-manual-tag]')?.remove();

    if (payload.present && payload.is_manual && statusCell) {
        const tag = document.createElement('span');
        tag.dataset.attendanceManualTag = '';
        tag.className = 'ml-1 rounded-full bg-navy/10 px-2 py-0.5 text-[10px] text-navy';
        tag.textContent = 'diisi ketua';
        statusCell.appendChild(tag);
    }

    if (timeCell) {
        timeCell.textContent = payload.time || '—';
    }

    const note = row.querySelector('[name="note"]');

    if (note) {
        note.disabled = ! payload.present;
        note.placeholder = payload.present ? 'Catatan untuk ketua' : 'Tandai hadir dulu';

        if (typeof payload.note === 'string') {
            note.value = payload.note;
        }
    }

    if (! payload.present) {
        const photoCell = row.querySelector('[data-attendance-photo-cell]');
        const locationCell = row.querySelector('[data-attendance-location-cell]');

        if (photoCell) {
            photoCell.innerHTML = '<span class="text-ink/40">—</span>';
        }

        if (locationCell) {
            locationCell.textContent = '—';
        }
    }
};

const applyAttendanceNote = (form, payload) => {
    const input = form.querySelector('[name="note"]');

    if (input && typeof payload.note === 'string') {
        input.value = payload.note;
    }
};

const applyPackageMeta = (payload) => {
    const packageText = document.querySelector('[data-package-text]');

    if (packageText && typeof payload.package_text === 'string') {
        packageText.value = payload.package_text;
    }

    const count = document.querySelector('[data-selected-count]');

    if (count && typeof payload.selected_count === 'number') {
        count.textContent = String(payload.selected_count);
    }
};

const applyQuestionStatus = (form, payload) => {
    const cell = form.closest('[data-status-cell]');

    if (cell && payload.html) {
        cell.innerHTML = payload.html;
    }

    cell?.closest('[data-filter-row]')?.setAttribute('data-status', payload.status);
    applyPackageMeta(payload);
};

const applyQuestionPackage = (form, payload) => {
    applyPackageMeta(payload);

    document.querySelectorAll('[data-filter-row][data-status="dipilih"]').forEach((row) => {
        row.setAttribute('data-status', 'terkirim');

        const label = row.querySelector('[data-status-label]');

        if (label) {
            label.textContent = 'Sudah dikirim';
        }

        row.querySelector('[data-curate-actions]')?.remove();
    });
};

const remotes = {
    like: applyLike,
    comment: applyComment,
    'comment-delete': applyCommentDelete,
    review: applyReview,
    role: applyRole,
    'attendance-mark': applyAttendanceMark,
    'attendance-note': applyAttendanceNote,
    'question-status': applyQuestionStatus,
    'question-package': applyQuestionPackage,
};

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-remote]');

    if (! form) {
        return;
    }

    event.preventDefault();

    const action = form.dataset.remote;
    const submitter = event.submitter;

    try {
        const payload = await submitRemote(form, submitter);
        remotes[action]?.(form, payload);
        toast(payload.message);
    } catch (error) {
        if (action === 'role') {
            const select = form.querySelector('select[name="role"]');

            if (select?.dataset.previous) {
                select.value = select.dataset.previous;
            }
        }

        if (action === 'attendance-mark') {
            const select = form.querySelector('select[name="present"]');

            if (select?.dataset.previous) {
                select.value = select.dataset.previous;
            }
        }

        toast(error instanceof Error ? error.message : 'Aksi gagal.', 'error');
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy]');

    if (! button) {
        return;
    }

    const target = document.querySelector(button.getAttribute('data-copy'));

    if (! target) {
        return;
    }

    await navigator.clipboard.writeText(target.value);
    const original = button.textContent;
    button.textContent = 'Tersalin';
    window.setTimeout(() => {
        button.textContent = original;
    }, 1600);
});

const normalize = (value) => value.toString().trim().toLowerCase();

const applyFilters = (root) => {
    const query = normalize(root.querySelector('[data-filter-q]')?.value ?? '');
    const filters = {};

    root.querySelectorAll('[data-filter-key]').forEach((input) => {
        filters[input.dataset.filterKey] = normalize(input.value);
    });

    let visible = 0;

    root.querySelectorAll('[data-filter-row]').forEach((row) => {
        const haystack = normalize(row.dataset.search ?? '');
        const matchesQuery = query === '' || haystack.includes(query);
        const matchesFilters = Object.entries(filters).every(([key, value]) => {
            if (value === '') {
                return true;
            }

            return normalize(row.dataset[key] ?? '') === value;
        });
        const show = matchesQuery && matchesFilters;

        row.classList.toggle('hidden', ! show);

        if (show) {
            visible += 1;
        }
    });

    const empty = root.querySelector('[data-filter-empty]');

    if (empty) {
        empty.classList.toggle('hidden', visible > 0);
    }
};

document.querySelectorAll('[data-attendance-select-all]').forEach((toggle) => {
    toggle.addEventListener('change', (event) => {
        const checked = event.target instanceof HTMLInputElement && event.target.checked;

        document.querySelectorAll('[data-attendance-pick]').forEach((box) => {
            if (box instanceof HTMLInputElement) {
                box.checked = checked;
            }
        });

        document.querySelectorAll('[data-attendance-select-all]').forEach((other) => {
            if (other instanceof HTMLInputElement) {
                other.checked = checked;
            }
        });
    });
});

document.querySelectorAll('[data-filter-root]').forEach((root) => {
    root.addEventListener('input', () => applyFilters(root));
    root.addEventListener('change', () => applyFilters(root));
});

const attendanceForm = document.querySelector('[data-attendance-form]');

if (attendanceForm) {
    const latInput = attendanceForm.querySelector('[data-attendance-lat]');
    const lngInput = attendanceForm.querySelector('[data-attendance-lng]');
    const accuracyInput = attendanceForm.querySelector('[data-attendance-accuracy]');
    const geoStatus = attendanceForm.querySelector('[data-attendance-geo]');
    const camStatus = attendanceForm.querySelector('[data-attendance-cam]');
    const submit = attendanceForm.querySelector('[data-attendance-submit]');
    const photo = attendanceForm.querySelector('[data-attendance-photo]');
    const fallback = attendanceForm.querySelector('[data-attendance-fallback]');
    const nativeBtn = attendanceForm.querySelector('[data-attendance-native]');
    const preview = attendanceForm.querySelector('[data-attendance-preview]');
    const video = attendanceForm.querySelector('[data-attendance-video]');
    const canvas = attendanceForm.querySelector('[data-attendance-canvas]');
    const snap = attendanceForm.querySelector('[data-attendance-snap]');
    const resnap = attendanceForm.querySelector('[data-attendance-resnap]');
    const gate = attendanceForm.querySelector('[data-attendance-gate]');
    const live = attendanceForm.querySelector('[data-attendance-live]');
    let capturedBlob = null;
    let submitting = false;
    let activeStream = null;
    let watchId = null;
    let bestAccuracy = Number.POSITIVE_INFINITY;

    const stopCamera = () => {
        activeStream?.getTracks().forEach((track) => track.stop());
        activeStream = null;

        if (video) {
            video.srcObject = null;
        }
    };

    const showNativeFallback = (message) => {
        if (message) {
            camStatus.textContent = message;
        }
    };

    const applyPosition = (position) => {
        const meters = Number.isFinite(position.coords.accuracy) ? Math.round(position.coords.accuracy) : 0;

        if (latInput.value && meters > 0 && bestAccuracy < Number.POSITIVE_INFINITY && meters > bestAccuracy) {
            return;
        }

        if (meters > 0) {
            bestAccuracy = meters;
        }

        latInput.value = Number(position.coords.latitude).toFixed(7);
        lngInput.value = Number(position.coords.longitude).toFixed(7);
        accuracyInput.value = meters > 0 ? String(Math.min(meters, 1000000)) : '';
        geoStatus.textContent = meters > 0
            ? `Lokasi siap: ${latInput.value}, ${lngInput.value} (±${meters} m)`
            : `Lokasi siap: ${latInput.value}, ${lngInput.value}`;
    };

    const locationError = (error) => {
        if (latInput.value) {
            return;
        }

        if (error?.code === 1) {
            geoStatus.textContent = 'Lokasi ditolak. Izinkan lokasi di pengaturan browser, lalu ketuk Ambil lokasi.';
            return;
        }

        if (error?.code === 3) {
            geoStatus.textContent = 'Lokasi masih dicari. Tunggu sebentar, atau ketuk Ambil lokasi lagi.';
            return;
        }

        geoStatus.textContent = 'Lokasi belum masuk. Nyalakan lokasi di HP, lalu ketuk Ambil lokasi.';
    };

    const requestLocation = () => {
        if (! navigator.geolocation) {
            geoStatus.textContent = 'Browser ini tidak bisa membaca lokasi. Buka lewat browser HP.';
            return;
        }

        if (! latInput.value) {
            geoStatus.textContent = 'Mengambil lokasi… Pilih Izinkan pada popup browser.';
        }

        navigator.geolocation.getCurrentPosition(applyPosition, locationError, {
            enableHighAccuracy: false,
            timeout: 8000,
            maximumAge: 60000,
        });

        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
        }

        watchId = navigator.geolocation.watchPosition(applyPosition, () => {}, {
            enableHighAccuracy: true,
            maximumAge: 0,
        });
    };

    const attachPhoto = (blob) => {
        capturedBlob = blob;

        if (preview?.src) {
            URL.revokeObjectURL(preview.src);
        }

        preview.src = URL.createObjectURL(blob);
        video.classList.add('hidden');
        preview.classList.remove('hidden');
        snap?.classList.add('hidden');
        resnap?.classList.remove('hidden');
        camStatus.textContent = 'Foto tersimpan. Kirim, atau jepret ulang.';
        stopCamera();
    };

    const canvasToJpeg = (source, mirror, onReady) => {
        const maxWidth = 720;
        const width = source.videoWidth || source.naturalWidth || source.width;
        const height = source.videoHeight || source.naturalHeight || source.height;

        if (! width || ! height) {
            toast('Foto belum siap. Coba lagi.', 'error');
            return;
        }

        const scale = Math.min(1, maxWidth / width);
        canvas.width = Math.round(width * scale);
        canvas.height = Math.round(height * scale);
        const context = canvas.getContext('2d');

        if (! context) {
            toast('Jepret gagal. Coba browser lain.', 'error');
            return;
        }

        context.save();

        if (mirror) {
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
        }

        context.drawImage(source, 0, 0, canvas.width, canvas.height);
        context.restore();

        const finish = (blob) => {
            if (! blob) {
                toast('Jepret gagal. Coba lagi.', 'error');
                return;
            }

            onReady(blob);
        };

        if (typeof canvas.toBlob === 'function') {
            canvas.toBlob(finish, 'image/jpeg', 0.82);
            return;
        }

        const dataUrl = canvas.toDataURL('image/jpeg', 0.82);
        const bytes = atob(dataUrl.split(',')[1] ?? '');
        const buffer = new Uint8Array(bytes.length);

        for (let i = 0; i < bytes.length; i += 1) {
            buffer[i] = bytes.charCodeAt(i);
        }

        finish(new Blob([buffer], { type: 'image/jpeg' }));
    };

    const showLive = () => {
        capturedBlob = null;
        video.classList.remove('hidden');
        preview.classList.add('hidden');
        snap?.classList.remove('hidden');
        resnap?.classList.add('hidden');

        if (photo) {
            photo.value = '';
        }
    };

    const startCamera = async () => {
        if (! navigator.mediaDevices?.getUserMedia) {
            showNativeFallback('Kamera langsung tidak tersedia. Pakai tombol "Ambil lewat kamera HP".');
            return;
        }

        if (activeStream) {
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'user' }, width: { ideal: 480 }, height: { ideal: 640 } },
                audio: false,
            });
            activeStream = stream;
            video.srcObject = stream;
            video.muted = true;
            await video.play().catch(() => {});
            camStatus.textContent = 'Kamera siap. Arahkan wajah, lalu tekan Jepret. Kalau layar hitam, pakai Ambil lewat kamera HP.';
        } catch {
            showNativeFallback('Kamera ditolak atau diblokir. Izinkan kamera, atau pakai "Ambil lewat kamera HP". Jangan buka dari dalam WhatsApp.');
        }
    };

    attendanceForm.querySelector('[data-attendance-allow]')?.addEventListener('click', () => {
        gate?.classList.add('hidden');
        live?.classList.remove('hidden');
        requestLocation();
        startCamera();
    });

    snap?.addEventListener('click', () => {
        if (! video.videoWidth) {
            toast('Tunggu kamera menyala dulu, atau pakai "Ambil lewat kamera HP".', 'error');
            showNativeFallback();
            return;
        }

        canvasToJpeg(video, true, attachPhoto);
    });

    resnap?.addEventListener('click', () => {
        showLive();
        startCamera();
        camStatus.textContent = 'Menghidupkan kamera…';
    });

    nativeBtn?.addEventListener('click', () => fallback?.click());

    fallback?.addEventListener('change', () => {
        const file = fallback.files?.[0];

        if (! file) {
            return;
        }

        const image = new Image();
        image.onload = () => canvasToJpeg(image, false, attachPhoto);
        image.onerror = () => toast('Foto tidak bisa dibaca. Coba jepret ulang.', 'error');
        image.src = URL.createObjectURL(file);
    });

    attendanceForm.querySelector('[data-attendance-geo-retry]')?.addEventListener('click', requestLocation);

    window.addEventListener('pagehide', () => {
        stopCamera();

        if (watchId !== null) {
            navigator.geolocation?.clearWatch(watchId);
        }
    });

    attendanceForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (submitting) {
            return;
        }

        if (live?.classList.contains('hidden')) {
            toast('Ketuk Izinkan kamera & lokasi dulu, lalu pilih Izinkan pada popup browser.', 'error');
            return;
        }

        if (! latInput.value || ! lngInput.value) {
            requestLocation();
            toast('Lokasi belum masuk. Pilih Izinkan, atau ketuk Ambil lokasi.', 'error');
            return;
        }

        if (! capturedBlob && ! photo.files?.length) {
            toast('Jepret foto muka dulu.', 'error');
            return;
        }

        submitting = true;
        const original = submit.textContent;
        submit.textContent = 'Mengirim…';
        submit.setAttribute('aria-busy', 'true');

        const body = new FormData(attendanceForm);
        body.set('photo', capturedBlob || photo.files[0], 'wajah.jpg');

        try {
            const response = await fetch(attendanceForm.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
                credentials: 'same-origin',
            });

            const payload = await readJson(response);

            if (response.status === 422) {
                const errors = payload.errors ? Object.values(payload.errors).flat() : ['Data tidak valid.'];
                throw new Error(errors[0]);
            }

            if (! response.ok) {
                throw new Error(payload.message || 'Kirim hadir gagal. Coba lagi.');
            }

            window.location.assign(payload.redirect || attendanceForm.getAttribute('action') || window.location.href);
        } catch (error) {
            submitting = false;
            submit.textContent = original;
            submit.removeAttribute('aria-busy');
            toast(error instanceof Error ? error.message : 'Kirim hadir gagal.', 'error');
        }
    });
}
