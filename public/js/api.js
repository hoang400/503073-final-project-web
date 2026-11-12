const API_BASE = 'api/index.php';
let csrfToken = '';

const money = value =>
    Number(value).toLocaleString('vi-VN') + ' VNĐ';

const esc = value =>
    String(value ?? '').replace(/[&<>"']/g, character => ({
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

        throw new Error(
            result?.error?.message
            || 'Không thể thực hiện yêu cầu.'
        );
    }

    if (changesData && [
        '/auth/register',
        '/auth/login',
        '/auth/logout',
        '/profile/password',
    ].includes(path)) {
        csrfToken = '';
    }

    return result.data;
}