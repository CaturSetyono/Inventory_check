   document.addEventListener('DOMContentLoaded', function() {
            // --- Elemen-elemen Modal Utama ---
            const userModal = document.getElementById('userModal');
            const addUserBtn = document.getElementById('addUserBtn');
            const modalTitle = document.getElementById('modalTitle');
            const userForm = document.getElementById('userForm');
            const formAction = document.getElementById('formAction');
            const userId = document.getElementById('userId');
            const passwordInput = document.getElementById('password');
            const passwordHint = document.getElementById('passwordHint');

            // --- Elemen-elemen Modal Hapus ---
            const deleteModal = document.getElementById('deleteModal');
            const deleteUserIdInput = document.getElementById('deleteUserId');
            const deleteUserNameSpan = document.getElementById('deleteUserName');
            
            // Tombol Batal untuk semua modal
            const cancelButtons = document.querySelectorAll('.cancel-modal-btn');

            const openModalWithTransition = (modal) => {
                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    modal.classList.remove('modal-enter');
                    modal.classList.add('modal-enter-active');
                });
            };

            const closeModalWithTransition = (modal) => {
                modal.classList.remove('modal-enter-active');
                modal.classList.add('modal-leave-active');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('modal-leave-active');
                }, 200);
            };

            addUserBtn.addEventListener('click', () => {
                userForm.reset();
                modalTitle.textContent = 'Tambah Pengguna Baru';
                formAction.value = 'create';
                passwordInput.setAttribute('required', 'true');
                passwordHint.classList.add('hidden');
                openModalWithTransition(userModal);
            });

            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    userForm.reset();
                    modalTitle.textContent = 'Edit Pengguna';
                    formAction.value = 'update';
                    passwordInput.removeAttribute('required');
                    passwordHint.classList.remove('hidden');

                    userId.value = this.dataset.id;
                    document.getElementById('nama_lengkap').value = this.dataset.nama_lengkap;
                    document.getElementById('username').value = this.dataset.username;
                    document.getElementById('role').value = this.dataset.role;

                    openModalWithTransition(userModal);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    deleteUserIdInput.value = this.dataset.id;
                    deleteUserNameSpan.textContent = this.dataset.nama_lengkap;
                    openModalWithTransition(deleteModal);
                });
            });

            cancelButtons.forEach(button => {
                button.addEventListener('click', () => {
                    closeModalWithTransition(userModal);
                    closeModalWithTransition(deleteModal);
                });
            });
            
            userModal.addEventListener('click', (e) => {
                if (e.target === userModal) closeModalWithTransition(userModal);
            });
            deleteModal.addEventListener('click', (e) => {
                if (e.target === deleteModal) closeModalWithTransition(deleteModal);
            });
        });