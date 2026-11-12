document.addEventListener('DOMContentLoaded', async () => {
    try {
        if (!await requireUser()) {
            return;
        }

        const user = await api('/profile');

        $('#profile-name').value = user.name;
        $('#profile-email').value = user.email;
        $('#profile-phone').value = user.phone || '';

        $('#profile-form').onsubmit = async event => {
            event.preventDefault();

            const button = event.target.querySelector('button');
            button.disabled = true;

            try {
                const body = Object.fromEntries(
                    new FormData(event.target)
                );

                await api('/profile', 'PATCH', body);
                await header();

                message('Đã cập nhật thông tin.');
            } catch (error) {
                message(error.message, true);
            } finally {
                button.disabled = false;
            }
        };

        $('#password-form').onsubmit = async event => {
            event.preventDefault();

            const button = event.target.querySelector('button');
            button.disabled = true;

            try {
                const body = Object.fromEntries(
                    new FormData(event.target)
                );

                if (body.newPassword !== body.confirm) {
                    throw new Error(
                        'Mật khẩu xác nhận không khớp.'
                    );
                }

                await api('/profile/password', 'POST', body);

                event.target.reset();
                message('Đã đổi mật khẩu.');
            } catch (error) {
                message(error.message, true);
            } finally {
                button.disabled = false;
            }
        };
    } catch (error) {
        message(error.message, true);
    }
});