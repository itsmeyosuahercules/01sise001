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
    const submit = attendanceForm.querySelector('[data-attendance-submit]');
    const photo = attendanceForm.querySelector('[data-attendance-photo]');
    const preview = attendanceForm.querySelector('[data-attendance-preview]');

    const applyPosition = (position) => {
        latInput.value = position.coords.latitude.toFixed(7);
        lngInput.value = position.coords.longitude.toFixed(7);
        accuracyInput.value = Math.round(position.coords.accuracy || 0);
        geoStatus.textContent = `Lokasi hidup siap (±${accuracyInput.value} m).`;
        submit.disabled = false;
    };

    const failPosition = () => {
        geoStatus.textContent = 'Lokasi wajib hidup. Izinkan akses lokasi, lalu muat ulang halaman.';
        submit.disabled = true;
    };

    if (! navigator.geolocation) {
        failPosition();
    } else {
        navigator.geolocation.getCurrentPosition(applyPosition, failPosition, {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 0,
        });
        navigator.geolocation.watchPosition(applyPosition, () => {}, {
            enableHighAccuracy: true,
            maximumAge: 0,
        });
    }

    photo?.addEventListener('change', () => {
        const file = photo.files?.[0];

        if (! file || ! preview) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    });

    attendanceForm.addEventListener('submit', (event) => {
        if (! latInput.value || ! lngInput.value) {
            event.preventDefault();
            failPosition();
            toast('Izinkan lokasi hidup sebelum mengirim hadir.', 'error');
        }
    });
}
