const API_BASE = new URL(
    '../api/index.php',
    document.currentScript.src
).href;
let csrfToken = '';


const IDEMPOTENCY_KEYS = new Map();

function generateIdempotencyKey() {
    return 'idem_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function getIdempotencyKey(path) {
    if (path === '/checkout') {
        // Tạo key mới cho mỗi lần checkout
        const key = generateIdempotencyKey();
        return key;
    }
    return null;
}

const money = value =>
    Number(value).toLocaleString('vi-VN') + ' VNĐ';

const esc = value =>
    String(value ?? '').replace(/[&<>\"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[character]));

async function api(path, method = 'GET', body) {
    method = method.toUpperCase();

    const changesData = ![
        'GET',
        'HEAD',
        'OPTIONS',
    ].includes(method);

    if (changesData && !csrfToken) {
        const result = await api('/csrf');
        csrfToken = result.token;
    }

    const headers = {
        Accept: 'application/json',
    };

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    if (changesData) {
        headers['X-CSRF-Token'] = csrfToken;
    }

    const idempotencyKey = getIdempotencyKey(path);
    if (idempotencyKey) {
        headers['Idempotency-Key'] = idempotencyKey;
    }

    const response = await fetch(
        API_BASE + '?route=' + encodeURIComponent(path),
        {
            method,
            credentials: 'same-origin',
            headers,
            ...(body !== undefined
                ? { body: JSON.stringify(body) }
                : {}),
        }
    );

    let result;

    try {
        result = await response.json();
    } catch {
        throw new Error(
            'Máy chủ trả về dữ liệu không hợp lệ.'
        );
    }

    if (!response.ok || result?.ok !== true) {
        if (response.status === 403) {
            csrfToken = '';
        }
        // Xử lý lỗi duplicate (409) - có thể retry hoặc báo cho user
        if (response.status === 409 && result?.error?.message?.includes('duplicate')) {
            throw new Error('Yêu cầu đã được xử lý. Vui lòng không bấm nhiều lần.');
        }
        throw new Error(
            result?.error?.message
            || 'Không thể thực hiện yêu cầu.'
        );
    }

    // Reset CSRF token sau các endpoint quan trọng
    if (changesData && [
        '/auth/register',
        '/auth/login',
        '/auth/logout',
        '/profile/password',
        '/holds',
        '/checkout',
    ].includes(path)) {
        csrfToken = '';
    }

    return result.data;
}
