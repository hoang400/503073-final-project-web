document.addEventListener('DOMContentLoaded', () => {
    const form = $('#auth-form');

    if (!form) {
        return;
    }

    const register = form.dataset.mode === 'register';
    const params = new URLSearchParams(location.search);

    const destination = params.has('next')
        ? returnPage()
        : 'profile.php';

    $('#switch-auth').href =
        (register ? 'login.php' : 'register.php')
        + '?next=' + encodeURIComponent(destination);

    if (!register && params.get('registered') === '1') {
        message('Đăng ký thành công. Vui lòng đăng nhập.');
    }

    form.onsubmit = async event => {
        event.preventDefault();

        const button = form.querySelector('button');
        button.disabled = true;

        try {
            const body = Object.fromEntries(
                new FormData(form)
            );

            if (
                register
                && body.password !== body.confirm
            ) {
                throw new Error(
                    'Mật khẩu xác nhận không khớp.'
                );
            }

            await api(
                register
                    ? '/auth/register'
                    : '/auth/login',
                'POST',
                body
            );

            if (register) {
                const loginParams = new URLSearchParams({
                    registered: '1',
                    next: destination,
                });

                location.href =
                    'login.php?' + loginParams.toString();

                return;
            }

            location.href = destination;
        } catch (error) {
            message(error.message, true);
        } finally {
            button.disabled = false;
        }
    };
});