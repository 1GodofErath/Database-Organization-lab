document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profileForm');
    const messageDiv = document.getElementById('message');
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner');

    changeAvatarBtn.addEventListener('click', () => {
        const newUrl = prompt('Введіть URL нового зображення:');
        if (newUrl) {
            avatarInput.value = newUrl;
            avatarPreview.src = newUrl;
            avatarPreview.onerror = () => {
                avatarPreview.src = '/images/default-avatar.png';
                showMessage('Не вдалося завантажити зображення', 'error');
            };
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = {
            name: document.getElementById('name').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            avatar: avatarInput.value.trim(),
            bio: document.getElementById('bio').value.trim()
        };

        if (!formData.name) {
            showMessage('Ім\'я обов\'язкове', 'error');
            return;
        }

        submitBtn.disabled = true;
        spinner.classList.remove('hidden');

        try {
            const response = await fetch('/dah/update-profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Профіль успішно оновлено!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showMessage(data.message || 'Помилка оновлення', 'error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showMessage('Помилка з\'єднання з сервером', 'error');
        } finally {
            submitBtn.disabled = false;
            spinner.classList.add('hidden');
        }
    });

    deleteAccountBtn.addEventListener('click', async () => {
        const confirmed = confirm('Ви впевнені, що хочете видалити свій акаунт? Це дія незворотна!');
        if (!confirmed) return;

        const doubleConfirm = prompt('Введіть "DELETE" для підтвердження:');
        if (doubleConfirm !== 'DELETE') {
            showMessage('Видалення скасовано', 'error');
            return;
        }

        try {
            const response = await fetch('/dah/delete-account.php', { method: 'POST' });
            const data = await response.json();

            if (data.success) {
                alert('Акаунт успішно видалено');
                window.location.href = '/login.php';
            } else {
                showMessage(data.message || 'Помилка видалення', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showMessage('Помилка з\'єднання з сервером', 'error');
        }
    });

    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = `message ${type}`;
        messageDiv.classList.remove('hidden');
        setTimeout(() => messageDiv.classList.add('hidden'), 5000);
    }
});
